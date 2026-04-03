// Backend/routes/auth.routes.js
const express = require('express');
const router = express.Router();

const {
    register,
    login,
    changePassword,
    recoverPassword,
    verify2FA,
    profile,
    logout,
    getAuditLog
} = require('../controllers/auth.controller');

const verifyToken = require('../middleware/verificarToken');

// Rutas públicas
router.post('/register', register);
router.post('/login', login);
router.post('/verify-2fa', verify2FA);

// Recuperar contraseña (pública)
router.post('/recover-password', recoverPassword);

// Rutas protegidas
router.get('/perfil', verifyToken, profile);
router.get('/bitacora', verifyToken, getAuditLog);
router.post('/logout', verifyToken, logout);

// Cambio de contraseña (requiere token)
router.post('/change-password', verifyToken, changePassword);

module.exports = router;