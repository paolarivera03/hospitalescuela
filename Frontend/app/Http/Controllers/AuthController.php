<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // Importante: Esto nos permite hacer peticiones a Node.js

class AuthController extends Controller
{
    private function getUsernameConstraints(): array
    {
        try {
            $row = DB::table('tbl_seg_parametro')
                ->whereIn('nombre_parametro', ['MIN_USUARIO', 'MAX_USUARIO'])
                ->pluck('valor', 'nombre_parametro');

            $min = (int) ($row->get('MIN_USUARIO', 1) ?? 1);
            $max = (int) ($row->get('MAX_USUARIO', 50) ?? 50);

            $min = $min > 0 ? $min : 1;
            $max = $max > 0 ? $max : 50;
            if ($max < $min) {
                $max = $min;
            }

            return [
                'min_usuario' => $min,
                'max_usuario' => $max,
            ];
        } catch (\Throwable $e) {
            return [
                'min_usuario' => 1,
                'max_usuario' => 50,
            ];
        }
    }

    // Devuelve valores de configuración de parámetros de seguridad (longitudes mínimas/máximas). 
    // Se basan en la tabla tbl_seg_parametro del backend.
    private function getPasswordConstraints(): array
    {
        try {
            $rows = DB::table('tbl_seg_parametro')
                ->whereIn('nombre_parametro', ['MIN_CONTRASENA', 'MAX_CONTRASENA'])
                ->pluck('valor', 'nombre_parametro');

            $username = $this->getUsernameConstraints();

            return [
                'min_contrasena' => $rows->get('MIN_CONTRASENA', 5),
                'max_contrasena' => $rows->get('MAX_CONTRASENA', 10),
                'min_usuario' => $username['min_usuario'],
                'max_usuario' => $username['max_usuario'],
            ];
        } catch (\Throwable $e) {
            $username = $this->getUsernameConstraints();

            // Si no puede leer la configuración, usar valores por defecto.
            return [
                'min_contrasena' => 5,
                'max_contrasena' => 10,
                'min_usuario' => $username['min_usuario'],
                'max_usuario' => $username['max_usuario'],
            ];
        }
    }

    // Muestra la pantalla de Login
    public function showLoginForm()
    {
        // Si ya tenemos un token y ya verificó las preguntas, intentamos ir al dashboard.
        if (session()->has('jwt_token') && session('security_verified', false)) {
            $token = session('jwt_token');
            $payload = $this->parseJwtPayload($token);

            // Validar token activo antes de redirigir para evitar bucles al dashboard.
            try {
                $perfilResp = Http::withToken($token)->get('http://localhost:3000/api/perfil');
                if (! $perfilResp->successful()) {
                    session()->forget(['jwt_token', 'security_verified', 'user_id', 'usuario', 'correo', 'recovery_via_email']);
                    return response()->view('auth.login', $this->getPasswordConstraints())
                        ->withCookie(cookie()->forget('jwt_token'));
                }
            } catch (\Throwable $e) {
                session()->forget(['jwt_token', 'security_verified', 'user_id', 'usuario', 'correo', 'recovery_via_email']);
                return response()->view('auth.login', $this->getPasswordConstraints())
                    ->withCookie(cookie()->forget('jwt_token'));
            }

            if (is_array($payload) && !empty($payload['id'])) {
                try {
                    $userResp = Http::withToken($token)->get("http://localhost:3000/api/usuarios/{$payload['id']}");
                    if ($userResp->successful() && data_get($userResp->json(), 'estado') === 'NUEVO') {
                        session()->forget(['jwt_token', 'security_verified', 'user_id', 'usuario', 'correo', 'recovery_via_email']);
                        return response()->view('auth.login')
                            ->withCookie(cookie()->forget('jwt_token'));
                    }
                } catch (\Throwable $e) {
                    // Si falla el backend, seguimos al dashboard (para no bloquear).
                }
            }

            return redirect()->route('dashboard');
        }

        // En cualquier otro caso mostramos el login (incluyendo cuando hay token, pero NO se verificaron preguntas).
        return view('auth.login', $this->getPasswordConstraints());
    }

