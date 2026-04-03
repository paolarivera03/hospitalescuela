// Backend/controllers/auth.controller.js
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const db = require('../config/db');
const nodemailer = require('nodemailer');
const { registerAudit, getClientIp } = require('../services/audit.service');
require('dotenv').config();

const SECRET_KEY = 'clave_secreta_universidad';

// ======================================
// IN-MEMORY LOGIN ATTEMPTS
// ======================================
const loginAttempts = {};

const authColumnCache = {
    intentos_fallidos_login: null,
};

async function hasUserColumn(columnName) {
    if (Object.prototype.hasOwnProperty.call(authColumnCache, columnName) && authColumnCache[columnName] !== null) {
        return authColumnCache[columnName];
    }

    const [rows] = await db.execute(
        `
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'tbl_seg_usuario'
          AND COLUMN_NAME = ?
        LIMIT 1
        `,
        [columnName]
    );

    const exists = rows.length > 0;
    authColumnCache[columnName] = exists;
    return exists;
}

async function setFailedAttempts(username, value, { lockUser = false } = {}) {
    const hasIntentos = await hasUserColumn('intentos_fallidos_login');

    if (hasIntentos) {
        if (lockUser) {
            await db.execute(
                "UPDATE tbl_seg_usuario SET estado = 'BLOQUEADO', intentos_fallidos_login = ? WHERE usuario = ?",
                [value, username]
            );
            return;
        }

        await db.execute(
            'UPDATE tbl_seg_usuario SET intentos_fallidos_login = ? WHERE usuario = ?',
            [value, username]
        );
        return;
    }

    if (lockUser) {
        await db.execute(
            "UPDATE tbl_seg_usuario SET estado = 'BLOQUEADO' WHERE usuario = ?",
            [username]
        );
    }
}

async function getNumericParameter(paramName, defaultValue, preferredField = 'valor') {
    const [rows] = await db.execute(
                `SELECT valor
         FROM tbl_seg_parametro
         WHERE UPPER(nombre_parametro) = UPPER(?)
           AND UPPER(COALESCE(estado, 'ACTIVO')) = 'ACTIVO'
         ORDER BY id_parametro DESC
         LIMIT 1`,
        [paramName]
    );

    if (rows.length === 0) {
        return defaultValue;
    }

    const row = rows[0];
    const value = Number(row.valor);
    if (!Number.isNaN(value) && value > 0) {
        return value;
    }

    return defaultValue;
}

async function getNumericParameterNullable(paramName, preferredField = 'valor') {
    const [rows] = await db.execute(
                `SELECT valor
         FROM tbl_seg_parametro
         WHERE UPPER(nombre_parametro) = UPPER(?)
           AND UPPER(COALESCE(estado, 'ACTIVO')) = 'ACTIVO'
         ORDER BY id_parametro DESC
         LIMIT 1`,
        [paramName]
    );

    if (rows.length === 0) {
        return null;
    }

    const value = Number(rows[0].valor);
    if (!Number.isNaN(value) && value > 0) {
        return value;
    }

    return null;
}

async function getNumericParameterFromNames(paramNames, defaultValue, preferredField = 'valor') {
    for (const name of paramNames) {
        const value = await getNumericParameterNullable(name, preferredField);
        if (value !== null) {
            return value;
        }
    }
    return defaultValue;
}

// Get max attempts from parameter table
async function getMaxAttempts() {
    return getNumericParameterFromNames(
        ['ADMIN_INTENTOS_FALLIDOS', 'ADMIN_INTENTOS_INVALIDOS'],
        3,
        'valor'
    );
}

// Get 2FA token expiration time (minutes)
async function get2FAExpirationMinutes() {
    return getNumericParameterFromNames(
        ['VENCIMIENTO_2FA_MINUTOS', 'VENCIMIENTO_SEGUNDA_FACTORIZACION_MINUTOS'],
        10,
        'valor'
    );
}

// Get temp password lifetime (hours)
async function getTempPasswordLifetimeHours() {
    return getNumericParameter('RECUP_CLAVE_HORAS', 24, 'valor');
}

// ======================================
// 2FA TOKEN GENERATOR (6 DIGITS)
// ======================================
function generate2FAToken() {
    return Math.floor(100000 + Math.random() * 900000).toString();
}

