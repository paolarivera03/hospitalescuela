// Backend/routes/usuarios.js
const express = require('express');
const router = express.Router();

const bcrypt = require('bcrypt');
const db = require('../config/db'); // conexión MySQL (usa db.execute)
const verifyToken = require('../middleware/verificarToken');
const autorizarPermiso = require('../middleware/autorizarPermiso');
const { getCapacidadesDeRol } = require('../services/capacidades.service');
const nodemailer = require('nodemailer');
require('dotenv').config();

// ==============================
// SMTP (Gmail / App Password)
// ==============================
const mailUser = process.env.MAIL_USER || process.env.SMTP_USER;
const mailPass = process.env.MAIL_PASS || process.env.SMTP_PASS;

const mailTransporter = nodemailer.createTransport({
    service: 'gmail',
    auth: {
        user: mailUser,
        pass: mailPass,
    },
});

// ==============================
// GENERADOR DE CONTRASEÑA TEMPORAL
// (copiado de tu lógica de auth.controller)
// ==============================
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

    const value = Number(rows[0].valor);
    if (!Number.isNaN(value) && value > 0) {
        return value;
    }

    return defaultValue;
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
    const min = await getNumericParameter('MIN_USUARIO', 1, 'valor');
    const max = await getNumericParameter('MAX_USUARIO', 50, 'valor');

    const normalizedMin = Math.max(1, Number(min) || 1);
    const normalizedMax = Math.max(normalizedMin, Number(max) || 50);

    return {
        min: normalizedMin,
        max: normalizedMax,
    };
}

// ==============================
// VALIDACIÓN POLÍTICA DE CONTRASEÑA
// (misma lógica que tu backend)
// ==============================
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

function buildNombreCompleto(nombre, apellido) {
    return `${String(nombre || '').trim()} ${String(apellido || '').trim()}`
        .replace(/\s+/g, ' ')
        .trim()
        .toUpperCase();
}

async function isClinicalRole(connection, idRol) {
    if (!idRol) return false;
    // Dinámic: un rol es clínico si tiene la capacidad 'inventario_solo_stock'
    const [rows] = await connection.execute(
        `SELECT 1 FROM tbl_seg_capacidades_rol
         WHERE id_rol = ? AND capacidad = 'inventario_solo_stock'
         LIMIT 1`,
        [idRol]
    );
    return rows.length > 0;
}

async function syncFarMedico(connection, { idUsuario, nombre, apellido, usuarioCreador }) {
    const nombreCompleto = buildNombreCompleto(nombre, apellido);
    const [medicoRows] = await connection.execute(
        'SELECT id_medico FROM tbl_far_medico WHERE id_usuario = ? LIMIT 1',
        [idUsuario]
    );

    if (medicoRows.length > 0) {
        await connection.execute(
            `UPDATE tbl_far_medico
                SET nombre_completo = ?, estado = 'ACTIVO'
              WHERE id_usuario = ?`,
            [nombreCompleto, idUsuario]
        );
        return;
    }

    await connection.execute(
        `INSERT INTO tbl_far_medico
            (id_usuario, nombre_completo, numero_colegiacion, especialidad, estado, usuario_creacion)
         VALUES (?, ?, ?, 'POR DEFINIR', 'ACTIVO', ?)`,
        [idUsuario, nombreCompleto, `AUTO-${idUsuario}`, usuarioCreador || null]
    );
}

/**
 * Devuelve los permisos por defecto según las capacidades del rol.
 * Ya no depende de nombres de rol estáticos.
 * @param {string[]} capacidades - array de capacidades del rol
 * @returns {Record<number, string[]>}
 */
