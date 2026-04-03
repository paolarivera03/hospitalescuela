const http = require('http');
const jwt = require('jsonwebtoken');
const db = require('./config/db');

const SECRET_KEY = 'clave_secreta_universidad';

const token = jwt.sign({ id: 15, usuario: 'NERU', correo: 'rammze00@gmail.com' }, SECRET_KEY, { expiresIn: '2h' });

const postData = JSON.stringify({
    usuario: 'NERU',
    correo: 'rammze00@gmail.com',
    nombre: 'Alejandro',
    apellido: 'Ávila',
    telefono: '',
    estado: 'ACTIVO',
    rol: '1'
});

const options = {
    hostname: 'localhost',
    port: 3000,
    path: '/api/usuarios/15',
    method: 'PUT',
    headers: {
        'Content-Type': 'application/json',
        'Content-Length': Buffer.byteLength(postData),
        'Authorization': 'Bearer ' + token
    }
};

const req = http.request(options, (res) => {
    let data = '';
    res.on('data', chunk => data += chunk);
    res.on('end', async () => {
        console.log('Status', res.statusCode);
        console.log('Body', data);

        const [rows] = await db.execute('SELECT id_usuario,id_rol FROM tbl_seg_usuario WHERE id_usuario=?', [15]);
        console.log('DB', rows);

        process.exit(0);
    });
});

req.on('error', (e) => {
    console.error('Request error', e);
    process.exit(1);
});

req.write(postData);
req.end();
