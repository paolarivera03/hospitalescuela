-- Agrega columna de intentos fallidos a tbl_seg_usuario.
-- Ejecutar UNA sola vez desde phpMyAdmin / DBeaver / MySQL CLI.
-- ALTER IGNORE equivale a "si ya existe, no hagas nada".

ALTER TABLE tbl_seg_usuario
  ADD COLUMN IF NOT EXISTS intentos_fallidos_login INT NOT NULL DEFAULT 0;

-- Verificación: debe aparecer la columna en la lista.
-- SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT
--   FROM information_schema.COLUMNS
--  WHERE TABLE_SCHEMA = DATABASE()
--    AND TABLE_NAME   = 'tbl_seg_usuario'
--    AND COLUMN_NAME  = 'intentos_fallidos_login';
