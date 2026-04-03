-- Crea el rol JEFE si aun no existe.
-- Esquema actual: tbl_seg_rol SIN columna id_usuario.

INSERT INTO tbl_seg_rol (nombre, descripcion, estado)
SELECT 'JEFE', 'Supervisa operaciones del area', 'ACTIVO'
WHERE NOT EXISTS (
    SELECT 1
    FROM tbl_seg_rol
    WHERE UPPER(nombre) = 'JEFE'
);

-- Verificacion opcional:
-- SELECT id_rol, nombre, descripcion, estado
-- FROM tbl_seg_rol
-- WHERE UPPER(nombre) = 'JEFE';
