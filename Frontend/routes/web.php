<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReaccionesAdversasController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SecurityQuestionsController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\ParametroController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ConfiguracionAccesosController;
use App\Http\Middleware\AdminOnly;
use App\Support\AuthenticatedUser;

// 1. Ruta principal
Route::get('/', function () {
    return redirect('/login');
});

// 2. Rutas del Login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// 2FA Verification
Route::get('/verificar-2fa', [AuthController::class, 'show2FAForm'])->name('verificar.2fa');
Route::post('/verificar-2fa', [AuthController::class, 'verify2FA'])->name('verificar.2fa.post');

// Pantalla de registro
Route::get('/registro', function () {
    return view('auth.register');
})->name('registro');

// Pantalla cambiar contraseña (requiere verificación de preguntas de seguridad)
Route::get('/cambiar-password', [SecurityQuestionsController::class, 'showChangePassword'])->name('cambiar-password');

// Recuperar contraseña
Route::get('/recuperar-contrasena', [AuthController::class, 'showRecoverForm'])->name('recuperar-contrasena');
Route::post('/recuperar-contrasena', [AuthController::class, 'handleRecoveryUser'])->name('recuperar-contrasena.validar');
Route::get('/recuperacion/opciones', [AuthController::class, 'showRecoveryOptions'])->name('recuperacion.opciones');
Route::post('/recuperacion/correo', [AuthController::class, 'recoverByEmail'])->name('recuperacion.correo');


// ==============================
// 3. DASHBOARD
// ==============================
Route::get('/dashboard', function () {
    if (session('pending_2fa', false)) {
        return redirect()->route('verificar.2fa');
    }

    $token = request()->cookie('jwt_token') ?: session('jwt_token');

    if ($token && !session()->has('jwt_token')) {
        session(['jwt_token' => $token]);
    }

    if (! session('security_verified', false)) {
        return redirect()->route('login');
    }

    $usuario = AuthenticatedUser::fromToken($token);

    if ($token) {
        try {
            if (!empty($usuario['id'])) {
                $perfilResp = Http::withToken($token)->get("http://localhost:3000/api/usuarios/{$usuario['id']}");
                if ($perfilResp->successful() && data_get($perfilResp->json(), 'estado') === 'NUEVO') {
                    return redirect()->route('cambiar-password');
                }
            }
        } catch (\Throwable $e) {}
    }

    return view('dashboard', [
        'usuario' => $usuario
    ]);
})->name('dashboard');

// ==============================
// 3.1 INVENTARIO
// ==============================
Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario');
Route::get('/inventario/datos', [InventarioController::class, 'getData'])->name('inventario.datos');
Route::post('/inventario', [InventarioController::class, 'store'])->name('inventario.store');
Route::put('/inventario/{id}', [InventarioController::class, 'update'])->name('inventario.update');
Route::delete('/inventario/{id}', [InventarioController::class, 'destroy'])->name('inventario.destroy');
Route::post('/inventario/bulk', [InventarioController::class, 'bulk'])->name('inventario.bulk');

