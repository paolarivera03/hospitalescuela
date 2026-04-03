const express = require('express');
const router = express.Router();
const db = require('../config/db');

const PROTECTED_PARAMETER_NAMES = new Set([
    'ADMIN_INTENTOS_FALLIDOS',
    'ADMIN_INTENTOS_INVALIDOS',
    'RECUP_CLAVE_HORAS',
    'VENCIMIENTO_2FA_MINUTOS',
    'VENCIMIENTO_SEGUNDA_FACTORIZACION_MINUTOS',
    'MIN_CONTRASENA',
    'MAX_CONTRASENA',
    'MIN_USUARIO',
    'MAX_USUARIO',
]);

function normalizeText(value) {
    return String(value || '').trim();
}

function isValidParameterName(name) {
    return /^[A-Z0-9_]+$/.test(name);
}

function validateParameterValue(nombre, valor) {
    return null;
}

router.get('/', async (req, res) => {
    try {
        const [rows] = await db.execute(
            `SELECT
                id_parametro,
                nombre_parametro,
                valor,
                ultimo_sesion,
                token,
                fecha_creacion,
                usuario_creacion,
                fecha_modificacion,
                usuario_modificacion
             FROM tbl_seg_parametro
             WHERE UPPER(nombre_parametro) <> 'ADMIN_PREGUNTAS'
             ORDER BY id_parametro ASC`
        );

        return res.json(rows);
    } catch (error) {
        console.error('Error al listar parametros:', error);
        return res.status(500).json({ message: 'No se pudieron cargar los parametros.' });
    }
});

router.post('/', async (req, res) => {
    try {
        const nombre = normalizeText(req.body.nombre_parametro).toUpperCase();
        const valor = normalizeText(req.body.valor);

        if (!nombre || !valor) {
            return res.status(400).json({ message: 'Nombre del parametro y valor son obligatorios.' });
        }

        if (!isValidParameterName(nombre)) {
            return res.status(400).json({ message: 'El nombre del parametro solo puede contener letras, numeros y guion bajo.' });
        }

        const valueError = validateParameterValue(nombre, valor);
        if (valueError) {
            return res.status(400).json({ message: valueError });
        }

        const [duplicates] = await db.execute(
            'SELECT id_parametro FROM tbl_seg_parametro WHERE UPPER(nombre_parametro) = ? LIMIT 1',
            [nombre]
        );

        if (duplicates.length > 0) {
            return res.status(409).json({ message: 'Ya existe un parametro con ese nombre.' });
        }

        await db.execute(
            `INSERT INTO tbl_seg_parametro
                (nombre_parametro, valor, estado, fecha_creacion, usuario_creacion, fecha_modificacion, usuario_modificacion)
             VALUES (?, ?, 'ACTIVO', NOW(), ?, NOW(), ?)`,
            [
                nombre,
                valor,
                req.user?.id || null,
                req.user?.id || null,
            ]
        );

        return res.status(201).json({ message: 'Parametro creado correctamente.' });
    } catch (error) {
        console.error('Error al crear parametro:', error);
        return res.status(500).json({ message: 'No se pudo crear el parametro.' });
    }
});

router.put('/:id', async (req, res) => {
    try {
        const id = Number(req.params.id);
        if (!id) {
            return res.status(400).json({ message: 'ID de parametro invalido.' });
        }

        const valor = normalizeText(req.body.valor);

        if (!valor) {
            return res.status(400).json({ message: 'El valor del parametro es obligatorio.' });
        }

        const [currentRows] = await db.execute(
            'SELECT nombre_parametro FROM tbl_seg_parametro WHERE id_parametro = ? LIMIT 1',
            [id]
        );

        if (currentRows.length === 0) {
            return res.status(404).json({ message: 'Parametro no encontrado.' });
        }

        const currentName = String(currentRows[0].nombre_parametro || '').toUpperCase();

        const valueError = validateParameterValue(currentName, valor);
        if (valueError) {
            return res.status(400).json({ message: valueError });
        }

        const [updated] = await db.execute(
            `UPDATE tbl_seg_parametro
             SET valor = ?,
                 fecha_modificacion = NOW(),
                 usuario_modificacion = ?
             WHERE id_parametro = ?`,
            [
                valor,
                req.user?.id || null,
                id,
            ]
        );

        if (updated.affectedRows === 0) {
            return res.status(404).json({ message: 'Parametro no encontrado.' });
        }

        return res.json({ message: 'Parametro actualizado correctamente.' });
    } catch (error) {
        console.error('Error al actualizar parametro:', error);
        return res.status(500).json({ message: 'No se pudo actualizar el parametro.' });
    }
});

module.exports = router;
