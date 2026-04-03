<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf; // Importación para los Reportes

class UserController extends Controller
{
    private $apiUrl = 'http://localhost:3000/api';

    private function getModuloCatalogo(): array
    {
        return [
            1 => 'Inventario',
            3 => 'Reacciones Adversas',
            4 => 'Gestion',
            5 => 'Bitácora',
            8 => 'Roles',
            10 => 'Backup DB',
            11 => 'Parámetros',
            12 => 'Preguntas',
            13 => 'Permisos',
        ];
    }

    private function normalizeRoleName(string $roleName): string
    {
        $upper = strtoupper(trim($roleName));

        $map = [
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'Ü' => 'U',
        ];

        return strtr($upper, $map);
    }

    private function buildDefaultPermissionsByRoleName(string $roleName): array
    {
        $allActions = $this->getAccionCatalogo();
        $catalog = $this->getModuloCatalogo();
        $role = $this->normalizeRoleName($roleName);

        // Plantilla solicitada por rol.
        if (str_contains($role, 'ADMINISTRADOR')) {
            $all = [];
            foreach (array_keys($catalog) as $moduleId) {
                $all[(int) $moduleId] = $allActions;
            }
            return $all;
        }

        if (str_contains($role, 'JEFE')) {
            return [
                1 => $allActions,
                3 => $allActions,
            ];
        }

        if (
            str_contains($role, 'ENFERMERO') ||
            str_contains($role, 'FARMACEUTICO') ||
            str_contains($role, 'MEDICO')
        ) {
            return [
                1 => ['VISUALIZAR'],
                3 => $allActions,
            ];
        }

        return [];
    }

    private function getAccionCatalogo(): array
    {
        return ['VISUALIZAR', 'GUARDAR', 'ACTUALIZAR', 'ELIMINAR'];
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

        try {
            $perfil = Http::withToken($token)->get("{$this->apiUrl}/perfil");
            if (in_array($perfil->status(), [401, 403], true)) {
                session()->forget(['jwt_token', 'security_verified', 'user_id', 'usuario', 'correo']);
                return redirect()->route('login')
                    ->withCookie(cookie()->forget('jwt_token'))
                    ->withErrors(['error' => 'Tu sesión expiró. Inicia sesión nuevamente.']);
            }
        } catch (\Throwable $e) {
        }

        return $token;
    }

