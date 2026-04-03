const fs = require('fs');
const fsPromises = require('fs/promises');
const path = require('path');
const { spawn } = require('child_process');
const db = require('../config/db');

const BACKUP_DIR = process.env.BACKUP_DIR
    ? path.resolve(process.env.BACKUP_DIR)
    : path.resolve(__dirname, '..', 'backups');

function formatTimestamp(date = new Date()) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    return `${year}${month}${day}_${hours}${minutes}${seconds}`;
}

function getDbConnectionConfig() {
    const config = db?.config?.connectionConfig || {};
    return {
        host: config.host || process.env.DB_HOST || '127.0.0.1',
        port: Number(config.port || process.env.DB_PORT || 3306),
        user: config.user || process.env.DB_USER || 'root',
        password: config.password || process.env.DB_PASSWORD || '',
        database: config.database || process.env.DB_NAME || 'hospitalescuela',
    };
}

function resolveMysqlDumpBinary() {
    const fromEnv = process.env.BACKUP_MYSQLDUMP_PATH || process.env.MYSQLDUMP_PATH;
    if (fromEnv) {
        return fromEnv;
    }

    const candidates = [
        'C:/xampp/mysql/bin/mysqldump.exe',
        'C:/xampp/mysql/bin/mysqldump',
        'C:/Program Files/MySQL/MySQL Server 8.0/bin/mysqldump.exe',
        'C:/Program Files/MySQL/MySQL Server 5.7/bin/mysqldump.exe',
    ];

    for (const candidate of candidates) {
        if (fs.existsSync(candidate)) {
            return candidate;
        }
    }

    return 'mysqldump';
}

function executeDump({ binary, args, destinationPath }) {
    return new Promise((resolve, reject) => {
        const output = fs.createWriteStream(destinationPath);
        const dumpProcess = spawn(binary, args, { windowsHide: true });
        let stderr = '';

        dumpProcess.stdout.pipe(output);
        dumpProcess.stderr.on('data', (chunk) => {
            stderr += chunk.toString();
        });

        dumpProcess.on('error', async (error) => {
            output.destroy();
            try {
                await fsPromises.unlink(destinationPath);
            } catch (_) {}
            reject(error);
        });

        dumpProcess.on('close', async (code) => {
            output.end();

            if (code !== 0) {
                try {
                    await fsPromises.unlink(destinationPath);
                } catch (_) {}
                reject(new Error(stderr || `mysqldump finalizo con codigo ${code}`));
                return;
            }

            resolve();
        });
    });
}

async function listBackups(req, res) {
    try {
        await fsPromises.mkdir(BACKUP_DIR, { recursive: true });
        const entries = await fsPromises.readdir(BACKUP_DIR, { withFileTypes: true });

        const backups = [];
        for (const entry of entries) {
            if (!entry.isFile() || !entry.name.toLowerCase().endsWith('.sql')) {
                continue;
            }

            const filePath = path.join(BACKUP_DIR, entry.name);
            const stats = await fsPromises.stat(filePath);
            backups.push({
                fileName: entry.name,
                sizeBytes: stats.size,
                sizeMb: Number((stats.size / (1024 * 1024)).toFixed(2)),
                createdAt: stats.mtime,
            });
        }

        backups.sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime());
        return res.json(backups);
    } catch (error) {
        console.error('Error listando backups:', error);
        return res.status(500).json({ message: 'No se pudieron listar los respaldos.' });
    }
}

async function createBackup(req, res) {
    const dbConfig = getDbConnectionConfig();
    const binary = resolveMysqlDumpBinary();
    const fileName = `${dbConfig.database}_backup_${formatTimestamp()}.sql`;
    const filePath = path.join(BACKUP_DIR, fileName);

    const args = [
        `--host=${dbConfig.host}`,
        `--port=${dbConfig.port}`,
        `--user=${dbConfig.user}`,
        '--single-transaction',
        '--routines',
        '--triggers',
        '--events',
        dbConfig.database,
    ];

    if (dbConfig.password) {
        args.splice(3, 0, `--password=${dbConfig.password}`);
    }

    try {
        await fsPromises.mkdir(BACKUP_DIR, { recursive: true });
        await executeDump({ binary, args, destinationPath: filePath });

        return res.json({
            message: 'Backup generado correctamente.',
            fileName,
        });
    } catch (error) {
        console.error('Error creando backup:', error);
        return res.status(500).json({
            message: 'No se pudo generar el backup. Verifique ruta de mysqldump de XAMPP y acceso a la base de datos.',
            detail: error.message,
        });
    }
}