// ======================================
// SEND 2FA TOKEN EMAIL
// ======================================
async function send2FATokenEmail(toEmail, username, token2fa, expirationMinutes = 10) {
    if (!toEmail) {
        console.warn('Intento de enviar correo 2FA sin email destino.');
        return;
    }

    const from = process.env.SMTP_FROM || process.env.SMTP_USER;

    const mailOptions = {
        from,
        to: toEmail,
        subject: 'Tu código de autenticación en dos pasos - Sistema de Gestión de Medicamentos',
        html: `
            <p>Hola <strong>${username}</strong>,</p>
            <p>Ingresaste a tu cuenta. Para completar el proceso, usa este código de autenticación:</p>
            <p style="font-size: 24px; font-weight: bold; color: #222c5e; letter-spacing: 2px;">${token2fa}</p>
            <p><em>Este código expira en ${expirationMinutes} minuto${expirationMinutes !== 1 ? 's' : ''}.</em></p>
            <br>
            <p>Si tú no intentaste iniciar sesión, ignora este correo.</p>
        `,
    };

    try {
        await mailTransporter.sendMail(mailOptions);
        console.log(`Código 2FA enviado a ${toEmail}`);
    } catch (error) {
        console.error('Error al enviar código 2FA:', error);
    }
}

async function getPasswordPolicyConstraints() {
    const min = await getNumericParameter('MIN_CONTRASENA', 8, 'valor');
    const max = await getNumericParameter('MAX_CONTRASENA', 64, 'valor');

    const normalizedMin = Math.max(4, Number(min) || 8);
    const normalizedMax = Math.max(normalizedMin, Number(max) || 64);

    return {
        min: normalizedMin,
        max: normalizedMax,
    };
}

async function getUsernameConstraints() {
    const minFromParams = await getNumericParameter('MIN_USUARIO', 1, 'valor');
    const maxFromParams = await getNumericParameter('MAX_USUARIO', 0, 'valor');

    const [rows] = await db.execute(
        `SELECT CHARACTER_MAXIMUM_LENGTH AS max_len
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = 'tbl_seg_usuario'
           AND COLUMN_NAME = 'usuario'
         LIMIT 1`
    );

    const schemaMax = Number(rows[0]?.max_len);
    const normalizedSchemaMax = !Number.isNaN(schemaMax) && schemaMax > 0 ? schemaMax : 50;
    const normalizedMin = Math.max(1, Number(minFromParams) || 1);
    const requestedMax = Number(maxFromParams) || normalizedSchemaMax;
    const normalizedMax = Math.max(normalizedMin, Math.min(requestedMax, normalizedSchemaMax));

    return {
        min: normalizedMin,
        max: normalizedMax,
    };
}

// ======================================
// TEMP PASSWORD GENERATOR (ROBUST)
// ======================================
function generateTempPassword(length = 10) {
    const safeLength = Math.max(4, Number(length) || 10);
    const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const lower = 'abcdefghijkmnpqrstuvwxyz';
    const digits = '23456789';
    const special = '!@#$%^&*()-_=+';

    const allChars = upper + lower + digits + special;

    let pwd = '';
    pwd += upper[Math.floor(Math.random() * upper.length)];
    pwd += lower[Math.floor(Math.random() * lower.length)];
    pwd += digits[Math.floor(Math.random() * digits.length)];
    pwd += special[Math.floor(Math.random() * special.length)];

    while (pwd.length < safeLength) {
        pwd += allChars[Math.floor(Math.random() * allChars.length)];
    }

    return pwd.split('').sort(() => Math.random() - 0.5).join('');
}

