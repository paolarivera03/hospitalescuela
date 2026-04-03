const db = require('../config/db');
const { userHasCapacidad } = require('../services/capacidades.service');

function normalizeAction(accion) {
    return String(accion || '').trim().toUpperCase();
}

async function hasModuleActionPermission(idUsuario, idFormulario, accion) {
    const [rows] = await db.execute(
        `SELECT 1
         FROM tbl_seg_permisos
         WHERE id_usuario = ?
           AND id_formulario = ?
           AND UPPER(accion) = ?
         LIMIT 1`,
        [idUsuario, idFormulario, normalizeAction(accion)]
    );

    return rows.length > 0;
}

function autorizarPermiso(idFormulario, accion) {
    const formId = Number(idFormulario);
    const normalizedAction = normalizeAction(accion);

    return async function (req, res, next) {
        try {
            const idUsuario = Number(req.user && req.user.id);

            if (!Number.isInteger(idUsuario) || idUsuario <= 0) {
                return res.status(401).json({ message: 'Sesion invalida. Inicie sesion nuevamente.' });
            }

            // Bypass para roles con acceso total (dinámico, no por nombre)
            if (await userHasCapacidad(idUsuario, 'acceso_total')) {
                return next();
            }

            const hasPermission = await hasModuleActionPermission(idUsuario, formId, normalizedAction);
            if (!hasPermission) {
                return res.status(403).json({
                    message: 'No tiene permisos para realizar esta accion.',
                });
            }

            return next();
        } catch (error) {
            console.error('Error validando permisos de acceso:', error);
            return res.status(500).json({ message: 'Error interno validando permisos.' });
        }
    };
}

module.exports = autorizarPermiso;
