-- ============================================================
-- Tabla de capacidades por rol (roles dinámicos)
-- Aplica este script manualmente una sola vez en la BD.
-- ============================================================

CREATE TABLE IF NOT EXISTS tbl_seg_capacidades_rol (
    id        INT          AUTO_INCREMENT PRIMARY KEY,
    id_rol    INT          NOT NULL,
    capacidad VARCHAR(100) NOT NULL,
    UNIQUE KEY uq_rol_cap (id_rol, capacidad)
);

-- ── acceso_total ─────────────────────────────────────────────
-- El rol puede acceder a todo el sistema sin restricciones.
-- (antes: nombre contiene 'ADMINISTRADOR')
INSERT IGNORE INTO tbl_seg_capacidades_rol (id_rol, capacidad)
SELECT id_rol, 'acceso_total'
FROM tbl_seg_rol
WHERE UPPER(nombre) LIKE '%ADMINISTRADOR%';

-- ── ver_inventario_completo ───────────────────────────────────
-- El rol puede ver la tabla completa de inventario con todos
-- los estados y columnas.
-- (antes: ADMINISTRADOR o JEFE)
INSERT IGNORE INTO tbl_seg_capacidades_rol (id_rol, capacidad)
SELECT id_rol, 'ver_inventario_completo'
FROM tbl_seg_rol
WHERE UPPER(nombre) LIKE '%ADMINISTRADOR%'
   OR UPPER(nombre) LIKE '%JEFE%';

-- ── inventario_solo_stock ─────────────────────────────────────
-- El rol solo puede ver nombre del medicamento y si hay stock
-- (estado ACTIVO con saldo > 0).
-- (antes: MEDICO, ENFERMERO, FARMACEUTICO)
INSERT IGNORE INTO tbl_seg_capacidades_rol (id_rol, capacidad)
SELECT id_rol, 'inventario_solo_stock'
FROM tbl_seg_rol
WHERE UPPER(nombre) LIKE '%MEDICO%'
   OR UPPER(nombre) LIKE '%ENFERMERO%'
   OR UPPER(nombre) LIKE '%FARMACEUT%';

-- ── Prefijos de nombre para la interfaz ──────────────────────
INSERT IGNORE INTO tbl_seg_capacidades_rol (id_rol, capacidad)
SELECT id_rol, 'prefijo_farm'
FROM tbl_seg_rol
WHERE UPPER(nombre) LIKE '%FARMACEUT%';

INSERT IGNORE INTO tbl_seg_capacidades_rol (id_rol, capacidad)
SELECT id_rol, 'prefijo_med'
FROM tbl_seg_rol
WHERE UPPER(nombre) LIKE '%MEDIC%'
   OR UPPER(nombre) LIKE '%DOCTOR%';

INSERT IGNORE INTO tbl_seg_capacidades_rol (id_rol, capacidad)
SELECT id_rol, 'prefijo_enf'
FROM tbl_seg_rol
WHERE UPPER(nombre) LIKE '%ENFERM%';