// ======================================
// PASSWORD POLICY VALIDATION
// ======================================
async function validatePasswordPolicy(newPassword, username) {
    const constraints = await getPasswordPolicyConstraints();

    if (/\s/.test(newPassword)) {
        return 'La contraseña no debe contener espacios.';
    }
    if (newPassword.length < constraints.min || newPassword.length > constraints.max) {
        return `La contraseña debe tener entre ${constraints.min} y ${constraints.max} caracteres.`;
    }
    if (!/[a-z]/.test(newPassword)) {
        return 'La contraseña debe contener al menos una letra minúscula.';
    }
    if (!/[A-Z]/.test(newPassword)) {
        return 'La contraseña debe contener al menos una letra mayúscula.';
    }
    if (!/[0-9]/.test(newPassword)) {
        return 'La contraseña debe contener al menos un número.';
    }
    if (!/[^\w\s]/.test(newPassword)) {
        return 'La contraseña debe contener al menos un carácter especial.';
    }
    if (String(newPassword).toUpperCase() === String(username || '').toUpperCase()) {
        return 'La contraseña no puede ser igual al usuario.';
    }

    return null;
}

// ======================================
// SMTP TRANSPORT & EMAIL SENDER
// ======================================
const mailTransporter = nodemailer.createTransport({
    host: process.env.SMTP_HOST,
    port: Number(process.env.SMTP_PORT) || 587,
    secure: false, // TLS explícito (STARTTLS)
    auth: {
        user: process.env.SMTP_USER,
        pass: process.env.SMTP_PASS,
    },
});

async function sendTempPasswordEmail(toEmail, username, tempPassword) {
    if (!toEmail) {
        console.warn('Intento de enviar correo sin email destino.');
        return;
    }

    const from = process.env.SMTP_FROM || process.env.SMTP_USER;

    const mailOptions = {
        from,
        to: toEmail,
        subject: 'Tu contraseña temporal - Sistema de Gestión de Medicamentos',
        html: `
            <p>Hola <strong>${username}</strong>,</p>
            <p>Se ha generado una contraseña temporal para que puedas acceder al sistema:</p>
            <p style="font-size: 18px;"><strong>${tempPassword}</strong></p>
            <p>Por seguridad, deberás cambiar esta contraseña cuando inicies sesión.</p>
            <br>
            <p>Si tú no solicitaste esta cuenta o este cambio, por favor informa al administrador del sistema.</p>
        `,
    };

    try {
        await mailTransporter.sendMail(mailOptions);
        console.log(`Correo de contraseña temporal enviado a ${toEmail}`);
    } catch (error) {
        console.error('Error al enviar correo de contraseña temporal:', error);
    }
}

// ==============================
// REGISTER
// ==============================
async function register(req, res) {
    try {
        // Note: API still receives Spanish field names from frontend
        let { usuario, correo, password, nombre, apellido } = req.body;

        if (!usuario || !correo) {
            return res.status(400).json({ message: 'Faltan campos obligatorios (usuario, correo).' });
        }

        const username = String(usuario).toUpperCase().trim();
        const email = String(correo).toLowerCase().trim();

        const usernameConstraints = await getUsernameConstraints();
        if (/\s/.test(username)) {
            return res.status(400).json({ message: 'El usuario no debe contener espacios.' });
        }
        if (username.length < usernameConstraints.min || username.length > usernameConstraints.max) {
            return res.status(400).json({
                message: `El usuario debe tener entre ${usernameConstraints.min} y ${usernameConstraints.max} caracteres.`
            });
        }

        const [existingUsers] = await db.execute(
            'SELECT * FROM tbl_seg_usuario WHERE correo = ? OR usuario = ?',
            [email, username]
        );

        if (existingUsers.length > 0) {
            return res.status(409).json({ message: 'El correo o nombre de usuario ya están registrados.' });
        }

        let plainPassword = password;
        if (!plainPassword) {
            const constraints = await getPasswordPolicyConstraints();
            plainPassword = generateTempPassword(Math.min(Math.max(constraints.min, 10), constraints.max));
        }

        const policyError = await validatePasswordPolicy(plainPassword, username);
        if (policyError) {
            return res.status(400).json({ message: policyError });
        }

        const passwordHash = await bcrypt.hash(plainPassword, 10);

        await db.execute(
            `INSERT INTO tbl_seg_usuario 
                (usuario, correo, contrasena_hash, nombre, apellido, estado)
             VALUES (?, ?, ?, ?, ?, 'NUEVO')`,
            [username, email, passwordHash, nombre, apellido]
        );

        // 📧 Enviar contraseña temporal por correo
        await sendTempPasswordEmail(email, username, plainPassword);

        return res.status(201).json({
            message: 'Usuario registrado exitosamente. Se ha enviado una contraseña temporal al correo registrado.'
        });

    } catch (error) {
        console.error('Error al registrar:', error);
        return res.status(500).json({ message: 'Error interno del servidor al registrar.' });
    }
}

