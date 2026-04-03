<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Support\AuthenticatedUser;
use Barryvdh\DomPDF\Facade\Pdf;

class PacienteController extends Controller
{
    private $apiUrl = 'http://localhost:3000/api';

    private function hasPacienteAction(int $userId, string $action): bool
    {
        if ($userId <= 0) {
            return false;
        }

        return DB::table('tbl_seg_permisos')
            ->where('id_usuario', $userId)
            ->where('id_formulario', 2)
            ->whereRaw('UPPER(accion) = ?', [strtoupper($action)])
            ->exists();
    }

    private function resolveAuthUserId(string $token): int
    {
        $usuario = AuthenticatedUser::fromToken($token);
        return (int) ($usuario['id'] ?? $usuario['id_usuario'] ?? 0);
    }

    private function getMedicos(string $token): array
    {
        try {
            $response = Http::withToken($token)->get("{$this->apiUrl}/medicos");
            if ($response->successful()) {
                return (array) $response->json();
            }
        } catch (\Throwable $e) {
            // Intentamos rutas alternativas y, por último, consulta directa a BD.
        }

        try {
            $fallbackApi = Http::withToken($token)->get("{$this->apiUrl}/reacciones-adversas/api/medicos");
            if ($fallbackApi->successful()) {
                return (array) $fallbackApi->json();
            }
        } catch (\Throwable $e) {
            // Continuar con fallback a BD.
        }

        try {
            return DB::table('tbl_far_medico as m')
                ->leftJoin('tbl_seg_usuario as u', 'u.id_usuario', '=', 'm.id_usuario')
                ->leftJoin('tbl_seg_rol as r', 'r.id_rol', '=', 'u.id_rol')
                ->select('m.id_medico', 'm.nombre_completo', 'm.especialidad', 'm.numero_colegiacion')
                ->where('m.estado', 'ACTIVO')
                ->where(function ($q) {
                    $q->where('r.nombre', 'MEDICO')
                      ->orWhereNull('m.id_usuario');
                })
                ->orderBy('m.nombre_completo')
                ->get()
                ->map(fn($m) => [
                    'id_medico' => (int) $m->id_medico,
                    'nombre_completo' => $m->nombre_completo,
                    'especialidad' => $m->especialidad,
                    'numero_colegiacion' => $m->numero_colegiacion,
                ])
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function getToken()
    {
        return request()->cookie('jwt_token') ?: session('jwt_token');
    }

    private function checkToken()
    {
        $token = $this->getToken();

        if ($token && !session()->has('jwt_token')) {
            session(['jwt_token' => $token]);
        }

        if (!$token) {
            return redirect()->route('login');
        }

        return $token;
    }

    // 1. Muestra la lista de pacientes (Con Buscador)
    public function lista(Request $request)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;
        if (!$token) return redirect()->route('login');

        $search  = $request->input('search');
        $sort    = $request->input('sort', 'recent');
        $perPage = max(1, (int) $request->input('limit', 10));
        $pageEnviados    = max(1, (int) $request->input('page_enviados', 1));
        $pageRegistrados = max(1, (int) $request->input('page_registrados', 1));

        $queryEnviados = DB::table('tbl_far_paciente as p')
            ->leftJoin('tbl_far_medico as m', 'm.id_medico', '=', 'p.id_medico')
            ->leftJoin('tbl_seg_usuario as um', 'um.id_usuario', '=', 'm.id_usuario')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('tbl_far_reaccion_adversa as ra')
                    ->whereColumn('ra.id_paciente', 'p.id_paciente');
            })
            ->select(
                'p.id_paciente',
                'p.numero_expediente',
                'p.nombre',
                'p.fecha_creacion',
                DB::raw("COALESCE(NULLIF(TRIM(CONCAT(COALESCE(um.nombre, ''), ' ', COALESCE(um.apellido, ''))), ''), NULLIF(TRIM(COALESCE(m.nombre_completo, '')), ''), 'MEDICO NO ASIGNADO') as nombre_medico")
            );