    // Muestra la pantalla de recuperación de contraseña
    public function showRecoverForm()
    {
        session()->forget([
            'recovery_username',
            'recovery_via_email',
        ]);

        return view('auth.recuperar-contrasena', $this->getPasswordConstraints());
    }

    private function findRecoveryUser(string $usuario)
    {
        return DB::table('tbl_seg_usuario')
            ->select('id_usuario', 'usuario', 'correo', 'estado')
            ->whereRaw('UPPER(usuario) = ?', [mb_strtoupper($usuario, 'UTF-8')])
            ->first();
    }

    public function handleRecoveryUser(Request $request)
    {
        $constraints = $this->getPasswordConstraints();
        $usuarioRaw = (string) $request->input('usuario', '');

        if (trim($usuarioRaw) === '') {
            return back()->withErrors(['recovery' => 'Debe ingresar usuario.'])->withInput();
        }

        if (preg_match('/\s/', $usuarioRaw)) {
            return back()->withErrors(['recovery' => 'El usuario no debe contener espacios en blanco.'])->withInput();
        }

        $usuario = mb_strtoupper(trim($usuarioRaw), 'UTF-8');

        if (strlen($usuario) < (int) $constraints['min_usuario'] || strlen($usuario) > (int) $constraints['max_usuario']) {
            return back()->withErrors([
                'recovery' => "El usuario debe tener entre {$constraints['min_usuario']} y {$constraints['max_usuario']} caracteres."
            ])->withInput();
        }

        $user = $this->findRecoveryUser($usuario);
        if (! $user) {
            return back()->withErrors(['recovery' => 'El usuario no existe.'])->withInput();
        }

        if (strtoupper((string) $user->estado) === 'INACTIVO') {
            return back()->withErrors(['recovery' => 'El usuario está inactivo. Contacte al administrador.'])->withInput();
        }

        session([
            'recovery_username' => $user->usuario,
        ]);

        return redirect()->route('recuperacion.opciones');
    }

    public function showRecoveryOptions()
    {
        $usuario = session('recovery_username');
        if (! $usuario) {
            return redirect()->route('recuperar-contrasena');
        }

        return view('auth.recuperacion-opciones', [
            'usuario' => $usuario,
        ]);
    }

    public function recoverByEmail(Request $request)
    {
        $usuario = session('recovery_username');
        if (! $usuario) {
            return redirect()->route('recuperar-contrasena');
        }

        $response = Http::post('http://localhost:3000/api/recover-password', [
            'usuario' => $usuario,
        ]);

        if (! $response->successful()) {
            return redirect()->route('recuperacion.opciones')->withErrors([
                'recovery' => data_get($response->json(), 'message', 'No se pudo iniciar la recuperación por correo.'),
            ]);
        }

        session()->forget([
            'recovery_username',
        ]);

        return redirect()->route('login')->with('status', data_get(
            $response->json(),
            'message',
            'Se envió una contraseña temporal al correo registrado. Usa esa contraseña para iniciar sesión y cambiar la contraseña sin preguntas.'
        ));
    }