// ==============================
// LOGIN (BY USERNAME)
// ==============================
async function login(req, res) {
    try {
        let { usuario, password } = req.body;

        const rawUsuario = String(usuario || '');
        const rawPassword = String(password || '');

        const missing = [];
        if (!rawUsuario.trim()) missing.push('usuario');
        if (!rawPassword.trim()) missing.push('contrasena');
        if (missing.length > 0) {
            return res.status(400).json({
                message: missing.length === 2
                    ? 'Debe ingresar usuario y contraseña.'
                    : `Debe ingresar ${missing[0]}.`
            });
        }

        if (/\s/.test(rawUsuario) || /\s/.test(rawPassword)) {
            return res.status(400).json({ message: 'Usuario y contraseña no permiten espacios en blanco.' });
        }

        const username = rawUsuario.toUpperCase().trim();
        const plainPassword = rawPassword;

        const usernameConstraints = await getUsernameConstraints();
        if (username.length < usernameConstraints.min || username.length > usernameConstraints.max) {
            return res.status(400).json({
                message: `El usuario debe tener entre ${usernameConstraints.min} y ${usernameConstraints.max} caracteres.`
            });
        }

        const pwdConstraints = await getPasswordPolicyConstraints();
        if (plainPassword.length < pwdConstraints.min || plainPassword.length > pwdConstraints.max) {
            return res.status(400).json({
                message: `La contraseña debe tener entre ${pwdConstraints.min} y ${pwdConstraints.max} caracteres.`
            });
        }

        const maxAttempts = await getMaxAttempts();

        const [rows] = await db.execute(
            `SELECT u.*, r.id_rol, r.nombre AS rol_nombre
             FROM tbl_seg_usuario u
             LEFT JOIN tbl_seg_rol r ON u.id_rol = r.id_rol
             WHERE u.usuario = ?`,
            [username]
        );

        if (rows.length === 0) {
            // Usuario inexistente: usar contador en memoria porque no hay fila en BD.
            loginAttempts[username] = (loginAttempts[username] || 0) + 1;
            const remaining = Math.max(0, maxAttempts - loginAttempts[username]);

            return res.status(401).json({
                message: `Usuario o contraseña inválidos. Intentos restantes: ${remaining}.`
            });
        }

        const user = rows[0];

        if (user.estado === 'BLOQUEADO') {
            return res.status(403).json({ message: 'Usuario bloqueado. Solicite activación al administrador o use recuperación de contraseña.' });
        }

        if (user.estado !== 'ACTIVO' && user.estado !== 'NUEVO') {
            return res.status(403).json({ message: 'Solo usuarios activos pueden ingresar al sistema.' });
        }

        // Check temp password lifetime if user is NEW
        if (user.estado === 'NUEVO') {
            const hoursLifetime = await getTempPasswordLifetimeHours();
            const lastDateText = user.fecha_modificacion || user.fecha_creacion;

            if (lastDateText) {
                const now = new Date();
                const lastDate = new Date(lastDateText);
                const diffHours = (now - lastDate) / (1000 * 60 * 60);

                if (diffHours > hoursLifetime) {
                    return res.status(403).json({
                        message: 'La contraseña temporal ha expirado. Solicite recuperación nuevamente.'
                    });
                }
            }
        }

        // Normalizar el prefijo $2y$ (PHP/Laravel) a $2b$ (Node.js) que es el mismo
        // algoritmo bcrypt pero Node v6 sólo acepta $2b$/$2a$.
        let storedHash = (user.contrasena_hash || '');
        if (storedHash.startsWith('$2y$')) {
            storedHash = '$2b$' + storedHash.slice(4);
        }

        const isValidPassword = storedHash ? await bcrypt.compare(plainPassword, storedHash) : false;

        if (!isValidPassword) {
            // Leer siempre el valor actual de la BD si la columna existe;
            // si no existe, usar contador en memoria como fallback.
            const hasIntentos = await hasUserColumn('intentos_fallidos_login');
            const currentFailed = hasIntentos
                ? Number(user.intentos_fallidos_login || 0)
                : (loginAttempts[username] || 0);
            const newFailed = currentFailed + 1;
            const remaining = Math.max(0, maxAttempts - newFailed);

            // Actualizar BD (si columna existe) y también memoria.
            loginAttempts[username] = newFailed;
            await setFailedAttempts(username, newFailed, { lockUser: newFailed >= maxAttempts });

            if (newFailed >= maxAttempts) {
                return res.status(403).json({
                    message: `Usuario bloqueado por intentos fallidos. Alcanzó el límite de ${maxAttempts} intento${maxAttempts !== 1 ? 's' : ''}.`
                });
            }

            return res.status(401).json({
                message: `Usuario o contraseña inválidos. Intentos restantes: ${remaining}.`
            });
        }

        // Reset al lograr ingreso exitoso.
        loginAttempts[username] = 0;
        await setFailedAttempts(username, 0);

        // ======================================
        // 2FA: Generate and send token
        // ======================================
        const token2fa = generate2FAToken();
        const ahora = new Date();
        const expirationMinutes = await get2FAExpirationMinutes();
        const expiracion = new Date(ahora.getTime() + expirationMinutes * 60 * 1000);

        // Store 2FA token in database
        await db.execute(
            `UPDATE tbl_seg_usuario 
             SET token_2fa = ?, fecha_expiracion_2fa = ?
             WHERE id_usuario = ?`,
            [token2fa, expiracion, user.id_usuario]
        );

        // Send 2FA token via email
        await send2FATokenEmail(user.correo, user.usuario, token2fa, expirationMinutes);

        // Store temporary JWT (sin expiración de 2h, solo para validar 2fa)
        const tempToken = jwt.sign(
            {
                id: user.id_usuario,
                usuario: user.usuario,
                correo: user.correo,
                nombre: user.nombre,
                apellido: user.apellido,
                telefono: user.telefono,
                pendiente_2fa: true,
            },
            SECRET_KEY,
            { expiresIn: '15m' } // Token temporal válido solo 15 minutos
        );

        const cookieOptions = {
            httpOnly: true,
            sameSite: 'lax',
            maxAge: 15 * 60 * 1000, // 15 minutos
        };

        if (process.env.NODE_ENV === 'production') {
            cookieOptions.secure = true;
        }

        res.cookie('jwt_token_2fa_pending', tempToken, cookieOptions);

        await registerAudit({
            idUsuario: user.id_usuario,
            usuario: user.usuario,
            accion: 'LOGIN PENDIENTE 2FA',
            formulario: 'SEGURIDAD',
            descripcion: `Código 2FA generado y enviado al correo ${user.correo}`,
            ruta: req.originalUrl?.split('?')[0] || '/api/login',
            direccionIp: getClientIp(req),
        });

        return res.json({
            message: 'Se ha enviado un código de autenticación a tu correo. Ingrésalo para continuar.',
            requiere_2fa: true,
            usuario: user.usuario,
            token_2fa_pendiente: tempToken,
            expira_en_minutos: expirationMinutes
        });

    } catch (error) {
        console.error('Error en el login:', error);
        return res.status(500).json({ message: 'Error interno del servidor.' });
    }
}

