const express = require('express');
const router = express.Router();
const db = require('../config/db');

const ALLOWED_TYPES = new Set([
  'CONSECUENCIAS_REACCION',
  'DESENLACE_REACCION',
  'ESTADO_REACCION',
]);

const DEFAULT_OPTIONS = {
  CONSECUENCIAS_REACCION: [
    'HAN PUESTO EN PELIGRO SU VIDA',
    'HAN SIDO LA CAUSA DE SU HOSPITALIZACION',
    'HAN PROLONGADO SU INGRESO EN EL HOSPITAL',
    'HAN ORIGINADO INCAPACIDAD PERSISTENTE O GRAVE',
    'HAN CAUSADO DEFECTO O ANOMALIA CONGENITA',
    'HAN CAUSADO LA MUERTE DEL PACIENTE',
    'NO HAN CAUSADO NADA DE LO ANTERIOR, PERO CONSIDERO QUE ES GRAVE',
    'NO HAN CAUSADO NADA DE LO ANTERIOR, PERO CONSIDERO QUE NO ES GRAVE',
  ],
  DESENLACE_REACCION: [
    'DESCONOCIDO',
    'RECUPERADO/RESUELTO',
    'EN RECUPERACION / EN RESOLUCION',
    'NO RECUPERADO / NO RESUELTO',
    'RECUPERADO/RESUELTO CON SECUELAS',
    'MORTAL',
  ],
  ESTADO_REACCION: [
    'REGISTRADA',
    'EN ANALISIS',
    'CERRADA',
  ],
};

function normalizeType(value) {
  return String(value || '')
    .trim()
    .toUpperCase()
    .replace(/\s+/g, '_')
    .replace(/[^A-Z_]/g, '');
}

function normalizeLabel(value) {
  return String(value || '')
    .trim()
    .toUpperCase()
    .replace(/\s{2,}/g, ' ');
}

function normalizeKey(value) {
  const upper = normalizeLabel(value)
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');

  return upper
    .replace(/\s+/g, '_')
    .replace(/[^A-Z0-9_]/g, '');
}

async function ensureConfigTable() {
  await db.execute(`
    CREATE TABLE IF NOT EXISTS tbl_seg_objeto_acceso (
      id_objeto INT NOT NULL AUTO_INCREMENT,
      tipo_objeto VARCHAR(80) NOT NULL,
      clave_objeto VARCHAR(120) NOT NULL,
      valor_objeto VARCHAR(255) NOT NULL,
      orden INT NOT NULL DEFAULT 1,
      estado ENUM('ACTIVO', 'INACTIVO') NOT NULL DEFAULT 'ACTIVO',
      fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      usuario_creacion INT NULL,
      fecha_modificacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      usuario_modificacion INT NULL,
      PRIMARY KEY (id_objeto),
      UNIQUE KEY uk_tipo_clave_objeto (tipo_objeto, clave_objeto)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  `);
}

async function seedDefaultsIfMissing() {
  for (const [tipo, values] of Object.entries(DEFAULT_OPTIONS)) {
    const [rows] = await db.execute(
      'SELECT id_objeto FROM tbl_seg_objeto_acceso WHERE tipo_objeto = ? LIMIT 1',
      [tipo]
    );

    if (rows.length > 0) continue;

    for (let i = 0; i < values.length; i += 1) {
      const valor = normalizeLabel(values[i]);
      const clave = normalizeKey(values[i]);

      await db.execute(
        `INSERT INTO tbl_seg_objeto_acceso
          (tipo_objeto, clave_objeto, valor_objeto, orden, estado, usuario_creacion, usuario_modificacion)
         VALUES (?, ?, ?, ?, 'ACTIVO', ?, ?)`,
        [tipo, clave, valor, i + 1, null, null]
      );
    }
  }
}

async function loadGroupedOptions() {
  const [rows] = await db.execute(
    `SELECT
      id_objeto,
      tipo_objeto,
      clave_objeto,
      valor_objeto,
      orden,
      estado
     FROM tbl_seg_objeto_acceso
     ORDER BY tipo_objeto ASC, orden ASC, id_objeto ASC`
  );

  const grouped = {
    CONSECUENCIAS_REACCION: [],
    DESENLACE_REACCION: [],
    ESTADO_REACCION: [],
  };

  for (const row of rows) {
    const tipo = normalizeType(row.tipo_objeto);
    if (!grouped[tipo]) continue;

    grouped[tipo].push({
      id_objeto: Number(row.id_objeto),
      tipo_objeto: tipo,
      clave_objeto: String(row.clave_objeto || ''),
      valor_objeto: String(row.valor_objeto || ''),
      orden: Number(row.orden || 0),
      estado: String(row.estado || 'ACTIVO').toUpperCase(),
    });
  }

  return grouped;
}

router.get('/accesos', async (_req, res) => {
  try {
    await ensureConfigTable();
    await seedDefaultsIfMissing();

    const grouped = await loadGroupedOptions();
    return res.json(grouped);
  } catch (error) {
    console.error('Error al listar configuraciones de accesos:', error);
    return res.status(500).json({ message: 'No se pudieron cargar las configuraciones de accesos.' });
  }
});