    private function getNumericParametro(string $nombre, int $default): int
    {
        try {
            $row = DB::table('tbl_seg_parametro')
                ->whereRaw('UPPER(nombre_parametro) = ?', [strtoupper($nombre)])
                ->whereRaw("UPPER(COALESCE(estado, 'ACTIVO')) = 'ACTIVO'")
                ->orderByDesc('id_parametro')
                ->first(['valor']);

            $value = (int) ($row->valor ?? 0);
            return $value > 0 ? $value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    private function getUsernameConstraints(): array
    {
        $min = $this->getNumericParametro('MIN_USUARIO', 1);
        $max = $this->getNumericParametro('MAX_USUARIO', 50);

        $min = max(1, $min);
        $max = max($min, $max);

        return [
            'min_usuario' => $min,
            'max_usuario' => $max,
        ];
    }

    private function getPasswordConstraints(): array
    {
        $min = $this->getNumericParametro('MIN_CONTRASENA', 5);
        $max = $this->getNumericParametro('MAX_CONTRASENA', 10);

        $min = max(4, $min);
        $max = max($min, $max);

        return [
            'min_contrasena' => $min,
            'max_contrasena' => $max,
        ];
    }

    public function lista(Request $request)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $modo = $request->input('modo', 'gestion') === 'permisos' ? 'permisos' : 'gestion';
        $rutaListado = $modo === 'permisos' ? 'usuarios.permisos.index' : 'usuarios.lista';

        $search = $request->input('search');
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $sort = $request->input('sort', 'desc');

        $response = Http::withToken($token)->get("{$this->apiUrl}/usuarios", [
            'search' => $search,
            'limit' => $limit,
            'page' => $page,
            'sort' => $sort 
        ]);

        if (in_array($response->status(), [401, 403], true)) {
            session()->forget(['jwt_token', 'security_verified', 'user_id', 'usuario', 'correo']);
            return redirect()->route('login')
                ->withCookie(cookie()->forget('jwt_token'))
                ->withErrors(['error' => 'Tu sesión expiró. Inicia sesión nuevamente.']);
        }
        
        $perfil = Http::withToken($token)->get("{$this->apiUrl}/perfil");

        if ($response->successful()) {
            $resData = $response->json();
            $usuarios = isset($resData['data']) ? $resData['data'] : (is_array($resData) && !isset($resData['message']) ? $resData : []);
            $pagination = isset($resData['pagination']) ? $resData['pagination'] : null;

            return view('usuarios.lista', [
                'usuarios' => $usuarios,
                'pagination' => $pagination,
                'usuario_sesion' => $perfil->json()['datos_del_token'] ?? null,
                'search' => $search,
                'limit' => $limit,
                'sort' => $sort,
                'modo' => $modo,
                'rutaListado' => $rutaListado,
            ]);
        }
        
        return redirect()->route('dashboard')->withErrors(['error' => 'Error de conexión con la API o sesión expirada.']);
    }

    public function permisosIndex(Request $request)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $responseRoles = Http::withToken($token)->get("{$this->apiUrl}/roles", ['include_all' => 1]);
        $responseUsuarios = Http::withToken($token)->get("{$this->apiUrl}/usuarios", [
            'limit' => 1000,
            'page' => 1,
            'sort' => 'asc',
        ]);
        $perfil = Http::withToken($token)->get("{$this->apiUrl}/perfil");

        $roles = $responseRoles->successful() ? (array) $responseRoles->json() : [];
        $usuariosPayload = $responseUsuarios->successful() ? $responseUsuarios->json() : [];
        $usuarios = isset($usuariosPayload['data']) && is_array($usuariosPayload['data'])
            ? $usuariosPayload['data']
            : (is_array($usuariosPayload) ? $usuariosPayload : []);

        $selectedRoleId = (int) $request->input('rol_id', 0);
        if ($selectedRoleId <= 0 && !empty($roles)) {
            $selectedRoleId = (int) ($roles[0]['id_rol'] ?? 0);
        }

        $usuariosDelRol = array_values(array_filter($usuarios, fn($u) => (int) ($u['id_rol'] ?? 0) === $selectedRoleId));
        $usuarioBase = $usuariosDelRol[0] ?? null;
        $rolSeleccionado = collect($roles)->firstWhere('id_rol', $selectedRoleId) ?? [];
        $rolNombre = (string) ($rolSeleccionado['nombre'] ?? '');

        $permisosActuales = [];
        if ($usuarioBase && isset($usuarioBase['id_usuario'])) {
            $resPermisos = Http::withToken($token)->get("{$this->apiUrl}/usuarios/{$usuarioBase['id_usuario']}/permisos");
            $permisosActuales = $resPermisos->successful() ? (array) $resPermisos->json() : [];
        }

        if (empty($permisosActuales)) {
            $defaultByModule = $this->buildDefaultPermissionsByRoleName($rolNombre);

            $permisosActuales = collect($defaultByModule)
                ->flatMap(function ($acciones, $moduleId) {
                    return collect($acciones)->map(fn($accion) => [
                        'id_formulario' => (int) $moduleId,
                        'accion' => strtoupper((string) $accion),
                    ]);
                })
                ->values()
                ->toArray();
        }

        $modulosAsignados = collect($permisosActuales)->pluck('id_formulario')->map(fn($v) => (int) $v)->unique()->values()->toArray();
        $accionesAsignadasPorModulo = collect($permisosActuales)
            ->groupBy(fn($p) => (int) ($p['id_formulario'] ?? 0))
            ->map(function ($group) {
                return collect($group)
                    ->pluck('accion')
                    ->map(fn($v) => strtoupper((string) $v))
                    ->unique()
                    ->values()
                    ->toArray();
            })
            ->toArray();

        return view('usuarios.permisos_roles', [
            'roles' => $roles,
            'selectedRoleId' => $selectedRoleId,
            'usuario_sesion' => $perfil->json()['datos_del_token'] ?? null,
            'usuariosDelRol' => $usuariosDelRol,
            'modulosAsignados' => $modulosAsignados,
            'accionesAsignadasPorModulo' => $accionesAsignadasPorModulo,
            'moduloCatalogo' => $this->getModuloCatalogo(),
            'accionCatalogo' => $this->getAccionCatalogo(),
        ]);
    }

