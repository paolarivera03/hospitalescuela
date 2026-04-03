<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Support\AuthenticatedUser;

class ReaccionesAdversasController extends Controller
{
    private $apiUrl = 'http://localhost:3000/api';

    private function uploadReaccionPhotos(string $token, Request $request, int $idReaccion): ?string
    {
        if (! $request->hasFile('foto') && ! $request->hasFile('foto_medicamento')) {
            return null;
        }

        $http = Http::withToken($token)->asMultipart();

        if ($request->hasFile('foto')) {
            $archivo = $request->file('foto');
            $http = $http->attach(
                'foto',
                fopen($archivo->getRealPath(), 'r'),
                $archivo->getClientOriginalName()
            );
        }

        if ($request->hasFile('foto_medicamento')) {
            $archivo = $request->file('foto_medicamento');
            $http = $http->attach(
                'foto_medicamento',
                fopen($archivo->getRealPath(), 'r'),
                $archivo->getClientOriginalName()
            );
        }

        $response = $http->post("{$this->apiUrl}/reacciones-adversas/{$idReaccion}/fotos");

        if (! $response->successful()) {
            return $response->json('message') ?: 'No se pudieron guardar las fotos de la reacción.';
        }

        return null;
    }

    private function resolveSafeReturnUrl(Request $request, string $defaultRoute): string
    {
        $returnUrl = trim((string) $request->query('return_url', ''));
        if ($returnUrl !== '' && str_starts_with($returnUrl, url('/'))) {
            return $returnUrl;
        }

        return $defaultRoute;
    }

    private function getBandejaPacientes(string $token): array
    {
        try {
            $response = Http::withToken($token)->get("{$this->apiUrl}/reacciones-adversas/bandeja-pacientes");
            if ($response->successful()) {
                $data = (array) $response->json();

                return [
                    'nuevos' => array_values($data['nuevos'] ?? []),
                    'vistos' => array_values($data['vistos'] ?? []),
                    'total_nuevos' => (int) ($data['total_nuevos'] ?? 0),
                ];
            }
        } catch (\Throwable $e) {
        }

        return [
            'nuevos' => [],
            'vistos' => [],
            'total_nuevos' => 0,
        ];
    }

