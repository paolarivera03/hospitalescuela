const { registerAudit, getClientIp } = require('../services/audit.service');

function resolveFormulario(pathname) {
    if (pathname.startsWith('/api/usuarios')) return 'USUARIOS';
    if (pathname.startsWith('/api/reacciones-adversas')) return 'REACCIONES ADVERSAS';
    if (pathname.startsWith('/api/backups')) return 'RESPALDOS';
    if (pathname.startsWith('/api/parametros')) return 'PARAMETROS';
    if (pathname.startsWith('/api/roles')) return 'ROLES';
    if (pathname.includes('/permisos')) return 'PERMISOS';
    if (pathname.startsWith('/api/bitacora')) return 'BITACORA';
    if (pathname.startsWith('/api/perfil') || pathname.startsWith('/api/change-password')) return 'SEGURIDAD';
    return 'SISTEMA';
}

function buildDefaultAction(method) {
    if (method === 'GET') return 'CONSULTAR';
    if (method === 'POST') return 'CREAR';
    if (method === 'PUT' || method === 'PATCH') return 'ACTUALIZAR';
    if (method === 'DELETE') return 'ELIMINAR';
    return 'ACCION';
}

function resolveAuditEvent(method, pathname, formulario) {
    if (pathname.startsWith('/api/logout')) {
        return {
            accion: 'CERRAR SESION',
            descripcion: 'Cierre de sesion del usuario',
            accionDescripcion: 'CIERRE DE SESION',
        };
    }

    if (pathname === '/api/reacciones-adversas' && method === 'POST') {
        return {
            accion: 'REGISTRAR REACCION',
            descripcion: 'Registro de reaccion adversa',
            accionDescripcion: 'REGISTRO REACCION ADVERSA',
        };
    }

    if (/^\/api\/reacciones-adversas\/\d+$/.test(pathname) && method === 'PUT') {
        return {
            accion: 'ACTUALIZAR REACCION',
            descripcion: 'Actualizacion de reaccion adversa',
            accionDescripcion: 'ACTUALIZACION DE REACCION',
        };
    }

    if (/^\/api\/reacciones-adversas\/\d+$/.test(pathname) && method === 'DELETE') {
        return {
            accion: 'ELIMINAR REACCION',
            descripcion: 'Eliminacion de reaccion adversa',
            accionDescripcion: 'ELIMINACION DE REACCION',
        };
    }

    if (pathname.startsWith('/api/reacciones-adversas/bandeja-pacientes') && method === 'GET') {
        return {
            accion: 'CONSULTAR BANDEJA RX',
            descripcion: 'Consulta de bandeja de pacientes para farmacovigilancia',
            accionDescripcion: 'CONSULTA BANDEJA REACCION',
        };
    }

    if (pathname === '/api/usuarios' && method === 'POST') {
        return {
            accion: 'REGISTRAR USUARIO',
            descripcion: 'Registro de nuevo usuario',
            accionDescripcion: 'REGISTRO DE USUARIO',
        };
    }

    if (/^\/api\/usuarios\/\d+$/.test(pathname) && (method === 'PUT' || method === 'PATCH')) {
        return {
            accion: 'ACTUALIZAR USUARIO',
            descripcion: 'Actualizacion de usuario',
            accionDescripcion: 'ACTUALIZACION DE USUARIO',
        };
    }

    if (/^\/api\/usuarios\/\d+$/.test(pathname) && method === 'DELETE') {
        return {
            accion: 'ELIMINAR USUARIO',
            descripcion: 'Eliminacion de usuario',
            accionDescripcion: 'ELIMINACION DE USUARIO',
        };
    }

    if (pathname === '/api/parametros' && method === 'POST') {
        return {
            accion: 'CREAR PARAMETRO',
            descripcion: 'Creacion de parametro del sistema',
            accionDescripcion: 'CREACION DE PARAMETRO',
        };
    }

    if (/^\/api\/parametros\/\d+$/.test(pathname) && (method === 'PUT' || method === 'PATCH')) {
        return {
            accion: 'ACTUALIZAR PARAMETRO',
            descripcion: 'Actualizacion de parametro del sistema',
            accionDescripcion: 'ACTUALIZACION PARAMETRO',
        };
    }

    const accion = buildDefaultAction(method);

    return {
        accion,
        descripcion: `${accion} en ${formulario}`,
        accionDescripcion: `ACCION ${accion}`,
    };
}

function shouldSkip(req) {
    const pathname = req.originalUrl.split('?')[0];

    if (pathname.startsWith('/api/login')) return true;
    if (pathname.startsWith('/api/register')) return true;
    if (pathname.startsWith('/api/recover-password')) return true;
    if (pathname.startsWith('/api/logout')) return true;
    if (pathname.startsWith('/api/test-db')) return true;

    return false;
}

function auditLogger(req, res, next) {
    if (shouldSkip(req)) {
        return next();
    }

    const pathname = req.originalUrl.split('?')[0];

    res.on('finish', async () => {
        try {
            if (res.statusCode >= 400) {
                return;
            }

            const idUsuario = req.user?.id;
            if (!idUsuario) {
                return;
            }

            const formulario = resolveFormulario(pathname);
            const event = resolveAuditEvent(req.method, pathname, formulario);

            await registerAudit({
                idUsuario,
                usuario: req.user?.usuario,
                accion: event.accion,
                formulario,
                descripcion: event.descripcion,
                accionDescripcion: event.accionDescripcion,
                ruta: pathname,
                direccionIp: getClientIp(req),
            });
        } catch (error) {
            console.error('No se pudo registrar auditoria:', error.message);
        }
    });

    next();
}

module.exports = auditLogger;