    public function permisosReporte(Request $request)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $responseRoles = Http::withToken($token)->get("{$this->apiUrl}/roles", ['include_all' => 1]);
        $responseUsuarios = Http::withToken($token)->get("{$this->apiUrl}/usuarios", [
            'limit' => 1000,
            'page' => 1,
            'sort' => 'asc',
        ]);
        $perfil = Http::withToken($token)->get("{$this->apiUrl}/perfil");

        $roles = $responseRoles->successful() ? (array) $responseRoles->json() : [];
        $usuariosPayload = $responseUsuarios->successful() ? $responseUsuarios->json() : [];
        $usuarios = isset($usuariosPayload['data']) && is_array($usuariosPayload['data'])
            ? $usuariosPayload['data']
            : (is_array($usuariosPayload) ? $usuariosPayload : []);

        $selectedRoleId = (int) $request->input('rol_id', 0);
        if ($selectedRoleId <= 0 && !empty($roles)) {
            $selectedRoleId = (int) ($roles[0]['id_rol'] ?? 0);
        }

        $rolSeleccionado = collect($roles)->firstWhere('id_rol', $selectedRoleId) ?? [];
        $rolNombre = strtoupper((string) ($rolSeleccionado['nombre'] ?? 'SIN ROL'));

        $usuariosDelRol = array_values(array_filter($usuarios, fn($u) => (int) ($u['id_rol'] ?? 0) === $selectedRoleId));
        $usuarioBase = $usuariosDelRol[0] ?? null;

        $permisosActuales = [];
        if ($usuarioBase && isset($usuarioBase['id_usuario'])) {
            $resPermisos = Http::withToken($token)->get("{$this->apiUrl}/usuarios/{$usuarioBase['id_usuario']}/permisos");
            $permisosActuales = $resPermisos->successful() ? (array) $resPermisos->json() : [];
        }

        if (empty($permisosActuales)) {
            $defaultByModule = $this->buildDefaultPermissionsByRoleName($rolNombre);
            $permisosActuales = collect($defaultByModule)
                ->flatMap(fn($acciones, $moduleId) => collect($acciones)->map(fn($accion) => [
                    'id_formulario' => (int) $moduleId,
                    'accion' => strtoupper((string) $accion),
                ]))
                ->values()
                ->toArray();
        }

        $modulosAsignados = collect($permisosActuales)
            ->pluck('id_formulario')
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values()
            ->toArray();

        $accionesAsignadasPorModulo = collect($permisosActuales)
            ->groupBy(fn($p) => (int) ($p['id_formulario'] ?? 0))
            ->map(fn($group) => collect($group)
                ->pluck('accion')
                ->map(fn($v) => strtoupper((string) $v))
                ->unique()
                ->values()
                ->toArray()
            )
            ->toArray();

        $admin = $perfil->json()['datos_del_token']['usuario'] ?? 'Sistema';

        $viewData = [
            'rolNombre'               => $rolNombre,
            'roles'                   => $roles,
            'selectedRoleId'          => $selectedRoleId,
            'usuariosDelRol'          => $usuariosDelRol,
            'modulosAsignados'        => $modulosAsignados,
            'accionesAsignadasPorModulo' => $accionesAsignadasPorModulo,
            'moduloCatalogo'          => $this->getModuloCatalogo(),
            'accionCatalogo'          => $this->getAccionCatalogo(),
            'admin'                   => $admin,
            'fecha'                   => now()->translatedFormat('d \d\e F \d\e Y'),
            'hasGd'                   => extension_loaded('gd'),
        ];

