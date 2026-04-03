-- Agrega nuevos estados a tbl_far_lote.
-- Estados nuevos: BAJA_ROTACION, EN_CUARENTENA
-- NOTA: Esta operación puede tardar si la tabla es grande.

ALTER TABLE tbl_far_lote
MODIFY COLUMN estado ENUM('ACTIVO','VENCIDO','AGOTADO','BAJA_ROTACION','EN_CUARENTENA') DEFAULT 'ACTIVO';

-- Columna auxiliar para registrar fecha de último movimiento (detección de baja rotación)
ALTER TABLE tbl_far_lote
ADD COLUMN IF NOT EXISTS fecha_ultimo_movimiento DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Verificación:
-- SELECT DISTINCT estado FROM tbl_far_lote;
-- SELECT COLUMN_NAME, COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_NAME = 'tbl_far_lote' AND COLUMN_NAME = 'estado';
