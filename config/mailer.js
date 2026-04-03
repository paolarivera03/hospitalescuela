// Backend/config/mailer.js
const nodemailer = require('nodemailer');
require('dotenv').config();

const mailUser = process.env.MAIL_USER;
const mailPass = process.env.MAIL_PASS;

// Transporter usando Gmail + contraseña de aplicación
const transporter = nodemailer.createTransport({
    service: 'gmail',
    auth: {
        user: mailUser,
        pass: mailPass
    }
});

// Enviar correo con contraseña temporal al crear usuario
async function sendTempPasswordEmail({ to, username, tempPassword }) {
    const mailOptions = {
        from: `"Sistema de Gestión de Medicamentos" <${mailUser}>`,
        to,
        subject: 'Cuenta creada - Contraseña temporal',
        html: `
            <p>Hola <strong>${username}</strong>,</p>
            <p>Se ha creado una cuenta en el <strong>Sistema de Gestión de Medicamentos</strong>.</p>
            <p>Tu contraseña temporal es:</p>
            <p style="font-size: 18px; font-weight: bold;">${tempPassword}</p>
            <p>Por seguridad, deberás cambiar esta contraseña al iniciar sesión por primera vez.</p>
            <p>Si tú no solicitaste esta cuenta, contacta al administrador del sistema.</p>
        `
    };

    await transporter.sendMail(mailOptions);
}

module.exports = {
    sendTempPasswordEmail
};