// ==============================
// 3.1 PERFIL DE USUARIO (DESDE MODAL)
// ==============================
Route::post('/perfil/actualizar', function (Request $request) {
    $token = request()->cookie('jwt_token') ?: session('jwt_token');
    if (! $token) {
        return redirect('/login');
    }

    $request->validate([
        'nombre' => 'required|string|max:50',
        'apellido' => 'required|string|max:50',
        'correo' => 'required|email|max:100',
        'telefono' => 'nullable|string|max:50',
    ]);

    try {
        $perfilResp = Http::withToken($token)->get('http://localhost:3000/api/perfil');
        if (! $perfilResp->successful()) {
            return back()->withErrors(['error' => 'No se pudo validar la sesión.'])->withInput()->with('open_profile_modal', true);
        }

        $tokenData = $perfilResp->json()['datos_del_token'] ?? [];
        $idUsuario = $tokenData['id'] ?? null;

        if (! $idUsuario) {
            return back()->withErrors(['error' => 'No se pudo identificar el usuario.'])->withInput()->with('open_profile_modal', true);
        }

        $usuarioResp = Http::withToken($token)->get("http://localhost:3000/api/usuarios/{$idUsuario}");
        if (! $usuarioResp->successful()) {
            return back()->withErrors(['error' => 'No se pudo cargar el perfil actual.'])->withInput()->with('open_profile_modal', true);
        }

        $actual = $usuarioResp->json();

        $payload = [
            'usuario' => $actual['usuario'] ?? ($tokenData['usuario'] ?? null),
            'correo' => $request->correo,
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'telefono' => $request->telefono,
            'estado' => $actual['estado'] ?? 'ACTIVO',
            'rol' => $actual['id_rol'] ?? null,
        ];

        $updateResp = Http::withToken($token)->put("http://localhost:3000/api/usuarios/{$idUsuario}", $payload);

        if (! $updateResp->successful()) {
            $msg = $updateResp->json()['message'] ?? 'No se pudo actualizar el perfil.';
            return back()->withErrors(['error' => $msg])->withInput()->with('open_profile_modal', true);
        }

        return back()->with('success', 'Perfil actualizado correctamente.')->with('open_profile_modal', true);
    } catch (\Throwable $e) {
        return back()->withErrors(['error' => 'Error de comunicación con el servidor.'])->withInput()->with('open_profile_modal', true);
    }
})->name('perfil.actualizar');

Route::post('/perfil/cambiar-password', function (Request $request) {
    $token = request()->cookie('jwt_token') ?: session('jwt_token');
    if (! $token) {
        return redirect('/login');
    }

    $request->validate([
        'contrasena_actual' => 'required|string',
        'contrasena_nueva' => 'required|string|min:8|confirmed',
    ]);

    try {
        $response = Http::withToken($token)->post('http://localhost:3000/api/change-password', [
            'contrasena_actual' => $request->contrasena_actual,
            'contrasena_nueva' => $request->contrasena_nueva,
            'confirmar_contrasena' => $request->contrasena_nueva_confirmation,
        ]);

        if (! $response->successful()) {
            $msg = $response->json()['message'] ?? 'No se pudo cambiar la contraseña.';
            return back()->withErrors(['error' => $msg])->with('open_profile_modal', true);
        }

        return back()->with('success', 'Contraseña actualizada correctamente.')->with('open_profile_modal', true);
    } catch (\Throwable $e) {
        return back()->withErrors(['error' => 'Error de comunicación con el servidor.'])->with('open_profile_modal', true);
    }
})->name('perfil.cambiar-password');


// ==============================
// 4. MANTENIMIENTO DE USUARIOS
// ==============================
Route::prefix('usuarios')->middleware(AdminOnly::class)->group(function () {
    // Listado
    Route::get('/', [UserController::class, 'lista'])->name('usuarios.lista');
    Route::get('/permisos', [UserController::class, 'permisosIndex'])->name('usuarios.permisos.index');
    Route::post('/permisos', [UserController::class, 'guardarPermisosPorRol'])->name('usuarios.permisos.role.store');
    Route::get('/permisos/reporte', [UserController::class, 'permisosReporte'])->name('usuarios.permisos.reporte');
    Route::get('/index', function () {
        return redirect()->route('usuarios.lista');
    })->name('usuarios.index');

    // Crear
    Route::get('/crear', [UserController::class, 'create'])->name('usuarios.create');
    Route::post('/', [UserController::class, 'store'])->name('usuarios.store');

    // Ver
    Route::get('/{id}', [UserController::class, 'show'])->name('usuarios.show');

    // Editar
    Route::get('/{id}/editar', [UserController::class, 'edit'])->name('usuarios.edit');
    Route::put('/{id}', [UserController::class, 'update'])->name('usuarios.update');

    // Eliminar (desactivar)
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');

    // Reportes
    Route::get('/{id}/reporte', [UserController::class, 'reporte'])->name('usuarios.reporte');
    Route::get('/reporte/general', [UserController::class, 'reporteGeneral'])->name('usuarios.reporte_general');

    // Permisos
    Route::get('/{id}/permisos', [UserController::class, 'permisos'])->name('usuarios.permisos');
    Route::post('/{id}/permisos', [UserController::class, 'guardarPermisos'])->name('usuarios.permisos.store');
});