async function downloadBackup(req, res) {
    try {
        const fileName = path.basename(req.params.fileName || '');
        if (!fileName || !fileName.toLowerCase().endsWith('.sql')) {
            return res.status(400).json({ message: 'Archivo de backup inválido.' });
        }

        const filePath = path.join(BACKUP_DIR, fileName);
        await fsPromises.access(filePath, fs.constants.R_OK);
        return res.download(filePath, fileName);
    } catch (error) {
        return res.status(404).json({ message: 'No se encontró el backup solicitado.' });
    }
}

function resolveMysqlBinary() {
    const fromEnv = process.env.MYSQL_PATH;
    if (fromEnv) return fromEnv;

    const candidates = [
        'C:/xampp/mysql/bin/mysql.exe',
        'C:/xampp/mysql/bin/mysql',
        'C:/Program Files/MySQL/MySQL Server 8.0/bin/mysql.exe',
        'C:/Program Files/MySQL/MySQL Server 5.7/bin/mysql.exe',
    ];

    for (const candidate of candidates) {
        if (fs.existsSync(candidate)) return candidate;
    }

    return 'mysql';
}

function executeRestore({ binary, dbConfig, sourcePath }) {
    return new Promise((resolve, reject) => {
        const args = [
            `--host=${dbConfig.host}`,
            `--port=${dbConfig.port}`,
            `--user=${dbConfig.user}`,
            dbConfig.database,
        ];

        if (dbConfig.password) {
            args.splice(3, 0, `--password=${dbConfig.password}`);
        }

        const input = fs.createReadStream(sourcePath);
        const mysqlProcess = spawn(binary, args, { windowsHide: true });
        let stderr = '';

        input.pipe(mysqlProcess.stdin);
        mysqlProcess.stderr.on('data', (chunk) => { stderr += chunk.toString(); });
        mysqlProcess.on('error', reject);
        mysqlProcess.on('close', (code) => {
            if (code !== 0) {
                reject(new Error(stderr || `mysql finalizó con código ${code}`));
                return;
            }
            resolve();
        });
    });
}

async function restoreBackup(req, res) {
    try {
        const fileName = path.basename(req.params.fileName || '');
        if (!fileName || !fileName.toLowerCase().endsWith('.sql')) {
            return res.status(400).json({ message: 'Archivo de backup inválido.' });
        }

        const filePath = path.join(BACKUP_DIR, fileName);
        await fsPromises.access(filePath, fs.constants.R_OK);

        const dbConfig = getDbConnectionConfig();
        const binary = resolveMysqlBinary();

        await executeRestore({ binary, dbConfig, sourcePath: filePath });

        return res.json({ message: `Backup "${fileName}" restaurado correctamente.` });
    } catch (error) {
        console.error('Error restaurando backup:', error);
        return res.status(500).json({
            message: 'No se pudo restaurar el backup. Verifique ruta de mysql de XAMPP y acceso a la base de datos.',
            detail: error.message,
        });
    }
}

async function forceLogoutAll(req, res) {
    try {
        const nowTimestamp = Math.floor(Date.now() / 1000); // Unix epoch en segundos (igual que JWT iat)

        const [existing] = await db.execute(
            "SELECT id_parametro FROM tbl_seg_parametro WHERE UPPER(nombre_parametro) = 'FORZAR_SESIONES_AT' LIMIT 1"
        );

        if (existing.length > 0) {
            await db.execute(
                "UPDATE tbl_seg_parametro SET valor = ?, estado = 'ACTIVO', fecha_modificacion = NOW(), usuario_modificacion = ? WHERE UPPER(nombre_parametro) = 'FORZAR_SESIONES_AT'",
                [nowTimestamp, req.user?.id || null]
            );
        } else {
            await db.execute(
                "INSERT INTO tbl_seg_parametro (nombre_parametro, descripcion, valor, estado, usuario_creacion) VALUES ('FORZAR_SESIONES_AT', 'Timestamp unix para invalidar sesiones anteriores', ?, 'ACTIVO', ?)",
                [nowTimestamp, req.user?.id || null]
            );
        }

        // Limpiar el caché del middleware para efecto inmediato
        try {
            const { clearForcedLogoutCache } = require('../middleware/verificarToken');
            if (typeof clearForcedLogoutCache === 'function') clearForcedLogoutCache();
        } catch (_e) {}

        return res.json({ message: 'Todas las sesiones activas han sido cerradas forzosamente.' });
    } catch (error) {
        console.error('Error forzando cierre de sesiones:', error);
        return res.status(500).json({ message: 'No se pudo forzar el cierre de sesiones.' });
    }
}

module.exports = {
    listBackups,
    createBackup,
    downloadBackup,
    restoreBackup,
    forceLogoutAll,
};
