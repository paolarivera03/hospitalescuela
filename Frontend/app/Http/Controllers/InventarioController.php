<?php

namespace App\Http\Controllers;

use App\Support\AuthenticatedUser;
use App\Support\Capacidad;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $token = $request->cookie('jwt_token') ?: session('jwt_token');

        if (! $token) {
            return redirect('/login');
        }

        if (! session('security_verified', false)) {
            return redirect()->route('login');
        }

        $usuario = AuthenticatedUser::fromToken($token);
        $userId  = (int) ($usuario['id'] ?? 0);
        $idRol   = (int) ($usuario['id_rol'] ?? 0);

        if (! $this->hasInventarioModule($userId) || ! $this->canViewInventario($userId)) {
            abort(403, 'No tienes permiso para acceder al módulo de inventario.');
        }

        // Dinámico: usa capacidades del rol, no nombre de rol
        $esRestringido  = Capacidad::rolHas($idRol, 'inventario_solo_stock');
        $esJefe         = Capacidad::rolHas($idRol, 'ver_inventario_completo') && ! Capacidad::rolHas($idRol, 'acceso_total');

        // Si solo ve stock, quitar acciones de escritura
        if ($esRestringido) {
            $canGuardar    = false;
            $canActualizar = false;
            $canEliminar   = false;
            $canViewFull   = false;
        } else {
            $canGuardar    = $this->hasInventarioAction($userId, 'GUARDAR');
            $canActualizar = $this->hasInventarioAction($userId, 'ACTUALIZAR');
            $canEliminar   = $this->hasInventarioAction($userId, 'ELIMINAR');
            $canViewFull   = true;
        }

        $ultimoMovimientoSub = DB::table('tbl_inv_movimiento as mov')
            ->select([
                'mov.id_lote',
                DB::raw('MAX(mov.fecha_movimiento) as fecha_ultimo_movimiento'),
            ])
            ->groupBy('mov.id_lote');

        $inventario = DB::table('tbl_far_lote as l')
            ->leftJoin('tbl_far_medicamento as m', 'm.id_medicamento', '=', 'l.id_medicamento')
            ->leftJoinSub($ultimoMovimientoSub, 'mov', function ($join) {
                $join->on('mov.id_lote', '=', 'l.id_lote');
            })
            ->select([
                'l.id_lote',
                'l.numero_lote as codigo',
                'l.numero_lote as lote',
                'm.nombre_comercial as nombre',
                'm.registro_sanitario as registro_sanitario_meta',
                'm.laboratorio_fabricante as proveedor',
                'l.fecha_expiracion as vencimiento',
                'l.cantidad_actual as saldo',
                'l.estado',
                'm.principio_activo as descripcion',
                DB::raw('COALESCE(mov.fecha_ultimo_movimiento, l.fecha_creacion) as fecha_ultimo_movimiento'),
            ])
            ->orderByDesc('l.id_lote')
            ->get();

        // Calcular estado dinámicamente para cada lote
        $inventario = $inventario->map(function ($lote) {
            $metaInventario = $this->extractInventarioMeta($lote->registro_sanitario_meta ?? null);
            $lote->categoria = $metaInventario['categoria'];
            $lote->unidad = $metaInventario['unidad'];
            unset($lote->registro_sanitario_meta);
            $lote->estado = $this->calculateLoteStatus($lote);
            return $lote;
        });

        if ($esRestringido) {
            $inventario = $inventario
                ->filter(function ($lote) {
                    $estado = strtoupper((string) ($lote->estado ?? ''));
                    $permitidos = ['ACTIVO'];

                    return ((int) ($lote->saldo ?? 0) > 0) && in_array($estado, $permitidos, true);
                })
                ->values();
        }

        return view('inventario', [
            'usuario'                  => $usuario,
            'inventario'               => $inventario,
            'canGuardarInventario'     => $canGuardar,
            'canActualizarInventario'  => $canActualizar,
            'canEliminarInventario'    => $canEliminar,
            'rolUsuario'               => strtoupper((string) ($usuario['rol_nombre'] ?? '')),
            'esRestringido'            => $esRestringido,
            'esJefe'                   => $esJefe,
            'canViewFull'              => $canViewFull,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->canUseInventario($request, 'GUARDAR')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'codigo' => ['required', 'string', 'max:50', 'regex:/^[^$&#!]*$/u'],
            'nombre' => ['required', 'string', 'max:150', 'regex:/^[^$&#!]*$/u'],
            'categoria' => ['nullable', 'string', 'max:100', 'regex:/^[^$&#!]*$/u'],
            'unidad' => ['nullable', 'string', 'max:20', 'regex:/^[^$&#!]*$/u'],
            'proveedor' => ['nullable', 'string', 'max:150', 'regex:/^[^$&#!]*$/u'],
            'saldo' => 'required|integer|min:0',
            'vencimiento' => 'nullable|date',
            'estado' => 'nullable|in:ACTIVO,VENCIDO,AGOTADO,BAJA_ROTACION,EN_CUARENTENA,PRONTO_VENCER,BAJO_STOCK',
            'descripcion' => ['nullable', 'string', 'max:255', 'regex:/^[^$&#!]*$/u'],
        ], [
            'codigo.regex' => 'No se permiten los caracteres $ & # ! en Codigo.',
            'nombre.regex' => 'No se permiten los caracteres $ & # ! en Nombre del Suministro.',
            'categoria.regex' => 'No se permiten los caracteres $ & # ! en Categoria / Tipo.',
            'unidad.regex' => 'No se permiten los caracteres $ & # ! en Unidad de Emision.',
            'proveedor.regex' => 'No se permiten los caracteres $ & # ! en Proveedor / Laboratorio.',
            'descripcion.regex' => 'No se permiten los caracteres $ & # ! en Descripcion / Notas.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            $idMedicamento = $this->resolveMedicamentoId($request);

            DB::table('tbl_far_medicamento')
                ->where('id_medicamento', $idMedicamento)
                ->update([
                    'registro_sanitario' => $this->buildInventarioMeta(
                        $request->input('categoria'),
                        $request->input('unidad')
                    ),
                ]);

            $idLote = DB::table('tbl_far_lote')->insertGetId([
                'id_medicamento' => $idMedicamento,
                'numero_lote' => strtoupper(trim((string) $request->input('codigo'))),
                'fecha_expiracion' => $request->input('vencimiento'),
                'cantidad_inicial' => (int) $request->input('saldo'),
                'cantidad_actual' => (int) $request->input('saldo'),
                'estado' => strtoupper((string) $request->input('estado', 'ACTIVO')),
                'usuario_creacion' => $this->getUsuarioId($request),
                'fecha_creacion' => now(),
            ]);
        } catch (QueryException $e) {
            if ($this->isDuplicateLoteException($e)) {
                return response()->json(['message' => 'Medicamento aun en existencia'], 409);
            }

            throw $e;
        }

        return response()->json([
            'message' => 'Registro creado correctamente.',
            'id_lote' => $idLote,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if (! $this->canUseInventario($request, 'ACTUALIZAR')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'codigo' => ['required', 'string', 'max:50', 'regex:/^[^$&#!]*$/u'],
            'nombre' => ['required', 'string', 'max:150', 'regex:/^[^$&#!]*$/u'],
            'categoria' => ['nullable', 'string', 'max:100', 'regex:/^[^$&#!]*$/u'],
            'unidad' => ['nullable', 'string', 'max:20', 'regex:/^[^$&#!]*$/u'],
            'proveedor' => ['nullable', 'string', 'max:150', 'regex:/^[^$&#!]*$/u'],
            'saldo' => 'required|integer|min:0',
            'vencimiento' => 'nullable|date',
            'estado' => 'nullable|in:ACTIVO,VENCIDO,AGOTADO,BAJA_ROTACION,EN_CUARENTENA,PRONTO_VENCER,BAJO_STOCK',
            'descripcion' => ['nullable', 'string', 'max:255', 'regex:/^[^$&#!]*$/u'],
        ], [
            'codigo.regex' => 'No se permiten los caracteres $ & # ! en Codigo.',
            'nombre.regex' => 'No se permiten los caracteres $ & # ! en Nombre del Suministro.',
            'categoria.regex' => 'No se permiten los caracteres $ & # ! en Categoria / Tipo.',
            'unidad.regex' => 'No se permiten los caracteres $ & # ! en Unidad de Emision.',
            'proveedor.regex' => 'No se permiten los caracteres $ & # ! en Proveedor / Laboratorio.',
            'descripcion.regex' => 'No se permiten los caracteres $ & # ! en Descripcion / Notas.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $lote = DB::table('tbl_far_lote')->where('id_lote', $id)->first();
        if (! $lote) {
            return response()->json(['message' => 'Registro no encontrado.'], 404);
        }

        try {
            DB::transaction(function () use ($request, $id, $lote) {
                DB::table('tbl_far_lote')->where('id_lote', $id)->update([
                    'numero_lote' => strtoupper(trim((string) $request->input('codigo'))),
                    'fecha_expiracion' => $request->input('vencimiento'),
                    'cantidad_actual' => (int) $request->input('saldo'),
                    'estado' => strtoupper((string) $request->input('estado', 'ACTIVO')),
                ]);

                DB::table('tbl_far_medicamento')->where('id_medicamento', $lote->id_medicamento)->update([
                    'nombre_comercial' => strtoupper(trim((string) $request->input('nombre'))),
                    'principio_activo' => $request->input('descripcion'),
                    'laboratorio_fabricante' => $request->input('proveedor'),
                    'registro_sanitario' => $this->buildInventarioMeta(
                        $request->input('categoria'),
                        $request->input('unidad')
                    ),
                ]);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateLoteException($e)) {
                return response()->json(['message' => 'Medicamento aun en existencia'], 409);
            }

            throw $e;
        }

        return response()->json(['message' => 'Registro actualizado correctamente.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if (! $this->canUseInventario($request, 'ELIMINAR')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $loteExiste = DB::table('tbl_far_lote')->where('id_lote', $id)->exists();
        if (! $loteExiste) {
            return response()->json(['message' => 'Registro no encontrado.'], 404);
        }

        $enUsoEnReacciones = DB::table('tbl_far_reaccion_detalle')
            ->where('id_lote', $id)
            ->exists();

        if ($enUsoEnReacciones) {
            return response()->json([
                'message' => 'No se puede eliminar este medicamento porque ya está asociado a una reacción adversa registrada.',
            ], 409);
        }

        try {
            $deleted = DB::table('tbl_far_lote')->where('id_lote', $id)->delete();
            if (! $deleted) {
                return response()->json(['message' => 'Registro no encontrado.'], 404);
            }
        } catch (QueryException $e) {
            $driverCode = (int) ($e->errorInfo[1] ?? 0);
            $isFkViolation = ($e->getCode() === '23000') || ($driverCode === 1451);

            if ($isFkViolation) {
                return response()->json([
                    'message' => 'No se puede eliminar este medicamento porque ya está asociado a registros del sistema.',
                ], 409);
            }

            return response()->json([
                'message' => 'No se pudo eliminar el registro. Intenta nuevamente.',
            ], 500);
        }

        return response()->json(['message' => 'Registro eliminado correctamente.']);
    }

    public function bulk(Request $request): JsonResponse
    {
        if (! $this->canUseInventario($request, 'GUARDAR')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $rows = $request->json()->all();
        if (! is_array($rows) || empty($rows)) {
            return response()->json(['insertados' => 0, 'errores' => [['fila' => 0, 'codigo' => '', 'error' => 'Archivo sin registros.']]], 422);
        }

        $insertados = 0;
        $actualizados = 0;
        $errores = [];

        foreach ($rows as $index => $row) {
            $fila = $index + 2;
            $codigo = strtoupper(trim((string) ($row['codigo'] ?? '')));
            $nombre = trim((string) ($row['nombre'] ?? ''));
            $saldo = (int) ($row['saldo'] ?? 0);

            if ($codigo === '' || $nombre === '') {
                $errores[] = ['fila' => $fila, 'codigo' => $codigo ?: '-', 'error' => 'Codigo y nombre son obligatorios.'];
                continue;
            }

            if ($saldo < 0) {
                $errores[] = ['fila' => $fila, 'codigo' => $codigo, 'error' => 'Saldo invalido.'];
                continue;
            }

            try {
                $normalizedDate = $this->normalizeDate((string) ($row['vencimiento'] ?? ''));
                $estado = strtoupper((string) ($row['estado'] ?? 'ACTIVO'));
                if (! in_array($estado, ['ACTIVO', 'VENCIDO', 'AGOTADO'], true)) {
                    $estado = 'ACTIVO';
                }

                $proveedor = trim((string) ($row['proveedor'] ?? ''));
                $descripcion = $row['descripcion'] ?? null;

                $loteExistente = DB::table('tbl_far_lote')
                    ->whereRaw('UPPER(numero_lote) = ?', [$codigo])
                    ->first();

                if ($loteExistente) {
                    DB::transaction(function () use ($loteExistente, $nombre, $proveedor, $descripcion, $normalizedDate, $saldo, $estado) {
                        DB::table('tbl_far_lote')
                            ->where('id_lote', $loteExistente->id_lote)
                            ->update([
                                'fecha_expiracion' => $normalizedDate,
                                'cantidad_actual' => $saldo,
                                'estado' => $estado,
                            ]);

                        DB::table('tbl_far_medicamento')
                            ->where('id_medicamento', $loteExistente->id_medicamento)
                            ->update([
                                'nombre_comercial' => strtoupper($nombre),
                                'principio_activo' => $descripcion,
                                'laboratorio_fabricante' => $proveedor !== '' ? $proveedor : null,
                            ]);
                    });

                    $actualizados++;
                    continue;
                }

                $data = [
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'proveedor' => $proveedor,
                    'descripcion' => $descripcion,
                ];

                $idMedicamento = $this->resolveMedicamentoId(new Request($data));

                DB::table('tbl_far_lote')->insert([
                    'id_medicamento' => $idMedicamento,
                    'numero_lote' => $codigo,
                    'fecha_expiracion' => $normalizedDate,
                    'cantidad_inicial' => $saldo,
                    'cantidad_actual' => $saldo,
                    'estado' => $estado,
                    'usuario_creacion' => $this->getUsuarioId($request),
                    'fecha_creacion' => now(),
                ]);

                $insertados++;
            } catch (\Throwable $e) {
                $errores[] = ['fila' => $fila, 'codigo' => $codigo ?: '-', 'error' => 'No se pudo importar el registro.'];
            }
        }

        return response()->json([
            'insertados' => $insertados,
            'actualizados' => $actualizados,
            'errores' => $errores,
        ]);
    }

    private function resolveMedicamentoId(Request $request): int
    {
        $nombre = strtoupper(trim((string) $request->input('nombre')));
        $proveedor = trim((string) ($request->input('proveedor') ?? ''));

        $existing = DB::table('tbl_far_medicamento')
            ->whereRaw('UPPER(nombre_comercial) = ?', [$nombre])
            ->when($proveedor !== '', function ($q) use ($proveedor) {
                return $q->where('laboratorio_fabricante', $proveedor);
            })
            ->first();

        if ($existing) {
            return (int) $existing->id_medicamento;
        }

        return (int) DB::table('tbl_far_medicamento')->insertGetId([
            'nombre_comercial' => $nombre,
            'principio_activo' => $request->input('descripcion'),
            'laboratorio_fabricante' => $proveedor !== '' ? $proveedor : null,
            'registro_sanitario' => null,
            'estado' => 'ACTIVO',
            'usuario_creacion' => $this->getUsuarioId($request),
            'fecha_creacion' => now(),
        ]);
    }

    private function hasSessionToken(Request $request): bool
    {
        $token = $request->cookie('jwt_token') ?: session('jwt_token');
        return (bool) $token;
    }

    private function getUsuarioId(Request $request): int
    {
        $token = $request->cookie('jwt_token') ?: session('jwt_token');
        $usuario = AuthenticatedUser::fromToken($token);
        return (int) ($usuario['id'] ?? 0);
    }

    private function hasInventarioModule(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        return DB::table('tbl_seg_permisos')
            ->where('id_usuario', $userId)
            ->where('id_formulario', 1)
            ->exists();
    }

    private function canUseInventario(Request $request, string $accion): bool
    {
        if (! $this->hasSessionToken($request)) {
            return false;
        }

        $userId = $this->getUsuarioId($request);
        if (! $this->hasInventarioModule($userId)) {
            return false;
        }

        return $this->hasInventarioAction($userId, strtoupper($accion));
    }

    private function canViewInventario(int $userId): bool
    {
        if ($this->hasInventarioAction($userId, 'VISUALIZAR')) {
            return true;
        }

        return DB::table('tbl_seg_permisos')
            ->where('id_usuario', $userId)
            ->where('id_formulario', 1)
            ->exists();
    }

    private function hasInventarioAction(int $userId, string $accion): bool
    {
        if ($userId <= 0) {
            return false;
        }

        return DB::table('tbl_seg_permisos')
            ->where('id_usuario', $userId)
            ->where('id_formulario', 1)
            ->whereRaw('UPPER(accion) = ?', [strtoupper($accion)])
            ->exists();
    }

    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }

        return null;
    }

    private function isDuplicateLoteException(QueryException $e): bool
    {
        $errorInfo = $e->errorInfo;
        $sqlState = (string) ($errorInfo[0] ?? '');
        $errorCode = (int) ($errorInfo[1] ?? 0);
        $message = (string) ($errorInfo[2] ?? $e->getMessage());

        if ($sqlState !== '23000' || $errorCode !== 1062) {
            return false;
        }

        return stripos($message, 'numero_lote') !== false;
    }

    private function calculateLoteStatus($lote)
    {
        $estadoActual = strtoupper((string) ($lote->estado ?? 'ACTIVO'));
        $saldo = (int) ($lote->saldo ?? 0);
        $hoy = \Carbon\Carbon::today();

        if ($estadoActual === 'EN_CUARENTENA') {
            return 'EN_CUARENTENA';
        }

        if ($saldo === 0 || $estadoActual === 'AGOTADO') {
            return 'AGOTADO';
        }

        if ($lote->vencimiento) {
            $fechaExpiracion = \Carbon\Carbon::parse($lote->vencimiento)->startOfDay();
            if ($fechaExpiracion->lt($hoy)) {
                return 'VENCIDO';
            }
        }

        if (! empty($lote->fecha_ultimo_movimiento)) {
            try {
                $ultimoMovimiento = \Carbon\Carbon::parse($lote->fecha_ultimo_movimiento);
                if ($ultimoMovimiento->lte($hoy->copy()->subMonths(3))) {
                    return 'BAJA_ROTACION';
                }
            } catch (\Throwable $e) {
                // Si la fecha no es parseable, continuar con la siguiente regla.
            }
        }

        if ($lote->vencimiento) {
            $fechaExpiracion = \Carbon\Carbon::parse($lote->vencimiento)->startOfDay();
            if ($fechaExpiracion->betweenIncluded($hoy, $hoy->copy()->addDays(30))) {
                return 'PRONTO_VENCER';
            }
        }

        if ($saldo <= 50) {
            return 'BAJO_STOCK';
        }

        return 'ACTIVO';
    }

    public function getData(Request $request): JsonResponse
    {
        $token = $request->cookie('jwt_token') ?: session('jwt_token');

        if (! $token) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $usuario       = AuthenticatedUser::fromToken($token);
        $userId        = (int) ($usuario['id'] ?? 0);
        $idRol         = (int) ($usuario['id_rol'] ?? 0);
        $esRestringido = Capacidad::rolHas($idRol, 'inventario_solo_stock');

        if (! $this->hasInventarioModule($userId) || ! $this->canViewInventario($userId)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $ultimoMovimientoSub = DB::table('tbl_inv_movimiento as mov')
            ->select([
                'mov.id_lote',
                DB::raw('MAX(mov.fecha_movimiento) as fecha_ultimo_movimiento'),
            ])
            ->groupBy('mov.id_lote');

        $inventario = DB::table('tbl_far_lote as l')
            ->leftJoin('tbl_far_medicamento as m', 'm.id_medicamento', '=', 'l.id_medicamento')
            ->leftJoinSub($ultimoMovimientoSub, 'mov', function ($join) {
                $join->on('mov.id_lote', '=', 'l.id_lote');
            })
            ->select([
                'l.id_lote',
                'l.numero_lote as codigo',
                'm.nombre_comercial as nombre',
                'm.registro_sanitario as registro_sanitario_meta',
                'm.laboratorio_fabricante as proveedor',
                'l.fecha_expiracion as vencimiento',
                'l.cantidad_actual as saldo',
                'l.estado',
                'm.principio_activo as descripcion',
                DB::raw('COALESCE(mov.fecha_ultimo_movimiento, l.fecha_creacion) as fecha_ultimo_movimiento'),
            ])
            ->orderByDesc('l.id_lote')
            ->get();

        // Calcular estado dinámicamente para cada lote
        $inventario = $inventario->map(function ($lote) {
            $metaInventario = $this->extractInventarioMeta($lote->registro_sanitario_meta ?? null);
            $lote->categoria = $metaInventario['categoria'];
            $lote->unidad = $metaInventario['unidad'];
            unset($lote->registro_sanitario_meta);
            $lote->estado = $this->calculateLoteStatus($lote);
            return $lote;
        });

        if ($esRestringido) {
            $inventario = $inventario
                ->filter(function ($lote) {
                    $estado = strtoupper((string) ($lote->estado ?? ''));
                    $permitidos = ['ACTIVO'];

                    return ((int) ($lote->saldo ?? 0) > 0) && in_array($estado, $permitidos, true);
                })
                ->values();
        }

        return response()->json($inventario);
    }

    private function buildInventarioMeta(?string $categoria, ?string $unidad): ?string
    {
        $categoriaLimpia = trim((string) ($categoria ?? ''));
        $unidadLimpia = trim((string) ($unidad ?? ''));

        if ($categoriaLimpia === '' && $unidadLimpia === '') {
            return null;
        }

        return 'INV_META:' . json_encode([
            'categoria' => $categoriaLimpia !== '' ? $categoriaLimpia : null,
            'unidad' => $unidadLimpia !== '' ? $unidadLimpia : null,
        ], JSON_UNESCAPED_UNICODE);
    }

    private function extractInventarioMeta(?string $rawMeta): array
    {
        $vacio = ['categoria' => null, 'unidad' => null];
        $valor = trim((string) ($rawMeta ?? ''));

        if ($valor === '' || ! str_starts_with($valor, 'INV_META:')) {
            return $vacio;
        }

        $json = substr($valor, 9);
        if ($json === '') {
            return $vacio;
        }

        $data = json_decode($json, true);
        if (! is_array($data)) {
            return $vacio;
        }

        return [
            'categoria' => isset($data['categoria']) && trim((string) $data['categoria']) !== ''
                ? (string) $data['categoria']
                : null,
            'unidad' => isset($data['unidad']) && trim((string) $data['unidad']) !== ''
                ? (string) $data['unidad']
                : null,
        ];
    }
}
