-- Agrega campos para captura manual en Reacciones Adversas.
-- Compatible con ejecuciones repetidas gracias a IF NOT EXISTS.

ALTER TABLE tbl_far_reaccion_adversa
    ADD COLUMN IF NOT EXISTS sala VARCHAR(50) NULL AFTER estado,
    ADD COLUMN IF NOT EXISTS numero_cama VARCHAR(50) NULL AFTER sala;

ALTER TABLE tbl_far_reaccion_detalle
    ADD COLUMN IF NOT EXISTS medicamento VARCHAR(150) NULL AFTER id_lote;