// ==============================
// CHANGE PASSWORD
// ==============================
async function changePassword(req, res) {
    try {
        // Accept both Spanish and English field names for robustness.
        const {
            contrasena_actual,
            contrasena_nueva,
            confirmar_contrasena,
            current_password,
            new_password,
            confirm_password,
        } = req.body;

        // Normalize names for backward-compatibility and multiple clients.
        const actualPassword = contrasena_actual || current_password;
        const newPassword = contrasena_nueva || new_password;
        const confirmPassword = confirmar_contrasena || confirm_password;

        const userId = req.user.id;

        const missingFields = [];
        if (!newPassword) missingFields.push('contrasena_nueva');
        if (!confirmPassword) missingFields.push('confirmar_contrasena');
        if (missingFields.length > 0) {
            return res.status(400).json({
                message: 'Todos los campos son obligatorios.',
                missing: missingFields,
            });
        }

        if (newPassword !== confirmPassword) {
            return res.status(400).json({ message: 'La nueva contraseña y la confirmación no coinciden.' });
        }

        const [rows] = await db.execute(
            'SELECT * FROM tbl_seg_usuario WHERE id_usuario = ?',
            [userId]
        );

        if (rows.length === 0) {
            return res.status(404).json({ message: 'Usuario no encontrado.' });
        }

        const user = rows[0];

        // Si proporcionaron contraseña actual, validarla. Si no, solo permitimos el cambio cuando el usuario está en estado NUEVO.
        if (actualPassword) {
            let currentHash = (user.contrasena_hash || '');
            if (currentHash.startsWith('$2y$')) currentHash = '$2b$' + currentHash.slice(4);
            const isCurrentCorrect = await bcrypt.compare(actualPassword, currentHash);
            if (!isCurrentCorrect) {
                return res.status(400).json({ message: 'La contraseña actual es incorrecta.' });
            }
        } else if (user.estado !== 'NUEVO') {
            return res.status(400).json({ message: 'La contraseña actual es obligatoria.' });
        }

        const policyError = await validatePasswordPolicy(newPassword, user.usuario);
        if (policyError) {
            return res.status(400).json({ message: policyError });
        }

        let oldHash = (user.contrasena_hash || '');
        if (oldHash.startsWith('$2y$')) oldHash = '$2b$' + oldHash.slice(4);
        const isSameAsOld = await bcrypt.compare(newPassword, oldHash);
        if (isSameAsOld) {
            return res.status(400).json({
                message: 'La nueva contraseña no debe ser igual a la anterior.'
            });
        }

        const newHash = await bcrypt.hash(newPassword, 10);
        const newState = (user.estado === 'NUEVO') ? 'ACTIVO' : user.estado;

        await db.execute(
            `UPDATE tbl_seg_usuario
             SET contrasena_hash = ?, estado = ?, omitir_preguntas_recuperacion = 'NO'
             WHERE id_usuario = ?`,
            [newHash, newState, userId]
        );

        return res.json({
            message: 'Contraseña actualizada correctamente.',
            nuevo_estado: newState
        });

    } catch (error) {
        console.error('Error al cambiar contraseña:', error);
        return res.status(500).json({ message: 'Error interno al cambiar la contraseña.' });
    }
}