function getDefaultPermissionsByCapacidades(capacidades) {
    const allActions = ['VISUALIZAR', 'GUARDAR', 'ACTUALIZAR', 'ELIMINAR'];

    // Acceso total: todos los módulos con todas las acciones
    if (capacidades.includes('acceso_total')) {
        return {
            1: allActions,
            3: allActions,
            4: allActions,
            5: allActions,
            8: allActions,
            10: allActions,
            11: allActions,
            12: allActions,
            13: allActions,
        };
    }

    // Inventario completo + reacciones adversas (ej. Jefe)
    if (capacidades.includes('ver_inventario_completo')) {
        return {
            1: allActions,
            3: allActions,
        };
    }

    // Solo stock en inventario + reacciones adversas (ej. clínico)
    if (capacidades.includes('inventario_solo_stock')) {
        return {
            1: ['VISUALIZAR'],
            3: allActions,
        };
    }

    return {};
}

async function seedPermissionsForNewUser(connection, { idUsuario, idRol }) {
    if (!idRol || !idUsuario) return;

    const [sourceRows] = await connection.execute(
        `SELECT u.id_usuario
         FROM tbl_seg_usuario u
         WHERE u.id_rol = ?
           AND u.id_usuario <> ?
           AND EXISTS (
               SELECT 1
               FROM tbl_seg_permisos p
               WHERE p.id_usuario = u.id_usuario
           )
         ORDER BY u.id_usuario DESC
         LIMIT 1`,
        [idRol, idUsuario]
    );

    let rowsToInsert = [];

    if (sourceRows.length > 0) {
        const sourceUserId = sourceRows[0].id_usuario;
        const [sourcePerms] = await connection.execute(
            `SELECT id_formulario, UPPER(accion) AS accion
             FROM tbl_seg_permisos
             WHERE id_usuario = ?`,
            [sourceUserId]
        );

        rowsToInsert = sourcePerms.map((perm) => [idUsuario, perm.id_formulario, perm.accion]);
    } else {
        // Obtener capacidades del rol y derivar permisos dinámicamente
        const capacidades = await getCapacidadesDeRol(idRol);
        const defaults = getDefaultPermissionsByCapacidades(capacidades);

        Object.entries(defaults).forEach(([idFormulario, acciones]) => {
            acciones.forEach((accion) => {
                rowsToInsert.push([idUsuario, Number(idFormulario), String(accion).toUpperCase()]);
            });
        });
    }

    if (!rowsToInsert.length) return;

    await connection.query(
        'INSERT INTO tbl_seg_permisos (id_usuario, id_formulario, accion) VALUES ?',
        [rowsToInsert]
    );
}

// ==============================
// ENVIAR CONTRASEÑA TEMPORAL POR CORREO
// ==============================
async function sendTempPasswordEmail(toEmail, username, tempPassword) {
    if (!toEmail) {
        console.warn('Intento de enviar correo sin email destino.');
        return;
    }

    const from = mailUser;

    const mailOptions = {
        from,
        to: toEmail,
        subject: 'Cuenta creada - Contraseña temporal',
        html: `
            <p>Hola <strong>${username}</strong>,</p>
            <p>Se ha creado/actualizado una cuenta en el <strong>Sistema de Gestión de Medicamentos</strong>.</p>
            <p>Tu contraseña temporal es:</p>
            <p style="font-size: 18px; font-weight: bold;">${tempPassword}</p>
            <p>Por seguridad, deberás cambiar esta contraseña al iniciar sesión.</p>
            <p>Si tú no solicitaste esta cuenta, contacta al administrador del sistema.</p>
        `,
    };

    try {
        await mailTransporter.sendMail(mailOptions);
        console.log(`Correo de contraseña temporal enviado a ${toEmail}`);
    } catch (error) {
        console.error('Error al enviar correo de contraseña temporal (usuarios.js):', error);
    }
}

async function getDuplicateUserFields(username, email, excludeUserId = null) {
    let query = `
        SELECT id_usuario, usuario, correo
        FROM tbl_seg_usuario
        WHERE (usuario = ? OR correo = ?)
    `;
    const params = [username, email];

    if (excludeUserId !== null) {
        query += ' AND id_usuario <> ?';
        params.push(excludeUserId);
    }

    const [rows] = await db.execute(query, params);

    const errors = {};
    if (rows.some((row) => String(row.usuario).toUpperCase() === username)) {
        errors.usuario = 'El usuario ya está registrado.';
    }
    if (rows.some((row) => String(row.correo).toLowerCase() === email)) {
        errors.correo = 'El correo ya está registrado.';
    }

    return errors;
}

