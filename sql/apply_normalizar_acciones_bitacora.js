const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');

async function main() {
  const sqlPath = path.join(__dirname, '20260320_normalizar_acciones_bitacora.sql');
  const sql = fs.readFileSync(sqlPath, 'utf8');

  const connection = await mysql.createConnection({
    host: '127.0.0.1',
    user: 'root',
    password: '',
    database: 'hospitalescuela',
    port: 3306,
    multipleStatements: true,
  });

  await connection.query(sql);

  const [rows] = await connection.query(`
    SELECT ta.id_tipo_accion, f.descripcion AS formulario, ta.accion, ta.descripcion
    FROM tbl_seg_tipo_accion ta
    INNER JOIN tbl_seg_formulario f ON f.id_formulario = ta.id_formulario
    ORDER BY ta.id_tipo_accion ASC
  `);

  console.log(`Total acciones: ${rows.length}`);
  console.log('Primeras 15 acciones normalizadas:');
  rows.slice(0, 15).forEach((r) => {
    console.log(`${r.id_tipo_accion} | ${r.formulario} | ${r.accion} | ${r.descripcion}`);
  });

  await connection.end();
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
