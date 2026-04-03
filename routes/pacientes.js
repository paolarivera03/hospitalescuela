// Backend/routes/pacientes.js
const express = require('express');
const router = express.Router();
const db = require('../config/db');
const multer = require('multer');
const path = require('path');
const fs = require('fs');

const uploadDir = path.join(__dirname, '..', 'uploads', 'pacientes');
if (!fs.existsSync(uploadDir)) {
  fs.mkdirSync(uploadDir, { recursive: true });
}

const storage = multer.diskStorage({
  destination: (_req, _file, cb) => cb(null, uploadDir),
  filename: (_req, file, cb) => {
    const ext = path.extname(file.originalname || '').toLowerCase() || '.jpg';
    cb(null, `px_${Date.now()}_${Math.round(Math.random() * 1e9)}${ext}`);
  },
});

const upload = multer({
  storage,
  limits: { fileSize: 20 * 1024 * 1024 },
  fileFilter: (_req, file, cb) => {
    const allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (allowed.includes(file.mimetype)) return cb(null, true);
    cb(new Error('Formato de imagen no permitido.'));
  },
});

async function columnExists(connection, tableName, columnName) {
  const [rows] = await connection.execute(
    `
      SELECT 1
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = ?
        AND COLUMN_NAME = ?
      LIMIT 1
    `,
    [tableName, columnName]
  );

  return rows.length > 0;
}

async function ensureColumn(connection, tableName, columnName, definition) {
  if (await columnExists(connection, tableName, columnName)) {
    return;
  }

  await connection.query(`ALTER TABLE ${tableName} ADD COLUMN ${columnName} ${definition}`);
}

function normalizarTexto(valor) {
  if (valor === undefined || valor === null) {
    return null;
  }

  const texto = String(valor).trim();
  return texto ? texto.toUpperCase() : null;
}

async function generateNumeroExpediente(connection) {
  const [[row]] = await connection.query(
    `
      SELECT COUNT(*) AS total
      FROM tbl_far_paciente
      WHERE numero_expediente LIKE CONCAT('PAC-', YEAR(CURDATE()), '-%')
    `
  );

  const correlativo = Number(row?.total || 0) + 1;
  return `PAC-${new Date().getFullYear()}-${String(correlativo).padStart(4, '0')}`;
}

async function createPacienteDirect(connection, payload) {
  const numeroExpediente = await generateNumeroExpediente(connection);

  const [result] = await connection.execute(
    `
      INSERT INTO tbl_far_paciente
      (numero_expediente, nombre, edad, sexo, sala, numero_cama, diagnostico, id_medico, usuario_creacion, fecha_creacion)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    `,
    [
      numeroExpediente,
      normalizarTexto(payload.nombre),
      payload.edad || null,
      payload.sexo || null,
      normalizarTexto(payload.sala),
      normalizarTexto(payload.numero_cama),
      normalizarTexto(payload.diagnostico),
      payload.id_medico || null,
      payload.usuario_creacion || null,
    ]
  );

  return {
    id_paciente: result.insertId,
    numero_expediente: numeroExpediente,
  };
}

// LISTAR PACIENTES (GET /api/pacientes)
router.get('/', async (req, res) => {
  try {
    const { search = '' } = req.query;

    const [rows] = await db.execute('CALL sp_ObtenerPacientesGeneral(?)', [search]);
    // MySQL devuelve un array de resultados cuando se usan PROCEDIMIENTOS
    const pacientes = Array.isArray(rows) && Array.isArray(rows[0]) ? rows[0] : [];

    res.json(pacientes);
  } catch (error) {
    console.error('Error al obtener pacientes:', error);
    res.status(500).json({ message: 'Error al obtener pacientes' });
  }
});

// OBTENER PACIENTE POR ID (GET /api/pacientes/:id)
router.get('/:id', async (req, res) => {
  try {
    const { id } = req.params;

    const [rows] = await db.execute('CALL sp_ObtenerPacientePorId(?)', [id]);
    const paciente = Array.isArray(rows) && Array.isArray(rows[0]) ? rows[0][0] : null;

    if (!paciente) {
      return res.status(404).json({ message: 'Paciente no encontrado' });
    }

    res.json(paciente);
  } catch (error) {
    console.error('Error al obtener paciente:', error);
    res.status(500).json({ message: 'Error al obtener el paciente' });
  }
});

