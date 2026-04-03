'use strict';

const db = require('../config/db');

/**
 * Verifica si un rol tiene determinada capacidad.
 * @param {number} idRol
 * @param {string} capacidad
 * @returns {Promise<boolean>}
 */
async function _fallbackByRoleName(idRol, capacidad) {
    try {
        const [roles] = await db.execute(
            'SELECT nombre FROM tbl_seg_rol WHERE id_rol = ? LIMIT 1',
            [idRol]
        );
        if (!roles.length) return false;
        const nombre = String(roles[0].nombre || '').toUpperCase();
        switch (capacidad) {
            case 'acceso_total':            return nombre.includes('ADMINISTRADOR');
            case 'ver_inventario_completo': return nombre.includes('ADMINISTRADOR') || nombre.includes('JEFE');
            case 'inventario_solo_stock':   return !nombre.includes('ADMINISTRADOR') && !nombre.includes('JEFE');
            case 'prefijo_farm':            return nombre.includes('FARMACEUT');
            case 'prefijo_med':             return nombre.includes('MEDIC') || nombre.includes('DOCTOR');
            case 'prefijo_enf':             return nombre.includes('ENFERM');
            default:                        return false;
        }
    } catch (_) { return false; }
}

async function rolHasCapacidad(idRol, capacidad) {
    if (!idRol) return false;
    try {
        const [rows] = await db.execute(
            `SELECT 1
             FROM tbl_seg_capacidades_rol
             WHERE id_rol = ? AND capacidad = ?
             LIMIT 1`,
            [idRol, String(capacidad)]
        );
        return rows.length > 0;
    } catch (_) {
        // Tabla no existe todavía: usar fallback por nombre
        return _fallbackByRoleName(idRol, capacidad);
    }
}

/**
 * Verifica si un usuario (por su id) tiene determinada capacidad,
 * a través del rol que tiene asignado.
 * @param {number} idUsuario
 * @param {string} capacidad
 * @returns {Promise<boolean>}
 */
async function userHasCapacidad(idUsuario, capacidad) {
    if (!idUsuario) return false;
    try {
        const [rows] = await db.execute(
            `SELECT 1
             FROM tbl_seg_capacidades_rol cap
             JOIN tbl_seg_usuario u ON u.id_rol = cap.id_rol
             WHERE u.id_usuario = ? AND cap.capacidad = ?
             LIMIT 1`,
            [idUsuario, String(capacidad)]
        );
        if (rows.length > 0) return true;

        // Si no encontró nada, verificar si la tabla existe pero no tiene datos
        // (rol no configurado) vs tabla inexistente
        const [chk] = await db.execute(
            'SELECT id_rol FROM tbl_seg_usuario WHERE id_usuario = ? LIMIT 1',
            [idUsuario]
        );
        if (!chk.length) return false;
        return _fallbackByRoleName(chk[0].id_rol, capacidad);
    } catch (_) {
        // Tabla capacidades no existe: fallback por nombre de rol
        try {
            const [u] = await db.execute(
                'SELECT id_rol FROM tbl_seg_usuario WHERE id_usuario = ? LIMIT 1',
                [idUsuario]
            );
            if (!u.length) return false;
            return _fallbackByRoleName(u[0].id_rol, capacidad);
        } catch (__) { return false; }
    }
}

/**
 * Devuelve todas las capacidades de un rol.
 * @param {number} idRol
 * @returns {Promise<string[]>}
 */
async function getCapacidadesDeRol(idRol) {
    if (!idRol) return [];
    const [rows] = await db.execute(
        `SELECT capacidad FROM tbl_seg_capacidades_rol WHERE id_rol = ?`,
        [idRol]
    );
    return rows.map((r) => String(r.capacidad));
}

module.exports = { rolHasCapacidad, userHasCapacidad, getCapacidadesDeRol };