        if (!empty($search)) {
            $searchLike = '%' . strtoupper($search) . '%';
            $queryEnviados->where(function ($q) use ($searchLike) {
                $q->whereRaw('UPPER(p.numero_expediente) LIKE ?', [$searchLike])
                  ->orWhereRaw('UPPER(p.nombre) LIKE ?', [$searchLike]);
            });
        }

        switch ($sort) {
            case 'expediente':
                $queryEnviados->orderBy('p.numero_expediente', 'asc');
                break;
            case 'paciente':
                $queryEnviados->orderBy('p.nombre', 'asc');
                break;
            case 'medico':
                $queryEnviados->orderByRaw('COALESCE(NULLIF(TRIM(CONCAT(COALESCE(um.nombre, ""), " ", COALESCE(um.apellido, ""))), ""), NULLIF(TRIM(COALESCE(m.nombre_completo, "")), ""), "ZZZ") ASC');
                break;
            case 'recent':
            default:
                $queryEnviados->orderByDesc('p.fecha_creacion')->orderByDesc('p.id_paciente');
                break;
        }

        $totalEnviados = (clone $queryEnviados)->count();
        $pacientesEnviados = $queryEnviados
            ->offset(($pageEnviados - 1) * $perPage)
            ->limit($perPage)
            ->get()->map(fn ($row) => [
                'id_paciente'      => (int) $row->id_paciente,
                'numero_expediente' => $row->numero_expediente,
                'nombre'           => $row->nombre,
                'fecha_creacion'   => $row->fecha_creacion,
                'nombre_medico'    => $row->nombre_medico,
            ])->toArray();
        $paginacionEnviados = [
            'currentPage' => $pageEnviados,
            'totalPages'  => (int) ceil($totalEnviados / $perPage),
            'total'       => $totalEnviados,
        ];

        $queryRegistrados = DB::table('tbl_far_reaccion_adversa as ra')
            ->join('tbl_far_paciente as p', 'p.id_paciente', '=', 'ra.id_paciente')
            ->leftJoin('tbl_far_medico as m', 'm.id_medico', '=', 'ra.id_medico')
            ->leftJoin('tbl_seg_usuario as um', 'um.id_usuario', '=', 'm.id_usuario')
            ->whereIn('ra.estado', ['REGISTRADA', 'EN_ANALISIS', 'CERRADA'])
            ->select(
                DB::raw('ra.id_reaccion as id_bandeja'),
                'ra.id_reaccion',
                'p.nombre as nombre_paciente',
                DB::raw("COALESCE(NULLIF(TRIM(CONCAT(COALESCE(um.nombre, ''), ' ', COALESCE(um.apellido, ''))), ''), NULLIF(TRIM(COALESCE(m.nombre_completo, '')), ''), 'MEDICO NO ASIGNADO') as nombre_medico"),
                'ra.descripcion_reaccion',
                'ra.fecha_inicio_reaccion',
                'ra.fecha_fin_reaccion',
                'ra.estado'
            )
            ->orderByDesc('ra.fecha_creacion')
            ->orderByDesc('ra.id_reaccion');

        if (!empty($search)) {
            $searchLike = '%' . strtoupper($search) . '%';
            $queryRegistrados->where(function ($q) use ($searchLike) {
                $q->whereRaw('UPPER(p.numero_expediente) LIKE ?', [$searchLike])
                  ->orWhereRaw('UPPER(p.nombre) LIKE ?', [$searchLike]);
            });
        }

        $totalRegistrados = (clone $queryRegistrados)->count();
        $pacientesRegistrados = $queryRegistrados
            ->offset(($pageRegistrados - 1) * $perPage)
            ->limit($perPage)
            ->get()->map(fn ($row) => [
                'id_bandeja'           => (int) $row->id_bandeja,
                'id_reaccion'          => (int) $row->id_reaccion,
                'nombre_paciente'      => $row->nombre_paciente,
                'nombre_medico'        => $row->nombre_medico,
                'descripcion_reaccion' => $row->descripcion_reaccion,
                'fecha_inicio_reaccion' => $row->fecha_inicio_reaccion,
                'fecha_fin_reaccion'   => $row->fecha_fin_reaccion,
                'estado'               => strtoupper(str_replace('_', ' ', $row->estado ?? 'REGISTRADA')),
            ])->toArray();
        $paginacionRegistrados = [
            'currentPage' => $pageRegistrados,
            'totalPages'  => max(1, (int) ceil($totalRegistrados / $perPage)),
            'total'       => $totalRegistrados,
        ];

