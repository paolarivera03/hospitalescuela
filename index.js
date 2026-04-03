// Backend/index.js
const express = require('express');
const cors = require('cors');
const cookieParser = require('cookie-parser');
const path = require('path');

const db = require('./config/db');

// ─── Auto-migración: crea y siembra tbl_seg_capacidades_rol si no existe ──────
async function ensureCapacidadesRol() {
    try {
        await db.execute(`
            CREATE TABLE IF NOT EXISTS tbl_seg_capacidades_rol (
                id        INT          AUTO_INCREMENT PRIMARY KEY,
                id_rol    INT          NOT NULL,
                capacidad VARCHAR(100) NOT NULL,
                UNIQUE KEY uq_rol_cap (id_rol, capacidad)
            )
        `);

        const seeds = [
            { like: '%ADMINISTRADOR%', cap: 'acceso_total' },
            { like: '%ADMINISTRADOR%', cap: 'ver_inventario_completo' },
            { like: '%JEFE%',          cap: 'ver_inventario_completo' },
            { like: '%MEDICO%',        cap: 'inventario_solo_stock' },
            { like: '%ENFERMERO%',     cap: 'inventario_solo_stock' },
            { like: '%FARMACEUT%',     cap: 'inventario_solo_stock' },
            { like: '%FARMACEUT%',     cap: 'prefijo_farm' },
            { like: '%MEDIC%',         cap: 'prefijo_med' },
            { like: '%DOCTOR%',        cap: 'prefijo_med' },
            { like: '%ENFERM%',        cap: 'prefijo_enf' },
        ];

        for (const { like, cap } of seeds) {
            await db.execute(`
                INSERT IGNORE INTO tbl_seg_capacidades_rol (id_rol, capacidad)
                SELECT id_rol, ?
                FROM tbl_seg_rol
                WHERE UPPER(nombre) LIKE ?
            `, [cap, like]);
        }

        console.log('✅ tbl_seg_capacidades_rol lista.');
    } catch (err) {
        console.warn('⚠️  No se pudo preparar tbl_seg_capacidades_rol:', err.message);
    }
}
// ─────────────────────────────────────────────────────────────────────────────
const authRoutes = require('./routes/auth.routes');
const usuariosRoutes = require('./routes/usuarios'); // 👈 NUEVO
console.log('DEBUG: usuarios route resolved to', require.resolve('./routes/usuarios'));
const reaccionesAdversasRoutes = require('./routes/reacciones_adversas');
const pacientesRoutes = require('./routes/pacientes');
const backupRoutes = require('./routes/backup');
const parametrosRoutes = require('./routes/parametros');
const configuracionesRoutes = require('./routes/configuraciones');
const verificarToken = require('./middleware/verificarToken');
const autorizarPermiso = require('./middleware/autorizarPermiso');
const auditLogger = require('./middleware/auditLogger');

const app = express();

// Habilitar CORS con credenciales para permitir que el navegador acepte el cookie enviado por el backend.
app.use(cors({
  origin: (origin, callback) => callback(null, true),
  credentials: true,
}));
app.use(cookieParser());
app.use(express.json());
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));
app.use('/api', auditLogger);

// 🔍 Test rápido de BD
app.get('/api/test-db', async (req, res) => {
    try {
        const [rows] = await db.execute('SELECT COUNT(*) AS total FROM tbl_seg_usuario');
        res.json({
            message: 'Conexión a BD OK',
            total_usuarios: rows[0].total
        });
    } catch (error) {
        console.error('Error probando la BD:', error);
        res.status(500).json({ message: 'Error conectando a la BD' });
    }
});

// 🔐 Rutas de autenticación / seguridad (login, register, recover, change, perfil, bitácora)
app.use('/api', authRoutes);

// 👥 Rutas de mantenimiento de usuarios (lista, crear, editar, borrar lógico)
app.use('/api/usuarios', usuariosRoutes); // 👈 AQUÍ MONTAMOS LO NUEVO

// 🏥 Rutas de reacciones adversas
app.use('/api/reacciones-adversas', reaccionesAdversasRoutes(db, verificarToken));

// 🧾 Rutas de pacientes
app.use('/api/pacientes', verificarToken, pacientesRoutes);

