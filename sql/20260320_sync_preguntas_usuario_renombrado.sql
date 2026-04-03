-- Repara los casos ya existentes donde el usuario fue renombrado
-- y no se sincronizo tbl_seg_pregunta_usuario.

USE hospitalescuela;

-- Caso reportado: GATO -> GATICO
UPDATE tbl_seg_pregunta_usuario
SET usuario = 'GATICO'
WHERE UPPER(usuario) = 'GATO';

-- Verificacion del caso
SELECT id, usuario, pregunta_id, estado
FROM tbl_seg_pregunta_usuario
WHERE UPPER(usuario) IN ('GATO', 'GATICO')
ORDER BY id;