        // También pedimos el perfil para mostrar el usuario en el header
        return view('modulos.pacientes.lista', [
            'usuario'               => AuthenticatedUser::fromToken($token),
            'search'                => $search,
            'sort'                  => $sort,
            'perPage'               => $perPage,
            'pacientesEnviados'     => $pacientesEnviados,
            'paginacionEnviados'    => $paginacionEnviados,
            'pacientesRegistrados'  => $pacientesRegistrados,
            'paginacionRegistrados' => $paginacionRegistrados,
        ]);
    }

    // 2. Muestra el formulario para crear un nuevo paciente
    public function create()
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $usuario = AuthenticatedUser::fromToken($token);
        $medicos = $this->getMedicos($token);
        $rolNombre = strtoupper((string) ($usuario['rol_nombre'] ?? $usuario['rol'] ?? ''));
        $esRolMedico = str_contains($rolNombre, 'MEDICO');
        $usuarioIdToken = (int) ($usuario['id_usuario'] ?? $usuario['id'] ?? 0);

        // Solo el rol MEDICO se autocompleta; enfermero/farmaceutico seleccionan manualmente.
        $medicoSesion = null;
        if ($esRolMedico && $usuarioIdToken > 0) {
            $medicoSesion = collect($medicos)->first(function ($medico) use ($usuarioIdToken) {
                return (int) ($medico['id_usuario'] ?? 0) === $usuarioIdToken;
            });
        }

        return view('modulos.pacientes.create', [
            'usuario' => $usuario,
            'medicos' => $medicos,
            'medicoSesion' => $medicoSesion,
            'esRolMedico' => $esRolMedico,
        ]);
    }

    // 3. Recibe los datos del formulario y los manda a la API
    public function store(Request $request)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $request->merge([
            'fecha_inicio_uso' => $request->filled('fecha_inicio_uso') ? $request->input('fecha_inicio_uso') : null,
            'fecha_fin_uso' => $request->filled('fecha_fin_uso') ? $request->input('fecha_fin_uso') : null,
        ]);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'edad' => 'required|integer|min:1|max:99',
            'sexo' => 'required|in:M,F,Otro',
            'sala' => 'nullable|string|max:100',
            'numero_cama' => 'nullable|string|max:20',
            'diagnostico' => 'required|string|max:255',
            'id_medico' => 'required|integer',
            'id_medicamento' => 'required|integer',
            'id_lote' => 'required|integer',
            'dosis_posologia' => 'required|string|max:255',
            'via_administracion' => 'required|string|max:100',
            'fecha_inicio_uso' => 'required|date_format:Y-m-d',
            'fecha_fin_uso' => 'nullable|date_format:Y-m-d|after_or_equal:fecha_inicio_uso',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
        ], [
            'id_medico.required' => 'Debe seleccionar un médico tratante válido.',
            'id_medicamento.required' => 'Debe seleccionar un medicamento válido.',
            'id_lote.required' => 'Debe seleccionar un lote válido.',
            'fecha_inicio_uso.required' => 'La fecha de inicio de uso es obligatoria.',
            'fecha_inicio_uso.date_format' => 'La fecha de inicio de uso no tiene un formato válido.',
            'fecha_fin_uso.date_format' => 'La fecha fin de uso no tiene un formato válido.',
            'fecha_fin_uso.after_or_equal' => 'La fecha fin de uso debe ser igual o posterior a la fecha de inicio de uso.',
            'foto.max' => 'Archivo muy pesado. La imagen no debe exceder los 20 MB.',
        ]);

        $requestHttp = Http::withToken($token)->asMultipart();

        if ($request->hasFile('foto')) {
            $archivo = $request->file('foto');
            $requestHttp = $requestHttp->attach(
                'foto',
                fopen($archivo->getRealPath(), 'r'),
                $archivo->getClientOriginalName()
            );
        }

        $response = $requestHttp->post("{$this->apiUrl}/pacientes", [
            'nombre'            => $request->nombre,
            'edad'              => $request->edad,
            'sexo'              => $request->sexo,
            'sala'              => $request->sala,
            'numero_cama'       => $request->numero_cama,
            'diagnostico'       => $request->diagnostico,
            'id_medico'         => $request->id_medico,
            'id_medicamento'    => $request->id_medicamento,
            'id_lote'           => $request->id_lote,
            'dosis_posologia'   => $request->dosis_posologia,
            'via_administracion'=> $request->via_administracion,
            'fecha_inicio_uso'  => $request->fecha_inicio_uso,
            'fecha_fin_uso'     => $request->fecha_fin_uso,
        ]);

        if ($response->successful()) {
            $idPaciente = (int) ($response->json('id_paciente') ?? 0);

            return redirect()
                ->route('pacientes.lista')
                ->with('success', 'Paciente ingresado correctamente.')
                ->with('report_prompt', [
                    'title' => '¿Desea generar reporte?',
                    'text' => 'Se registró el paciente correctamente.',
                    'confirm_text' => 'Sí, generar reporte',
                    'cancel_text' => 'No, ir al inicio',
                    'yes_url' => $idPaciente > 0 ? route('pacientes.reporte', $idPaciente) : route('pacientes.reporte_general'),
                    'no_url' => route('dashboard'),
                ]);
        }

        $message = $response->json('message') ?: 'No se pudo registrar el paciente. Verifique la conexión con la API.';

        return back()->withErrors(['error' => $message])->withInput();
    }

    // 4. Muestra el formulario de edición
    public function edit($id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $userId = $this->resolveAuthUserId($token);
        if (! $this->hasPacienteAction($userId, 'ACTUALIZAR')) {
            abort(403, 'No tienes permiso para editar pacientes.');
        }

        $paciente = null;

        try {
            $response = Http::withToken($token)->get("{$this->apiUrl}/pacientes/{$id}");
            if ($response->successful()) {
                $paciente = $response->json();
            }
        } catch (\Throwable $e) {
            // Continuar con fallback directo a BD.
        }

        if (!is_array($paciente) || empty($paciente)) {
            $pacienteRow = DB::table('tbl_far_paciente as p')
                ->leftJoin('tbl_far_medico as m', 'm.id_medico', '=', 'p.id_medico')
                ->leftJoin('tbl_seg_usuario as um', 'um.id_usuario', '=', 'm.id_usuario')
                ->select(
                    'p.id_paciente',
                    'p.numero_expediente',
                    'p.nombre',
                    'p.edad',
                    'p.sexo',
                    'p.sala',
                    'p.numero_cama',
                    'p.diagnostico',
                    'p.id_medico',
                    DB::raw("COALESCE(NULLIF(TRIM(CONCAT(COALESCE(um.nombre, ''), ' ', COALESCE(um.apellido, ''))), ''), NULLIF(TRIM(COALESCE(m.nombre_completo, '')), ''), 'MEDICO NO ASIGNADO') as nombre_medico")
                )
                ->where('p.id_paciente', (int) $id)
                ->first();

            if (!$pacienteRow) {
                return redirect()->route('pacientes.lista')->withErrors(['error' => 'No se pudo cargar el paciente.']);
            }

            $paciente = [
                'id_paciente' => (int) $pacienteRow->id_paciente,
                'numero_expediente' => $pacienteRow->numero_expediente,
                'nombre' => $pacienteRow->nombre,
                'edad' => $pacienteRow->edad,
                'sexo' => $pacienteRow->sexo,
                'sala' => $pacienteRow->sala,
                'numero_cama' => $pacienteRow->numero_cama,
                'diagnostico' => $pacienteRow->diagnostico,
                'id_medico' => $pacienteRow->id_medico,
                'nombre_medico' => $pacienteRow->nombre_medico,
            ];
        }

        return view('modulos.pacientes.edit', [
            'paciente' => $paciente,
            'usuario' => AuthenticatedUser::fromToken($token),
            'medicos' => $this->getMedicos($token),
        ]);
    }

    public function buscarMedicamentos(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $rows = DB::table('tbl_far_medicamento')
            ->select('id_medicamento', 'nombre_comercial', 'principio_activo', 'laboratorio_fabricante')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('nombre_comercial', 'like', $like)
                       ->orWhere('principio_activo', 'like', $like)
                       ->orWhereRaw('CAST(id_medicamento AS CHAR) LIKE ?', [$like]);
                });
            })
            ->orderBy('nombre_comercial')
            ->limit(10)
            ->get();

        return response()->json($rows);
    }

    public function buscarLotes(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $idMedicamento = $request->input('id_medicamento');

        $rows = DB::table('tbl_far_lote')
            ->select('id_lote', 'numero_lote', 'fecha_expiracion', 'id_medicamento')
            ->when($idMedicamento, fn($query) => $query->where('id_medicamento', $idMedicamento))
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('numero_lote', 'like', $like)
                       ->orWhereRaw('CAST(id_lote AS CHAR) LIKE ?', [$like]);
                });
            })
            ->orderBy('numero_lote')
            ->limit(10)
            ->get();

        return response()->json($rows);
    }

    public function buscarMedicos(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $like = $q !== '' ? '%' . $q . '%' : '%';

        $rows = DB::table('tbl_far_medico as m')
            ->leftJoin('tbl_seg_usuario as u', 'u.id_usuario', '=', 'm.id_usuario')
            ->leftJoin('tbl_seg_rol as r', 'r.id_rol', '=', 'u.id_rol')
            ->select('m.id_medico', 'm.nombre_completo', 'm.especialidad', 'm.numero_colegiacion')
            ->where('m.estado', 'ACTIVO')
            ->where(function ($query) {
                $query->where('r.nombre', 'MEDICO')->orWhereNull('m.id_usuario');
            })
            ->where(function ($query) use ($like) {
                $query->where('m.nombre_completo', 'like', $like)
                      ->orWhere('m.numero_colegiacion', 'like', $like);
            })
            ->orderBy('m.nombre_completo')
            ->limit(10)
            ->get()
            ->map(fn($m) => [
                'id_medico'         => (int) $m->id_medico,
                'nombre_completo'   => $m->nombre_completo,
                'especialidad'      => $m->especialidad ?? '',
                'numero_colegiacion'=> $m->numero_colegiacion ?? '',
            ]);

        return response()->json($rows);
    }

    // 5. Procesa la actualización
    public function update(Request $request, $id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $userId = $this->resolveAuthUserId($token);
        if (! $this->hasPacienteAction($userId, 'ACTUALIZAR')) {
            abort(403, 'No tienes permiso para actualizar pacientes.');
        }

        $request->validate([
            'numero_expediente' => 'nullable|string|max:50',
            'nombre' => 'required|string|max:255',
            'edad' => 'required|integer|min:1|max:99',
            'sexo' => 'required|in:M,F,Otro',
            'sala' => 'nullable|string|max:100',
            'numero_cama' => 'nullable|string|max:20',
            'diagnostico' => 'required|string|max:255',
            'id_medico' => 'required|integer',
        ], [
            'id_medico.required' => 'Debe seleccionar un médico tratante válido.',
        ]);
        
        // Aquí SÍ enviamos el expediente para no perder el que ya se generó antes
        $response = Http::withToken($token)->put("{$this->apiUrl}/pacientes/{$id}", [
            'numero_expediente' => $request->numero_expediente,
            'nombre'            => $request->nombre,
            'edad'              => $request->edad,
            'sexo'              => $request->sexo,
            'sala'              => $request->sala,
            'numero_cama'       => $request->numero_cama,
            'diagnostico'       => $request->diagnostico,
            'id_medico'         => $request->id_medico,
        ]);
        
        if ($response->successful()) {
            return redirect()->route('pacientes.lista')->with('success', 'Datos del paciente actualizados.');
        }

        return back()->withErrors(['error' => 'No se pudo actualizar la información del paciente.']);
    }

    // 6. Elimina el registro
    public function destroy($id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $response = Http::withToken($token)->delete("{$this->apiUrl}/pacientes/{$id}");

        if ($response->successful()) {
            return redirect()->route('pacientes.lista')->with('success', 'Paciente dado de alta o eliminado del registro.');
        }

        $message = $response->json('message') ?: 'No se pudo eliminar el paciente.';

        return redirect()->route('pacientes.lista')->withErrors(['error' => $message]);
    }

    // 7. Mostrar detalle del paciente
    public function show($id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $paciente = DB::table('tbl_far_paciente as p')
            ->leftJoin('tbl_far_medico as m', 'm.id_medico', '=', 'p.id_medico')
            ->leftJoin('tbl_seg_usuario as um', 'um.id_usuario', '=', 'm.id_usuario')
            ->leftJoin('tbl_seg_usuario as uc', 'uc.id_usuario', '=', 'p.usuario_creacion')
            ->where('p.id_paciente', $id)
            ->select(
                'p.id_paciente',
                'p.numero_expediente',
                'p.nombre',
                'p.edad',
                'p.sexo',
                'p.sala',
                'p.numero_cama',
                'p.diagnostico',
                'p.id_medico',
                'p.fecha_creacion',
                'p.usuario_creacion',
                DB::raw("COALESCE(NULLIF(TRIM(CONCAT(COALESCE(um.nombre, ''), ' ', COALESCE(um.apellido, ''))), ''), NULLIF(TRIM(COALESCE(m.nombre_completo, '')), ''), 'SIN ASIGNAR') as nombre_medico"),
                DB::raw("COALESCE(NULLIF(TRIM(CONCAT(COALESCE(uc.nombre, ''), ' ', COALESCE(uc.apellido, ''))), ''), COALESCE(uc.usuario, 'N/A')) as usuario_creador_nombre")
            )
            ->first();

        if (! $paciente) {
            return redirect()->route('pacientes.lista')->withErrors(['error' => 'No se pudo cargar el paciente.']);
        }

        try {
            $fotoRow = DB::table('tbl_far_reaccion_adversa as ra')
                ->join('tbl_far_reaccion_foto as rf', 'rf.id_reaccion', '=', 'ra.id_reaccion')
                ->where('ra.id_paciente', $id)
                ->orderByDesc('rf.id_foto')
                ->select('rf.ruta_archivo')
                ->first();

            if ($fotoRow?->ruta_archivo) {
                $paciente->foto = $fotoRow->ruta_archivo;
            } else {
                $fotoFallback = DB::selectOne('SELECT foto FROM tbl_far_paciente WHERE id_paciente = ? LIMIT 1', [$id]);
                $paciente->foto = $fotoFallback->foto ?? null;
            }
        } catch (\Throwable $e) {
            $paciente->foto = null;
        }

        $medicacion = null;
        try {
            $medicacion = DB::table('tbl_far_prescripcion as pr')
                ->join('tbl_far_prescripcion_detalle as pd', 'pd.id_prescripcion', '=', 'pr.id_prescripcion')
                ->leftJoin('tbl_far_medicamento as med', 'med.id_medicamento', '=', 'pd.id_medicamento')
                ->leftJoin('tbl_far_lote as l', 'l.id_lote', '=', 'pd.id_lote')
                ->where('pr.id_paciente', $id)
                ->orderByDesc('pr.id_prescripcion')
                ->select(
                    'med.nombre_comercial',
                    'l.numero_lote',
                    'pd.dosis_instrucciones',
                    DB::raw('pd.via_administracion as via_administracion'),
                    DB::raw('pd.fecha_inicio_uso as fecha_inicio_uso'),
                    DB::raw('pd.fecha_fin_uso as fecha_fin_uso')
                )
                ->first();
        } catch (\Throwable $e) {
            $medicacion = null;
        }

        $usuario = AuthenticatedUser::fromToken($token);
        $userRole = null;
        
        // Obtener el rol del usuario
        if ($usuario['id'] ?? null) {
            $userRoleRow = DB::table('tbl_seg_usuario as u')
                ->leftJoin('tbl_seg_rol as r', 'r.id_rol', '=', 'u.id_rol')
                ->where('u.id_usuario', $usuario['id'])
                ->select('r.nombre as rol_nombre')
                ->first();
            $userRole = $userRoleRow?->rol_nombre;
        }

        return view('modulos.pacientes.show', [
            'paciente' => (array) $paciente,
            'medicacion' => $medicacion ? (array) $medicacion : null,
            'usuario' => $usuario,
            'userRole' => $userRole,
        ]);
    }

    // 7b. Vista de Farmacéutico
    public function showPharmacy($id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $paciente = DB::table('tbl_far_paciente as p')
            ->leftJoin('tbl_far_medico as m', 'm.id_medico', '=', 'p.id_medico')
            ->leftJoin('tbl_seg_usuario as um', 'um.id_usuario', '=', 'm.id_usuario')
            ->leftJoin('tbl_seg_usuario as uc', 'uc.id_usuario', '=', 'p.usuario_creacion')
            ->where('p.id_paciente', $id)
            ->select(
                'p.id_paciente',
                'p.numero_expediente',
                'p.nombre',
                'p.edad',
                'p.sexo',
                'p.sala',
                'p.numero_cama',
                'p.diagnostico',
                'p.id_medico',
                'p.fecha_creacion',
                'p.usuario_creacion',
                DB::raw("COALESCE(NULLIF(TRIM(CONCAT(COALESCE(um.nombre, ''), ' ', COALESCE(um.apellido, ''))), ''), NULLIF(TRIM(COALESCE(m.nombre_completo, '')), ''), 'SIN ASIGNAR') as nombre_medico"),
                DB::raw("COALESCE(NULLIF(TRIM(CONCAT(COALESCE(uc.nombre, ''), ' ', COALESCE(uc.apellido, ''))), ''), COALESCE(uc.usuario, 'N/A')) as usuario_creador_nombre")
            )
            ->first();

        if (! $paciente) {
            return redirect()->route('reacciones_adversas.index')->withErrors(['error' => 'No se pudo cargar el paciente.']);
        }

        $medicacion = null;
        try {
            $medicacion = DB::table('tbl_far_prescripcion as pr')
                ->join('tbl_far_prescripcion_detalle as pd', 'pd.id_prescripcion', '=', 'pr.id_prescripcion')
                ->leftJoin('tbl_far_medicamento as med', 'med.id_medicamento', '=', 'pd.id_medicamento')
                ->leftJoin('tbl_far_lote as l', 'l.id_lote', '=', 'pd.id_lote')
                ->where('pr.id_paciente', $id)
                ->orderByDesc('pr.id_prescripcion')
                ->select(
                    'med.nombre_comercial',
                    'l.numero_lote',
                    'pd.dosis_instrucciones',
                    DB::raw('pd.via_administracion as via_administracion'),
                    DB::raw('pd.fecha_inicio_uso as fecha_inicio_uso'),
                    DB::raw('pd.fecha_fin_uso as fecha_fin_uso')
                )
                ->first();
        } catch (\Throwable $e) {
            $medicacion = null;
        }

        return view('modulos.pacientes.show-pharmacy', [
            'paciente' => (array) $paciente,
            'medicacion' => $medicacion ? (array) $medicacion : null,
            'usuario' => AuthenticatedUser::fromToken($token),
        ]);
    }

    // 8. Reporte individual
    public function reporte($id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        try {
            $pacienteRow = DB::table('tbl_far_paciente as p')
                ->select(
                    'p.numero_expediente',
                    'p.nombre',
                    'p.edad',
                    'p.sexo',
                    'p.sala',
                    'p.numero_cama',
                    'p.diagnostico'
                )
                ->where('p.id_paciente', $id)
                ->first();
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'No se pudo generar el reporte individual.']);
        }

        if (! $pacienteRow) {
            return back()->withErrors(['error' => 'No se pudo generar el reporte individual.']);
        }

        $paciente = [
            'numero_expediente' => $pacienteRow->numero_expediente,
            'nombre' => $pacienteRow->nombre,
            'edad' => $pacienteRow->edad,
            'sexo' => $pacienteRow->sexo,
            'sala' => $pacienteRow->sala,
            'numero_cama' => $pacienteRow->numero_cama,
            'diagnostico' => $pacienteRow->diagnostico,
        ];

        $usuario = AuthenticatedUser::fromToken($token);
        $descargadoPor = $usuario['usuario'] ?? 'Sistema';

        $viewData = [
            'paciente' => $paciente,
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => $descargadoPor,
        ];

        // DomPDF requiere GD para ciertos recursos (imagenes/fuentes). Si no esta
        // disponible, mostramos el reporte en HTML para guardar desde el navegador.
        if (!extension_loaded('gd')) {
            return view('modulos.pacientes.pdf_individual', $viewData + [
                'htmlPreview' => true,
            ]);
        }

        try {
            $pdf = Pdf::loadView('modulos.pacientes.pdf_individual', $viewData);
            return $pdf->stream('Paciente_' . ($paciente['numero_expediente'] ?? $id) . '.pdf');
        } catch (\Throwable $e) {
            return view('modulos.pacientes.pdf_individual', $viewData + [
                'htmlPreview' => true,
            ]);
        }
    }

    // Ver detalle de reacción adversa desde Registro PX
    public function showReaccionDesdePx(Request $request, $id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $returnUrl = trim((string) $request->query('return_url', ''));
        $backRoute = ($returnUrl !== '' && str_starts_with($returnUrl, url('/')))
            ? $returnUrl
            : route('pacientes.lista');

        $response = Http::withToken($token)->get("{$this->apiUrl}/reacciones-adversas/{$id}");

        if (! $response->successful()) {
            return redirect()->route('pacientes.lista')
                ->withErrors(['error' => 'No se pudo cargar el detalle de la reacción adversa.']);
        }

        $data = (array) $response->json();

        return view('reacciones_adversas.show', [
            'reaccion' => $data['cabecera'] ?? [],
            'detalles' => $data['detalles'] ?? [],
            'consecuencias' => $data['consecuencias'] ?? [],
            'usuario' => AuthenticatedUser::fromToken($token),
            'backRoute' => $backRoute,
            'backLabel' => 'Regresar',
        ]);
    }

    // 9. Reporte general
    public function reporteGeneral(Request $request)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $search = trim((string) $request->input('search', ''));

        try {
            $query = DB::table('tbl_far_paciente as p')
                ->select(
                    'p.numero_expediente',
                    'p.nombre',
                    'p.edad',
                    'p.sexo',
                    'p.sala',
                    'p.numero_cama',
                    'p.diagnostico'
                )
                ->orderByDesc('p.fecha_creacion')
                ->orderByDesc('p.id_paciente');

            if ($search !== '') {
                $like = '%' . strtoupper($search) . '%';
                $query->where(function ($q) use ($like) {
                    $q->whereRaw('UPPER(p.numero_expediente) LIKE ?', [$like])
                      ->orWhereRaw('UPPER(p.nombre) LIKE ?', [$like]);
                });
            }

            $pacientes = $query->get()->map(function ($p) {
                return [
                    'numero_expediente' => $p->numero_expediente,
                    'nombre' => $p->nombre,
                    'edad' => $p->edad,
                    'sexo' => $p->sexo,
                    'sala' => $p->sala,
                    'numero_cama' => $p->numero_cama,
                    'diagnostico' => $p->diagnostico,
                ];
            })->toArray();
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'No se pudo generar el reporte general.']);
        }

        $usuario = AuthenticatedUser::fromToken($token);
        $descargadoPor = $usuario['usuario'] ?? 'Sistema';

        $viewData = [
            'pacientes' => $pacientes,
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => $descargadoPor,
        ];

        // DomPDF requiere GD para ciertos recursos (imagenes/fuentes). Si no esta
        // disponible, mostramos una vista HTML para guardar desde el navegador.
        if (!extension_loaded('gd')) {
            return view('modulos.pacientes.pdf_general', $viewData + [
                'htmlPreview' => true,
            ]);
        }

        try {
            $pdf = Pdf::loadView('modulos.pacientes.pdf_general', $viewData)
                ->setPaper('a4', 'landscape');

            return $pdf->stream('Catalogo_Pacientes.pdf');
        } catch (\Throwable $e) {
            return view('modulos.pacientes.pdf_general', $viewData + [
                'htmlPreview' => true,
            ]);
        }
    }
}
