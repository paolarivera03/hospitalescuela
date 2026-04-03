const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');

async function main() {
  const sqlPath = path.join(__dirname, '20260318_bandeja_reaccion_paciente.sql');
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
  const [rows] = await connection.query("SHOW TABLES LIKE 'tbl_far_bandeja_reaccion_paciente'");

  console.log(rows.length ? 'tray table OK' : 'tray table missing');

  await connection.end();
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});