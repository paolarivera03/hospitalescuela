const express = require('express');
const router = express.Router();
const db = require('../config/db');

function normalizeText(value) {
    return String(value || '').trim();
}

router.get('/', async (req, res) => {
    try {
        const [rows] = await db.execute(
            `SELECT id_pregunta, pregunta, estado
             FROM tbl_seg_preguntas
             ORDER BY id_pregunta ASC`
        );

        return res.json(rows);
    } catch (error) {
        console.error('Error al listar preguntas:', error);
        return res.status(500).json({ message: 'No se pudieron cargar las preguntas.' });
    }
});

router.post('/', async (req, res) => {
    try {
        const pregunta = normalizeText(req.body.pregunta);
        const estado = normalizeText(req.body.estado || 'ACTIVO').toUpperCase();

        if (!pregunta) {
            return res.status(400).json({ message: 'La pregunta es obligatoria.' });
        }

        const [duplicates] = await db.execute(
            'SELECT id_pregunta FROM tbl_seg_preguntas WHERE UPPER(pregunta) = UPPER(?) LIMIT 1',
            [pregunta]
        );

        if (duplicates.length > 0) {
            return res.status(409).json({ message: 'Esa pregunta ya existe.' });
        }

        await db.execute(
            'INSERT INTO tbl_seg_preguntas (pregunta, estado) VALUES (?, ?)',
            [pregunta, estado || 'ACTIVO']
        );

        return res.status(201).json({ message: 'Pregunta creada correctamente.' });
    } catch (error) {
        console.error('Error al crear pregunta:', error);
        return res.status(500).json({ message: 'No se pudo crear la pregunta.' });
    }
});

router.put('/:id', async (req, res) => {
    try {
        const id = Number(req.params.id);
        const pregunta = normalizeText(req.body.pregunta);
        const estado = normalizeText(req.body.estado || 'ACTIVO').toUpperCase();

        if (!id) {
            return res.status(400).json({ message: 'ID de pregunta invalido.' });
        }

        if (!pregunta) {
            return res.status(400).json({ message: 'La pregunta es obligatoria.' });
        }

        const [duplicates] = await db.execute(
            'SELECT id_pregunta FROM tbl_seg_preguntas WHERE UPPER(pregunta) = UPPER(?) AND id_pregunta <> ? LIMIT 1',
            [pregunta, id]
        );

        if (duplicates.length > 0) {
            return res.status(409).json({ message: 'Esa pregunta ya existe.' });
        }

        const [updated] = await db.execute(
            'UPDATE tbl_seg_preguntas SET pregunta = ?, estado = ? WHERE id_pregunta = ?',
            [pregunta, estado || 'ACTIVO', id]
        );

        if (updated.affectedRows === 0) {
            return res.status(404).json({ message: 'Pregunta no encontrada.' });
        }

        return res.json({ message: 'Pregunta actualizada correctamente.' });
    } catch (error) {
        console.error('Error al actualizar pregunta:', error);
        return res.status(500).json({ message: 'No se pudo actualizar la pregunta.' });
    }
});

module.exports = router;