        return view('usuarios.permisos_pdf', $viewData + [
            'htmlPreview' => true,
            'backUrl' => route('usuarios.permisos.index', ['rol_id' => $selectedRoleId]),
        ]);
    }

    public function guardarPermisosPorRol(Request $request)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $rolId = (int) $request->input('rol_id', 0);
        if ($rolId <= 0) {
            return back()->withErrors(['error' => 'Selecciona un rol válido.']);
        }

        $responseUsuarios = Http::withToken($token)->get("{$this->apiUrl}/usuarios", [
            'limit' => 1000,
            'page' => 1,
            'sort' => 'asc',
        ]);

        $usuariosPayload = $responseUsuarios->successful() ? $responseUsuarios->json() : [];
        $usuarios = isset($usuariosPayload['data']) && is_array($usuariosPayload['data'])
            ? $usuariosPayload['data']
            : (is_array($usuariosPayload) ? $usuariosPayload : []);

        $usuariosDelRol = array_values(array_filter($usuarios, fn($u) => (int) ($u['id_rol'] ?? 0) === $rolId));
        if (empty($usuariosDelRol)) {
            return back()->withErrors(['error' => 'El rol seleccionado no tiene usuarios asignados.']);
        }

        $modulosSeleccionados = collect($request->input('modulos', []))
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values()
            ->toArray();

        $modulosSeleccionados = array_values(array_intersect($modulosSeleccionados, array_keys($this->getModuloCatalogo())));

        if (empty($modulosSeleccionados)) {
            return back()->withErrors(['error' => 'Selecciona al menos un módulo.']);
        }

        $accionesPermitidas = $this->getAccionCatalogo();
        $accionesPorModulo = $request->input('permisos', []);

        $modulosConAcciones = collect($accionesPorModulo)
            ->mapWithKeys(function ($acciones, $idModulo) use ($accionesPermitidas) {
                $id = (int) $idModulo;
                $accionesValidas = collect((array) $acciones)
                    ->map(fn($v) => strtoupper((string) $v))
                    ->filter(fn($accion) => in_array($accion, $accionesPermitidas, true))
                    ->unique()
                    ->values()
                    ->toArray();

                return [$id => $accionesValidas];
            })
            ->filter(fn($acciones) => !empty($acciones))
            ->keys()
            ->map(fn($v) => (int) $v)
            ->values()
            ->toArray();

        $modulosSinAcceso = array_values(array_diff($modulosConAcciones, $modulosSeleccionados));
        if (!empty($modulosSinAcceso)) {
            return back()->withInput()->withErrors([
                'error' => 'No se puede asignar Visualizar/Guardar/Actualizar/Eliminar si Acceso está apagado en el módulo.',
            ]);
        }

        $permisosBase = [];
        foreach ($modulosSeleccionados as $id_modulo) {
            $accionesModulo = collect($accionesPorModulo[$id_modulo] ?? [])
                ->map(fn($v) => strtoupper((string) $v))
                ->unique()
                ->values()
                ->toArray();

            $accionesModulo = array_values(array_intersect($accionesModulo, $accionesPermitidas));

            if (empty($accionesModulo)) {
                return back()->withErrors(['error' => 'Cada módulo seleccionado debe tener al menos una acción.']);
            }

            foreach ($accionesModulo as $accion) {
                $permisosBase[] = [
                    'id_formulario' => $id_modulo,
                    'accion' => $accion,
                ];
            }
        }

        foreach ($usuariosDelRol as $usuario) {
            $idUsuario = (int) ($usuario['id_usuario'] ?? 0);
            if ($idUsuario <= 0) {
                continue;
            }

            $payload = [
                'permisos' => array_map(fn($p) => [
                    'id_usuario' => $idUsuario,
                    'id_formulario' => $p['id_formulario'],
                    'accion' => $p['accion'],
                ], $permisosBase),
            ];

            $response = Http::withToken($token)->post("{$this->apiUrl}/usuarios/{$idUsuario}/permisos", $payload);
            if (!$response->successful()) {
                return back()->withErrors(['error' => 'No se pudieron aplicar los permisos al rol completo.']);
            }

            // Sincronizar permisos en BD de Laravel para cada usuario del rol
            try {
                DB::transaction(function () use ($idUsuario, $payload) {
                    // Borrar permisos antiguos del usuario
                    DB::table('tbl_seg_permisos')->where('id_usuario', $idUsuario)->delete();
                    
                    // Insertar nuevos permisos
                    if (!empty($payload['permisos'])) {
                        $permisosConTimestamp = array_map(function ($permiso) {
                            return array_merge($permiso, [
                                'created_at' => now(),
                            ]);
                        }, $payload['permisos']);
                        
                        DB::table('tbl_seg_permisos')->insert($permisosConTimestamp);
                    }
                });
            } catch (\Throwable $e) {
                // Si falla la sincronización local, log pero sigue (API ya guardó)
                \Log::warning("Error sincronizando permisos en BD local para usuario {$idUsuario}: {$e->getMessage()}");
            }
        }

        return redirect()->route('usuarios.permisos.index', ['rol_id' => $rolId])->with('success', 'Permisos aplicados al rol correctamente.');
    }

    public function create()
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;
        
        $responseRoles = Http::withToken($token)->get("{$this->apiUrl}/roles");
        $responsePerfil = Http::withToken($token)->get("{$this->apiUrl}/perfil");

        $usernameConstraints = $this->getUsernameConstraints();

        return view('usuarios.create', [
            'roles' => $responseRoles->successful() ? $responseRoles->json() : [],
            'usuario_sesion' => $responsePerfil->successful() ? $responsePerfil->json()['datos_del_token'] : null,
            'min_usuario' => $usernameConstraints['min_usuario'],
            'max_usuario' => $usernameConstraints['max_usuario'],
        ]);
    }

    public function store(Request $request)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $usernameConstraints = $this->getUsernameConstraints();

        $request->merge([
            'usuario' => strtoupper(trim((string) $request->input('usuario'))),
            'correo' => strtolower(trim((string) $request->input('correo'))),
        ]);

        $request->validate([
            'usuario' => ['required', 'min:' . $usernameConstraints['min_usuario'], 'max:' . $usernameConstraints['max_usuario'], Rule::unique('tbl_seg_usuario', 'usuario')],
            'correo' => ['required', 'email', 'max:50', Rule::unique('tbl_seg_usuario', 'correo')],
            'nombre' => 'required|max:50',
            'apellido' => 'required|max:50',
            'rol' => 'nullable'
        ], [
            'usuario.unique' => 'El usuario ya está registrado.',
            'usuario.min' => 'El usuario debe tener al menos ' . $usernameConstraints['min_usuario'] . ' caracteres.',
            'usuario.max' => 'El usuario no puede exceder ' . $usernameConstraints['max_usuario'] . ' caracteres.',
            'correo.unique' => 'El correo ya está registrado.',
        ]);

        $response = Http::withToken($token)->post("{$this->apiUrl}/usuarios", $request->all());

        if ($response->successful()) {
            return redirect()->route('usuarios.lista')->with('success', 'Usuario creado correctamente.');
        }

        if ($response->status() === 409) {
            $apiErrors = $response->json('errors', []);
            if (!empty($apiErrors)) {
                return back()->withInput()->withErrors($apiErrors);
            }
        }

        return back()->withInput()->withErrors([
            'error' => $response->json('message') ?: 'Error al crear el usuario.'
        ]);
    }

    public function edit($id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $responseUsuario = Http::withToken($token)->get("{$this->apiUrl}/usuarios/{$id}");
        $responseRoles = Http::withToken($token)->get("{$this->apiUrl}/roles");
        $responsePerfil = Http::withToken($token)->get("{$this->apiUrl}/perfil");

        $usernameConstraints = $this->getUsernameConstraints();

        if ($responseUsuario->successful()) {
            return view('usuarios.edit', [
                'usuario' => $responseUsuario->json(),
                'roles' => $responseRoles->successful() ? $responseRoles->json() : [],
                'usuario_sesion' => $responsePerfil->successful() ? $responsePerfil->json()['datos_del_token'] : null,
                'min_usuario' => $usernameConstraints['min_usuario'],
                'max_usuario' => $usernameConstraints['max_usuario'],
            ]);
        }

        return redirect()->route('usuarios.lista')->withErrors(['error' => 'Usuario no encontrado']);
    }

    public function update(Request $request, $id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $usernameConstraints = $this->getUsernameConstraints();

        $request->merge([
            'usuario' => strtoupper(trim((string) $request->input('usuario'))),
            'correo' => strtolower(trim((string) $request->input('correo'))),
        ]);

        $request->validate([
            'usuario' => ['required', 'min:' . $usernameConstraints['min_usuario'], 'max:' . $usernameConstraints['max_usuario'], Rule::unique('tbl_seg_usuario', 'usuario')->ignore($id, 'id_usuario')],
            'correo' => ['required', 'email', 'max:50', Rule::unique('tbl_seg_usuario', 'correo')->ignore($id, 'id_usuario')],
            'nombre' => 'required|max:50',
            'apellido' => 'required|max:50',
            'estado' => 'required',
            'rol' => 'nullable'
        ], [
            'usuario.unique' => 'El usuario ya está registrado.',
            'usuario.min' => 'El usuario debe tener al menos ' . $usernameConstraints['min_usuario'] . ' caracteres.',
            'usuario.max' => 'El usuario no puede exceder ' . $usernameConstraints['max_usuario'] . ' caracteres.',
            'correo.unique' => 'El correo ya está registrado.',
        ]);

        $response = Http::withToken($token)->put("{$this->apiUrl}/usuarios/{$id}", $request->all());

        if ($response->successful()) {
            return redirect()->route('usuarios.lista')->with('success', 'Usuario actualizado correctamente.');
        }

        if ($response->status() === 409) {
            $apiErrors = $response->json('errors', []);
            if (!empty($apiErrors)) {
                return back()->withInput()->withErrors($apiErrors);
            }
        }

        return back()->withInput()->withErrors([
            'error' => $response->json('message') ?: 'Error al actualizar el usuario.'
        ]);
    }

    public function destroy($id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $response = Http::withToken($token)->delete("{$this->apiUrl}/usuarios/{$id}");

        if ($response->successful()) {
            return redirect()->route('usuarios.lista')->with('success', 'Usuario desactivado correctamente.');
        }

        return redirect()->route('usuarios.lista')->withErrors(['error' => 'Error al eliminar el usuario.']);
    }

    public function show($id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $response = Http::withToken($token)->get("{$this->apiUrl}/usuarios/{$id}");
        $perfil = Http::withToken($token)->get("{$this->apiUrl}/perfil");

        if ($response->successful()) {
            return view('usuarios.show', [
                'usuario' => $response->json(),
                'usuario_sesion' => $perfil->json()['datos_del_token'] ?? null
            ]);
        }

        return redirect()->route('usuarios.lista')->withErrors(['error' => 'No se pudo obtener el detalle.']);
    }

    // ==========================================
    // REPORTES PDF (DomPDF)
    // ==========================================

    public function reporte($id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $response = Http::withToken($token)->get("{$this->apiUrl}/usuarios/{$id}");
        $perfil = Http::withToken($token)->get("{$this->apiUrl}/perfil");

        if ($response->successful()) {
            $usuario = $response->json();
            $admin = $perfil->json()['datos_del_token']['usuario'] ?? 'Sistema';

            $pdf = Pdf::loadView('usuarios.pdf_individual', [
                'u' => $usuario,
                'admin' => $admin,
                'fecha' => now()->translatedFormat('d \d\e F \d\e Y')
            ]);

            return $pdf->stream('Ficha_Usuario_' . $usuario['usuario'] . '.pdf');
        }

        return back()->withErrors(['error' => 'No se pudo generar el reporte individual.']);
    }

    public function reporteGeneral()
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $response = Http::withToken($token)->get("{$this->apiUrl}/usuarios", ['limit' => 500]);
        $perfil = Http::withToken($token)->get("{$this->apiUrl}/perfil");

        if ($response->successful()) {
            $usuarios = $response->json();
            // Si la API devuelve el objeto con 'data', lo extraemos
            if(isset($usuarios['data'])) $usuarios = $usuarios['data'];
            
            $admin = $perfil->json()['datos_del_token']['usuario'] ?? 'Sistema';

            $pdf = Pdf::loadView('usuarios.pdf_general', [
                'usuarios' => $usuarios,
                'admin' => $admin,
                'fecha' => now()->translatedFormat('d \d\e F \d\e Y')
            ])->setPaper('a4', 'landscape');

            return $pdf->stream('Catalogo_Usuarios.pdf');
        }

        return back()->withErrors(['error' => 'No se pudo generar el reporte general.']);
    }

    // ==========================================
    // MÓDULO DE PERMISOS
    // ==========================================

    public function permisos($id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $response = Http::withToken($token)->get("{$this->apiUrl}/usuarios/{$id}");
        $perfil = Http::withToken($token)->get("{$this->apiUrl}/perfil");

        $resPermisos = Http::withToken($token)->get("{$this->apiUrl}/usuarios/{$id}/permisos");
        $permisosActuales = $resPermisos->successful() ? $resPermisos->json() : [];

        $modulosAsignados = collect($permisosActuales)->pluck('id_formulario')->map(fn($v) => (int) $v)->unique()->values()->toArray();
        $accionesAsignadasPorModulo = collect($permisosActuales)
            ->groupBy(fn($p) => (int) ($p['id_formulario'] ?? 0))
            ->map(function ($group) {
                return collect($group)
                    ->pluck('accion')
                    ->map(fn($v) => strtoupper((string) $v))
                    ->unique()
                    ->values()
                    ->toArray();
            })
            ->toArray();

        if ($response->successful()) {
            $usuario = $response->json();

            return view('usuarios.permisos', [
                'usuario' => $usuario,
                'usuario_sesion' => $perfil->json()['datos_del_token'] ?? null,
                'modulosAsignados' => $modulosAsignados,
                'accionesAsignadasPorModulo' => $accionesAsignadasPorModulo,
                'moduloCatalogo' => $this->getModuloCatalogo(),
                'accionCatalogo' => $this->getAccionCatalogo(),
            ]);
        }

        return redirect()->route('usuarios.lista')->withErrors(['error' => 'No se pudo cargar el módulo de permisos.']);
    }

    public function guardarPermisos(Request $request, $id)
    {
        $token = $this->checkToken();
        if ($token instanceof \Illuminate\Http\RedirectResponse) return $token;

        $modulosSeleccionados = collect($request->input('modulos', []))
            ->map(fn($v) => (int) $v)
            ->unique()
            ->values()
            ->toArray();

        $modulosSeleccionados = array_values(array_intersect($modulosSeleccionados, array_keys($this->getModuloCatalogo())));

        if (empty($modulosSeleccionados)) {
            return back()->withErrors(['error' => 'Este usuario no tiene módulos asignados. Selecciona al menos un módulo.']);
        }

        $accionesPermitidas = $this->getAccionCatalogo();
        $accionesPorModulo = $request->input('permisos', []);

        $modulosConAcciones = collect($accionesPorModulo)
            ->mapWithKeys(function ($acciones, $idModulo) use ($accionesPermitidas) {
                $id = (int) $idModulo;
                $accionesValidas = collect((array) $acciones)
                    ->map(fn($v) => strtoupper((string) $v))
                    ->filter(fn($accion) => in_array($accion, $accionesPermitidas, true))
                    ->unique()
                    ->values()
                    ->toArray();

                return [$id => $accionesValidas];
            })
            ->filter(fn($acciones) => !empty($acciones))
            ->keys()
            ->map(fn($v) => (int) $v)
            ->values()
            ->toArray();

        $modulosSinAcceso = array_values(array_diff($modulosConAcciones, $modulosSeleccionados));
        if (!empty($modulosSinAcceso)) {
            return back()->withInput()->withErrors([
                'error' => 'No se puede asignar Visualizar/Guardar/Actualizar/Eliminar si Acceso está apagado en el módulo.',
            ]);
        }

        $permisosParaGuardar = [];
        foreach ($modulosSeleccionados as $id_modulo) {
            $accionesModulo = collect($accionesPorModulo[$id_modulo] ?? [])
                ->map(fn($v) => strtoupper((string) $v))
                ->unique()
                ->values()
                ->toArray();

            $accionesModulo = array_values(array_intersect($accionesModulo, $accionesPermitidas));

            if (empty($accionesModulo)) {
                return back()->withErrors(['error' => 'Cada módulo seleccionado debe tener al menos una acción.']);
            }

            foreach ($accionesModulo as $accion) {
                $permisosParaGuardar[] = [
                    'id_usuario' => $id,
                    'id_formulario' => $id_modulo,
                    'accion' => $accion
                ];
            }
        }

        $response = Http::withToken($token)->post("{$this->apiUrl}/usuarios/{$id}/permisos", [
            'permisos' => $permisosParaGuardar
        ]);

        if ($response->successful()) {
            // Sincronizar permisos en BD de Laravel para que se actualicen inmediatamente
            try {
                DB::transaction(function () use ($id, $permisosParaGuardar) {
                    // Borrar permisos antiguos del usuario
                    DB::table('tbl_seg_permisos')->where('id_usuario', $id)->delete();
                    
                    // Insertar nuevos permisos
                    if (!empty($permisosParaGuardar)) {
                        $permisosConTimestamp = array_map(function ($permiso) {
                            return array_merge($permiso, [
                                'created_at' => now(),
                            ]);
                        }, $permisosParaGuardar);
                        
                        DB::table('tbl_seg_permisos')->insert($permisosConTimestamp);
                    }
                });
            } catch (\Throwable $e) {
                // Si falla la sincronización local, log pero sigue (API ya guardó)
                \Log::warning("Error sincronizando permisos en BD local: {$e->getMessage()}");
            }
            
            return redirect()->route('usuarios.permisos.index')->with('success', 'Permisos asignados correctamente.');
        }

        return back()->withErrors(['error' => 'Error al guardar permisos.']);
    }
}