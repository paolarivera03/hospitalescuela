const express = require('express');
const router = express.Router();

const {
    listBackups,
    createBackup,
    downloadBackup,
    restoreBackup,
    forceLogoutAll,
} = require('../controllers/backup.controller');

router.get('/', listBackups);
router.post('/', createBackup);
router.get('/:fileName/download', downloadBackup);
router.post('/:fileName/restore', restoreBackup);
router.post('/force-logout', forceLogoutAll);

module.exports = router;
