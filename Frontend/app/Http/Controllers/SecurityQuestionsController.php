<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SecurityQuestionsController extends Controller
{
    private function matchesStoredSecret(string $plain, ?string $stored): bool
    {
        $value = (string) $stored;
        if ($value === '') {
            return false;
        }

        try {
            return Hash::check($plain, $value);
        } catch (\RuntimeException $e) {
            // Compatibilidad con datos legados no bcrypt.
            if (hash_equals($value, $plain)) {
                return true;
            }

            $lower = strtolower($value);
            if (preg_match('/^[a-f0-9]{32}$/', $lower)) {
                return hash_equals($lower, md5($plain));
            }

            if (preg_match('/^[a-f0-9]{40}$/', $lower)) {
                return hash_equals($lower, sha1($plain));
            }

            if (preg_match('/^[a-f0-9]{64}$/', $lower)) {
                return hash_equals($lower, hash('sha256', $plain));
            }

            return false;
        }
    }

    private function getSecurityAnswerConstraints(): array
    {
        $passwordConstraints = $this->getPasswordConstraints();
        $columnMax = $this->getRecoveryAnswerMaxLength();

        $min = max(3, (int) ($passwordConstraints['min_contrasena'] ?? 5));
        $maxFromParams = max($min, (int) ($passwordConstraints['max_contrasena'] ?? 10));
        $max = min($maxFromParams, $columnMax);

        return [
            'min_respuesta' => $min,
            'max_respuesta' => $max,
        ];
    }

    private function getConfiguredQuestionLimit(int $availableQuestions = 0): int
    {
        try {
            $value = (int) DB::table('tbl_seg_parametro')
                ->where('nombre_parametro', 'ADMIN_PREGUNTAS')
                ->value('valor');

            $limit = $value > 0 ? $value : 3;
            $maxAvailable = $availableQuestions > 0 ? $availableQuestions : max(1, (int) $this->getAllQuestions()->count());

            return max(1, min($limit, $maxAvailable));
        } catch (\Throwable $e) {
            $maxAvailable = $availableQuestions > 0 ? $availableQuestions : max(1, (int) $this->getAllQuestions()->count());
            return max(1, min(3, $maxAvailable));
        }
    }

    private function getUsername(): ?string
    {
        // Prefer stored session value.
        if (session()->has('usuario')) {
            return session('usuario');
        }

        // Try to parse from JWT token
        $token = session('jwt_token');
        if (! $token) {
            return null;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $payloadBase64 = strtr($parts[1], '-_', '+/');
        $padding = 4 - (strlen($payloadBase64) % 4);
        if ($padding < 4) {
            $payloadBase64 .= str_repeat('=', $padding);
        }

        $payload = json_decode(base64_decode($payloadBase64), true);
        if (!is_array($payload)) {
            return null;
        }

        return $payload['usuario'] ?? null;
    }

    private function hasSecurityQuestions(string $usuario): bool
    {
        try {
            return DB::table('tbl_seg_pregunta_usuario')
                ->where('usuario', $usuario)
                ->count() >= 2;
        } catch (\Throwable $e) {
            // Si no existe la tabla o hay un error, asumimos que no hay configurado.
            return false;
        }
    }

    private function getUserQuestions(string $usuario)
    {
        try {
            return DB::table('tbl_seg_pregunta_usuario as up')
                ->join('tbl_seg_preguntas as p', 'p.id_pregunta', '=', 'up.pregunta_id')
                ->where('up.usuario', $usuario)
                ->select('up.pregunta_id', 'p.pregunta', 'up.respuesta')
                ->get();
        } catch (\Throwable $e) {
            return collect([]);
        }
    }

    private function getAllQuestions()
    {
        try {
            return DB::table('tbl_seg_preguntas')
                ->select('id_pregunta', 'pregunta')
                ->orderBy('id_pregunta')
                ->get();
        } catch (\Throwable $e) {
            return collect([]);
        }
    }

    private function getRecoveryAnswerMaxLength(): int
    {
        try {
            $row = DB::selectOne(
                "SELECT CHARACTER_MAXIMUM_LENGTH AS max_len
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'tbl_seg_pregunta_usuario'
                   AND COLUMN_NAME = 'respuesta'
                 LIMIT 1"
            );

            $max = (int) ($row->max_len ?? 0);
            return $max > 0 ? $max : 255;
        } catch (\Throwable $e) {
            return 255;
        }
    }

    private function normalizeRecoveryAnswer(?string $value): string
    {
        return preg_replace('/\s+/u', ' ', trim((string) $value));
    }

    private function clearRecoverySession(): void
    {
        session()->forget([
            'recovery_username',
            'recovery_questions_verified',
            'recovery_answered_questions',
            'recovery_question_error',
        ]);
    }

    private function getRecoveryQuestions(string $usuario)
    {
        return $this->getUserQuestions($usuario)
            ->sortBy('pregunta_id')
            ->values();
    }

    public function showConfig()
    {
        $usuario = $this->getUsername();
        if (! $usuario) {
            return redirect()->route('login');
        }

        // If already configured, redirect to verification (or dashboard)
        if ($this->hasSecurityQuestions($usuario)) {
            return redirect()->route('seguridad.verificar');
        }

        $questions = $this->getAllQuestions();
        $required = $this->getConfiguredQuestionLimit($questions->count());
        $answerConstraints = $this->getSecurityAnswerConstraints();

        return view('auth.preguntas-seguridad', [
            'questions' => $questions,
            'requiredQuestions' => $required,
            'min_respuesta' => $answerConstraints['min_respuesta'],
            'max_respuesta' => $answerConstraints['max_respuesta'],
        ]);
    }

    public function storeConfig(Request $request)
    {
        $usuario = $this->getUsername();
        if (! $usuario) {
            return redirect()->route('login');
        }

        $required = $this->getConfiguredQuestionLimit($this->getAllQuestions()->count());
        $answerConstraints = $this->getSecurityAnswerConstraints();
        $preguntas = $request->input('preguntas', []);
        $respuestas = $request->input('respuestas', []);

        if (! is_array($preguntas) || ! is_array($respuestas)) {
            return back()->withErrors(['error' => 'Formato de preguntas inválido.'])->withInput();
        }

        if (count($preguntas) < $required || count($respuestas) < $required) {
            return back()->withErrors(['error' => "Debes configurar {$required} preguntas con sus respuestas."])->withInput();
        }

        $questionIds = $this->getAllQuestions()->pluck('id_pregunta')->map(fn ($id) => (int) $id)->all();
        $answers = [];
        $usedQuestions = [];

        for ($i = 0; $i < $required; $i++) {
            $questionId = (int) ($preguntas[$i] ?? 0);
            $answer = trim((string) ($respuestas[$i] ?? ''));

            if ($questionId <= 0) {
                return back()->withErrors(['error' => 'Debes seleccionar todas las preguntas.'])->withInput();
            }

            if (! in_array($questionId, $questionIds, true)) {
                return back()->withErrors(['error' => 'Una de las preguntas seleccionadas no es válida.'])->withInput();
            }

            if (in_array($questionId, $usedQuestions, true)) {
                return back()->withErrors(['error' => 'Debes seleccionar preguntas diferentes.'])->withInput();
            }

            if (preg_match('/\s/u', $answer)) {
                return back()->withErrors(['error' => 'Las respuestas no permiten espacios en blanco.'])->withInput();
            }

            if (mb_strlen($answer) < $answerConstraints['min_respuesta'] || mb_strlen($answer) > $answerConstraints['max_respuesta']) {
                return back()->withErrors([
                    'error' => "Cada respuesta debe tener entre {$answerConstraints['min_respuesta']} y {$answerConstraints['max_respuesta']} caracteres."
                ])->withInput();
            }

            $usedQuestions[] = $questionId;
            $answers[] = [
                'pregunta_id' => $questionId,
                'respuesta' => Hash::make($answer),
            ];
        }

        DB::transaction(function () use ($usuario, $answers) {
            DB::table('tbl_seg_pregunta_usuario')->where('usuario', $usuario)->delete();
            foreach ($answers as $a) {
                DB::table('tbl_seg_pregunta_usuario')->insert([
                    'usuario' => $usuario,
                    'pregunta_id' => $a['pregunta_id'],
                    'respuesta' => $a['respuesta'],
                ]);
            }
        });

        session(['security_verified' => true]);
        return redirect()->route('cambiar-password');
    }

    private function getPasswordConstraints(): array
    {
        try {
            $rows = DB::table('tbl_seg_parametro')
                ->whereIn('nombre_parametro', ['MIN_CONTRASENA', 'MAX_CONTRASENA'])
                ->whereRaw("UPPER(COALESCE(estado, 'ACTIVO')) = 'ACTIVO'")
                ->pluck('valor', 'nombre_parametro');

            return [
                'min_contrasena' => $rows->get('MIN_CONTRASENA', 5),
                'max_contrasena' => $rows->get('MAX_CONTRASENA', 10),
            ];
        } catch (\Throwable $e) {
            return [
                'min_contrasena' => 5,
                'max_contrasena' => 10,
            ];
        }
    }

    public function showChangePassword()
    {
        $usuario = $this->getUsername();
        if (! $usuario) {
            return redirect()->route('login');
        }

        if (! session('security_verified', false)) {
            return redirect()->route('login');
        }

        return view('auth.cambiar-contrasena', array_merge(
            [
                'jwtToken' => session('jwt_token'),
                'requireCurrentPassword' => ! session('recovery_via_email', false),
            ],
            $this->getPasswordConstraints()
        ));
    }

    public function showRecoveryQuestions()
    {
        $usuario = session('recovery_username');
        if (! $usuario) {
            return redirect()->route('recuperar-contrasena');
        }

        $questions = $this->getRecoveryQuestions($usuario);
        if ($questions->isEmpty()) {
            return redirect()->route('recuperacion.opciones')->withErrors([
                'recovery' => 'El usuario no tiene preguntas de seguridad configuradas.',
            ]);
        }

        $answeredIds = collect(session('recovery_answered_questions', []))->map(fn ($id) => (int) $id)->values();
        $answeredQuestions = $questions->whereIn('pregunta_id', $answeredIds)->values();
        $answerConstraints = $this->getSecurityAnswerConstraints();

        return view('auth.recuperacion-preguntas', array_merge(
            $this->getPasswordConstraints(),
            [
                'usuario' => $usuario,
                'answeredQuestions' => $answeredQuestions,
                'availableQuestions' => $questions,
                'questionsVerified' => session('recovery_questions_verified', false),
                'maxAnswerLength' => $this->getRecoveryAnswerMaxLength(),
                'min_respuesta' => $answerConstraints['min_respuesta'],
                'max_respuesta' => min($answerConstraints['max_respuesta'], $this->getRecoveryAnswerMaxLength()),
            ]
        ));
    }

    public function validateRecoveryQuestion(Request $request)
    {
        $usuario = session('recovery_username');
        if (! $usuario) {
            return redirect()->route('recuperar-contrasena');
        }

        if (session('recovery_questions_verified', false)) {
            return redirect()->route('recuperacion.preguntas');
        }

        $questions = $this->getRecoveryQuestions($usuario);
        if ($questions->isEmpty()) {
            return redirect()->route('recuperacion.opciones');
        }

        $answerMax = $this->getRecoveryAnswerMaxLength();
        $questionId = (int) $request->input('pregunta_id');
        $answer = $this->normalizeRecoveryAnswer($request->input('respuesta'));

        $selectedQuestion = $questions->first(fn ($item) => (int) $item->pregunta_id === $questionId);
        if (! $selectedQuestion) {
            return back()->withErrors(['recovery' => 'La pregunta seleccionada no pertenece al usuario.'])->withInput();
        }

        if ($answer === '') {
            return back()->withErrors(['recovery' => 'Debe ingresar una respuesta.'])->withInput();
        }

        if (mb_strlen($answer) > $answerMax) {
            return back()->withErrors(['recovery' => "La respuesta no debe exceder {$answerMax} caracteres."])->withInput();
        }

        if (preg_match('/\s{2,}/u', $answer)) {
            return back()->withErrors(['recovery' => 'La respuesta solo permite un espacio entre palabras.'])->withInput();
        }

        if (! $this->matchesStoredSecret($answer, $selectedQuestion->respuesta)) {
            DB::table('tbl_seg_usuario')
                ->whereRaw('UPPER(usuario) = ?', [mb_strtoupper($usuario, 'UTF-8')])
                ->update(['estado' => 'BLOQUEADO']);

            $this->clearRecoverySession();

            return redirect()->route('login')->withErrors([
                'login' => 'Usuario bloqueado por respuestas incorrectas. Solicite activación al administrador o use recuperación por correo.'
            ]);
        }

        session([
            'recovery_answered_questions' => [(int) $selectedQuestion->pregunta_id],
            'recovery_questions_verified' => true,
        ]);

        return redirect()->route('recuperacion.preguntas');
    }

    public function updatePasswordAfterRecovery(Request $request)
    {
        $usuario = session('recovery_username');
        if (! $usuario || ! session('recovery_questions_verified', false)) {
            return redirect()->route('recuperar-contrasena');
        }

        $constraints = $this->getPasswordConstraints();
        $newPassword = (string) $request->input('contrasena_nueva', '');
        $confirmPassword = (string) $request->input('confirmar_contrasena', '');

        if ($newPassword === '' || $confirmPassword === '') {
            return back()->withErrors(['recovery' => 'Debe ingresar la nueva contraseña y la confirmación.'])->withInput();
        }

        if (preg_match('/\s/u', $newPassword) || preg_match('/\s/u', $confirmPassword)) {
            return back()->withErrors(['recovery' => 'La contraseña no debe contener espacios en blanco.'])->withInput();
        }

        if ($newPassword !== $confirmPassword) {
            return back()->withErrors(['recovery' => 'Las contraseñas no coinciden.'])->withInput();
        }

        $min = (int) $constraints['min_contrasena'];
        $max = (int) $constraints['max_contrasena'];
        if (strlen($newPassword) < $min || strlen($newPassword) > $max) {
            return back()->withErrors(['recovery' => "La contraseña debe tener entre {$min} y {$max} caracteres."])->withInput();
        }

        if (! preg_match('/[a-z]/', $newPassword)
            || ! preg_match('/[A-Z]/', $newPassword)
            || ! preg_match('/\d/', $newPassword)
            || ! preg_match('/[^\w\s]/', $newPassword)) {
            return back()->withErrors(['recovery' => 'La contraseña debe contener mayúscula, minúscula, número y carácter especial.'])->withInput();
        }

        if (mb_strtoupper($newPassword, 'UTF-8') === mb_strtoupper($usuario, 'UTF-8')) {
            return back()->withErrors(['recovery' => 'La contraseña no puede ser igual al usuario.'])->withInput();
        }

        $user = DB::table('tbl_seg_usuario')
            ->select('id_usuario', 'contrasena_hash')
            ->whereRaw('UPPER(usuario) = ?', [mb_strtoupper($usuario, 'UTF-8')])
            ->first();

        if (! $user) {
            $this->clearRecoverySession();
            return redirect()->route('recuperar-contrasena')->withErrors(['recovery' => 'El usuario no existe.']);
        }

        if ($this->matchesStoredSecret($newPassword, $user->contrasena_hash)) {
            return back()->withErrors(['recovery' => 'La nueva contraseña no debe ser igual a la anterior.'])->withInput();
        }

        DB::table('tbl_seg_usuario')
            ->where('id_usuario', $user->id_usuario)
            ->update([
                'contrasena_hash' => Hash::make($newPassword),
                'estado' => 'ACTIVO',
                'fecha_modificacion' => now(),
            ]);

        $this->clearRecoverySession();

        return redirect()->route('login')->with('status', 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.');
    }

    public function showVerify()
    {
        $usuario = $this->getUsername();
        if (! $usuario) {
            return redirect()->route('login');
        }

        if (! $this->hasSecurityQuestions($usuario)) {
            return redirect()->route('seguridad.preguntas');
        }

        $questions = $this->getUserQuestions($usuario);
        $answerConstraints = $this->getSecurityAnswerConstraints();

        return view('auth.validar-preguntas', [
            'questions' => $questions,
            'min_respuesta' => $answerConstraints['min_respuesta'],
            'max_respuesta' => $answerConstraints['max_respuesta'],
        ]);
    }

    public function validateAnswers(Request $request)
    {
        $usuario = $this->getUsername();
        if (! $usuario) {
            return redirect()->route('login');
        }

        $questions = $this->getUserQuestions($usuario);
        if ($questions->count() < 2) {
            return redirect()->route('seguridad.preguntas');
        }

        $request->validate([
            'respuesta_1' => 'required|string|min:3|max:100',
            'respuesta_2' => 'required|string|min:3|max:100',
        ]);

        $answers = [
            trim($request->respuesta_1),
            trim($request->respuesta_2),
        ];

        // Verify the two provided answers match stored ones (order not guaranteed)
        $stored = $questions->mapWithKeys(function ($item) {
            return [$item->pregunta_id => $item->respuesta];
        })->toArray();

        $matched = 0;
        foreach ($answers as $answer) {
            foreach ($stored as $hash) {
                if ($this->matchesStoredSecret($answer, $hash)) {
                    $matched++;
                    // prevent re-checking the same hash
                    $stored = array_diff($stored, [$hash]);
                    break;
                }
            }
        }

        if ($matched < 2) {
            try {
                DB::table('tbl_seg_usuario')
                    ->whereRaw('UPPER(usuario) = ?', [mb_strtoupper($usuario, 'UTF-8')])
                    ->update(['estado' => 'BLOQUEADO']);
            } catch (\Throwable $e) {
                // Si falla el bloqueo, al menos devolvemos el error de validación.
                return back()->withErrors(['error' => 'Las respuestas no coinciden.']);
            }

            session()->forget(['jwt_token', 'security_verified', 'user_id', 'usuario', 'correo']);

            return redirect()->route('login')->withErrors([
                'login' => 'Usuario bloqueado por respuestas incorrectas. Solicite activación al administrador o use recuperación de contraseña.'
            ]);
        }

        session(['security_verified' => true]);
        return redirect()->route('cambiar-password');
    }
}