// CREAR PACIENTE (POST /api/pacientes)
router.post('/', upload.single('foto'), async (req, res) => {
  const connection = await db.getConnection();
  try {
    const {
      nombre,
      edad,
      sexo,
      sala,
      numero_cama,
      diagnostico,
      id_medico,
      id_medicamento,
      id_lote,
      dosis_posologia,
      via_administracion,
      fecha_inicio_uso,
      fecha_fin_uso,
    } = req.body;

    if (!nombre) {
      return res.status(400).json({ message: 'El nombre del paciente es obligatorio.' });
    }

    const usuario_creacion = req.user?.id ?? req.user?.id_usuario ?? null;

    await connection.beginTransaction();

    let id_paciente;

    try {
      await connection.execute('CALL sp_InsertarPaciente(?, ?, ?, ?, ?, ?, ?, ?)', [
        nombre,
        edad || null,
        sexo || null,
        sala || null,
        numero_cama || null,
        diagnostico || null,
        id_medico || null,
        usuario_creacion
      ]);

      const [[lastInsertRow]] = await connection.execute('SELECT LAST_INSERT_ID() AS id_paciente');
      id_paciente = Number(lastInsertRow?.id_paciente || 0) || null;

      // Algunos SP insertan correctamente pero no propagan LAST_INSERT_ID().
      // En ese caso, recuperamos el ultimo registro coincidente para evitar un falso error 500.
      if (!id_paciente) {
        const [candidateRows] = await connection.execute(
          `
            SELECT id_paciente
            FROM tbl_far_paciente
            WHERE UPPER(COALESCE(nombre, '')) = UPPER(COALESCE(?, ''))
              AND (edad <=> ?)
              AND UPPER(COALESCE(sexo, '')) = UPPER(COALESCE(?, ''))
              AND UPPER(COALESCE(sala, '')) = UPPER(COALESCE(?, ''))
              AND UPPER(COALESCE(numero_cama, '')) = UPPER(COALESCE(?, ''))
              AND UPPER(COALESCE(diagnostico, '')) = UPPER(COALESCE(?, ''))
              AND (id_medico <=> ?)
            ORDER BY id_paciente DESC
            LIMIT 1
          `,
          [
            nombre || null,
            edad || null,
            sexo || null,
            sala || null,
            numero_cama || null,
            diagnostico || null,
            id_medico || null,
          ]
        );

        if (Array.isArray(candidateRows) && candidateRows.length > 0) {
          id_paciente = Number(candidateRows[0].id_paciente || 0) || null;
        }
      }
    } catch (insertError) {
      console.warn('sp_InsertarPaciente fallo, usando insercion directa:', insertError.message);
      const pacienteCreado = await createPacienteDirect(connection, {
        nombre,
        edad,
        sexo,
        sala,
        numero_cama,
        diagnostico,
        id_medico,
        usuario_creacion,
      });

      id_paciente = pacienteCreado.id_paciente;
    }

    if (!id_paciente) {
      throw new Error('No se pudo resolver el identificador del paciente creado.');
    }

    if (req.file?.filename) {
      const rutaFoto = `pacientes/${req.file.filename}`;
      try {
        await ensureColumn(connection, 'tbl_far_paciente', 'foto', 'VARCHAR(255) NULL AFTER diagnostico');
        await connection.execute(
          'UPDATE tbl_far_paciente SET foto = ? WHERE id_paciente = ?',
          [rutaFoto, id_paciente]
        );
      } catch (errorFoto) {
        console.warn('No se pudo persistir foto en tbl_far_paciente:', errorFoto.message);
      }
    }

    // Guardar medicacion asociada al registro PX en tablas relacionadas de prescripcion.
    // Si esta parte falla, no debe impedir registrar el paciente para RAM.
    if (id_medicamento && id_lote && dosis_posologia && via_administracion && fecha_inicio_uso) {
      try {
        const [prescripcionResult] = await connection.execute(`
          INSERT INTO tbl_far_prescripcion
          (id_paciente, id_medico, fecha_prescripcion, observaciones, estado_receta, usuario_creacion)
          VALUES (?, ?, NOW(), ?, 'PENDIENTE', ?)
        `, [id_paciente, id_medico || null, 'REGISTRO PX', usuario_creacion]);

        const id_prescripcion = prescripcionResult.insertId;

        try {
          await ensureColumn(connection, 'tbl_far_prescripcion_detalle', 'via_administracion', 'VARCHAR(100) NULL AFTER dosis_instrucciones');
          await ensureColumn(connection, 'tbl_far_prescripcion_detalle', 'fecha_inicio_uso', 'DATE NULL AFTER via_administracion');
          await ensureColumn(connection, 'tbl_far_prescripcion_detalle', 'fecha_fin_uso', 'DATE NULL AFTER fecha_inicio_uso');
        } catch (errAlter) {
          console.warn('No se pudieron asegurar columnas extendidas en tbl_far_prescripcion_detalle:', errAlter.message);
        }

        await connection.execute(`
          INSERT INTO tbl_far_prescripcion_detalle
          (id_prescripcion, id_medicamento, id_lote, cantidad_prescrita, dosis_instrucciones, duracion_tratamiento, via_administracion, fecha_inicio_uso, fecha_fin_uso, estado, usuario_creacion)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDIENTE', ?)
        `, [
          id_prescripcion,
          id_medicamento,
          id_lote,
          null,
          dosis_posologia,
          null,
          via_administracion,
          fecha_inicio_uso,
          fecha_fin_uso || null,
          usuario_creacion,
        ]);
      } catch (prescripcionError) {
        console.warn('Paciente creado sin prescripcion asociada:', prescripcionError.message);
      }
    }

    await connection.commit();

    res.status(201).json({ id_paciente, foto: req.file?.filename || null });
  } catch (error) {
    await connection.rollback();
    console.error('Error al crear paciente:', error);
    res.status(500).json({ message: 'Error al crear el paciente' });
  } finally {
    connection.release();
  }
});

