const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');

async function main() {
  const sqlPath = path.join(__dirname, '20260401_drop_unused_inv_tables.sql');
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

  const [configRows] = await connection.query("SHOW TABLES LIKE 'tbl_inv_configuracion_stock'");
  const [ajusteRows] = await connection.query("SHOW TABLES LIKE 'tbl_inv_ajuste_inventario'");
  const [movimientoRows] = await connection.query("SHOW TABLES LIKE 'tbl_inv_movimiento'");

  console.log(configRows.length ? 'tbl_inv_configuracion_stock still exists' : 'tbl_inv_configuracion_stock removed');
  console.log(ajusteRows.length ? 'tbl_inv_ajuste_inventario still exists' : 'tbl_inv_ajuste_inventario removed');
  console.log(movimientoRows.length ? 'tbl_inv_movimiento preserved' : 'tbl_inv_movimiento missing');

  await connection.end();
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});