// ==========================================
// 1. OBTENER TODOS LOS USUARIOS (GET /api/usuarios)
// ==========================================
router.get('/', verifyToken, autorizarPermiso(4, 'VISUALIZAR'), async (req, res) => {
    console.log('DEBUG: GET /api/usuarios');
    try {
        const { search, sort } = req.query;
        const page = parseInt(req.query.page) || 1;
        const limit = parseInt(req.query.limit) || 10;
        const offset = (page - 1) * limit;
        const orderDirection = (sort === 'asc') ? 'ASC' : 'DESC';

        let whereClause = '';
        const params = [];

        if (search && search.trim() !== '') {
            whereClause = ` WHERE CAST(u.id_usuario AS CHAR) LIKE ?
                           OR UPPER(u.usuario) LIKE ?
                           OR UPPER(CONCAT(u.nombre, ' ', IFNULL(u.apellido, ''))) LIKE ?
                           OR UPPER(u.correo) LIKE ?`;
            const searchParam = `%${search.toUpperCase()}%`;
            params.push(searchParam, searchParam, searchParam, searchParam);
        }

        const query = `
            SELECT 
                u.id_usuario,
                u.usuario,
                u.correo,
                u.nombre,
                u.apellido,
                u.telefono,
                u.estado,
                u.id_rol,
                IFNULL(r.nombre, 'SIN ROL') AS rol_nombre
            FROM tbl_seg_usuario u
            LEFT JOIN tbl_seg_rol r ON u.id_rol = r.id_rol
            ${whereClause}
            ORDER BY u.id_usuario ${orderDirection}
            LIMIT ? OFFSET ?
        `;

        const [usuarios] = await db.execute(query, [...params, limit, offset]);

        const countQuery = `SELECT COUNT(*) AS count FROM tbl_seg_usuario u ${whereClause}`;
        const [countRows] = await db.execute(countQuery, params);
        const totalRecords = countRows[0].count;

        return res.json({
            data: usuarios,
            pagination: {
                totalRecords,
                currentPage: page,
                totalPages: Math.ceil(totalRecords / limit),
                limit
            }
        });
    } catch (error) {
        console.error('Error al obtener usuarios:', error);
        return res.status(500).json({ message: 'Error al obtener los usuarios.' });
    }
});

// ==========================================
// 2. OBTENER UN USUARIO POR ID (GET /api/usuarios/:id)
// ==========================================
router.get('/:id', verifyToken, autorizarPermiso(4, 'VISUALIZAR'), async (req, res) => {
    try {
        const { id } = req.params;

        const [rows] = await db.execute(`
            SELECT 
                u.id_usuario,
                u.usuario,
                u.correo,
                u.nombre,
                u.apellido,
                u.telefono,
                u.estado,
                u.id_rol,
                IFNULL(r.nombre, 'SIN ROL') AS rol_nombre
            FROM tbl_seg_usuario u
            LEFT JOIN tbl_seg_rol r ON u.id_rol = r.id_rol
            WHERE u.id_usuario = ?
        `, [id]);

        if (rows.length === 0) {
            return res.status(404).json({ message: 'Usuario no encontrado.' });
        }

        console.log('DEBUG: usuario cargado', rows[0]);
        return res.json(rows[0]);
    } catch (error) {
        console.error('Error al obtener usuario por ID:', error);
        return res.status(500).json({ message: 'Error al obtener el usuario.' });
    }
});

