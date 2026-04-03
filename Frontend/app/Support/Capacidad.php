<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Helper para consultar capacidades por rol desde la BD compartida.
 * No depende de ningún nombre de rol estático.
 */
class Capacidad
{
    /**
     * Verifica si un rol tiene determinada capacidad.
     */
    public static function rolHas(int $idRol, string $capacidad): bool
    {
        if ($idRol <= 0) {
            return false;
        }

        try {
            // Si la tabla aún no existe, usar fallback por nombre de rol
            $tableExists = DB::select("SHOW TABLES LIKE 'tbl_seg_capacidades_rol'");
            if (empty($tableExists)) {
                return self::fallbackByName($idRol, $capacidad);
            }

            $result = DB::table('tbl_seg_capacidades_rol')
                ->where('id_rol', $idRol)
                ->where('capacidad', $capacidad)
                ->exists();

            // Si la tabla existe pero está vacía (nunca se sembró), también usar fallback
            if (!$result) {
                $total = DB::table('tbl_seg_capacidades_rol')->count();
                if ($total === 0) {
                    return self::fallbackByName($idRol, $capacidad);
                }
            }

            return $result;
        } catch (\Throwable $e) {
            return self::fallbackByName($idRol, $capacidad);
        }
    }

    /**
     * Fallback estático mientras la tabla de capacidades no exista o esté vacía.
     * Se elimina automáticamente en cuanto la tabla esté sembrada.
     */
    private static function fallbackByName(int $idRol, string $capacidad): bool
    {
        try {
            $rol = DB::table('tbl_seg_rol')->where('id_rol', $idRol)->first();
            if (!$rol) return false;
            $nombre = strtoupper((string)($rol->nombre ?? ''));

            $esAdmin = str_contains($nombre, 'ADMIN');

            return match ($capacidad) {
                'acceso_total'            => $esAdmin,
                'ver_inventario_completo' => $esAdmin || str_contains($nombre, 'JEFE'),
                'inventario_solo_stock'   => !$esAdmin && !str_contains($nombre, 'JEFE'),
                'prefijo_farm'            => str_contains($nombre, 'FARMACEUT'),
                'prefijo_med'             => str_contains($nombre, 'MEDIC') || str_contains($nombre, 'DOCTOR'),
                'prefijo_enf'             => str_contains($nombre, 'ENFERM'),
                default                   => false,
            };
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Verifica si un usuario tiene determinada capacidad (via su rol).
     */
    public static function usuarioHas(int $idUsuario, string $capacidad): bool
    {
        if ($idUsuario <= 0) {
            return false;
        }

        try {
            return DB::table('tbl_seg_capacidades_rol as cap')
                ->join('tbl_seg_usuario as u', 'u.id_rol', '=', 'cap.id_rol')
                ->where('u.id_usuario', $idUsuario)
                ->where('cap.capacidad', $capacidad)
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Devuelve todas las capacidades de un rol como array de strings.
     */
    public static function getParaRol(int $idRol): array
    {
        if ($idRol <= 0) {
            return [];
        }

        try {
            return DB::table('tbl_seg_capacidades_rol')
                ->where('id_rol', $idRol)
                ->pluck('capacidad')
                ->map(fn ($c) => (string) $c)
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