    private function getPendienteBandeja(string $token, ?int $idPendiente): ?array
    {
        if (!$idPendiente) {
            return null;
        }

        try {
            $response = Http::withToken($token)->get("{$this->apiUrl}/reacciones-adversas/bandeja-pacientes/{$idPendiente}");

            if ($response->successful()) {
                return (array) $response->json();
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    private function getToken()
    {
        return request()->cookie('jwt_token') ?: session('jwt_token');
    }

    private function getUserIdFromToken(string $token): int
    {
        $usuario = AuthenticatedUser::fromToken($token);
        return (int) ($usuario['id'] ?? 0);
    }

    private function hasReaccionesAction(int $userId, string $action): bool
    {
        if ($userId <= 0) {
            return false;
        }

        return DB::table('tbl_seg_permisos')
            ->where('id_usuario', $userId)
            ->where('id_formulario', 3)
            ->whereRaw('UPPER(accion) = ?', [strtoupper(trim($action))])
            ->exists();
    }

    private function canUseReacciones(string $token, string $action): bool
    {
        $userId = $this->getUserIdFromToken($token);
        return $this->hasReaccionesAction($userId, $action);
    }

    private function denyByPermission(string $message)
    {
        return redirect()->route('dashboard')->withErrors(['error' => $message]);
    }

    private function getGravedadOptions(string $token): array
    {
        $fallback = ['LEVE', 'MODERADA', 'GRAVE'];

        try {
            $response = Http::withToken($token)->get("{$this->apiUrl}/parametros");
            if (! $response->successful()) {
                return $fallback;
            }

            $rows = $response->json();
            if (!is_array($rows)) {
                return $fallback;
            }

            $options = [];
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $nombre = strtoupper(trim((string) ($row['nombre_parametro'] ?? '')));
                $estado = strtoupper(trim((string) ($row['estado'] ?? '')));
                $valor = strtoupper(trim((string) ($row['valor'] ?? '')));

                if ($estado !== 'ACTIVO' || $valor === '') {
                    continue;
                }

                if (
                    str_starts_with($nombre, 'RAM_GRAVEDAD_') ||
                    str_starts_with($nombre, 'GRAVEDAD_')
                ) {
                    $valor = preg_replace('/\s+/', '_', $valor);
                    $valor = preg_replace('/[^A-Z_]/', '', (string) $valor);

                    if ($valor !== '') {
                        $options[] = $valor;
                    }
                }
            }

            $options = array_values(array_unique($options));

            return count($options) > 0 ? $options : $fallback;
        } catch (\Throwable $e) {
            return $fallback;
        }
    }

    private function normalizeCatalogKey(string $value): string
    {
        $value = mb_strtoupper(trim($value), 'UTF-8');
        $value = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú'], ['A', 'E', 'I', 'O', 'U'], $value);
        $value = preg_replace('/\s+/', '_', $value);
        return preg_replace('/[^A-Z0-9_]/', '', (string) $value) ?? '';
    }

    private function getAccesosConfig(string $token): array
    {
        $fallback = [
            'consecuencias' => [
                ['clave' => 'HAN_PUESTO_EN_PELIGRO_SU_VIDA', 'valor' => 'HAN PUESTO EN PELIGRO SU VIDA'],
                ['clave' => 'HAN_SIDO_LA_CAUSA_DE_SU_HOSPITALIZACION', 'valor' => 'HAN SIDO LA CAUSA DE SU HOSPITALIZACION'],
                ['clave' => 'HAN_PROLONGADO_SU_INGRESO_EN_EL_HOSPITAL', 'valor' => 'HAN PROLONGADO SU INGRESO EN EL HOSPITAL'],
                ['clave' => 'HAN_ORIGINADO_INCAPACIDAD_PERSISTENTE_O_GRAVE', 'valor' => 'HAN ORIGINADO INCAPACIDAD PERSISTENTE O GRAVE'],
                ['clave' => 'HAN_CAUSADO_DEFECTO_O_ANOMALIA_CONGENITA', 'valor' => 'HAN CAUSADO DEFECTO O ANOMALIA CONGENITA'],
                ['clave' => 'HAN_CAUSADO_LA_MUERTE_DEL_PACIENTE', 'valor' => 'HAN CAUSADO LA MUERTE DEL PACIENTE'],
                ['clave' => 'NO_HAN_CAUSADO_NADA_DE_LO_ANTERIOR_PERO_CONSIDERO_QUE_ES_GRAVE', 'valor' => 'NO HAN CAUSADO NADA DE LO ANTERIOR, PERO CONSIDERO QUE ES GRAVE'],
                ['clave' => 'NO_HAN_CAUSADO_NADA_DE_LO_ANTERIOR_PERO_CONSIDERO_QUE_NO_ES_GRAVE', 'valor' => 'NO HAN CAUSADO NADA DE LO ANTERIOR, PERO CONSIDERO QUE NO ES GRAVE'],
            ],
            'desenlace' => [
                ['clave' => 'DESCONOCIDO', 'valor' => 'DESCONOCIDO'],
                ['clave' => 'RECUPERADO_RESUELTO', 'valor' => 'RECUPERADO/RESUELTO'],
                ['clave' => 'EN_RECUPERACION_EN_RESOLUCION', 'valor' => 'EN RECUPERACION / EN RESOLUCION'],
                ['clave' => 'NO_RECUPERADO_NO_RESUELTO', 'valor' => 'NO RECUPERADO / NO RESUELTO'],
                ['clave' => 'RECUPERADO_RESUELTO_CON_SECUELAS', 'valor' => 'RECUPERADO/RESUELTO CON SECUELAS'],
                ['clave' => 'MORTAL', 'valor' => 'MORTAL'],
            ],
            'estados' => [
                ['clave' => 'REGISTRADA', 'valor' => 'REGISTRADA'],
                ['clave' => 'EN_ANALISIS', 'valor' => 'EN ANALISIS'],
                ['clave' => 'CERRADA', 'valor' => 'CERRADA'],
            ],
        ];

        try {
            $response = Http::withToken($token)->get("{$this->apiUrl}/configuraciones/accesos");
            if (! $response->successful()) {
                return $fallback;
            }

            $raw = (array) $response->json();

            $mapItems = function (array $items): array {
                $result = [];
                foreach ($items as $item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    $estado = strtoupper(trim((string) ($item['estado'] ?? 'ACTIVO')));
                    if ($estado !== 'ACTIVO') {
                        continue;
                    }

                    $clave = $this->normalizeCatalogKey((string) ($item['clave_objeto'] ?? ''));
                    $valor = strtoupper(trim((string) ($item['valor_objeto'] ?? '')));
                    if ($clave === '' || $valor === '') {
                        continue;
                    }

                    $result[] = [
                        'clave' => $clave,
                        'valor' => $valor,
                    ];
                }

                return array_values(array_unique($result, SORT_REGULAR));
            };

            $consecuencias = $mapItems((array) ($raw['CONSECUENCIAS_REACCION'] ?? []));
            $desenlace = $mapItems((array) ($raw['DESENLACE_REACCION'] ?? []));
            $estados = $mapItems((array) ($raw['ESTADO_REACCION'] ?? []));

            return [
                'consecuencias' => !empty($consecuencias) ? $consecuencias : $fallback['consecuencias'],
                'desenlace' => !empty($desenlace) ? $desenlace : $fallback['desenlace'],
                'estados' => !empty($estados) ? $estados : $fallback['estados'],
            ];
        } catch (\Throwable $e) {
            return $fallback;
        }
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

    // LISTA
    public function index()
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        if (! $this->canUseReacciones($token, 'VISUALIZAR')) {
            return $this->denyByPermission('No tienes permiso para visualizar el módulo de Reacciones Adversas.');
        }

        $canGuardar = $this->canUseReacciones($token, 'GUARDAR');
        $canActualizar = $this->canUseReacciones($token, 'ACTUALIZAR');
        $canEliminar = $this->canUseReacciones($token, 'ELIMINAR');

        $response = Http::withToken($token)->get('http://localhost:3000/api/reacciones-adversas');
        $perfil   = Http::withToken($token)->get('http://localhost:3000/api/perfil');
        $usuario = AuthenticatedUser::fromToken($token);

        $errorMessage = null;

        if (! $perfil->successful()) {
            $errorMessage = 'No se pudo verificar el usuario. Por favor inicia sesión de nuevo.';
        }

        if (! $response->successful()) {
            if ($response->status() === 401 || $response->status() === 403) {
                return redirect()->route('login');
            }
            $errorMessage = 'No se pudo cargar las reacciones adversas. Intenta de nuevo.';
        }

        $bandejaPacientes = $this->getBandejaPacientes($token);

        return view('reacciones_adversas.index', [
            'reacciones' => $response->successful() ? $response->json() : [],
            'usuario' => $usuario,
            'errorMessage' => $errorMessage,
            'bandejaPacientes' => $bandejaPacientes,
            'reaccionPermisos' => [
                'guardar' => $canGuardar,
                'actualizar' => $canActualizar,
                'eliminar' => $canEliminar,
            ],
        ]);
    }

    // FORM CREAR
    public function create(Request $request)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        if (! $this->canUseReacciones($token, 'GUARDAR')) {
            return $this->denyByPermission('No tienes permiso para crear reacciones adversas.');
        }

        $pacientes = Http::withToken($token)->get('http://localhost:3000/api/pacientes');
        $medicos = Http::withToken($token)->get('http://localhost:3000/api/medicos');
        $personalClinicoResponse = Http::withToken($token)->get('http://localhost:3000/api/personal-clinico');
        $pendienteSeleccionado = $this->getPendienteBandeja($token, $request->integer('pendiente'));

        $medicosData = $medicos->json() ?? [];
        $personalClinico = $personalClinicoResponse->successful()
            ? ($personalClinicoResponse->json() ?? [])
            : array_map(function ($medico) {
                $medico['rol_nombre'] = 'MEDICO';
                return $medico;
            }, $medicosData);

        $gravedadOptions = $this->getGravedadOptions($token);
        $accesosConfig = $this->getAccesosConfig($token);
        $desenlaceKeys = array_values(array_unique(array_filter(array_map(fn($item) => $item['clave'] ?? null, $accesosConfig['desenlace'] ?? []))));
        $estadoKeys = array_values(array_unique(array_filter(array_map(fn($item) => $item['clave'] ?? null, $accesosConfig['estados'] ?? []))));
        $consecuenciaKeys = array_values(array_unique(array_filter(array_map(fn($item) => $item['clave'] ?? null, $accesosConfig['consecuencias'] ?? []))));
        $accesosConfig = $this->getAccesosConfig($token);

        return view('reacciones_adversas.create', [
            'usuario' => AuthenticatedUser::fromToken($token),
            'pacientes' => $pacientes->json() ?? [],
            'medicos' => $medicosData,
            'personalClinico' => $personalClinico,
            'pendienteSeleccionado' => $pendienteSeleccionado,
            'pacienteSeleccionado' => null,
            'gravedadOptions' => $gravedadOptions,
            'consecuenciaOptions' => $accesosConfig['consecuencias'] ?? [],
            'desenlaceOptions' => $accesosConfig['desenlace'] ?? [],
            'estadoReaccionOptions' => $accesosConfig['estados'] ?? [],
        ]);
    }

    // GUARDAR
    public function store(Request $request)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        if (! $this->canUseReacciones($token, 'GUARDAR')) {
            return $this->denyByPermission('No tienes permiso para guardar reacciones adversas.');
        }

        $gravedadOptions = $this->getGravedadOptions($token);
        $accesosConfig = $this->getAccesosConfig($token);
        $desenlaceKeys = array_values(array_unique(array_filter(array_map(fn($item) => $item['clave'] ?? null, $accesosConfig['desenlace'] ?? []))));
        $estadoKeys = array_values(array_unique(array_filter(array_map(fn($item) => $item['clave'] ?? null, $accesosConfig['estados'] ?? []))));
        $consecuenciaKeys = array_values(array_unique(array_filter(array_map(fn($item) => $item['clave'] ?? null, $accesosConfig['consecuencias'] ?? []))));

        $request->validate([
            'id_paciente' => 'nullable|integer',
            'paciente_nombre' => 'required_without:id_paciente|string|max:255',
            'paciente_edad' => 'required_without:id_paciente|integer|min:1|max:99',
            'paciente_sexo' => 'required_without:id_paciente|in:M,F,Otro',
            'notificador' => 'required|in:MEDICO,ENFERMERO,FARMACEUTICO',
            'id_medico' => 'required|integer',
            'diagnostico_ingreso' => 'required_without:id_paciente|string|max:255',
            'id_medicamento' => 'required|integer',
            'id_lote' => 'required|integer',
            'dosis_posologia' => 'required|string|max:255',
            'via_administracion' => 'required|string|max:100',
            'fecha_inicio_uso' => 'required|date|before_or_equal:today|after_or_equal:1900-01-01',
            'fecha_fin_uso' => 'nullable|date|after_or_equal:fecha_inicio_uso|after_or_equal:1900-01-01',
            'descripcion_reaccion' => 'nullable|string',
            'fecha_inicio_reaccion' => 'required|date|before_or_equal:today|after_or_equal:1900-01-01',
            'fecha_fin_reaccion' => 'nullable|date|after_or_equal:fecha_inicio_reaccion|after_or_equal:1900-01-01',
            'desenlace' => ['nullable', Rule::in($desenlaceKeys)],
            'estado' => ['required', Rule::in($estadoKeys)],
            'sala' => 'required|string|max:50',
            'numero_cama' => 'required|string|max:50',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:15360',
            'foto_medicamento' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:15360',
            'id_pendiente_bandeja' => 'nullable|integer',
            'descripcion_consecuencia' => ['nullable', Rule::in($consecuenciaKeys)],
            'gravedad' => ['required', Rule::in($gravedadOptions)],
        ]);

        $descripcionReaccion = trim((string) $request->input('descripcion_reaccion', ''));
        if ($descripcionReaccion === '') {
            $descripcionReaccion = trim((string) $request->input('observaciones', ''));
        }
        if ($descripcionReaccion === '') {
            $descripcionReaccion = trim((string) $request->input('desenlace', ''));
        }
        if ($descripcionReaccion === '') {
            $descripcionReaccion = 'SIN DESCRIPCION';
        }

        $idPaciente = (int) ($request->input('id_paciente') ?? 0);

        if ($idPaciente <= 0) {
            $requestPaciente = Http::withToken($token)->asMultipart();

            $responsePaciente = $requestPaciente->post("{$this->apiUrl}/pacientes", [
                'nombre' => $request->input('paciente_nombre'),
                'edad' => $request->input('paciente_edad'),
                'sexo' => $request->input('paciente_sexo'),
                'sala' => $request->input('sala'),
                'numero_cama' => $request->input('numero_cama'),
                'diagnostico' => $request->input('diagnostico_ingreso'),
                'id_medico' => $request->input('id_medico'),
                'id_medicamento' => $request->input('id_medicamento'),
                'id_lote' => $request->input('id_lote'),
                'dosis_posologia' => $request->input('dosis_posologia'),
                'via_administracion' => $request->input('via_administracion'),
                'fecha_inicio_uso' => $request->input('fecha_inicio_uso'),
                'fecha_fin_uso' => $request->input('fecha_fin_uso'),
            ]);

            if (! $responsePaciente->successful()) {
                $msg = $responsePaciente->json('message') ?: 'No se pudo registrar el paciente desde Reacciones Adversas.';
                return back()->withErrors(['error' => $msg])->withInput();
            }

            $idPaciente = (int) ($responsePaciente->json('id_paciente') ?? 0);

            if ($idPaciente <= 0) {
                return back()->withErrors(['error' => 'No se pudo resolver el paciente creado.'])->withInput();
            }
        }

        $consecuenciasPayload = array_values($request->input('consecuencias', []));
        if (empty($consecuenciasPayload) && $request->filled('descripcion_consecuencia')) {
            $consecuenciasPayload[] = [
                'descripcion_consecuencia' => $request->input('descripcion_consecuencia'),
                'gravedad' => $request->input('gravedad'),
            ];
        }

        $payload = [
            'cabecera' => [
                'id_paciente' => $idPaciente,
                'id_medico' => (int) $request->id_medico,
                'descripcion_reaccion' => $descripcionReaccion,
                'fecha_inicio_reaccion' => $request->fecha_inicio_reaccion,
                'fecha_fin_reaccion' => $request->fecha_fin_reaccion,
                'desenlace' => $this->normalizeCatalogKey((string) $request->desenlace),
                'observaciones' => $request->observaciones,
                'estado' => $this->normalizeCatalogKey((string) $request->estado),
                'sala' => $request->sala,
                'numero_cama' => $request->numero_cama,
                'paciente_nombre' => $request->input('paciente_nombre'),
                'paciente_edad' => $request->input('paciente_edad'),
                'paciente_sexo' => $request->input('paciente_sexo'),
                'diagnostico_ingreso' => $request->input('diagnostico_ingreso'),
                'descripcion_consecuencia' => $request->input('descripcion_consecuencia'),
                'gravedad' => $request->input('gravedad'),
            ],
            'detalles' => [[
                'medicamento' => $request->input('medicamento_nombre') ?: $request->input('buscarMedicamento') ?: null,
                'id_medicamento' => (int) $request->input('id_medicamento'),
                'id_lote' => (int) $request->input('id_lote'),
                'dosis_posologia' => $request->input('dosis_posologia'),
                'via_administracion' => $request->input('via_administracion'),
                'fecha_inicio_uso' => $request->input('fecha_inicio_uso'),
                'fecha_fin_uso' => $request->input('fecha_fin_uso'),
            ]],
            'consecuencias' => $consecuenciasPayload,
            'id_pendiente_bandeja' => $request->filled('id_pendiente_bandeja') ? (int) $request->id_pendiente_bandeja : null,
        ];

        $response = Http::withToken($token)
            ->post("{$this->apiUrl}/reacciones-adversas", $payload);

        if ($response->successful()) {
            $idReaccion = (int) ($response->json('id_reaccion') ?? 0);

            $photoError = null;
            if ($idReaccion > 0) {
                $photoError = $this->uploadReaccionPhotos($token, $request, $idReaccion);
            }

            $redirect = redirect()
                ->route('reacciones_adversas.index')
                ->with('success', 'Reacción adversa registrada correctamente.')
                ->with('report_prompt', [
                    'title' => 'Registro completado',
                    'text' => 'Desea generar el reporte de este registro?',
                    'confirm_text' => 'Si, ver ficha',
                    'cancel_text' => 'No, ir al listado',
                    'yes_url' => $idReaccion > 0
                        ? route('reacciones_adversas.print', ['id' => $idReaccion, 'return_url' => route('reacciones_adversas.index')])
                        : route('reacciones_adversas.index'),
                    'no_url' => route('reacciones_adversas.index'),
                ]);

            if ($photoError) {
                $redirect->with('warning', $photoError);
            }

            return $redirect;
        }

        $message = $response->json('message') ?: 'Error al guardar.';
        return back()->withErrors(['error' => $message])->withInput();
    }

