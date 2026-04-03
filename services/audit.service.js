const db = require('../config/db');

function toUpperSafe(value) {
    return String(value || '').trim().toUpperCase();
}

function truncate(value, maxLength) {
    const text = String(value || '').trim();
    return text.length > maxLength ? text.slice(0, maxLength) : text;
}

function buildFormCode(formName) {
    const base = toUpperSafe(formName)
        .replace(/[^A-Z0-9]/g, '')
        .slice(0, 10);

    if (base) {
        return base;
    }

    return `FORM${Date.now().toString().slice(-6)}`;
}

function getClientIp(req) {
    const forwarded = req.headers['x-forwarded-for'];
    if (forwarded) {
        const first = String(forwarded).split(',')[0].trim();
        if (first) {
            return first;
        }
    }

    return req.ip || req.socket?.remoteAddress || null;
}

async function getOrCreateFormulario(idUsuario, formulario) {
    const formDescripcion = truncate(toUpperSafe(formulario), 25);

    const [existing] = await db.execute(
        'SELECT id_formulario FROM tbl_seg_formulario WHERE UPPER(descripcion) = ? LIMIT 1',
        [formDescripcion]
    );

    if (existing.length > 0) {
        return existing[0].id_formulario;
    }

    const codigo = buildFormCode(formDescripcion);

    const [inserted] = await db.execute(
        `INSERT INTO tbl_seg_formulario
            (id_usuario, codigo_formulario, descripcion, activo, usuario_creacion)
         VALUES (?, ?, ?, 1, ?)`,
        [idUsuario, codigo, formDescripcion, idUsuario]
    );

    return inserted.insertId;
}

async function getOrCreateTipoAccion(idUsuario, idFormulario, accion, accionDescripcion = null) {
    const action = truncate(toUpperSafe(accion), 25);
    const actionDescription = truncate(
        toUpperSafe(accionDescripcion || `ACCION ${action}`),
        50
    );

    const [existing] = await db.execute(
        `SELECT id_tipo_accion
         FROM tbl_seg_tipo_accion
         WHERE id_formulario = ? AND UPPER(accion) = ?
         LIMIT 1`,
        [idFormulario, action]
    );

    if (existing.length > 0) {
        return existing[0].id_tipo_accion;
    }

    const [inserted] = await db.execute(
        `INSERT INTO tbl_seg_tipo_accion
            (id_formulario, id_usuario, accion, descripcion, estado, usuario_creacion)
         VALUES (?, ?, ?, ?, 'ACTIVO', ?)`,
        [idFormulario, idUsuario, action, actionDescription, idUsuario]
    );

    return inserted.insertId;
}

async function resolveUsername(idUsuario, usuario) {
    const normalizedUser = toUpperSafe(usuario);
    if (normalizedUser) {
        return truncate(normalizedUser, 50);
    }

    const [rows] = await db.execute(
        'SELECT usuario FROM tbl_seg_usuario WHERE id_usuario = ? LIMIT 1',
        [idUsuario]
    );

    if (rows.length > 0) {
        return truncate(toUpperSafe(rows[0].usuario), 50);
    }

    return null;
}

async function registerAudit({ idUsuario, usuario, accion, formulario, descripcion, direccionIp, ruta, accionDescripcion }) {
    if (!idUsuario || !accion || !formulario) {
        return;
    }

    const idFormulario = await getOrCreateFormulario(idUsuario, formulario);
    const idTipoAccion = await getOrCreateTipoAccion(idUsuario, idFormulario, accion, accionDescripcion);
    const username = await resolveUsername(idUsuario, usuario);

    await db.execute(
        `INSERT INTO tbl_bitacora_auditoria
            (id_usuario, usuario, id_tipo_accion, id_formulario, descripcion, ruta, direccion_ip)
         VALUES (?, ?, ?, ?, ?, ?, ?)`,
        [
            idUsuario,
            username,
            idTipoAccion,
            idFormulario,
            truncate(descripcion || `${accion} en ${formulario}`, 500),
            truncate(ruta, 255),
            truncate(direccionIp, 45),
        ]
    );
}

module.exports = {
    registerAudit,
    getClientIp,
};