// ==============================
// RECOVER PASSWORD (FORGOT PASSWORD)
// ==============================
async function recoverPassword(req, res) {
    try {
        let { usuario } = req.body;

        if (!usuario || !String(usuario).trim()) {
            return res.status(400).json({ message: 'El usuario es obligatorio.' });
        }

        if (/\s/.test(String(usuario))) {
            return res.status(400).json({ message: 'El usuario no debe contener espacios en blanco.' });
        }

        const username = String(usuario).toUpperCase().trim();

        const usernameConstraints = await getUsernameConstraints();
        if (username.length < usernameConstraints.min || username.length > usernameConstraints.max) {
            return res.status(400).json({
                message: `El usuario debe tener entre ${usernameConstraints.min} y ${usernameConstraints.max} caracteres.`
            });
        }

        const [rows] = await db.execute(
            'SELECT * FROM tbl_seg_usuario WHERE usuario = ?',
            [username]
        );

        if (rows.length === 0) {
            return res.status(404).json({ message: 'El usuario no existe.' });
        }

        const user = rows[0];

        if (user.estado === 'INACTIVO') {
            return res.status(403).json({
                message: 'El usuario está inactivo. Contacte al administrador.'
            });
        }

        const constraints = await getPasswordPolicyConstraints();
        const tempPassword = generateTempPassword(Math.min(Math.max(constraints.min, 10), constraints.max));

        const policyError = await validatePasswordPolicy(tempPassword, user.usuario);
        if (policyError) {
            return res.status(500).json({
                message: 'Error al generar la contraseña temporal.',
                detalle: policyError
            });
        }

        const tempHash = await bcrypt.hash(tempPassword, 10);

        await db.execute(
            `UPDATE tbl_seg_usuario
             SET contrasena_hash = ?, estado = 'NUEVO', fecha_modificacion = NOW(), omitir_preguntas_recuperacion = 'SI'
             WHERE id_usuario = ?`,
            [tempHash, user.id_usuario]
        );

        // 📧 Enviar contraseña temporal por correo
        await sendTempPasswordEmail(user.correo, user.usuario, tempPassword);

        return res.json({
            message: 'Se ha generado una contraseña temporal y se ha enviado al correo registrado.'
        });

    } catch (error) {
        console.error('Error en recoverPassword:', error);
        return res.status(500).json({ message: 'Error interno al recuperar la contraseña.' });
    }
}