    // VER
    public function show(Request $request, $id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        if (! $this->canUseReacciones($token, 'VISUALIZAR')) {
            return $this->denyByPermission('No tienes permiso para visualizar reacciones adversas.');
        }

        $backRoute = $this->resolveSafeReturnUrl($request, route('reacciones_adversas.index'));

        $response = Http::withToken($token)
            ->get("http://localhost:3000/api/reacciones-adversas/$id");

        $data = $response->json();

        return view('reacciones_adversas.show', [
            'reaccion' => $data['cabecera'] ?? [],
            'detalles' => $data['detalles'] ?? [],
            'consecuencias' => $data['consecuencias'] ?? [],
            'usuario' => AuthenticatedUser::fromToken($token),
            'backRoute' => $backRoute,
            'backLabel' => 'Regresar',
        ]);
    }

    // EDITAR
    public function edit($id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        if (! $this->canUseReacciones($token, 'ACTUALIZAR')) {
            return $this->denyByPermission('No tienes permiso para editar reacciones adversas.');
        }

        $response = Http::withToken($token)
            ->get("http://localhost:3000/api/reacciones-adversas/$id");

        $data = $response->json();
        $gravedadOptions = $this->getGravedadOptions($token);
        $accesosConfig = $this->getAccesosConfig($token);
        $desenlaceKeys = array_values(array_unique(array_filter(array_map(fn($item) => $item['clave'] ?? null, $accesosConfig['desenlace'] ?? []))));
        $estadoKeys = array_values(array_unique(array_filter(array_map(fn($item) => $item['clave'] ?? null, $accesosConfig['estados'] ?? []))));
        $consecuenciaKeys = array_values(array_unique(array_filter(array_map(fn($item) => $item['clave'] ?? null, $accesosConfig['consecuencias'] ?? []))));
        $accesosConfig = $this->getAccesosConfig($token);
        $gravedadActual = strtoupper(trim((string) data_get($data, 'cabecera.gravedad', '')));
        if ($gravedadActual !== '' && !in_array($gravedadActual, $gravedadOptions, true)) {
            $gravedadOptions[] = $gravedadActual;
        }

        $personalClinicoResponse = Http::withToken($token)->get('http://localhost:3000/api/personal-clinico');
        $personalClinico = $personalClinicoResponse->successful()
            ? ($personalClinicoResponse->json() ?? [])
            : [];

        $idMedicoActual = (int) ($data['cabecera']['id_medico'] ?? 0);
        $notificadorActual = 'MEDICO';
        foreach ($personalClinico as $persona) {
            if ((int) ($persona['id_medico'] ?? 0) === $idMedicoActual) {
                $rol = strtoupper(trim((string) ($persona['rol_nombre'] ?? '')));
                if (in_array($rol, ['MEDICO', 'ENFERMERO', 'FARMACEUTICO'], true)) {
                    $notificadorActual = $rol;
                }
                break;
            }
        }

        return view('reacciones_adversas.edit', [
            'reaccion' => $data['cabecera'] ?? [],
            'detalles' => $data['detalles'] ?? [],
            'consecuencias' => $data['consecuencias'] ?? [],
            'usuario' => AuthenticatedUser::fromToken($token),
            'gravedadOptions' => $gravedadOptions,
            'personalClinico' => $personalClinico,
            'notificadorActual' => $notificadorActual,
            'consecuenciaOptions' => $accesosConfig['consecuencias'] ?? [],
            'desenlaceOptions' => $accesosConfig['desenlace'] ?? [],
            'estadoReaccionOptions' => $accesosConfig['estados'] ?? [],
        ]);
    }