    // Procesa el Login enviando los datos a la API
    public function login(Request $request)
    {
        $constraints = $this->getPasswordConstraints();

        $usuarioRaw = (string) $request->input('usuario', '');
        $passwordRaw = (string) $request->input('password', '');

        $missing = [];
        if (trim($usuarioRaw) === '') {
            $missing[] = 'usuario';
        }
        if (trim($passwordRaw) === '') {
            $missing[] = 'contraseña';
        }
        if (! empty($missing)) {
            $msg = count($missing) === 2
                ? 'Debe ingresar usuario y contraseña.'
                : 'Debe ingresar ' . $missing[0] . '.';

            return back()->withErrors(['login' => $msg])->withInput();
        }

        if (preg_match('/\s/', $usuarioRaw) || preg_match('/\s/', $passwordRaw)) {
            return back()->withErrors(['login' => 'Usuario y contraseña no permiten espacios en blanco.'])->withInput();
        }

        $usuario = mb_strtoupper(trim($usuarioRaw), 'UTF-8');
        $password = $passwordRaw;

        if (strlen($usuario) < (int) $constraints['min_usuario'] || strlen($usuario) > (int) $constraints['max_usuario']) {
            return back()->withErrors([
                'login' => "El usuario debe tener entre {$constraints['min_usuario']} y {$constraints['max_usuario']} caracteres."
            ])->withInput();
        }

        if (strlen($password) < (int) $constraints['min_contrasena'] || strlen($password) > (int) $constraints['max_contrasena']) {
            return back()->withErrors([
                'login' => "La contraseña debe tener entre {$constraints['min_contrasena']} y {$constraints['max_contrasena']} caracteres."
            ])->withInput();
        }

        // 2. Hacer la petición POST a tu API de Node.js
        $response = Http::post('http://localhost:3000/api/login', [
            'usuario' => $usuario,
            'password' => $password,
        ]);

        // 3. Verificar si la API respondió con éxito (Status 200 OK)
        if ($response->successful()) {
            // Extraer los datos
            $data = $response->json();

            // ===================================
            // CHECK FOR 2FA REQUIREMENT
            // ===================================
            if (data_get($data, 'requiere_2fa', false)) {
                session()->forget([
                    'jwt_token',
                    'security_verified',
                    'user_id',
                    'correo',
                    'recovery_via_email',
                ]);

                session([
                    'pending_2fa' => true,
                    'usuario' => data_get($data, 'usuario'),
                    'pending_2fa_token' => data_get($data, 'token_2fa_pendiente'),
                    'pending_2fa_expira_minutos' => (int) data_get($data, 'expira_en_minutos', 10),
                ]);

                return redirect()->route('verificar.2fa')
                    ->withCookie(cookie()->forget('jwt_token'));
            }

            // If we reach here, it means 2FA is already verified (or was skipped)
            // Guardar el Token en la sesión de Laravel para usarlo en futuras pantallas
            session(['jwt_token' => $data['token']]);

            // Extraer el ID de usuario desde el JWT (para las preguntas de seguridad)
            $payload = $this->parseJwtPayload($data['token']);

            if (is_array($payload)) {
                session(['user_id' => $payload['id'] ?? null]);
                session(['usuario' => $payload['usuario'] ?? null]);
                session(['correo' => $payload['correo'] ?? null]);
            }

            // También propagamos el token en una cookie para que todas las rutas web (bitácora, reacciones, etc.) puedan usarlo
            $secure = app()->environment('production');
            $cookie = cookie('jwt_token', $data['token'], 120, '/', null, $secure, true, false, 'lax');

            // Consultar el estado actual del usuario en el backend (más confiable que depender solo del flag enviado en el login).
            $estado = null;
            $usuario = session('usuario');
            $payloadId = $payload['id'] ?? null;

            if ($payloadId) {
                try {
                    $userResp = Http::withToken($data['token'])->get("http://localhost:3000/api/usuarios/{$payloadId}");
                    if ($userResp->successful()) {
                        $estado = data_get($userResp->json(), 'estado');
                    }
                } catch (\Throwable $e) {
                    // Ignorar; usaremos el flag de login si no podemos consultar.
                }
            }

            $necesitaCambio = false;
            if ($estado !== null) {
                $necesitaCambio = (strtoupper(trim($estado)) === 'NUEVO');
            } else {
                $necesitaCambio = (bool) data_get($data, 'requiere_cambio_contrasena', false);
            }

            // Marcar si el usuario ya pasó el flujo de preguntas o aún lo necesita.
            // Si el usuario está en estado NUEVO, deberá responder preguntas + cambiar contraseña.
            // Si ya está ACTIVO, se considera verificado para el dashboard.
            $sesionVerificada = ! $necesitaCambio;
            session(['security_verified' => $sesionVerificada]);

            if ($necesitaCambio) {
                session([
                    'security_verified' => true,
                    'recovery_via_email' => (bool) data_get($data, 'omitir_preguntas_recuperacion', false),
                ]);

                return redirect()->route('cambiar-password')->withCookie($cookie);
            }

            // Para usuarios activos, ir directo al dashboard.
            return redirect()->route('dashboard')->withCookie($cookie);
        }

        // 4. Si la API devuelve error (credenciales incorrectas), regresamos al login con un mensaje
        return back()->withErrors([
            'login' => data_get($response->json(), 'message', 'Usuario o contraseña inválidos.'),
        ])->withInput();
    }

