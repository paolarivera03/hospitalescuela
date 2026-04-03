-- Elimina el flujo de preguntas de seguridad y su configuracion asociada.
-- Ejecutar en ambiente controlado y con respaldo previo.

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS tbl_seg_pregunta_usuario;
DROP TABLE IF EXISTS tbl_seg_preguntas;

SET FOREIGN_KEY_CHECKS = 1;

DELETE FROM tbl_seg_parametro
WHERE nombre_parametro = 'ADMIN_PREGUNTAS';