    // ACTUALIZAR
    public function update(Request $request, $id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        if (! $this->canUseReacciones($token, 'ACTUALIZAR')) {
            return $this->denyByPermission('No tienes permiso para actualizar reacciones adversas.');
        }

        $gravedadOptions = $this->getGravedadOptions($token);
        $accesosConfig = $this->getAccesosConfig($token);
        $desenlaceKeys = array_values(array_unique(array_filter(array_map(fn($item) => $item['clave'] ?? null, $accesosConfig['desenlace'] ?? []))));
        $estadoKeys = array_values(array_unique(array_filter(array_map(fn($item) => $item['clave'] ?? null, $accesosConfig['estados'] ?? []))));
        $consecuenciaKeys = array_values(array_unique(array_filter(array_map(fn($item) => $item['clave'] ?? null, $accesosConfig['consecuencias'] ?? []))));

        $request->validate([
            'id_paciente' => 'required|integer',
            'paciente_nombre' => 'required|string|max:255',
            'paciente_edad' => 'required|integer|min:1|max:99',
            'paciente_sexo' => 'required|in:M,F,Otro',
            'id_medico' => 'required|integer',
            'id_medicamento' => 'required|integer',
            'id_lote' => 'required|integer',
            'dosis_posologia' => 'required|string|max:255',
            'via_administracion' => 'required|string|max:100',
            'fecha_inicio_uso' => 'required|date|before_or_equal:today|after_or_equal:1900-01-01',
            'fecha_fin_uso' => 'nullable|date|after_or_equal:fecha_inicio_uso|after_or_equal:1900-01-01',
            'descripcion_reaccion' => 'nullable|string',
            'fecha_inicio_reaccion' => 'required|date|before_or_equal:today|after_or_equal:1900-01-01',
            'fecha_fin_reaccion' => 'nullable|date|after_or_equal:fecha_inicio_reaccion|after_or_equal:1900-01-01',
            'desenlace' => ['nullable', Rule::in($desenlaceKeys)],
            'estado' => ['required', Rule::in($estadoKeys)],
            'sala' => 'required|string|max:50',
            'numero_cama' => 'required|string|max:50',
            'descripcion_consecuencia' => ['nullable', Rule::in($consecuenciaKeys)],
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:15360',
            'foto_medicamento' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:15360',
            'gravedad' => ['required', Rule::in($gravedadOptions)],
            'diagnostico_ingreso' => 'required|string|max:255',
        ]);

        $descripcionReaccion = trim((string) $request->input('descripcion_reaccion', ''));
        if ($descripcionReaccion === '') {
            $descripcionReaccion = trim((string) $request->input('observaciones', ''));
        }
        if ($descripcionReaccion === '') {
            $descripcionReaccion = trim((string) $request->input('desenlace', ''));
        }
        if ($descripcionReaccion === '') {
            $descripcionReaccion = 'SIN DESCRIPCION';
        }

        $estado = $this->normalizeCatalogKey((string) $request->input('estado', ''));
        if ($estado === '') {
            $estado = $estadoKeys[0] ?? 'REGISTRADA';
        }

        $consecuenciasPayload = array_values($request->input('consecuencias', []));
        if (empty($consecuenciasPayload) && $request->filled('descripcion_consecuencia')) {
            $consecuenciasPayload[] = [
                'descripcion_consecuencia' => $request->input('descripcion_consecuencia'),
                'gravedad' => $request->input('gravedad'),
            ];
        }

        $payload = [
            'cabecera' => [
                'id_paciente' => (int) $request->id_paciente,
                'id_medico' => (int) $request->id_medico,
                'descripcion_reaccion' => $descripcionReaccion,
                'fecha_inicio_reaccion' => $request->fecha_inicio_reaccion,
                'fecha_fin_reaccion' => $request->fecha_fin_reaccion,
                'desenlace' => $this->normalizeCatalogKey((string) $request->desenlace),
                'observaciones' => $request->observaciones,
                'estado' => $estado,
                'sala' => $request->sala,
                'numero_cama' => $request->numero_cama,
                'paciente_nombre' => $request->input('paciente_nombre'),
                'paciente_edad' => $request->input('paciente_edad'),
                'paciente_sexo' => $request->input('paciente_sexo'),
                'diagnostico_ingreso' => $request->input('diagnostico_ingreso'),
                'descripcion_consecuencia' => $request->input('descripcion_consecuencia'),
                'gravedad' => $request->input('gravedad'),
            ],
            'detalles' => [[
                'medicamento' => $request->input('medicamento_nombre') ?: $request->input('buscarMedicamento') ?: null,
                'id_medicamento' => (int) $request->input('id_medicamento'),
                'id_lote' => (int) $request->input('id_lote'),
                'dosis_posologia' => $request->input('dosis_posologia'),
                'via_administracion' => $request->input('via_administracion'),
                'fecha_inicio_uso' => $request->input('fecha_inicio_uso'),
                'fecha_fin_uso' => $request->input('fecha_fin_uso'),
            ]],
            'consecuencias' => $consecuenciasPayload,
        ];

        $response = Http::withToken($token)
            ->put("{$this->apiUrl}/reacciones-adversas/$id", $payload);

        if ($response->successful()) {
            $photoError = $this->uploadReaccionPhotos($token, $request, (int) $id);

            $redirect = redirect()
                ->route('reacciones_adversas.index')
                ->with('success', 'Reacción actualizada.')
                ->with('report_prompt', [
                    'title' => 'Actualización completada',
                    'text' => 'Desea generar el reporte de este registro?',
                    'confirm_text' => 'Si, ver ficha',
                    'cancel_text' => 'No, ir al listado',
                    'yes_url' => route('reacciones_adversas.print', ['id' => $id, 'return_url' => route('reacciones_adversas.index')]),
                    'no_url' => route('reacciones_adversas.index'),
                ]);

            if ($photoError) {
                $redirect->with('warning', $photoError);
            }

            return $redirect;
        }

        $message = $response->json('message') ?: 'Error al actualizar.';
        return back()->withErrors(['error' => $message])->withInput();
    }

