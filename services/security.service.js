// Backend/services/security.service.js
const db = require('../config/db');

/**
 * Obtiene el máximo de intentos permitidos desde la tabla parámetros.
 * Si no existe el parámetro, devuelve 3 por defecto.
 */
async function getMaxLoginAttempts() {
    const [rows] = await db.execute(
                `SELECT valor
         FROM tbl_seg_parametro 
                 WHERE UPPER(nombre_parametro) IN ('ADMIN_INTENTOS_FALLIDOS', 'ADMIN_INTENTOS_INVALIDOS')
                     AND UPPER(COALESCE(estado, 'ACTIVO')) = 'ACTIVO'
                 ORDER BY id_parametro DESC
         LIMIT 1`,
                []
    );

    if (rows.length === 0) {
        return 3;
    }

    const fromValor = Number(rows[0].valor);
    if (!Number.isNaN(fromValor) && fromValor > 0) {
        return fromValor;
    }

    return 3;
}

/**
 * Maneja un intento fallido:
 * - Incrementa contador
 * - Bloquea si supera el máximo
 */
async function handleFailedLogin(user) {
    const maxIntentos = await getMaxLoginAttempts();

    const intentosActuales = user.intentos_fallidos_login || 0;
    const nuevosIntentos = intentosActuales + 1;

    if (nuevosIntentos >= maxIntentos) {
        await db.execute(
            `UPDATE tbl_seg_usuario 
             SET intentos_fallidos_login = ?, estado = 'BLOQUEADO'
             WHERE id_usuario = ?`,
            [nuevosIntentos, user.id_usuario]
        );

        return {
            blocked: true,
            remaining: 0,
            maxIntentos
        };
    } else {
        await db.execute(
            `UPDATE tbl_seg_usuario 
             SET intentos_fallidos_login = ?
             WHERE id_usuario = ?`,
            [nuevosIntentos, user.id_usuario]
        );

        return {
            blocked: false,
            remaining: maxIntentos - nuevosIntentos,
            maxIntentos
        };
    }
}

/**
 * Pone intentos en 0 después de un login exitoso.
 */
async function resetLoginAttempts(userId) {
    await db.execute(
        `UPDATE tbl_seg_usuario 
         SET intentos_fallidos_login = 0
         WHERE id_usuario = ?`,
        [userId]
    );
}

module.exports = {
    getMaxLoginAttempts,
    handleFailedLogin,
    resetLoginAttempts
};