// 💾 Rutas de backup de base de datos
app.use('/api/backups', verificarToken, backupRoutes);

// ⚙️ Rutas de parametros de seguridad
app.use('/api/parametros', verificarToken, parametrosRoutes);

// ⚙️ Rutas de configuracion de accesos del sistema
app.use('/api/configuraciones', verificarToken, configuracionesRoutes);

// ==========================================
// ENDPOINT DE ROLES
// ==========================================
app.get('/api/roles', verificarToken, autorizarPermiso(8, 'VISUALIZAR'), async (req, res) => {
  try {
    const includeAll = String(req.query.include_all || '').trim() === '1';
    const sql = includeAll
      ? `SELECT id_rol, nombre, descripcion, estado
           FROM tbl_seg_rol
          ORDER BY id_rol DESC`
      : `SELECT id_rol, nombre, descripcion, estado
           FROM tbl_seg_rol
          WHERE estado = 'ACTIVO'
          ORDER BY id_rol DESC`;

    const [roles] = await db.execute(
      sql
    );
    res.json(roles);
  } catch (error) {
    console.error('Error al obtener roles:', error);
    res.status(500).json({ message: 'Error al obtener los roles.' });
  }
});

app.post('/api/roles', verificarToken, autorizarPermiso(8, 'GUARDAR'), async (req, res) => {
  try {
    const nombre = String(req.body.nombre || '').trim().toUpperCase();
    const descripcionRaw = req.body.descripcion;
    const descripcion = descripcionRaw == null ? null : String(descripcionRaw).trim();
    const estado = String(req.body.estado || 'ACTIVO').trim().toUpperCase();

    if (!nombre) {
      return res.status(400).json({ message: 'El nombre del rol es obligatorio.' });
    }

    if (!['ACTIVO', 'INACTIVO'].includes(estado)) {
      return res.status(400).json({ message: 'El estado del rol no es valido.' });
    }

    const [exists] = await db.execute(
      `SELECT id_rol FROM tbl_seg_rol WHERE UPPER(nombre) = ? LIMIT 1`,
      [nombre]
    );

    if (exists.length > 0) {
      return res.status(409).json({ message: 'Ya existe un rol con ese nombre.' });
    }

    const [result] = await db.execute(
      `INSERT INTO tbl_seg_rol (nombre, descripcion, estado)
       VALUES (?, ?, ?)`,
      [nombre, descripcion, estado]
    );

    res.status(201).json({
      message: 'Rol creado correctamente.',
      id_rol: result.insertId,
    });
  } catch (error) {
    console.error('Error al crear rol:', error);
    res.status(500).json({ message: 'Error al crear el rol.' });
  }
});

app.put('/api/roles/:id', verificarToken, autorizarPermiso(8, 'ACTUALIZAR'), async (req, res) => {
  try {
    const id = Number(req.params.id);
    const nombre = String(req.body.nombre || '').trim().toUpperCase();
    const descripcionRaw = req.body.descripcion;
    const descripcion = descripcionRaw == null ? null : String(descripcionRaw).trim();
    const estado = String(req.body.estado || 'ACTIVO').trim().toUpperCase();

    if (!Number.isInteger(id) || id <= 0) {
      return res.status(400).json({ message: 'Id de rol invalido.' });
    }

    if (!nombre) {
      return res.status(400).json({ message: 'El nombre del rol es obligatorio.' });
    }

    if (!['ACTIVO', 'INACTIVO'].includes(estado)) {
      return res.status(400).json({ message: 'El estado del rol no es valido.' });
    }

    const [targetRole] = await db.execute(
      `SELECT id_rol, nombre FROM tbl_seg_rol WHERE id_rol = ? LIMIT 1`,
      [id]
    );

    if (targetRole.length === 0) {
      return res.status(404).json({ message: 'Rol no encontrado.' });
    }

    const isAdminRole = Number(targetRole[0].id_rol) === 1 || String(targetRole[0].nombre || '').trim().toUpperCase() === 'ADMINISTRADOR';
    if (isAdminRole) {
      return res.status(403).json({ message: 'El rol Administrador no se puede editar.' });
    }

    const [exists] = await db.execute(
      `SELECT id_rol FROM tbl_seg_rol WHERE UPPER(nombre) = ? AND id_rol <> ? LIMIT 1`,
      [nombre, id]
    );

    if (exists.length > 0) {
      return res.status(409).json({ message: 'Ya existe otro rol con ese nombre.' });
    }

    const [result] = await db.execute(
      `UPDATE tbl_seg_rol
          SET nombre = ?, descripcion = ?, estado = ?
        WHERE id_rol = ?`,
      [nombre, descripcion, estado, id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ message: 'Rol no encontrado.' });
    }

    res.json({ message: 'Rol actualizado correctamente.' });
  } catch (error) {
    console.error('Error al actualizar rol:', error);
    res.status(500).json({ message: 'Error al actualizar el rol.' });
  }
});