    // ELIMINAR
    public function destroy($id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        if (! $this->canUseReacciones($token, 'ELIMINAR')) {
            return $this->denyByPermission('No tienes permiso para eliminar reacciones adversas.');
        }

        $response = Http::withToken($token)
            ->delete("http://localhost:3000/api/reacciones-adversas/$id");

        if ($response->successful()) {
            return redirect()
                ->route('reacciones_adversas.index')
                ->with('success', 'Reacción eliminada.');
        }

        $message = $response->json('message') ?: 'Error al eliminar.';

        return back()->withErrors(['error' => $message]);
    }

    // DETALLES FORM
    public function detallesForm($id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        if (! $this->canUseReacciones($token, 'ACTUALIZAR')) {
            return $this->denyByPermission('No tienes permiso para editar detalles de reacciones adversas.');
        }

        $response = Http::withToken($token)
            ->get("http://localhost:3000/api/reacciones-adversas/{$id}");

        $cabecera = $response->successful()
            ? ((array) ($response->json()['cabecera'] ?? []))
            : [];

        $detalles = $response->successful()
            ? ((array) ($response->json()['detalles'] ?? []))
            : [];

        $detalleInicial = null;

        if (!empty($detalles)) {
            $detalleInicial = (array) $detalles[0];
        } elseif (!empty($cabecera['id_paciente'])) {
            // Si aún no se guardaron detalles RAM, precargar desde el último registro PX.
            $detalleInicial = (array) (DB::table('tbl_far_prescripcion as pr')
                ->join('tbl_far_prescripcion_detalle as pd', 'pd.id_prescripcion', '=', 'pr.id_prescripcion')
                ->leftJoin('tbl_far_medicamento as med', 'med.id_medicamento', '=', 'pd.id_medicamento')
                ->leftJoin('tbl_far_lote as l', 'l.id_lote', '=', 'pd.id_lote')
                ->where('pr.id_paciente', (int) $cabecera['id_paciente'])
                ->whereRaw("UPPER(COALESCE(pr.observaciones, '')) LIKE '%REGISTRO PX%'")
                ->orderByDesc('pr.id_prescripcion')
                ->select(
                    'pd.id_medicamento',
                    'pd.id_lote',
                    'pd.dosis_instrucciones',
                    DB::raw('pd.via_administracion as via_administracion'),
                    DB::raw('pd.fecha_inicio_uso as fecha_inicio_uso'),
                    DB::raw('pd.fecha_fin_uso as fecha_fin_uso'),
                    'med.nombre_comercial',
                    'l.numero_lote'
                )
                ->first() ?? []);
        }

        return view('reacciones_adversas.detalles', [
            'id_reaccion' => $id,
            'reaccion' => $cabecera,
            'detalleInicial' => $detalleInicial,
            'usuario' => AuthenticatedUser::fromToken($token),
        ]);
    }

