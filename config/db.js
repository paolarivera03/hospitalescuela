// Backend/config/db.js
const mysql = require('mysql2/promise');

const pool = mysql.createPool({
    host: '127.0.0.1',   // mejor que 'localhost' en Windows
    user: 'root',
    password: '',
    database: 'hospitalescuela',
    port: 3306,          // si en XAMPP sale otro, pon ese
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

module.exports = pool;