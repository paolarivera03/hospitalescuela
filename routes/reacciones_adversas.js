const express = require('express');
const path = require('path');
const fs = require('fs');
const multer = require('multer');

const uploadDir = path.join(__dirname, '..', 'uploads', 'reacciones_adversas');
if (!fs.existsSync(uploadDir)) {
  fs.mkdirSync(uploadDir, { recursive: true });
}

const storage = multer.diskStorage({
  destination: (_req, _file, cb) => cb(null, uploadDir),
  filename: (_req, file, cb) => {
    const ext = path.extname(file.originalname || '').toLowerCase() || '.jpg';
    cb(null, `ram_${Date.now()}_${Math.round(Math.random() * 1e9)}${ext}`);
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

function textoMayus(valor) {
  return String(valor || '')
    .toUpperCase()
    .replace(/[^A-ZÁÉÍÓÚÑ0-9\s.,-]/g, '')
    .replace(/(.)\1{2,}/g, '$1$1')
    .replace(/\s{2,}/g, ' ')
    .trim();
}

function soloLetrasMayus(valor) {
  return String(valor || '')
    .toUpperCase()
    .replace(/[^A-ZÁÉÍÓÚÑ\s]/g, '')
    .replace(/(.)\1{2,}/g, '$1$1')
    .replace(/\s{2,}/g, ' ')
    .trim();
}

function normalizarEstadoReaccion(valor) {
  const estado = String(valor || '')
    .trim()
    .toUpperCase()
    .replace(/\s+/g, '_');

  if (!estado) return 'REGISTRADA';
  return estado.replace(/[^A-Z0-9_]/g, '');
}

function normalizarSexo(valor) {
  const sexo = String(valor || '').trim().toUpperCase();
  if (sexo === 'M' || sexo === 'F') return sexo;
  if (sexo === 'OTRO') return 'Otro';
  return null;
}

function normalizarConsecuenciasEntrada(consecuencias, cabecera) {
  const lista = Array.isArray(consecuencias) ? [...consecuencias] : [];
  const descripcionCabecera = normalizarClaveConsecuencia(cabecera?.descripcion_consecuencia);
  const gravedadCabecera = String(cabecera?.gravedad || '').trim().toUpperCase();

  if (lista.length === 0 && (descripcionCabecera || gravedadCabecera)) {
    lista.push({
      descripcion_consecuencia: descripcionCabecera,
      gravedad: gravedadCabecera || 'LEVE',
    });
  }

  return lista;
}

function normalizarClaveConsecuencia(valor) {
  return String(valor || '')
    .trim()
    .toUpperCase()
    .replace(/\s+/g, '_')
    .replace(/[^A-Z0-9_]/g, '');
}

async function sincronizarPacienteDesdeCabecera(connection, cabecera, usuario) {
  const idPaciente = Number(cabecera?.id_paciente || 0);
  if (!idPaciente) return;

  const nombre = textoMayus(cabecera?.paciente_nombre || cabecera?.nombre_completo);
  const edadRaw = Number(cabecera?.paciente_edad ?? cabecera?.edad ?? 0);
  const edad = Number.isFinite(edadRaw) && edadRaw > 0 ? Math.trunc(edadRaw) : null;
  const sexo = normalizarSexo(cabecera?.paciente_sexo ?? cabecera?.sexo);
  const diagnostico = textoMayus(cabecera?.diagnostico_ingreso || cabecera?.diagnostico);
  const sala = textoMayus(cabecera?.sala);
  const numeroCama = textoMayus(cabecera?.numero_cama);
  const idMedico = Number(cabecera?.id_medico || 0) || null;

  const setPaciente = [
    'nombre = ?',
    'edad = ?',
    'sexo = ?',
    'sala = ?',
    'numero_cama = ?',
    'diagnostico = ?',
    'id_medico = ?',
  ];
  const valoresPaciente = [
    nombre || null,
    edad,
    sexo,
    sala || null,
    numeroCama || null,
    diagnostico || null,
    idMedico,
  ];

  if (await columnaExiste(connection, 'tbl_far_paciente', 'usuario_modificacion')) {
    setPaciente.push('usuario_modificacion = ?');
    valoresPaciente.push(usuario || null);
  }

  if (await columnaExiste(connection, 'tbl_far_paciente', 'fecha_modificacion')) {
    setPaciente.push('fecha_modificacion = NOW()');
  }

  valoresPaciente.push(idPaciente);

  const [pacienteResult] = await connection.execute(
    `UPDATE tbl_far_paciente SET ${setPaciente.join(', ')} WHERE id_paciente = ?`,
    valoresPaciente
  );

  if ((pacienteResult?.affectedRows || 0) === 0) {
    throw new Error('No se pudo sincronizar el paciente relacionado a la reacción.');
  }
}

function fechaValida(valor) {
  if (!valor) return null;
  const fecha = String(valor).slice(0, 10);
  return /^\d{4}-\d{2}-\d{2}$/.test(fecha) ? fecha : null;
}

function fechaFinNoMenor(inicio, fin) {
  const fInicio = fechaValida(inicio);
  const fFin = fechaValida(fin);
  if (!fInicio || !fFin) return true;
  return fFin >= fInicio;
}

async function columnaExiste(connection, tableName, columnName) {
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

async function existeUbicacionActiva(connection, sala, numeroCama, idExcluir = null) {
  const salaNorm = textoMayus(sala);
  const camaNorm = textoMayus(numeroCama);

  if (!salaNorm || !camaNorm) {
    return false;
  }

  let sql = `
    SELECT 1
    FROM tbl_far_reaccion_adversa
    WHERE UPPER(TRIM(COALESCE(sala, ''))) = ?
      AND UPPER(TRIM(COALESCE(numero_cama, ''))) = ?
      AND UPPER(COALESCE(estado, 'REGISTRADA')) <> 'CERRADA'
  `;
  const params = [salaNorm, camaNorm];

  if (idExcluir) {
    sql += ' AND id_reaccion <> ?';
    params.push(Number(idExcluir));
  }

  sql += ' LIMIT 1';

  const [rows] = await connection.execute(sql, params);
  return rows.length > 0;
}

async function marcarLotesEnCuarentena(connection, lotes) {
  const ids = [...new Set(
    (Array.isArray(lotes) ? lotes : [])
      .map((id) => Number(id))
      .filter((id) => Number.isInteger(id) && id > 0)
  )];

  if (ids.length === 0) {
    return;
  }

  const placeholders = ids.map(() => '?').join(',');
  await connection.execute(
    `UPDATE tbl_far_lote
     SET estado = 'EN_CUARENTENA'
     WHERE id_lote IN (${placeholders})`,
    ids
  );
}

function mapearPendiente(row) {
  return {
    id_bandeja: row.id_bandeja,
    id_paciente: row.id_paciente,
    id_medico: row.id_medico,
    id_reaccion: row.id_reaccion,
    estado_bandeja: row.estado_bandeja,
    fecha_envio: row.fecha_envio,
    fecha_visto: row.fecha_visto,
    fecha_procesado: row.fecha_procesado,
    numero_expediente: row.numero_expediente,
    nombre_paciente: row.nombre_paciente,
    edad: row.edad,
    sexo: row.sexo,
    sala: row.sala,
    numero_cama: row.numero_cama,
    diagnostico: row.diagnostico,
    nombre_medico: row.nombre_medico,
    numero_colegiacion: row.numero_colegiacion,
    creado_por: row.creado_por,
  };
}

async function obtenerBandejaPacientes(executor, idBandeja = null) {
  let sql = `
    SELECT
      p.id_paciente AS id_bandeja,
      p.id_paciente,
      p.id_medico,
      NULL AS id_reaccion,
      'NUEVO' AS estado_bandeja,
      p.fecha_creacion AS fecha_envio,
      NULL AS fecha_visto,
      NULL AS fecha_procesado,
      p.numero_expediente,
      p.nombre AS nombre_paciente,
      p.edad,
      p.sexo,
      p.sala,
      p.numero_cama,
      p.diagnostico,
      COALESCE(
        NULLIF(TRIM(CONCAT(COALESCE(um.nombre, ''), ' ', COALESCE(um.apellido, ''))), ''),
        NULLIF(TRIM(COALESCE(m.nombre_completo, '')), ''),
        'MEDICO NO ASIGNADO'
      ) AS nombre_medico,
      m.numero_colegiacion,
      CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.apellido, '')) AS creado_por
    FROM tbl_far_paciente p
    LEFT JOIN tbl_far_medico m ON m.id_medico = p.id_medico
    LEFT JOIN tbl_seg_usuario um ON um.id_usuario = m.id_usuario
    LEFT JOIN tbl_seg_usuario u ON u.id_usuario = p.usuario_creacion
    WHERE EXISTS (
      SELECT 1
      FROM tbl_far_prescripcion pr
      WHERE pr.id_paciente = p.id_paciente
        AND UPPER(COALESCE(pr.observaciones, '')) LIKE '%REGISTRO PX%'
    )
      AND NOT EXISTS (
        SELECT 1
        FROM tbl_far_reaccion_adversa ra
        WHERE ra.id_paciente = p.id_paciente
      )
  `;

  const params = [];
  if (idBandeja !== null) {
    sql += ' AND p.id_paciente = ?';
    params.push(idBandeja);
  }

  sql += '\n ORDER BY p.fecha_creacion DESC, p.id_paciente DESC';

  const [rows] = await executor.execute(sql, params);
  return rows.map(mapearPendiente);
}

module.exports = (db, verificarToken) => {
  const router = express.Router();

  // ✅ LISTAR REACCIONES (CABECERA)
  router.get('/', verificarToken, async (req, res) => {
    try {
      const tieneSalaReaccion = await columnaExiste(db, 'tbl_far_reaccion_adversa', 'sala');
      const tieneCamaReaccion = await columnaExiste(db, 'tbl_far_reaccion_adversa', 'numero_cama');
      const tieneMedicamentoManual = await columnaExiste(db, 'tbl_far_reaccion_detalle', 'medicamento');

      const campoSala = tieneSalaReaccion ? 'COALESCE(NULLIF(TRIM(ra.sala), \'\'), p.sala)' : 'p.sala';
      const campoCama = tieneCamaReaccion ? 'COALESCE(NULLIF(TRIM(ra.numero_cama), \'\'), p.numero_cama)' : 'p.numero_cama';
      const exprMedicamento = tieneMedicamentoManual
        ? "COALESCE(NULLIF(TRIM(d2.medicamento), ''), CAST(d2.id_medicamento AS CHAR), CONCAT('DET-', d2.id_detalle))"
        : "COALESCE(CAST(d2.id_medicamento AS CHAR), CONCAT('DET-', d2.id_detalle))";

      const [rows] = await db.execute(`
        SELECT 
            ra.id_reaccion,
            p.nombre AS nombre_completo,
            CONCAT(u.nombre, ' ', u.apellido) AS nombre_medico,
            ra.descripcion_reaccion,
            ra.fecha_inicio_reaccion,
            ra.fecha_fin_reaccion,
            ra.estado,
            ${campoSala} AS sala,
            ${campoCama} AS numero_cama,
            (
              SELECT COUNT(DISTINCT ${exprMedicamento})
              FROM tbl_far_reaccion_detalle d2
              INNER JOIN tbl_far_reaccion_adversa ra2 ON ra2.id_reaccion = d2.id_reaccion
              WHERE ra2.id_paciente = ra.id_paciente
            ) AS total_medicamentos_paciente
        FROM tbl_far_reaccion_adversa ra
        INNER JOIN tbl_far_paciente p 
            ON p.id_paciente = ra.id_paciente
        INNER JOIN tbl_far_medico m 
            ON m.id_medico = ra.id_medico
        LEFT JOIN tbl_seg_usuario u 
            ON u.id_usuario = m.id_usuario
        ORDER BY ra.fecha_inicio_reaccion DESC, ra.id_reaccion DESC
      `)
      return res.json(rows);
    } catch (error) {
      console.error(error);
      return res.status(500).json({ message: 'Error al obtener reacciones adversas' });
    }
  });

  router.get('/bandeja-pacientes', verificarToken, async (req, res) => {
    try {
      const pendientes = await obtenerBandejaPacientes(db);

      const nuevos = pendientes.filter((item) => item.estado_bandeja === 'NUEVO');
      const vistos = pendientes.filter((item) => item.estado_bandeja === 'VISTO');

      return res.json({
        nuevos,
        vistos,
        total_nuevos: nuevos.length,
      });
    } catch (error) {
      console.error(error);
      return res.status(500).json({ message: 'Error al obtener la bandeja de pacientes.' });
    }
  });

  router.get('/bandeja-pacientes/:id', verificarToken, async (req, res) => {
    try {
      const pendientes = await obtenerBandejaPacientes(db, req.params.id);

      if (pendientes.length === 0) {
        return res.status(404).json({ message: 'Registro pendiente no encontrado.' });
      }

      return res.json(pendientes[0]);
    } catch (error) {
      console.error(error);
      return res.status(500).json({ message: 'Error al obtener el registro pendiente.' });
    }
  });

  router.post('/bandeja-pacientes/:id/marcar-visto', verificarToken, async (req, res) => {
    try {
      return res.json({ message: 'La bandeja de pacientes no está habilitada en este esquema.' });
    } catch (error) {
      console.error(error);
      return res.status(500).json({ message: 'Error al marcar el registro como visto.' });
    }
  });

  // ✅ OBTENER UNA REACCIÓN (CABECERA + DETALLES + CONSECUENCIAS)
  router.get('/:id', verificarToken, async (req, res) => {
    try {
      const { id } = req.params;

      const tieneSalaReaccion = await columnaExiste(db, 'tbl_far_reaccion_adversa', 'sala');
      const tieneCamaReaccion = await columnaExiste(db, 'tbl_far_reaccion_adversa', 'numero_cama');
      const tieneMedicamentoManual = await columnaExiste(db, 'tbl_far_reaccion_detalle', 'medicamento');

      const campoSala = tieneSalaReaccion ? 'COALESCE(NULLIF(TRIM(ra.sala), \'\'), p.sala)' : 'p.sala';
      const campoCama = tieneCamaReaccion ? 'COALESCE(NULLIF(TRIM(ra.numero_cama), \'\'), p.numero_cama)' : 'p.numero_cama';
      const campoMedicamento = tieneMedicamentoManual
        ? "COALESCE(NULLIF(TRIM(d.medicamento), ''), med.nombre_comercial) AS medicamento"
        : 'med.nombre_comercial AS medicamento';

      const [cabRows] = await db.execute(`
        SELECT 
          ra.*,
          p.nombre AS nombre_completo,
          p.numero_expediente,
          p.edad,
          p.sexo,
          ${campoSala} AS sala,
          ${campoCama} AS numero_cama,
          p.diagnostico,
          CONCAT(u.nombre, ' ', u.apellido) AS nombre_medico,
          m.numero_colegiacion
        FROM tbl_far_reaccion_adversa ra
        INNER JOIN tbl_far_paciente p ON p.id_paciente = ra.id_paciente
        INNER JOIN tbl_far_medico m ON m.id_medico = ra.id_medico
        LEFT JOIN tbl_seg_usuario u ON u.id_usuario = m.id_usuario
        WHERE ra.id_reaccion = ?
      `, [id]);

      if (cabRows.length === 0) {
        return res.status(404).json({ message: 'Reacción no encontrada' });
      }

      const cabecera = cabRows[0];

      const [fotos] = await db.execute(`
        SELECT id_foto, ruta_archivo, nombre_archivo, tipo_archivo, descripcion
        FROM tbl_far_reaccion_foto
        WHERE id_reaccion = ?
        ORDER BY id_foto DESC
      `, [id]);

      const fotoEvidencia = fotos.find((foto) => String(foto.descripcion || '').toUpperCase() === 'EVIDENCIA_FOTOGRAFICA');
      const fotoMedicamento = fotos.find((foto) => String(foto.descripcion || '').toUpperCase() === 'FOTO_MEDICAMENTO');
      const fotoGeneral = fotos[0] || null;

      cabecera.ruta_foto = fotoGeneral?.ruta_archivo || null;
      cabecera.nombre_archivo = fotoGeneral?.nombre_archivo || null;
      cabecera.tipo_archivo = fotoGeneral?.tipo_archivo || null;
      cabecera.ruta_foto_evidencia = fotoEvidencia?.ruta_archivo || null;
      cabecera.nombre_foto_evidencia = fotoEvidencia?.nombre_archivo || null;
      cabecera.tipo_foto_evidencia = fotoEvidencia?.tipo_archivo || null;
      cabecera.ruta_foto_medicamento = fotoMedicamento?.ruta_archivo || null;
      cabecera.nombre_foto_medicamento = fotoMedicamento?.nombre_archivo || null;
      cabecera.tipo_foto_medicamento = fotoMedicamento?.tipo_archivo || null;

      const [detalles] = await db.execute(`
        SELECT 
          d.*,
          ${campoMedicamento},
          med.nombre_comercial,
          l.numero_lote
        FROM tbl_far_reaccion_detalle d
        LEFT JOIN tbl_far_medicamento med ON med.id_medicamento = d.id_medicamento
        LEFT JOIN tbl_far_lote l ON l.id_lote = d.id_lote
        WHERE d.id_reaccion = ?
        ORDER BY d.id_detalle ASC
      `, [id]);

      const [consecuencias] = await db.execute(`
        SELECT *
        FROM tbl_far_reaccion_consecuencia
        WHERE id_reaccion = ?
        ORDER BY id_consecuencia ASC
      `, [id]);

      return res.json({ cabecera, detalles, consecuencias });

    } catch (error) {
      console.error(error);
      return res.status(500).json({ message: 'Error al obtener la reacción' });
    }
  });

  router.post('/:id/fotos', verificarToken, upload.fields([
    { name: 'foto', maxCount: 1 },
    { name: 'foto_medicamento', maxCount: 1 },
  ]), async (req, res) => {
    const connection = await db.getConnection();

    try {
      const { id } = req.params;
      const usuario = req.user?.id ?? null;
      const archivos = req.files || {};
      const fotos = [];

      if (Array.isArray(archivos.foto) && archivos.foto[0]) {
        fotos.push({
          file: archivos.foto[0],
          descripcion: 'EVIDENCIA_FOTOGRAFICA',
        });
      }

      if (Array.isArray(archivos.foto_medicamento) && archivos.foto_medicamento[0]) {
        fotos.push({
          file: archivos.foto_medicamento[0],
          descripcion: 'FOTO_MEDICAMENTO',
        });
      }

      if (fotos.length === 0) {
        return res.json({ message: 'No se recibieron fotos para guardar.' });
      }

      await connection.beginTransaction();

      for (const item of fotos) {
        const rutaArchivo = `reacciones_adversas/${item.file.filename}`;

        await connection.execute(
          `DELETE FROM tbl_far_reaccion_foto WHERE id_reaccion = ? AND descripcion = ?`,
          [id, item.descripcion]
        );

        await connection.execute(
          `INSERT INTO tbl_far_reaccion_foto
           (id_reaccion, nombre_archivo, tipo_archivo, ruta_archivo, descripcion, usuario_creacion, fecha_creacion)
           VALUES (?, ?, ?, ?, ?, ?, NOW())`,
          [
            id,
            item.file.originalname,
            item.file.mimetype,
            rutaArchivo,
            item.descripcion,
            usuario,
          ]
        );
      }

      await connection.commit();
      return res.json({ message: 'Fotos guardadas correctamente.' });
    } catch (error) {
      await connection.rollback();
      console.error(error);
      return res.status(500).json({ message: 'Error al guardar las fotos de la reacción.' });
    } finally {
      connection.release();
    }
  });

  // ✅ CREAR REACCIÓN (TRANSACCIÓN)
  // body: { cabecera:{...}, detalles:[...], consecuencias:[...] }
  router.post('/', verificarToken, async (req, res) => {
    const connection = await db.getConnection();
    try {
      const { cabecera, detalles = [], consecuencias = [] } = req.body;
      const consecuenciasNormalizadas = normalizarConsecuenciasEntrada(consecuencias, cabecera);

      if (!cabecera?.id_paciente || !cabecera?.id_medico || !cabecera?.descripcion_reaccion || !cabecera?.fecha_inicio_reaccion) {
        return res.status(400).json({ message: 'Faltan campos obligatorios en cabecera' });
      }

      if (!fechaFinNoMenor(cabecera?.fecha_inicio_reaccion, cabecera?.fecha_fin_reaccion)) {
        return res.status(400).json({ message: 'La fecha fin de la reacción no puede ser menor que la fecha inicio.' });
      }

      const descripcion = textoMayus(cabecera.descripcion_reaccion);
      const desenlace = normalizarClaveConsecuencia(cabecera.desenlace);
      const observaciones = textoMayus(cabecera.observaciones);
      const descripcionConsecuencia = normalizarClaveConsecuencia(cabecera.descripcion_consecuencia);
      const hospitalizacion = textoMayus(cabecera.hospitalizacion);
      const gravedad = String(cabecera.gravedad || '').trim().toUpperCase();

      await connection.beginTransaction();

      const usuario_creacion = req.user.id;

      const tieneSalaReaccion = await columnaExiste(connection, 'tbl_far_reaccion_adversa', 'sala');
      const tieneCamaReaccion = await columnaExiste(connection, 'tbl_far_reaccion_adversa', 'numero_cama');
      const tieneMedicamentoManual = await columnaExiste(connection, 'tbl_far_reaccion_detalle', 'medicamento');
      const tieneDescripcionConsecuencia = await columnaExiste(connection, 'tbl_far_reaccion_adversa', 'descripcion_consecuencia');
      const tieneHospitalizacion = await columnaExiste(connection, 'tbl_far_reaccion_adversa', 'hospitalizacion');
      const tieneGravedad = await columnaExiste(connection, 'tbl_far_reaccion_adversa', 'gravedad');

      await sincronizarPacienteDesdeCabecera(connection, cabecera, usuario_creacion);

      if (tieneSalaReaccion && tieneCamaReaccion) {
        const ubicacionOcupada = await existeUbicacionActiva(connection, cabecera?.sala, cabecera?.numero_cama);
        if (ubicacionOcupada) {
          await connection.rollback();
          return res.status(409).json({
            message: 'La ubicación seleccionada (Sala/Cama) ya tiene una reacción activa. Solo puedes reutilizarla cuando la reacción anterior esté en estado CERRADA.',
          });
        }
      }

      const columnasCabecera = [
        'id_paciente',
        'id_medico',
        'descripcion_reaccion',
        'fecha_inicio_reaccion',
        'fecha_fin_reaccion',
        'desenlace',
        'observaciones',
        'estado',
      ];
      const valoresCabecera = [
        cabecera.id_paciente,
        cabecera.id_medico,
        descripcion,
        cabecera.fecha_inicio_reaccion,
        cabecera.fecha_fin_reaccion ?? null,
        desenlace || null,
        observaciones || null,
        normalizarEstadoReaccion(cabecera.estado),
      ];

      if (tieneSalaReaccion) {
        columnasCabecera.push('sala');
        valoresCabecera.push(textoMayus(cabecera.sala) || null);
      }

      if (tieneCamaReaccion) {
        columnasCabecera.push('numero_cama');
        valoresCabecera.push(textoMayus(cabecera.numero_cama) || null);
      }

      if (tieneDescripcionConsecuencia) {
        columnasCabecera.push('descripcion_consecuencia');
        valoresCabecera.push(descripcionConsecuencia || null);
      }

      if (tieneHospitalizacion) {
        columnasCabecera.push('hospitalizacion');
        valoresCabecera.push(hospitalizacion || null);
      }

      if (tieneGravedad) {
        columnasCabecera.push('gravedad');
        valoresCabecera.push(['LEVE', 'MODERADA', 'GRAVE', 'MUY_GRAVE'].includes(gravedad) ? gravedad : null);
      }

      columnasCabecera.push('usuario_creacion');
      valoresCabecera.push(usuario_creacion);

      const placeholdersCabecera = columnasCabecera.map(() => '?').join(',');

      const [result] = await connection.execute(
        `INSERT INTO tbl_far_reaccion_adversa (${columnasCabecera.join(',')}) VALUES (${placeholdersCabecera})`,
        valoresCabecera
      );

      const id_reaccion = result.insertId;

      // Insertar detalles (medicamentos sospechosos)
      const lotesEnReaccion = [];
      for (const d of detalles) {
        const medicamentoManual = textoMayus(d?.medicamento || d?.nombre_comercial || '');
        const dosisLimpia = textoMayus(d?.dosis_posologia);
        const viaLimpia = textoMayus(d?.via_administracion);
        const inicioUso = d?.fecha_inicio_uso ?? null;
        const finUso = d?.fecha_fin_uso ?? null;

        if (
          !medicamentoManual &&
          !d?.id_medicamento &&
          !d?.id_lote &&
          !dosisLimpia &&
          !viaLimpia &&
          !inicioUso &&
          !finUso
        ) {
          continue;
        }

        if (!fechaFinNoMenor(inicioUso, finUso)) {
          await connection.rollback();
          return res.status(400).json({ message: 'La fecha fin de uso no puede ser menor que la fecha inicio de uso.' });
        }

        const columnasDetalle = [
          'id_reaccion',
          'id_medicamento',
          'id_lote',
          'dosis_posologia',
          'via_administracion',
          'fecha_inicio_uso',
          'fecha_fin_uso',
          'usuario_creacion',
        ];
        const valoresDetalle = [
          id_reaccion,
          d.id_medicamento ?? null,
          d.id_lote ?? null,
          dosisLimpia || null,
          viaLimpia || null,
          inicioUso,
          finUso,
          usuario_creacion,
        ];

        if (tieneMedicamentoManual) {
          columnasDetalle.splice(3, 0, 'medicamento');
          valoresDetalle.splice(3, 0, medicamentoManual || null);
        }

        await connection.execute(
          `INSERT INTO tbl_far_reaccion_detalle (${columnasDetalle.join(',')}) VALUES (${columnasDetalle.map(() => '?').join(',')})`,
          valoresDetalle
        );

        if (d?.id_lote) {
          lotesEnReaccion.push(d.id_lote);
        }

      }

      // Todo lote asociado a una reaccion queda en cuarentena.
      await marcarLotesEnCuarentena(connection, lotesEnReaccion);

      // Insertar consecuencias
      for (const c of consecuenciasNormalizadas) {
        if (!c.descripcion_consecuencia) continue;

        await connection.execute(`
          INSERT INTO tbl_far_reaccion_consecuencia
          (id_reaccion, descripcion_consecuencia, gravedad, usuario_creacion)
          VALUES (?,?,?,?)
        `, [
          id_reaccion,
          normalizarClaveConsecuencia(c.descripcion_consecuencia),
          c.gravedad ?? 'LEVE',
          usuario_creacion
        ]);
      }

      await connection.commit();
      return res.status(201).json({ message: 'Reacción creada', id_reaccion });

    } catch (error) {
      await connection.rollback();
      console.error(error);
      return res.status(500).json({ message: 'Error al crear la reacción' });
    } finally {
      connection.release();
    }

    
  });

  // ✅ ACTUALIZAR REACCIÓN COMPLETA (CABECERA + DETALLES + CONSECUENCIAS)
  router.put('/:id', verificarToken, async (req, res) => {
    const connection = await db.getConnection();

    try {
      const { id } = req.params;
      const { cabecera, detalles = [], consecuencias = [] } = req.body;
      const consecuenciasNormalizadas = normalizarConsecuenciasEntrada(consecuencias, cabecera);

      if (!fechaFinNoMenor(cabecera?.fecha_inicio_reaccion, cabecera?.fecha_fin_reaccion)) {
        return res.status(400).json({ message: 'La fecha fin de la reacción no puede ser menor que la fecha inicio.' });
      }

      await connection.beginTransaction();

      const usuario_modificacion = req.user.id;
      const tieneSalaReaccion = await columnaExiste(connection, 'tbl_far_reaccion_adversa', 'sala');
      const tieneCamaReaccion = await columnaExiste(connection, 'tbl_far_reaccion_adversa', 'numero_cama');
      const tieneMedicamentoManual = await columnaExiste(connection, 'tbl_far_reaccion_detalle', 'medicamento');
      const tieneDescripcionConsecuencia = await columnaExiste(connection, 'tbl_far_reaccion_adversa', 'descripcion_consecuencia');
      const tieneHospitalizacion = await columnaExiste(connection, 'tbl_far_reaccion_adversa', 'hospitalizacion');
      const tieneGravedad = await columnaExiste(connection, 'tbl_far_reaccion_adversa', 'gravedad');

      await sincronizarPacienteDesdeCabecera(connection, cabecera, usuario_modificacion);

      if (tieneSalaReaccion && tieneCamaReaccion) {
        const ubicacionOcupada = await existeUbicacionActiva(connection, cabecera?.sala, cabecera?.numero_cama, id);
        if (ubicacionOcupada) {
          await connection.rollback();
          return res.status(409).json({
            message: 'La ubicación seleccionada (Sala/Cama) ya tiene una reacción activa. Solo puedes reutilizarla cuando la reacción anterior esté en estado CERRADA.',
          });
        }
      }

      // ==========================
      // LIMPIEZA CABECERA
      // ==========================
      const descripcion = textoMayus(cabecera?.descripcion_reaccion);
      const desenlace = normalizarClaveConsecuencia(cabecera?.desenlace);
      const observaciones = textoMayus(cabecera?.observaciones);
      const descripcionConsecuencia = normalizarClaveConsecuencia(cabecera?.descripcion_consecuencia);
      const hospitalizacion = textoMayus(cabecera?.hospitalizacion);
      const gravedad = String(cabecera?.gravedad || '').trim().toUpperCase();

      // ==========================
      // ACTUALIZAR CABECERA
      // ==========================
      const setCabecera = [
        'id_paciente = ?',
        'id_medico = ?',
        'descripcion_reaccion = ?',
        'fecha_inicio_reaccion = ?',
        'fecha_fin_reaccion = ?',
        'desenlace = ?',
        'observaciones = ?',
        'estado = ?',
      ];
      const valoresCabecera = [
        cabecera?.id_paciente ?? null,
        cabecera?.id_medico ?? null,
        descripcion || null,
        cabecera?.fecha_inicio_reaccion ?? null,
        cabecera?.fecha_fin_reaccion ?? null,
        desenlace || null,
        observaciones || null,
        normalizarEstadoReaccion(cabecera?.estado),
      ];

      if (tieneSalaReaccion) {
        setCabecera.push('sala = ?');
        valoresCabecera.push(textoMayus(cabecera?.sala) || null);
      }

      if (tieneCamaReaccion) {
        setCabecera.push('numero_cama = ?');
        valoresCabecera.push(textoMayus(cabecera?.numero_cama) || null);
      }

      if (tieneDescripcionConsecuencia) {
        setCabecera.push('descripcion_consecuencia = ?');
        valoresCabecera.push(descripcionConsecuencia || null);
      }

      if (tieneHospitalizacion) {
        setCabecera.push('hospitalizacion = ?');
        valoresCabecera.push(hospitalizacion || null);
      }

      if (tieneGravedad) {
        setCabecera.push('gravedad = ?');
        valoresCabecera.push(['LEVE', 'MODERADA', 'GRAVE', 'MUY_GRAVE'].includes(gravedad) ? gravedad : null);
      }

      setCabecera.push('usuario_modificacion = ?');
      setCabecera.push('fecha_modificacion = NOW()');
      valoresCabecera.push(usuario_modificacion, id);

      const [result] = await connection.execute(
        `UPDATE tbl_far_reaccion_adversa SET ${setCabecera.join(', ')} WHERE id_reaccion = ?`,
        valoresCabecera
      );

      if (result.affectedRows === 0) {
        await connection.rollback();
        return res.status(404).json({ message: 'Reacción no encontrada' });
      }

      // ==========================
      // ACTUALIZAR DETALLES
      // Estrategia: borrar y volver a insertar
      // ==========================
      await connection.execute(`
        DELETE FROM tbl_far_reaccion_detalle
        WHERE id_reaccion = ?
      `, [id]);

      const lotesEnReaccion = [];
      for (const d of detalles) {
        const id_medicamento = d?.id_medicamento ?? null;
        const id_lote = d?.id_lote ?? null;
        const medicamento = textoMayus(d?.medicamento || d?.nombre_comercial || '');
        const dosis_posologia = textoMayus(d?.dosis_posologia);
        const via_administracion = textoMayus(d?.via_administracion);
        const fecha_inicio_uso = d?.fecha_inicio_uso ?? null;
        const fecha_fin_uso = d?.fecha_fin_uso ?? null;

        // saltar filas completamente vacías
        if (
          !medicamento &&
          !id_medicamento &&
          !id_lote &&
          !dosis_posologia &&
          !via_administracion &&
          !fecha_inicio_uso &&
          !fecha_fin_uso
        ) {
          continue;
        }

        if (!fechaFinNoMenor(fecha_inicio_uso, fecha_fin_uso)) {
          await connection.rollback();
          return res.status(400).json({ message: 'La fecha fin de uso no puede ser menor que la fecha inicio de uso.' });
        }

        const columnasDetalle = [
          'id_reaccion',
          'id_medicamento',
          'id_lote',
          'dosis_posologia',
          'via_administracion',
          'fecha_inicio_uso',
          'fecha_fin_uso',
          'usuario_creacion',
          'fecha_creacion',
        ];

        const valoresDetalle = [
          id,
          id_medicamento,
          id_lote,
          dosis_posologia || null,
          via_administracion || null,
          fecha_inicio_uso,
          fecha_fin_uso,
          usuario_modificacion,
          new Date(),
        ];

        if (tieneMedicamentoManual) {
          columnasDetalle.splice(3, 0, 'medicamento');
          valoresDetalle.splice(3, 0, medicamento || null);
        }

        await connection.execute(
          `INSERT INTO tbl_far_reaccion_detalle (${columnasDetalle.join(', ')}) VALUES (${columnasDetalle.map(() => '?').join(', ')})`,
          valoresDetalle
        );

        if (id_lote) {
          lotesEnReaccion.push(id_lote);
        }

      }

      await marcarLotesEnCuarentena(connection, lotesEnReaccion);

      // ==========================
      // ACTUALIZAR CONSECUENCIAS
      // Estrategia: borrar y volver a insertar
      // ==========================
      await connection.execute(`
        DELETE FROM tbl_far_reaccion_consecuencia
        WHERE id_reaccion = ?
      `, [id]);

      for (const c of consecuenciasNormalizadas) {
        const descripcion_consecuencia = normalizarClaveConsecuencia(c?.descripcion_consecuencia);
        const gravedad = textoMayus(c?.gravedad);

        // saltar filas vacías
        if (!descripcion_consecuencia && !gravedad) {
          continue;
        }

        await connection.execute(`
          INSERT INTO tbl_far_reaccion_consecuencia (
            id_reaccion,
            descripcion_consecuencia,
            gravedad,
            usuario_creacion,
            fecha_creacion
          )
          VALUES (?, ?, ?, ?, NOW())
        `, [
          id,
          descripcion_consecuencia || null,
          gravedad || null,
          usuario_modificacion
        ]);
      }

      await connection.commit();
      return res.json({ message: 'Reacción actualizada correctamente' });

    } catch (error) {
      await connection.rollback();
      console.error(error);
      return res.status(500).json({ message: 'Error al actualizar la reacción' });
    } finally {
      connection.release();
    }

    function textoMayus(valor) {
      return String(valor || '')
        .toUpperCase()
        .replace(/[^A-ZÁÉÍÓÚÑ0-9\s.,\-\/]/g, '')
        .replace(/(.)\1{2,}/g, '$1$1')
        .replace(/\s{2,}/g, ' ')
        .trim();
    }
  });

  // ✅ ELIMINAR REACCIÓN (BORRA EN CASCADA DETALLES/CONSECUENCIAS/FOTOS)
  router.delete('/:id', verificarToken, async (req, res) => {
    const connection = await db.getConnection();
    try {
      const { id } = req.params;

      await connection.beginTransaction();

      await connection.execute('DELETE FROM tbl_far_reaccion_detalle WHERE id_reaccion = ?', [id]);
      await connection.execute('DELETE FROM tbl_far_reaccion_consecuencia WHERE id_reaccion = ?', [id]);
      await connection.execute('DELETE FROM tbl_far_reaccion_foto WHERE id_reaccion = ?', [id]);

      const [result] = await connection.execute(
        'DELETE FROM tbl_far_reaccion_adversa WHERE id_reaccion = ?',
        [id]
      );

      if (result.affectedRows === 0) {
        await connection.rollback();
        return res.status(404).json({ message: 'Reacción no encontrada' });
      }

      await connection.commit();

      return res.json({ message: 'Reacción eliminada' });
    } catch (error) {
      await connection.rollback();
      console.error(error);

      if (error?.code === 'ER_ROW_IS_REFERENCED_2') {
        return res.status(409).json({ message: 'No se puede eliminar la reacción porque tiene registros relacionados.' });
      }

      return res.status(500).json({ message: 'Error al eliminar la reacción' });
    } finally {
      connection.release();
    }
  });


  // ✅ GUARDAR DETALLES 
  router.post('/:id/detalles', verificarToken, async (req, res) => {
    const connection = await db.getConnection();
    try {
      const { id } = req.params;
      const { detalles = [] } = req.body;
      const usuario_creacion = req.user?.id ?? null;
      const tieneMedicamentoManual = await columnaExiste(connection, 'tbl_far_reaccion_detalle', 'medicamento');

      await connection.beginTransaction();

      // Limpiar detalles anteriores para evitar duplicados al re-enviar el formulario
      await connection.execute(
        `DELETE FROM tbl_far_reaccion_detalle WHERE id_reaccion = ?`,
        [id]
      );

      for (const d of detalles) {
        const medicamento = textoMayus(d?.medicamento || d?.nombre_comercial || '');
        const dosis = textoMayus(d?.dosis_posologia);
        const via = textoMayus(d?.via_administracion);
        const fechaInicioUso = d?.fecha_inicio_uso ?? null;
        const fechaFinUso = d?.fecha_fin_uso ?? null;

        // si viene fila vacía, la ignoramos
        if (!d || (!medicamento && !d.id_medicamento && !d.id_lote && !dosis && !via && !fechaInicioUso && !fechaFinUso)) continue;

        if (!fechaFinNoMenor(fechaInicioUso, fechaFinUso)) {
          await connection.rollback();
          return res.status(400).json({ message: 'La fecha fin de uso no puede ser menor que la fecha inicio de uso.' });
        }

        const columnasDetalle = [
          'id_reaccion',
          'id_medicamento',
          'id_lote',
          'dosis_posologia',
          'via_administracion',
          'fecha_inicio_uso',
          'fecha_fin_uso',
          'usuario_creacion',
        ];

        const valoresDetalle = [
          id,
          d.id_medicamento ?? null,
          d.id_lote ?? null,
          dosis || null,
          via || null,
          fechaInicioUso,
          fechaFinUso,
          usuario_creacion,
        ];

        if (tieneMedicamentoManual) {
          columnasDetalle.splice(3, 0, 'medicamento');
          valoresDetalle.splice(3, 0, medicamento || null);
        }

        await connection.execute(
          `INSERT INTO tbl_far_reaccion_detalle (${columnasDetalle.join(',')}) VALUES (${columnasDetalle.map(() => '?').join(',')})`,
          valoresDetalle
        );
      }

      await connection.commit();
      return res.json({ message: 'Detalles guardados' });

    } catch (e) {
      await connection.rollback();
      console.error(e);
      return res.status(500).json({ message: 'Error al guardar detalles' });
    } finally {
      connection.release();
    }
  });

  // ✅ GUARDAR CONSECUENCIAS 
  router.post('/:id/consecuencias', verificarToken, async (req, res) => {
    const connection = await db.getConnection();
    try {
      const { id } = req.params;
      const { consecuencias = [] } = req.body;
      const usuario_creacion = req.user?.id ?? null;

      await connection.beginTransaction();

      for (const c of consecuencias) {
        if (!c?.descripcion_consecuencia) continue;

        await connection.execute(`
          INSERT INTO tbl_far_reaccion_consecuencia
          (id_reaccion, descripcion_consecuencia, gravedad, usuario_creacion)
          VALUES (?,?,?,?)
        `, [
          id,
          c.descripcion_consecuencia,
          c.gravedad ?? 'LEVE',
          usuario_creacion
        ]);
      }

      await connection.commit();
      return res.json({ message: 'Consecuencias guardadas' });

    } catch (e) {
      await connection.rollback();
      console.error(e);
      return res.status(500).json({ message: 'Error al guardar consecuencias' });
    } finally {
      connection.release();
    }
  });

  // ==========================================
  // LISTAR PACIENTES
  // ==========================================
  router.get('/api/pacientes', async (req, res) => {
      try {

          const [rows] = await db.execute(`
              SELECT id_paciente,
                    numero_expediente,
            nombre AS nombre_completo
              FROM tbl_far_paciente
              ORDER BY nombre ASC
          `);

          res.json(rows);

      } catch (error) {
          console.error(error);
          res.status(500).json({ message: 'Error al obtener pacientes' });
      }
  });

  router.get('/api/medicos', async (req, res) => {
    try {

      const [rows] = await db.execute(`
        SELECT m.id_medico,
              COALESCE(
                NULLIF(TRIM(CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.apellido, ''))), ''),
                NULLIF(TRIM(COALESCE(m.nombre_completo, '')), ''),
                'MEDICO NO ASIGNADO'
              ) AS nombre_completo,
              m.especialidad,
              m.numero_colegiacion
        FROM tbl_far_medico m
        LEFT JOIN tbl_seg_usuario u ON u.id_usuario = m.id_usuario
        LEFT JOIN tbl_seg_rol r ON r.id_rol = u.id_rol
        WHERE r.nombre = 'MEDICO' OR (m.id_usuario IS NULL AND m.nombre_completo IS NOT NULL)
        ORDER BY nombre_completo ASC
      `);

      res.json(rows);

    } catch (error) {
      console.error(error);
      res.status(500).json({ message: 'Error al obtener médicos' });
    }
  });

  // ==========================================
  // BUSCAR PACIENTES
  // ==========================================
  router.get('/pacientes/buscar', async (req, res) => {
    try {
      const q = `%${(req.query.q || '').trim()}%`;

      const [rows] = await db.execute(`
        SELECT 
          p.id_paciente,
          p.numero_expediente,
          p.nombre AS nombre_completo,
          p.edad,
          p.sexo,
          p.sala,
          p.numero_cama,
          p.diagnostico,
          p.id_medico,
          COALESCE(
            NULLIF(TRIM(CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.apellido, ''))), ''),
            NULLIF(TRIM(COALESCE(m.nombre_completo, '')), ''),
            ''
          ) AS nombre_medico,
          m.numero_colegiacion
        FROM tbl_far_paciente p
        LEFT JOIN tbl_far_medico m ON m.id_medico = p.id_medico
        LEFT JOIN tbl_seg_usuario u ON u.id_usuario = m.id_usuario
        WHERE p.nombre LIKE ?
          OR p.numero_expediente LIKE ?
          OR CAST(p.id_paciente AS CHAR) LIKE ?
        ORDER BY p.nombre ASC
        LIMIT 10
      `, [q, q, q]);

      return res.json(rows);
    } catch (error) {
      console.error(error);
      return res.status(500).json({ message: 'Error al buscar pacientes' });
    }
  });


  // ==========================================
  // BUSCAR MEDICOS
  // ==========================================
  router.get('/medicos/buscar', async (req, res) => {
    try {

      const q = `%${(req.query.q || '').trim()}%`;

      const [rows] = await db.execute(`
        SELECT m.id_medico,
               COALESCE(
                 NULLIF(TRIM(CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.apellido, ''))), ''),
                 NULLIF(TRIM(COALESCE(m.nombre_completo, '')), ''),
                 'MEDICO NO ASIGNADO'
               ) AS nombre_completo,
               m.numero_colegiacion
        FROM tbl_far_medico m
        LEFT JOIN tbl_seg_usuario u ON u.id_usuario = m.id_usuario
        WHERE COALESCE(
                NULLIF(TRIM(CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.apellido, ''))), ''),
                NULLIF(TRIM(COALESCE(m.nombre_completo, '')), ''),
                'MEDICO NO ASIGNADO'
              ) LIKE ?
           OR m.numero_colegiacion LIKE ?
           OR CAST(m.id_medico AS CHAR) LIKE ?
        ORDER BY nombre_completo ASC
        LIMIT 10
      `,[q,q,q]);

      res.json(rows);

    } catch (error) {

      console.error(error);
      res.status(500).json({ message: 'Error al buscar médicos' });

    }
  });

  // ================================
  // BUSCAR MEDICAMENTOS
  // ================================
  router.get('/medicamentos/buscar', async (req, res) => {
    try {
      const q = `%${(req.query.q || '').trim()}%`;

      const [rows] = await db.execute(`
        SELECT id_medicamento,
              nombre_comercial,
              principio_activo,
              laboratorio_fabricante
        FROM tbl_far_medicamento
        WHERE nombre_comercial LIKE ?
          OR principio_activo LIKE ?
          OR CAST(id_medicamento AS CHAR) LIKE ?
        ORDER BY nombre_comercial ASC
        LIMIT 10
      `, [q, q, q]);

      res.json(rows);
    } catch (error) {
      console.error(error);
      res.status(500).json({ message: 'Error al buscar medicamentos' });
    }
  });

  // ==========================================
  // BUSCAR LOTES POR MEDICAMENTO
  // ==========================================
  router.get('/lotes/buscar', async (req, res) => {
    try {
      const q = `%${(req.query.q || '').trim()}%`;
      const id_medicamento = req.query.id_medicamento || null;

      let sql = `
        SELECT id_lote,
              numero_lote,
              fecha_expiracion,
              id_medicamento
        FROM tbl_far_lote
        WHERE 1=1
      `;

      const params = [];

      if (id_medicamento) {
        sql += ` AND id_medicamento = ?`;
        params.push(id_medicamento);
      }

      sql += ` AND (numero_lote LIKE ? OR CAST(id_lote AS CHAR) LIKE ?)`;
      params.push(q, q);

      sql += ` ORDER BY numero_lote ASC LIMIT 10`;

      const [rows] = await db.execute(sql, params);

      return res.json(rows);

    } catch (error) {
      console.error(error);
      return res.status(500).json({ message: 'Error al buscar lotes' });
    }
  });

  router.get('/estadisticas/panel', verificarToken, async (req, res) => {
    try {
      const tieneMedicamentoManual = await columnaExiste(db, 'tbl_far_reaccion_detalle', 'medicamento');
      const exprNombre = tieneMedicamentoManual
        ? "COALESCE(NULLIF(TRIM(d.medicamento), ''), med.nombre_comercial, 'SIN NOMBRE')"
        : "COALESCE(med.nombre_comercial, 'SIN NOMBRE')";

      const [productos] = await db.execute(`
        SELECT
          ${exprNombre} AS medicamento,
          COUNT(*) AS total_registros
        FROM tbl_far_reaccion_detalle d
        LEFT JOIN tbl_far_medicamento med ON med.id_medicamento = d.id_medicamento
        GROUP BY ${exprNombre}
        ORDER BY total_registros DESC, medicamento ASC
        LIMIT 50
      `);

      const [pacientes] = await db.execute(`
        SELECT
          p.id_paciente,
          p.numero_expediente,
          p.nombre AS paciente,
          COUNT(*) AS total_medicamentos
        FROM tbl_far_reaccion_detalle d
        INNER JOIN tbl_far_reaccion_adversa ra ON ra.id_reaccion = d.id_reaccion
        INNER JOIN tbl_far_paciente p ON p.id_paciente = ra.id_paciente
        GROUP BY p.id_paciente, p.numero_expediente, p.nombre
        ORDER BY total_medicamentos DESC, p.nombre ASC
        LIMIT 50
      `);

      return res.json({
        total_productos_asociados: productos.length,
        productos,
        pacientes,
      });
    } catch (error) {
      console.error(error);
      return res.status(500).json({ message: 'Error al obtener estadísticas del panel.' });
    }
  });

  return router;
};