// ACTUALIZAR PACIENTE (PUT /api/pacientes/:id)
router.put('/:id', async (req, res) => {
  try {
    const { id } = req.params;
    const { numero_expediente, nombre, edad, sexo, sala, numero_cama, diagnostico, id_medico } = req.body;

    await db.execute('CALL sp_ActualizarPaciente(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
      id,
      numero_expediente || null,
      nombre || null,
      edad || null,
      sexo || null,
      sala || null,
      numero_cama || null,
      diagnostico || null,
      id_medico || null,
    ]);

    const [[{ affectedRows }]] = await db.execute('SELECT ROW_COUNT() AS affectedRows');

    if (!affectedRows) {
      return res.status(404).json({ message: 'Paciente no encontrado' });
    }

    res.json({ message: 'Paciente actualizado correctamente' });
  } catch (error) {
    console.error('Error al actualizar paciente:', error);
    res.status(500).json({ message: 'Error al actualizar el paciente' });
  }
});

// ELIMINAR PACIENTE (DELETE /api/pacientes/:id)
router.delete('/:id', async (req, res) => {
  const connection = await db.getConnection();
  try {
    const { id } = req.params;

    await connection.beginTransaction();

    const [[pacienteRow]] = await connection.execute(
      'SELECT id_paciente FROM tbl_far_paciente WHERE id_paciente = ? LIMIT 1',
      [id]
    );

    if (!pacienteRow) {
      await connection.rollback();
      return res.status(404).json({ message: 'Paciente no encontrado' });
    }

    const [prescripciones] = await connection.execute(
      'SELECT id_prescripcion FROM tbl_far_prescripcion WHERE id_paciente = ?',
      [id]
    );

    if (prescripciones.length > 0) {
      const idsPrescripcion = prescripciones.map((r) => r.id_prescripcion);
      const placeholders = idsPrescripcion.map(() => '?').join(',');

      await connection.execute(
        `DELETE FROM tbl_far_prescripcion_detalle WHERE id_prescripcion IN (${placeholders})`,
        idsPrescripcion
      );

      await connection.execute(
        `DELETE FROM tbl_far_prescripcion WHERE id_prescripcion IN (${placeholders})`,
        idsPrescripcion
      );
    }

    // En esta BD existen FKs RESTRICT duplicadas sobre id_reaccion, por eso limpiamos hijos explícitamente.
    const [reacciones] = await connection.execute(
      'SELECT id_reaccion FROM tbl_far_reaccion_adversa WHERE id_paciente = ?',
      [id]
    );

    if (reacciones.length > 0) {
      const idsReaccion = reacciones.map((r) => r.id_reaccion);
      const placeholdersRx = idsReaccion.map(() => '?').join(',');

      await connection.execute(
        `DELETE FROM tbl_far_reaccion_detalle WHERE id_reaccion IN (${placeholdersRx})`,
        idsReaccion
      );

      await connection.execute(
        `DELETE FROM tbl_far_reaccion_consecuencia WHERE id_reaccion IN (${placeholdersRx})`,
        idsReaccion
      );

      await connection.execute(
        `DELETE FROM tbl_far_reaccion_foto WHERE id_reaccion IN (${placeholdersRx})`,
        idsReaccion
      );

      await connection.execute(
        `DELETE FROM tbl_far_reaccion_adversa WHERE id_reaccion IN (${placeholdersRx})`,
        idsReaccion
      );
    }

    const [deletePaciente] = await connection.execute('DELETE FROM tbl_far_paciente WHERE id_paciente = ?', [id]);

    if (!deletePaciente.affectedRows) {
      await connection.rollback();
      return res.status(404).json({ message: 'Paciente no encontrado' });
    }

    await connection.commit();

    res.json({ message: 'Paciente eliminado correctamente' });
  } catch (error) {
    await connection.rollback();
    console.error('Error al eliminar paciente:', error);

    if (error?.code === 'ER_ROW_IS_REFERENCED_2') {
      return res.status(409).json({
        message: 'No se puede eliminar el paciente porque tiene registros relacionados activos.',
      });
    }

    res.status(500).json({ message: 'Error al eliminar el paciente' });
  } finally {
    connection.release();
  }
});

module.exports = router;