// ==============================
// PROFILE (PROTECTED ROUTE)
// ==============================
async function profile(req, res) {
    return res.json({
        message: '¡Acceso autorizado!',
        datos_del_token: req.user
    });
}

// ==============================
// LOGOUT / CERRAR SESIÓN
// ==============================
async function logout(req, res) {
    try {
        if (req.user?.id) {
            await registerAudit({
                idUsuario: req.user.id,
                usuario: req.user.usuario,
                accion: 'CERRAR SESION',
                formulario: 'SEGURIDAD',
                descripcion: `Cierre de sesion del usuario ${req.user.usuario || req.user.id}`,
                ruta: req.originalUrl?.split('?')[0] || '/api/logout',
                direccionIp: getClientIp(req),
            });
        }
    } catch (error) {
        console.error('No se pudo registrar cierre de sesion:', error.message);
    }

    const cookieOptions = {
        httpOnly: true,
        sameSite: 'lax'
    };

    if (process.env.NODE_ENV === 'production') {
        cookieOptions.secure = true;
    }

    res.clearCookie('jwt_token', cookieOptions);
    res.clearCookie('token', cookieOptions);

    return res.json({ message: 'Sesión cerrada correctamente.' });
}

// ==============================
// VERIFY 2FA TOKEN
// ==============================
async function verify2FA(req, res) {
    try {
        // Get the temporary token from request body or cookie.
        const tempToken = req.body?.token_2fa_pendiente || req.cookies?.jwt_token_2fa_pending;
        if (!tempToken) {
            return res.status(401).json({ message: 'Sesión 2FA no válida. Por favor, inicia sesión nuevamente.' });
        }

        let decodedToken;
        try {
            decodedToken = jwt.verify(tempToken, SECRET_KEY);
        } catch (error) {
            return res.status(401).json({ message: 'Su sesión temporal ha expirado. Por favor, inicie sesión nuevamente.' });
        }

        if (!decodedToken.pendiente_2fa) {
            return res.status(400).json({ message: 'No hay autenticación de dos pasos pendiente.' });
        }

        const { codigo2fa } = req.body;

        if (!codigo2fa || String(codigo2fa).trim().length !== 6) {
            return res.status(400).json({ message: 'El código debe tener exactamente 6 dígitos.' });
        }

        const [rows] = await db.execute(
            'SELECT * FROM tbl_seg_usuario WHERE id_usuario = ?',
            [decodedToken.id]
        );

        if (rows.length === 0) {
            return res.status(404).json({ message: 'Usuario no encontrado.' });
        }

        const user = rows[0];

        // Check if token has expired
        if (!user.fecha_expiracion_2fa || new Date() > new Date(user.fecha_expiracion_2fa)) {
            return res.status(400).json({ message: 'El código de autenticación ha expirado. Por favor, vuelva a iniciar sesión.' });
        }

        // Verify the code
        if (String(user.token_2fa) !== String(codigo2fa).trim()) {
            return res.status(400).json({ message: 'El código de autenticación es incorrecto.' });
        }

        // Clear the 2FA token
        await db.execute(
            'UPDATE tbl_seg_usuario SET token_2fa = NULL, fecha_expiracion_2fa = NULL WHERE id_usuario = ?',
            [user.id_usuario]
        );

        // Generate final JWT token
        const finalToken = jwt.sign(
            {
                id: user.id_usuario,
                usuario: user.usuario,
                correo: user.correo,
                nombre: user.nombre,
                apellido: user.apellido,
                telefono: user.telefono,
            },
            SECRET_KEY,
            { expiresIn: '2h' }
        );

        const cookieOptions = {
            httpOnly: true,
            sameSite: 'lax',
            maxAge: 2 * 60 * 60 * 1000, // 2 horas
        };

        if (process.env.NODE_ENV === 'production') {
            cookieOptions.secure = true;
        }

        res.cookie('jwt_token', finalToken, cookieOptions);
        res.clearCookie('jwt_token_2fa_pending', cookieOptions);

        await registerAudit({
            idUsuario: user.id_usuario,
            usuario: user.usuario,
            accion: '2FA VERIFICADO',
            formulario: 'SEGURIDAD',
            descripcion: `Autenticación en dos pasos verificada correctamente`,
            accionDescripcion: 'VERIFICACION 2FA',
            ruta: req.originalUrl?.split('?')[0] || '/api/verify-2fa',
            direccionIp: getClientIp(req),
        });

        if (user.estado === 'NUEVO') {
            return res.json({
                message: 'Debe cambiar su contraseña antes de continuar.',
                requiere_cambio_contrasena: true,
                omitir_preguntas_recuperacion: String(user.omitir_preguntas_recuperacion || 'NO').toUpperCase() === 'SI',
                token: finalToken
            });
        }

        return res.json({
            message: `Bienvenido ${user.nombre || user.usuario}, acceso concedido.`,
            requiere_cambio_contrasena: false,
            omitir_preguntas_recuperacion: false,
            token: finalToken
        });

    } catch (error) {
        console.error('Error en verify2FA:', error);
        return res.status(500).json({ message: 'Error interno al verificar el código de autenticación.' });
    }
}

