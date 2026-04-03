<?php

namespace App\Http\Middleware;

use App\Support\AuthenticatedUser;
use App\Support\Capacidad;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->cookie('jwt_token') ?: session('jwt_token');

        if (! $token) {
            return redirect()->route('login');
        }

        $usuario = AuthenticatedUser::fromToken($token);
        $idRol   = (int) ($usuario['id_rol'] ?? ($usuario['rol_id'] ?? 0));
        $nombreRol = strtoupper((string) ($usuario['rol_nombre'] ?? ($usuario['rol'] ?? '')));

        if ($idRol <= 0 && !empty($usuario['id'])) {
            try {
                $idRol = (int) (DB::table('tbl_seg_usuario')
                    ->where('id_usuario', (int) $usuario['id'])
                    ->value('id_rol') ?? 0);

                if ($idRol > 0 && $nombreRol === '') {
                    $nombreRol = strtoupper((string) (DB::table('tbl_seg_rol')
                        ->where('id_rol', $idRol)
                        ->value('nombre') ?? ''));
                }
            } catch (\Throwable $e) {
                // Mantener controlado: si BD falla, se evalua fallback por nombre.
            }
        }

        $isAdminByName = str_contains($nombreRol, 'ADMIN');
        if (($idRol <= 0 || ! Capacidad::rolHas($idRol, 'acceso_total')) && ! $isAdminByName) {
            abort(403, 'No autorizado.');
        }

        return $next($request);
    }
}