app.patch('/api/roles/:id/estado', verificarToken, autorizarPermiso(8, 'ACTUALIZAR'), async (req, res) => {
  try {
    const id = Number(req.params.id);
    const estado = String(req.body.estado || '').trim().toUpperCase();

    if (!Number.isInteger(id) || id <= 0) {
      return res.status(400).json({ message: 'Id de rol invalido.' });
    }

    if (!['ACTIVO', 'INACTIVO'].includes(estado)) {
      return res.status(400).json({ message: 'Estado de rol invalido.' });
    }

    const [targetRole] = await db.execute(
      `SELECT id_rol, nombre FROM tbl_seg_rol WHERE id_rol = ? LIMIT 1`,
      [id]
    );

    if (targetRole.length === 0) {
      return res.status(404).json({ message: 'Rol no encontrado.' });
    }

    const isAdminRole = Number(targetRole[0].id_rol) === 1 || String(targetRole[0].nombre || '').trim().toUpperCase() === 'ADMINISTRADOR';
    if (isAdminRole) {
      return res.status(403).json({ message: 'El rol Administrador no se puede desactivar ni activar.' });
    }

    const [result] = await db.execute(
      `UPDATE tbl_seg_rol SET estado = ? WHERE id_rol = ?`,
      [estado, id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ message: 'Rol no encontrado.' });
    }

    res.json({ message: 'Estado del rol actualizado correctamente.' });
  } catch (error) {
    console.error('Error al actualizar estado del rol:', error);
    res.status(500).json({ message: 'Error al actualizar estado del rol.' });
  }
});

// ==========================================
// ENDPOINT DE MEDICOS
// ==========================================
app.get('/api/medicos', verificarToken, async (req, res) => {
  try {
    const [rows] = await db.execute(`
      SELECT m.id_medico, m.id_usuario, m.nombre_completo, m.especialidad, m.numero_colegiacion
      FROM tbl_far_medico m
      LEFT JOIN tbl_seg_usuario u ON u.id_usuario = m.id_usuario
      LEFT JOIN tbl_seg_rol r ON r.id_rol = u.id_rol
      WHERE m.estado = 'ACTIVO'
        AND (r.nombre = 'MEDICO' OR (m.id_usuario IS NULL AND m.nombre_completo IS NOT NULL))
      ORDER BY m.nombre_completo ASC
    `);
    res.json(rows);
  } catch (error) {
    console.error('Error al obtener médicos:', error);
    res.status(500).json({ message: 'Error al obtener médicos.' });
  }
});

// ==========================================
// ENDPOINT DE PERSONAL CLINICO (NOTIFICADOR)
// ==========================================
app.get('/api/personal-clinico', verificarToken, async (req, res) => {
  try {
    const [rows] = await db.execute(`
      SELECT
        m.id_medico,
        m.id_usuario,
        COALESCE(
          NULLIF(TRIM(CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.apellido, ''))), ''),
          NULLIF(TRIM(COALESCE(m.nombre_completo, '')), ''),
          'PERSONAL NO ASIGNADO'
        ) AS nombre_completo,
        m.numero_colegiacion,
        UPPER(COALESCE(r.nombre, 'MEDICO')) AS rol_nombre
      FROM tbl_far_medico m
      LEFT JOIN tbl_seg_usuario u ON u.id_usuario = m.id_usuario
      LEFT JOIN tbl_seg_rol r ON r.id_rol = u.id_rol
      WHERE m.estado = 'ACTIVO'
        AND (
          UPPER(COALESCE(r.nombre, '')) IN ('MEDICO', 'ENFERMERO', 'FARMACEUTICO')
          OR (m.id_usuario IS NULL AND m.nombre_completo IS NOT NULL)
        )
      ORDER BY rol_nombre ASC, nombre_completo ASC
    `);

    res.json(rows);
  } catch (error) {
    console.error('Error al obtener personal clinico:', error);
    res.status(500).json({ message: 'Error al obtener personal clinico.' });
  }
});