router.post('/accesos/:tipo', async (req, res) => {
  try {
    await ensureConfigTable();
    await seedDefaultsIfMissing();

    const tipo = normalizeType(req.params.tipo);
    const valor = normalizeLabel(req.body.valor_objeto);
    const estado = String(req.body.estado || 'ACTIVO').trim().toUpperCase();
    const orden = Number(req.body.orden || 0) || 0;

    if (!ALLOWED_TYPES.has(tipo)) {
      return res.status(400).json({ message: 'Tipo de configuracion invalido.' });
    }

    if (!valor) {
      return res.status(400).json({ message: 'El valor es obligatorio.' });
    }

    if (!['ACTIVO', 'INACTIVO'].includes(estado)) {
      return res.status(400).json({ message: 'Estado invalido.' });
    }

    const clave = normalizeKey(valor);
    if (!clave) {
      return res.status(400).json({ message: 'El valor no tiene un formato valido.' });
    }

    const [dup] = await db.execute(
      `SELECT id_objeto
       FROM tbl_seg_objeto_acceso
       WHERE tipo_objeto = ? AND clave_objeto = ?
       LIMIT 1`,
      [tipo, clave]
    );

    if (dup.length > 0) {
      return res.status(409).json({ message: 'Ya existe una opcion con ese valor.' });
    }

    await db.execute(
      `INSERT INTO tbl_seg_objeto_acceso
        (tipo_objeto, clave_objeto, valor_objeto, orden, estado, usuario_creacion, usuario_modificacion)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [tipo, clave, valor, orden, estado, req.user?.id || null, req.user?.id || null]
    );

    return res.status(201).json({ message: 'Opcion creada correctamente.' });
  } catch (error) {
    console.error('Error al crear opcion de configuracion:', error);
    return res.status(500).json({ message: 'No se pudo crear la opcion.' });
  }
});

router.put('/accesos/:tipo/:id', async (req, res) => {
  try {
    await ensureConfigTable();
    await seedDefaultsIfMissing();

    const tipo = normalizeType(req.params.tipo);
    const id = Number(req.params.id);
    const valor = normalizeLabel(req.body.valor_objeto);
    const estado = String(req.body.estado || 'ACTIVO').trim().toUpperCase();
    const orden = Number(req.body.orden || 0) || 0;

    if (!ALLOWED_TYPES.has(tipo)) {
      return res.status(400).json({ message: 'Tipo de configuracion invalido.' });
    }

    if (!Number.isInteger(id) || id <= 0) {
      return res.status(400).json({ message: 'Id invalido.' });
    }

    if (!valor) {
      return res.status(400).json({ message: 'El valor es obligatorio.' });
    }

    if (!['ACTIVO', 'INACTIVO'].includes(estado)) {
      return res.status(400).json({ message: 'Estado invalido.' });
    }

    const clave = normalizeKey(valor);
    if (!clave) {
      return res.status(400).json({ message: 'El valor no tiene un formato valido.' });
    }

    const [target] = await db.execute(
      `SELECT id_objeto
       FROM tbl_seg_objeto_acceso
       WHERE id_objeto = ? AND tipo_objeto = ?
       LIMIT 1`,
      [id, tipo]
    );

    if (target.length === 0) {
      return res.status(404).json({ message: 'Opcion no encontrada.' });
    }

    const [dup] = await db.execute(
      `SELECT id_objeto
       FROM tbl_seg_objeto_acceso
       WHERE tipo_objeto = ? AND clave_objeto = ? AND id_objeto <> ?
       LIMIT 1`,
      [tipo, clave, id]
    );

    if (dup.length > 0) {
      return res.status(409).json({ message: 'Ya existe otra opcion con ese valor.' });
    }

    await db.execute(
      `UPDATE tbl_seg_objeto_acceso
       SET clave_objeto = ?,
           valor_objeto = ?,
           orden = ?,
           estado = ?,
           usuario_modificacion = ?
       WHERE id_objeto = ? AND tipo_objeto = ?`,
      [clave, valor, orden, estado, req.user?.id || null, id, tipo]
    );

    return res.json({ message: 'Opcion actualizada correctamente.' });
  } catch (error) {
    console.error('Error al actualizar opcion de configuracion:', error);
    return res.status(500).json({ message: 'No se pudo actualizar la opcion.' });
  }
});

router.delete('/accesos/:tipo/:id', async (req, res) => {
  try {
    await ensureConfigTable();
    await seedDefaultsIfMissing();

    const tipo = normalizeType(req.params.tipo);
    const id = Number(req.params.id);

    if (!ALLOWED_TYPES.has(tipo)) {
      return res.status(400).json({ message: 'Tipo de configuracion invalido.' });
    }

    if (!Number.isInteger(id) || id <= 0) {
      return res.status(400).json({ message: 'Id invalido.' });
    }

    const [deleted] = await db.execute(
      'DELETE FROM tbl_seg_objeto_acceso WHERE id_objeto = ? AND tipo_objeto = ?',
      [id, tipo]
    );

    if (deleted.affectedRows === 0) {
      return res.status(404).json({ message: 'Opcion no encontrada.' });
    }

    return res.json({ message: 'Opcion eliminada correctamente.' });
  } catch (error) {
    console.error('Error al eliminar opcion de configuracion:', error);
    return res.status(500).json({ message: 'No se pudo eliminar la opcion.' });
  }
});

module.exports = router;