    // GUARDAR DETALLES
    public function detallesStore(Request $request, $id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        if (! $this->canUseReacciones($token, 'ACTUALIZAR')) {
            return $this->denyByPermission('No tienes permiso para guardar detalles de reacciones adversas.');
        }

        $request->validate([
            'detalles' => 'required|array|min:1',
            'detalles.*.medicamento' => 'nullable|string|max:150',
            'detalles.*.dosis_posologia' => 'nullable|string|max:100',
            'detalles.*.via_administracion' => 'nullable|string|max:100',
            'detalles.*.fecha_inicio_uso' => 'nullable|date',
            'detalles.*.fecha_fin_uso' => 'nullable|date',
        ]);

        foreach ((array) $request->input('detalles', []) as $detalle) {
            $inicio = data_get($detalle, 'fecha_inicio_uso');
            $fin = data_get($detalle, 'fecha_fin_uso');
            if ($inicio && $fin && $fin < $inicio) {
                return back()->withErrors(['error' => 'La fecha fin de uso no puede ser menor que la fecha inicio de uso.'])->withInput();
            }
        }

        $response = Http::withToken($token)
            ->post("http://localhost:3000/api/reacciones-adversas/$id/detalles", $request->all());

        if ($response->successful()) {
            return redirect()
                ->route('reacciones_adversas.consecuencias.form', $id)
                ->with('success', 'Detalles guardados.');
        }

        return back()->withErrors(['error' => 'Error guardando detalles.']);
    }