app.post("/api/usuarios/:id/permisos", verificarToken, autorizarPermiso(13, 'ACTUALIZAR'), async (req, res) => {
  const connection = await db.getConnection();
  try {
    await connection.beginTransaction();
    const { id } = req.params;
    const { permisos } = req.body;
    const accionesPermitidas = new Set(['VISUALIZAR', 'GUARDAR', 'ACTUALIZAR', 'ELIMINAR']);

    if (!Array.isArray(permisos)) {
      await connection.rollback();
      return res.status(400).json({ message: 'El formato de permisos es inválido.' });
    }

    const permisosNormalizados = permisos.map((p) => ({
      id_usuario: Number(p?.id_usuario),
      id_formulario: Number(p?.id_formulario),
      accion: String(p?.accion || '').toUpperCase().trim(),
    }));

    const invalidos = permisosNormalizados.some((p) => {
      if (!Number.isInteger(p.id_usuario) || p.id_usuario <= 0) return true;
      if (!Number.isInteger(p.id_formulario) || p.id_formulario <= 0) return true;
      if (!accionesPermitidas.has(p.accion)) return true;
      return false;
    });

    if (invalidos) {
      await connection.rollback();
      return res.status(400).json({ message: 'Hay permisos inválidos. Solo se permite: Visualizar, Guardar, Actualizar y Eliminar.' });
    }

    const idUsuarioRuta = Number(id);
    const mismatchUsuario = permisosNormalizados.some((p) => p.id_usuario !== idUsuarioRuta);
    if (mismatchUsuario) {
      await connection.rollback();
      return res.status(400).json({ message: 'Los permisos enviados no corresponden al usuario de la ruta.' });
    }

    // A. Limpiar permisos anteriores del usuario para evitar duplicados
    // NOTA: Asumo que la tabla se llama 'tbl_seg_permisos'. Si tu tabla se llama distinto (ej. 'tbl_permisos'), cámbialo aquí.
    await connection.execute("DELETE FROM tbl_seg_permisos WHERE id_usuario = ?", [id]);

    // B. Insertar los nuevos permisos
    if (permisosNormalizados.length > 0) {
      // Formateamos los datos para un Bulk Insert (inserción masiva) de MySQL
      const valores = permisosNormalizados.map((p) => [p.id_usuario, p.id_formulario, p.accion]);
      
      // Usamos .query en lugar de .execute para inserciones masivas con array multidimensional
      await connection.query(
        "INSERT INTO tbl_seg_permisos (id_usuario, id_formulario, accion) VALUES ?",
        [valores]
      );
    }

    await connection.commit();
    res.json({ message: "Permisos guardados con éxito." });
  } catch (error) {
    await connection.rollback();
    console.error("Error al guardar permisos:", error);
    res.status(500).json({ message: "Error interno al guardar los permisos en la base de datos." });
  } finally {
    connection.release();
  }
});

// ==========================================
// OBTENER PERMISOS DE UN USUARIO (Para marcar los checkboxes)
// ==========================================
app.get("/api/usuarios/:id/permisos", verificarToken, autorizarPermiso(13, 'VISUALIZAR'), async (req, res) => {
  try {
    const { id } = req.params;
    const [permisos] = await db.execute(
      "SELECT id_formulario, accion FROM tbl_seg_permisos WHERE id_usuario = ?", 
      [id]
    );
    res.json(permisos);
  } catch (error) {
    console.error("Error al cargar permisos:", error);
    res.status(500).json({ message: "Error interno al cargar los permisos." });
  }
});

const PORT = 3000;
ensureCapacidadesRol().then(() => {
    app.listen(PORT, () => {
        console.log(`Backend de la API corriendo en http://localhost:${PORT}`);
    });
});