    private function parseJwtPayload(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $payloadBase64 = strtr($parts[1], '-_', '+/');
        $padding = 4 - (strlen($payloadBase64) % 4);
        if ($padding < 4) {
            $payloadBase64 .= str_repeat('=', $padding);
        }

        $payloadJson = base64_decode($payloadBase64);
        if (! $payloadJson) {
            return null;
        }

        $payload = json_decode($payloadJson, true);
        return is_array($payload) ? $payload : null;
    }

    // ==============================
    // 2FA VERIFICATION
    // ==============================

    /**
     * Show 2FA verification form
     */
    public function show2FAForm()
    {
        if (! session()->has('pending_2fa')) {
            return redirect()->route('login');
        }

        return view('auth.verificar-2fa', [
            'usuario' => session('usuario'),
            'expira_en_minutos' => (int) session('pending_2fa_expira_minutos', 10),
        ]);
    }

    /**
     * Verify 2FA token
     */
    public function verify2FA(Request $request)
    {
        if (! session()->has('pending_2fa') || ! session()->has('pending_2fa_token')) {
            return redirect()->route('login');
        }

        $codigo2fa = (string) $request->input('codigo2fa', '');

        if (strlen(trim($codigo2fa)) !== 6 || ! ctype_digit($codigo2fa)) {
            return back()->withErrors(['codigo2fa' => 'El código debe tener exactamente 6 dígitos.'])->withInput();
        }

        // Send 2FA verification to backend
        $response = Http::post('http://localhost:3000/api/verify-2fa', [
            'codigo2fa' => trim($codigo2fa),
            'token_2fa_pendiente' => session('pending_2fa_token'),
        ]);

        if ($response->successful()) {
            $data = $response->json();

            // Clear 2FA session
            session()->forget(['pending_2fa', 'pending_2fa_token', 'pending_2fa_expira_minutos']);

            // Save JWT token
            session(['jwt_token' => $data['token']]);

            // Parse JWT to get user info
            $payload = $this->parseJwtPayload($data['token']);

            if (is_array($payload)) {
                session(['user_id' => $payload['id'] ?? null]);
                session(['usuario' => $payload['usuario'] ?? null]);
                session(['correo' => $payload['correo'] ?? null]);
            }

            // Set cookie
            $secure = app()->environment('production');
            $cookie = cookie('jwt_token', $data['token'], 120, '/', null, $secure, true, false, 'lax');

            // Check if user needs password change
            $necesitaCambio = (bool) data_get($data, 'requiere_cambio_contrasena', false);
            $sesionVerificada = ! $necesitaCambio;
            session(['security_verified' => $sesionVerificada]);

            if ($necesitaCambio) {
                session([
                    'security_verified' => true,
                    'recovery_via_email' => (bool) data_get($data, 'omitir_preguntas_recuperacion', false),
                ]);

                return redirect()->route('cambiar-password')->withCookie($cookie);
            }

            return redirect()->route('dashboard')->withCookie($cookie);
        }

        // If verification failed
        return back()->withErrors([
            'codigo2fa' => data_get($response->json(), 'message', 'El código de autenticación es incorrecto.')
        ])->withInput();
    }

}