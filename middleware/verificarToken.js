const jwt = require('jsonwebtoken');
const db = require('../config/db');

const SECRET_KEY = 'clave_secreta_universidad';

// Cache del timestamp de forzado de cierre de sesiones
let _forcedLogoutCache = { ts: null, fetchedAt: 0 };
const CACHE_TTL_MS = 30000; // 30 segundos

async function _getForcedLogoutTimestamp() {
    const now = Date.now();
    if (now - _forcedLogoutCache.fetchedAt < CACHE_TTL_MS) {
        return _forcedLogoutCache.ts;
    }

    try {
        const [rows] = await db.execute(
            "SELECT valor FROM tbl_seg_parametro WHERE UPPER(nombre_parametro) = 'FORZAR_SESIONES_AT' AND UPPER(COALESCE(estado,'ACTIVO')) = 'ACTIVO' LIMIT 1"
        );
        const ts = rows.length > 0 && rows[0].valor ? parseInt(rows[0].valor, 10) : null;
        _forcedLogoutCache = { ts, fetchedAt: now };
        return ts;
    } catch (_e) {
        // Si la BD no responde, no bloquear las peticiones
        _forcedLogoutCache.fetchedAt = now;
        return _forcedLogoutCache.ts;
    }
}

function clearForcedLogoutCache() {
    _forcedLogoutCache = { ts: null, fetchedAt: 0 };
}

async function verificarToken(req, res, next) {
    const authHeader = req.headers['authorization'];
    const tokenFromHeader = authHeader && authHeader.split(' ')[1];
    const tokenFromCookie = req.cookies && (req.cookies.jwt_token || req.cookies.token);

    const token = tokenFromHeader || tokenFromCookie;

    if (!token) {
        return res.status(403).json({ message: 'Acceso denegado: no se proporcionó token.' });
    }

    let decodificado;
    try {
        decodificado = jwt.verify(token, SECRET_KEY);
    } catch (error) {
        return res.status(401).json({ message: 'Token inválido o expirado.' });
    }

    req.user = decodificado;

    // Verificar si se forzó el cierre de todas las sesiones
    try {
        const forcedTs = await _getForcedLogoutTimestamp();
        if (forcedTs && decodificado.iat && decodificado.iat < forcedTs) {
            return res.status(401).json({ message: 'Sesión cerrada por el administrador. Inicie sesión nuevamente.' });
        }
    } catch (_e) {
        // No bloquear si la BD falla
    }

    next();
}

module.exports = verificarToken;
module.exports.clearForcedLogoutCache = clearForcedLogoutCache;