    // CONSECUENCIAS FORM
    public function consecuenciasForm($id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        if (! $this->canUseReacciones($token, 'ACTUALIZAR')) {
            return $this->denyByPermission('No tienes permiso para editar consecuencias de reacciones adversas.');
        }

        return view('reacciones_adversas.consecuencias', [
            'id_reaccion' => $id,
            'usuario' => AuthenticatedUser::fromToken($token),
        ]);
    }

    // GUARDAR CONSECUENCIAS
    public function consecuenciasStore(Request $request, $id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        if (! $this->canUseReacciones($token, 'ACTUALIZAR')) {
            return $this->denyByPermission('No tienes permiso para guardar consecuencias de reacciones adversas.');
        }

        $response = Http::withToken($token)
            ->post("http://localhost:3000/api/reacciones-adversas/$id/consecuencias", $request->all());

        if ($response->successful()) {
            return redirect()
                ->route('reacciones_adversas.show', $id)
                ->with('success', 'Consecuencias guardadas.')
                ->with('report_prompt', [
                    'title' => '¿Desea generar reporte?',
                    'text' => 'Se registró la reacción adversa correctamente.',
                    'confirm_text' => 'Sí, generar reporte',
                    'cancel_text' => 'No, ir al inicio',
                    'yes_url' => route('reacciones_adversas.print', $id),
                    'no_url' => route('dashboard'),
                ]);
        }

        return back()->withErrors(['error' => 'Error guardando consecuencias.']);
    }

    // IMPRIMIR (Vista HTML)
    public function print(Request $request, $id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        if (! $this->canUseReacciones($token, 'VISUALIZAR')) {
            return $this->denyByPermission('No tienes permiso para visualizar reacciones adversas.');
        }

        $backRoute = $this->resolveSafeReturnUrl($request, route('reacciones_adversas.index'));

        $response = Http::withToken($token)
            ->get("http://localhost:3000/api/reacciones-adversas/$id");

        $data = $response->json();

        return view('reacciones_adversas.print', [
            'reaccion' => $data['cabecera'] ?? [],
            'detalles' => $data['detalles'] ?? [],
            'consecuencias' => $data['consecuencias'] ?? [],
            'backRoute' => $backRoute,
        ]);
    }

    // REPORTE INDIVIDUAL (PDF)
    public function reporteIndividual(Request $request, $id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        if (! $this->canUseReacciones($token, 'VISUALIZAR')) {
            return $this->denyByPermission('No tienes permiso para generar reportes de reacciones adversas.');
        }

        $returnUrl = $this->resolveSafeReturnUrl($request, route('reacciones_adversas.index'));

        // DomPDF needs GD for some rendering paths (images/fonts). If GD is not
        // available in local PHP, fallback to the browser print view.
        if (!extension_loaded('gd')) {
            return redirect()
                ->route('reacciones_adversas.print', ['id' => $id, 'return_url' => $returnUrl])
                ->withErrors(['error' => 'No se pudo generar el PDF porque falta la extensión GD en PHP. Se abrió la vista de impresión como alternativa.']);
        }

        $response = Http::withToken($token)
            ->get("http://localhost:3000/api/reacciones-adversas/$id");

        if (! $response->successful()) {
            return back()->withErrors(['error' => 'No se pudo obtener la información para el reporte.']);
        }

        $data = $response->json();
        $reaccion = $data['cabecera'] ?? [];
        $detalles = $data['detalles'] ?? [];
        $consecuencias = $data['consecuencias'] ?? [];

        try {
            $pdf = Pdf::loadView('reacciones_adversas.pdf_individual', [
                'reaccion' => $reaccion,
                'detalles' => $detalles,
                'consecuencias' => $consecuencias,
                'fecha' => now()->translatedFormat('d \d\e F \d\e Y')
            ]);

            $fileName = 'Reaccion_Adversa_' . ($reaccion['id_reaccion'] ?? 'sin_id') . '.pdf';
            return $pdf->stream($fileName);
        } catch (\Throwable $e) {
            return redirect()
                ->route('reacciones_adversas.print', ['id' => $id, 'return_url' => $returnUrl])
                ->withErrors(['error' => 'No se pudo generar el PDF en este entorno. Se abrió la vista de impresión como alternativa.']);
        }
    }

