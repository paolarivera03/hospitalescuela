-- Parametros para opciones dinamicas de gravedad en RAM
-- Convencion: nombre_parametro con prefijo RAM_GRAVEDAD_

INSERT INTO tbl_seg_parametro (nombre_parametro, descripcion, valor, estado, fecha_creacion)
SELECT 'RAM_GRAVEDAD_LEVE', 'Opcion de gravedad para RAM', 'LEVE', 'ACTIVO', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_seg_parametro WHERE UPPER(nombre_parametro) = 'RAM_GRAVEDAD_LEVE'
);

INSERT INTO tbl_seg_parametro (nombre_parametro, descripcion, valor, estado, fecha_creacion)
SELECT 'RAM_GRAVEDAD_MODERADA', 'Opcion de gravedad para RAM', 'MODERADA', 'ACTIVO', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_seg_parametro WHERE UPPER(nombre_parametro) = 'RAM_GRAVEDAD_MODERADA'
);

INSERT INTO tbl_seg_parametro (nombre_parametro, descripcion, valor, estado, fecha_creacion)
SELECT 'RAM_GRAVEDAD_GRAVE', 'Opcion de gravedad para RAM', 'GRAVE', 'ACTIVO', NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_seg_parametro WHERE UPPER(nombre_parametro) = 'RAM_GRAVEDAD_GRAVE'
);