// ==========================================
// 3. CREAR UN NUEVO USUARIO (POST /api/usuarios)
//    → Genera CONTRASEÑA TEMPORAL y la envía por correo
// ==========================================
router.post('/', verifyToken, autorizarPermiso(4, 'GUARDAR'), async (req, res) => {
    let connection;
    try {
        let { usuario, correo, nombre, apellido, telefono, rol } = req.body;

        if (!usuario || !correo || !nombre || !apellido) {
            return res.status(400).json({ message: 'Faltan campos obligatorios.' });
        }

        const username = String(usuario).toUpperCase().trim();
        const email = String(correo).toLowerCase().trim();

        const usernameConstraints = await getUsernameConstraints();
        if (/\s/.test(username)) {
            return res.status(400).json({ message: 'El usuario no debe contener espacios en blanco.' });
        }
        if (username.length < usernameConstraints.min || username.length > usernameConstraints.max) {
            return res.status(400).json({
                message: `El usuario debe tener entre ${usernameConstraints.min} y ${usernameConstraints.max} caracteres.`
            });
        }

        // Verificar que no exista el usuario/correo
        const duplicateErrors = await getDuplicateUserFields(username, email);

        if (Object.keys(duplicateErrors).length > 0) {
            return res.status(409).json({
                message: 'El usuario o el correo ya están registrados.',
                errors: duplicateErrors,
            });
        }

        // Generar contraseña temporal y validar política
        const constraints = await getPasswordPolicyConstraints();
        const plainPassword = generateTempPassword(Math.min(Math.max(constraints.min, 10), constraints.max));
        const policyError = await validatePasswordPolicy(plainPassword, username);
        if (policyError) {
            return res.status(500).json({
                message: 'Error al generar la contraseña temporal.',
                detalle: policyError
            });
        }

        const passwordHash = await bcrypt.hash(plainPassword, 10);

        // Resolver rol (puede venir como id o nombre) o quedar NULL si no se especifica
        let id_rol = null;
        if (rol) {
            const [rolesFound] = await db.execute(
                'SELECT id_rol FROM tbl_seg_rol WHERE id_rol = ? OR nombre = ? LIMIT 1',
                [rol, rol]
            );
            if (rolesFound.length > 0) id_rol = rolesFound[0].id_rol;
        }

        connection = await db.getConnection();
        await connection.beginTransaction();

        const [insertResult] = await connection.execute(
            `INSERT INTO tbl_seg_usuario
                (usuario, correo, contrasena_hash, nombre, apellido, telefono, estado, id_rol)
             VALUES (?, ?, ?, ?, ?, ?, 'NUEVO', ?)`,
            [username, email, passwordHash, nombre, apellido, telefono || null, id_rol]
        );

        if (await isClinicalRole(connection, id_rol)) {
            await syncFarMedico(connection, {
                idUsuario: insertResult.insertId,
                nombre,
                apellido,
                usuarioCreador: req.user?.usuario || null,
            });
        }

        await seedPermissionsForNewUser(connection, {
            idUsuario: insertResult.insertId,
            idRol: id_rol,
        });

        await connection.commit();

        // Enviar contraseña temporal por correo
        await sendTempPasswordEmail(email, username, plainPassword);

        return res.status(201).json({
            message: 'Usuario creado exitosamente. Se ha enviado una contraseña temporal al correo registrado.'
        });

    } catch (error) {
        if (connection) {
            try {
                await connection.rollback();
            } catch (rollbackError) {
                console.error('Error al revertir transacción de creación de usuario:', rollbackError);
            }
        }
        console.error('Error al crear usuario:', error);

        if (error.code === 'ER_DUP_ENTRY') {
            return res.status(409).json({ message: 'El usuario o el correo ya están registrados.' });
        }

        return res.status(500).json({ message: 'Error al crear el usuario.' });
    }
    finally {
        if (connection) {
            connection.release();
        }
    }
});