Route::prefix('roles')->middleware(AdminOnly::class)->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/', [RoleController::class, 'store'])->name('roles.store');
    Route::put('/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::patch('/{id}/estado', [RoleController::class, 'toggleStatus'])->name('roles.toggle');
});


// ==============================
// 5. CERRAR SESIÓN
// ==============================
Route::post('/logout', function () {
    $token = request()->cookie('jwt_token') ?: session('jwt_token');

    if ($token) {
        try {
            Http::withToken($token)->post('http://localhost:3000/api/logout');
        } catch (\Throwable $e) {
            // Si el backend no responde, igual cerramos sesión local para no bloquear al usuario.
        }
    }

    // Limpiar cookie + sesión
    session()->forget([
        'jwt_token',
        'security_verified',
        'user_id',
        'usuario',
        'correo',
        'recovery_via_email',
        'recovery_username',
        'pending_2fa',
        'pending_2fa_token',
    ]);
    return redirect('/login')
        ->withCookie(cookie()->forget('jwt_token'));
})->name('logout');


// ==============================
// 6. BITÁCORA
// ==============================
Route::get('/bitacora', function () {
    $token = request()->cookie('jwt_token') ?: session('jwt_token');

    if (!$token) {
        return redirect('/login');
    }

    $perPage = (int) request('per_page', 10);
    if (!in_array($perPage, [5, 10, 15], true)) {
        $perPage = 10;
    }
    $currentPage = max(1, (int) request('page', 1));
    $usuario  = AuthenticatedUser::fromToken($token);

    // Filtros opcionales
    $filtros = array_filter([
        'usuario'     => request('usuario'),
        'accion'      => request('accion'),
        'fecha_desde' => request('fecha_desde'),
        'fecha_hasta' => request('fecha_hasta'),
    ]);

    try {
        $response = Http::withToken($token)->get('http://localhost:3000/api/bitacora', $filtros);
    } catch (\Throwable $e) {
        return redirect('/dashboard')
            ->withErrors(['error' => 'No se pudo cargar la bitácora.']);
    }

    if ($response->successful()) {
        $allRegistros = $response->json();
        $allRegistros = is_array($allRegistros) ? array_values($allRegistros) : [];
        $total = count($allRegistros);
        $offset = ($currentPage - 1) * $perPage;
        $pageItems = array_slice($allRegistros, $offset, $perPage);

        $registros = new LengthAwarePaginator(
            $pageItems,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('bitacora', [
            'registros' => $registros,
            'usuario'   => $usuario,
            'filtros'   => $filtros,
        ]);
    }

    return redirect('/dashboard')
            ->withErrors(['error' => 'No se pudo cargar la bitácora.']);
})->middleware(AdminOnly::class)->name('bitacora');

Route::get('/bitacora/reporte', [BitacoraController::class, 'reporte'])->middleware(AdminOnly::class)->name('bitacora.reporte');


// ==============================
// 6.1 BACKUPS
// ==============================
Route::prefix('backup')->middleware(AdminOnly::class)->group(function () {
    Route::get('/', [BackupController::class, 'index'])->name('backup.index');
    Route::get('/reporte', [BackupController::class, 'reporte'])->name('backup.reporte');
    Route::post('/generar', [BackupController::class, 'create'])->name('backup.create');
    Route::get('/descargar/{fileName}', [BackupController::class, 'download'])
        ->where('fileName', '.*')
        ->name('backup.download');
    Route::post('/restaurar/{fileName}', [BackupController::class, 'restore'])
        ->where('fileName', '.*')
        ->name('backup.restore');
    Route::post('/forzar-sesiones', [BackupController::class, 'forceLogout'])->name('backup.force-logout');
});

Route::prefix('parametros')->middleware(AdminOnly::class)->group(function () {
    Route::get('/', [ParametroController::class, 'index'])->name('parametros.index');
    Route::post('/', [ParametroController::class, 'store'])->name('parametros.store');
    Route::put('/{id}', [ParametroController::class, 'update'])->name('parametros.update');
});

Route::prefix('configuraciones-accesos')->middleware(AdminOnly::class)->group(function () {
    Route::get('/', [ConfiguracionAccesosController::class, 'index'])->name('configuraciones.accesos.index');
    Route::post('/{tipo}', [ConfiguracionAccesosController::class, 'store'])->name('configuraciones.accesos.store');
    Route::put('/{tipo}/{id}', [ConfiguracionAccesosController::class, 'update'])->name('configuraciones.accesos.update');
    Route::delete('/{tipo}/{id}', [ConfiguracionAccesosController::class, 'destroy'])->name('configuraciones.accesos.destroy');
});

// ==============================
// 7. REACCIONES ADVERSAS
// ==============================
Route::get('/reacciones', function () {
    return redirect()->route('reacciones_adversas.index');
})->name('reacciones.legacy');

Route::prefix('reacciones-adversas')->group(function () {

    // Listar
    Route::get('/', [ReaccionesAdversasController::class, 'index'])
        ->name('reacciones_adversas.index');

    // Crear
    Route::get('/crear', [ReaccionesAdversasController::class, 'create'])
        ->name('reacciones_adversas.create');
    Route::post('/', [ReaccionesAdversasController::class, 'store'])
        ->name('reacciones_adversas.store');

    // Ver
    Route::get('/{id}', [ReaccionesAdversasController::class, 'show'])
        ->name('reacciones_adversas.show');

    // Editar
    Route::get('/{id}/editar', [ReaccionesAdversasController::class, 'edit'])
        ->name('reacciones_adversas.edit');
    Route::put('/{id}', [ReaccionesAdversasController::class, 'update'])
        ->name('reacciones_adversas.update');

    // Eliminar
    Route::delete('/{id}', [ReaccionesAdversasController::class, 'destroy'])
        ->name('reacciones_adversas.destroy');

    // Imprimir (vista web)
    Route::get('/{id}/imprimir', [ReaccionesAdversasController::class, 'print'])
        ->name('reacciones_adversas.print');

    // Reporte PDF individual
    Route::get('/{id}/reporte', [ReaccionesAdversasController::class, 'reporteIndividual'])
        ->name('reacciones_adversas.reporte');

    // Detalles
    Route::get('/{id}/detalles', [ReaccionesAdversasController::class, 'detallesForm'])
        ->name('reacciones_adversas.detalles.form');
    Route::post('/{id}/detalles', [ReaccionesAdversasController::class, 'detallesStore'])
        ->name('reacciones_adversas.detalles.store');

    // Consecuencias
    Route::get('/{id}/consecuencias', [ReaccionesAdversasController::class, 'consecuenciasForm'])
        ->name('reacciones_adversas.consecuencias.form');
    Route::post('/{id}/consecuencias', [ReaccionesAdversasController::class, 'consecuenciasStore'])
        ->name('reacciones_adversas.consecuencias.store');

    // Búsquedas AJAX
    Route::get('/ajax/pacientes', [ReaccionesAdversasController::class, 'buscarPacientes'])
        ->name('ajax.pacientes');
    Route::get('/ajax/medicos', [ReaccionesAdversasController::class, 'buscarMedicos'])
        ->name('ajax.medicos');
    Route::get('/ajax/bandeja-pacientes', [ReaccionesAdversasController::class, 'bandejaPacientes'])
        ->name('ajax.bandeja_pacientes');
    Route::post('/ajax/bandeja-pacientes/{id}/visto', [ReaccionesAdversasController::class, 'marcarPendienteVisto'])
        ->name('ajax.bandeja_pacientes.visto');
    Route::get('/ajax/estadisticas-panel', [ReaccionesAdversasController::class, 'estadisticasPanel'])
        ->name('ajax.reacciones_estadisticas_panel');
    Route::get('/ajax/medicamentos', [ReaccionesAdversasController::class, 'buscarMedicamentos'])
        ->name('ajax.medicamentos');
    Route::get('/ajax/lotes', [ReaccionesAdversasController::class, 'buscarLotes'])
        ->name('ajax.lotes');

    // Reporte general
    Route::get('/reporte/general', [ReaccionesAdversasController::class, 'reporteGeneral'])
        ->name('reacciones-adversas.reporte.general');
});