// ==============================
// AUDIT LOG (BITÁCORA)
// ==============================
async function getAuditLog(req, res) {
    try {
        const { usuario, accion, fecha_desde, fecha_hasta } = req.query;

        const conditions = [];
        const params = [];

        if (usuario && String(usuario).trim()) {
            conditions.push("COALESCE(b.usuario, u.usuario, '') LIKE ?");
            params.push(`%${String(usuario).trim()}%`);
        }

        if (accion && String(accion).trim()) {
            conditions.push("COALESCE(a.accion, '') LIKE ?");
            params.push(`%${String(accion).trim()}%`);
        }

        if (fecha_desde && String(fecha_desde).trim()) {
            conditions.push("DATE(b.fecha_hora) >= ?");
            params.push(String(fecha_desde).trim());
        }

        if (fecha_hasta && String(fecha_hasta).trim()) {
            conditions.push("DATE(b.fecha_hora) <= ?");
            params.push(String(fecha_hasta).trim());
        }

        const whereClause = conditions.length > 0 ? `WHERE ${conditions.join(' AND ')}` : '';

        const query = `
            SELECT 
                b.id_bitacora,
                COALESCE(b.usuario, u.usuario, 'USUARIO ELIMINADO') AS nombre_usuario,
                COALESCE(a.accion, 'SIN ACCION') AS tipo_accion,
                COALESCE(f.descripcion, 'SIN FORMULARIO') AS nombre_formulario,
                b.descripcion,
                COALESCE(
                    NULLIF(TRIM(b.ruta), ''),
                    CASE
                        WHEN UPPER(COALESCE(a.accion, '')) = 'INICIAR SESION' THEN '/api/login'
                        WHEN UPPER(COALESCE(a.accion, '')) = 'CERRAR SESION' THEN '/api/logout'
                        ELSE NULL
                    END
                ) AS ruta,
                b.direccion_ip,
                DATE_FORMAT(b.fecha_hora, '%d/%m/%Y %H:%i:%s') AS fecha
            FROM tbl_bitacora_auditoria b
            LEFT JOIN tbl_seg_usuario u ON b.id_usuario = u.id_usuario
            LEFT JOIN tbl_seg_tipo_accion a ON b.id_tipo_accion = a.id_tipo_accion
            LEFT JOIN tbl_seg_formulario f ON b.id_formulario = f.id_formulario
            ${whereClause}
            ORDER BY b.fecha_hora DESC 
        `;
        const [rows] = await db.execute(query, params);
        return res.json(rows);
    } catch (error) {
        console.error('Error en bitácora:', error);
        return res.status(500).json({ message: 'Error al obtener los registros de la bitácora.' });
    }
}

module.exports = {
    register,
    login,
    changePassword,
    recoverPassword,
    verify2FA,
    profile,
    logout,
    getAuditLog
};