    // BUSCAR PACIENTES
    public function buscarPacientes(Request $request)
    {
        $response = Http::get('http://localhost:3000/api/reacciones-adversas/pacientes/buscar', [
            'q' => $request->q
        ]);

        return response()->json($response->json());
    }

    public function buscarMedicos(Request $request)
    {
        $response = Http::get('http://localhost:3000/api/reacciones-adversas/medicos/buscar', [
            'q' => $request->q
        ]);

        return response()->json($response->json());
    }

    public function buscarMedicamentos(Request $request)
    {
        $token = $this->getToken();

        $response = Http::withToken($token)
            ->get('http://localhost:3000/api/reacciones-adversas/medicamentos/buscar', [
                'q' => $request->q
            ]);

        return response()->json($response->json());
    }

    public function buscarLotes(Request $request)
    {
        $response = Http::get('http://localhost:3000/api/reacciones-adversas/lotes/buscar', [
            'q' => $request->q,
            'id_medicamento' => $request->id_medicamento
        ]);

        return response()->json($response->json());
    }

    public function bandejaPacientes()
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json(['message' => 'Sesión no válida.'], 401);
        }

        return response()->json($this->getBandejaPacientes($token));
    }

    public function marcarPendienteVisto($id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) {
            return response()->json(['message' => 'Sesión no válida.'], 401);
        }

        $response = Http::withToken($token)
            ->post("{$this->apiUrl}/reacciones-adversas/bandeja-pacientes/{$id}/marcar-visto");

        return response()->json(
            $response->json() ?: ['message' => 'No se pudo procesar la solicitud.'],
            $response->status()
        );
    }

    // REPORTE
    public function reporteGeneral(Request $request)
    {
        $reacciones = DB::table('tbl_far_reaccion_adversa as ra')
            ->join('tbl_far_paciente as p', 'p.id_paciente', '=', 'ra.id_paciente')
            ->join('tbl_far_medico as m', 'm.id_medico', '=', 'ra.id_medico')
            ->leftJoin('tbl_seg_usuario as u', 'u.id_usuario', '=', 'm.id_usuario')
            ->select(
                'ra.id_reaccion',
                'ra.descripcion_reaccion',
                'ra.fecha_inicio_reaccion',
                'ra.fecha_fin_reaccion',
                'ra.estado',
                // La tabla tbl_far_paciente no tiene nombre_completo; usamos nombre directamente
                'p.nombre as paciente',
                'p.numero_expediente',
                // El médico se guarda en tbl_seg_usuario (nombre + apellido)
                DB::raw("CONCAT(u.nombre, ' ', u.apellido) as medico")
            )
            ->orderByDesc('ra.id_reaccion')
            ->get();

        $viewData = [
            'reacciones' => $reacciones,
            'fechaGeneracion' => Carbon::now()->format('d/m/Y h:i A'),
            'totalRegistros' => $reacciones->count()
        ];

        // DomPDF requiere GD para ciertos recursos (imagenes/fuentes). Si no esta
        // disponible, mostramos una vista HTML para guardar desde el navegador.
        if (!extension_loaded('gd')) {
            return view('reacciones_adversas.reportes', $viewData + [
                'htmlPreview' => true,
            ]);
        }

        try {
            $pdf = Pdf::loadView('reacciones_adversas.reportes', $viewData);
            return $pdf->stream('listado_general_reacciones_adversas.pdf');
        } catch (\Throwable $e) {
            return view('reacciones_adversas.reportes', $viewData + [
                'htmlPreview' => true,
            ]);
        }
    }

    public function estadisticasPanel()
    {
        $tieneMedicamentoManual = Schema::hasColumn('tbl_far_reaccion_detalle', 'medicamento');

        $exprMedicamento = $tieneMedicamentoManual
            ? "COALESCE(NULLIF(TRIM(d.medicamento), ''), med.nombre_comercial, 'SIN NOMBRE')"
            : "COALESCE(med.nombre_comercial, 'SIN NOMBRE')";

        $productos = DB::table('tbl_far_reaccion_detalle as d')
            ->leftJoin('tbl_far_medicamento as med', 'med.id_medicamento', '=', 'd.id_medicamento')
            ->selectRaw($exprMedicamento . ' as medicamento, COUNT(*) as total_registros')
            ->groupByRaw($exprMedicamento)
            ->orderByDesc('total_registros')
            ->orderBy('medicamento')
            ->limit(50)
            ->get();

        $pacientes = DB::table('tbl_far_reaccion_detalle as d')
            ->join('tbl_far_reaccion_adversa as ra', 'ra.id_reaccion', '=', 'd.id_reaccion')
            ->join('tbl_far_paciente as p', 'p.id_paciente', '=', 'ra.id_paciente')
            ->select('p.id_paciente', 'p.numero_expediente', 'p.nombre as paciente')
            ->selectRaw('COUNT(*) as total_medicamentos')
            ->selectRaw('MAX(ra.id_reaccion) as id_reaccion')
            ->selectRaw('MAX(ra.fecha_inicio_reaccion) as fecha_ingreso')
            ->selectRaw("SUBSTRING_INDEX(GROUP_CONCAT(COALESCE(ra.descripcion_reaccion, '') ORDER BY ra.fecha_inicio_reaccion DESC SEPARATOR '||'), '||', 1) as descripcion_reaccion")
            ->groupBy('p.id_paciente', 'p.numero_expediente', 'p.nombre')
            ->orderByDesc('total_medicamentos')
            ->orderBy('p.nombre')
            ->limit(50)
            ->get();

        return response()->json([
            'total_productos_asociados' => $productos->count(),
            'productos' => $productos,
            'pacientes' => $pacientes,
        ]);
    }
}