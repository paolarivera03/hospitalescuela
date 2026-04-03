-- Asegura el parametro de configuracion de preguntas de seguridad.
INSERT INTO tbl_seg_parametro (nombre_parametro, valor, descripcion, estado)
SELECT 'ADMIN_PREGUNTAS', '3', 'Cantidad de preguntas de recuperacion a configurar por usuario nuevo', 'ACTIVO'
WHERE NOT EXISTS (
    SELECT 1
    FROM tbl_seg_parametro
    WHERE nombre_parametro = 'ADMIN_PREGUNTAS'
);

-- Normaliza el valor estandar del parametro.
UPDATE tbl_seg_parametro
SET valor = '3',
    estado = 'ACTIVO'
WHERE nombre_parametro = 'ADMIN_PREGUNTAS';