// ==========================================
// 4. ACTUALIZAR UN USUARIO (PUT /api/usuarios/:id)
// ==========================================
router.put('/:id', verifyToken, autorizarPermiso(4, 'ACTUALIZAR'), async (req, res) => {
    let connection;
    try {
        const { id } = req.params;
        let { usuario, correo, nombre, apellido, telefono, estado, rol } = req.body;

        if (!usuario || !correo || !nombre || !apellido || !estado) {
            return res.status(400).json({ message: 'Faltan campos obligatorios.' });
        }

        const username = String(usuario).toUpperCase().trim();
        const email = String(correo).toLowerCase().trim();

        const usernameConstraints = await getUsernameConstraints();
        if (/\s/.test(username)) {
            return res.status(400).json({ message: 'El usuario no debe contener espacios en blanco.' });
        }
        if (username.length < usernameConstraints.min || username.length > usernameConstraints.max) {
            return res.status(400).json({
                message: `El usuario debe tener entre ${usernameConstraints.min} y ${usernameConstraints.max} caracteres.`
            });
        }

        // Validar que usuario/correo no estén duplicados en OTRO registro
        const duplicateErrors = await getDuplicateUserFields(username, email, id);

        if (Object.keys(duplicateErrors).length > 0) {
            return res.status(409).json({
                message: 'El usuario o el correo ya están en uso por otro registro.',
                errors: duplicateErrors,
            });
        }

        connection = await db.getConnection();
        await connection.beginTransaction();

        const [currentRows] = await connection.execute(
            'SELECT usuario FROM tbl_seg_usuario WHERE id_usuario = ? LIMIT 1',
            [id]
        );

        if (currentRows.length === 0) {
            await connection.rollback();
            return res.status(404).json({ message: 'Usuario no encontrado.' });
        }

        const previousUsername = String(currentRows[0].usuario || '').toUpperCase().trim();

        // Resolver rol (puede venir como id o nombre) o quedar NULL si no se especifica
        let id_rol = null;
        if (rol) {
            const [rolesFound] = await db.execute(
                'SELECT id_rol FROM tbl_seg_rol WHERE id_rol = ? OR nombre = ? LIMIT 1',
                [rol, rol]
            );
            if (rolesFound.length > 0) id_rol = rolesFound[0].id_rol;
        }

        const [result] = await connection.execute(
            `UPDATE tbl_seg_usuario
                SET usuario = ?, correo = ?, nombre = ?, apellido = ?, telefono = ?, estado = ?, id_rol = ?
              WHERE id_usuario = ?`,
            [username, email, nombre, apellido, telefono || null, estado, id_rol, id]
        );

        if (result.affectedRows === 0) {
            await connection.rollback();
            return res.status(404).json({ message: 'Usuario no encontrado.' });
        }

        if (await isClinicalRole(connection, id_rol)) {
            await syncFarMedico(connection, {
                idUsuario: id,
                nombre,
                apellido,
                usuarioCreador: req.user?.usuario || null,
            });
        }

        await connection.commit();

        return res.json({ message: 'Usuario actualizado exitosamente.' });
    } catch (error) {
        if (connection) {
            try {
                await connection.rollback();
            } catch (rollbackError) {
                console.error('Error al revertir transacción de actualización de usuario:', rollbackError);
            }
        }
        console.error('Error al actualizar usuario:', error);

        if (error.code === 'ER_DUP_ENTRY') {
            return res.status(409).json({ message: 'El usuario o el correo ya están en uso por otro registro.' });
        }

        return res.status(500).json({ message: 'Error al actualizar el usuario.' });
    } finally {
        if (connection) {
            connection.release();
        }
    }
});

// ==========================================
// 5. BORRADO LÓGICO (DELETE /api/usuarios/:id)
// ==========================================
router.delete('/:id', verifyToken, autorizarPermiso(4, 'ELIMINAR'), async (req, res) => {
    try {
        const { id } = req.params;

        const [result] = await db.execute(
            `UPDATE tbl_seg_usuario
                SET estado = 'INACTIVO'
              WHERE id_usuario = ?`,
            [id]
        );

        if (result.affectedRows === 0) {
            return res.status(404).json({ message: 'Usuario no encontrado.' });
        }

        return res.json({ message: 'Usuario desactivado exitosamente.' });
    } catch (error) {
        console.error('Error al desactivar usuario:', error);
        return res.status(500).json({ message: 'Error al desactivar el usuario.' });
    }
});

module.exports = router;



module.exports = router;