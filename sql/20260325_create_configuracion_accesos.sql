CREATE TABLE IF NOT EXISTS tbl_seg_objeto_acceso (
  id_objeto INT NOT NULL AUTO_INCREMENT,
  tipo_objeto VARCHAR(80) NOT NULL,
  clave_objeto VARCHAR(120) NOT NULL,
  valor_objeto VARCHAR(255) NOT NULL,
  orden INT NOT NULL DEFAULT 1,
  estado ENUM('ACTIVO', 'INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  usuario_creacion INT NULL,
  fecha_modificacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  usuario_modificacion INT NULL,
  PRIMARY KEY (id_objeto),
  UNIQUE KEY uk_tipo_clave_objeto (tipo_objeto, clave_objeto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'CONSECUENCIAS_REACCION', 'HAN_PUESTO_EN_PELIGRO_SU_VIDA', 'HAN PUESTO EN PELIGRO SU VIDA', 1, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'CONSECUENCIAS_REACCION' AND clave_objeto = 'HAN_PUESTO_EN_PELIGRO_SU_VIDA'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'CONSECUENCIAS_REACCION', 'HAN_SIDO_LA_CAUSA_DE_SU_HOSPITALIZACION', 'HAN SIDO LA CAUSA DE SU HOSPITALIZACION', 2, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'CONSECUENCIAS_REACCION' AND clave_objeto = 'HAN_SIDO_LA_CAUSA_DE_SU_HOSPITALIZACION'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'CONSECUENCIAS_REACCION', 'HAN_PROLONGADO_SU_INGRESO_EN_EL_HOSPITAL', 'HAN PROLONGADO SU INGRESO EN EL HOSPITAL', 3, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'CONSECUENCIAS_REACCION' AND clave_objeto = 'HAN_PROLONGADO_SU_INGRESO_EN_EL_HOSPITAL'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'CONSECUENCIAS_REACCION', 'HAN_ORIGINADO_INCAPACIDAD_PERSISTENTE_O_GRAVE', 'HAN ORIGINADO INCAPACIDAD PERSISTENTE O GRAVE', 4, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'CONSECUENCIAS_REACCION' AND clave_objeto = 'HAN_ORIGINADO_INCAPACIDAD_PERSISTENTE_O_GRAVE'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'CONSECUENCIAS_REACCION', 'HAN_CAUSADO_DEFECTO_O_ANOMALIA_CONGENITA', 'HAN CAUSADO DEFECTO O ANOMALIA CONGENITA', 5, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'CONSECUENCIAS_REACCION' AND clave_objeto = 'HAN_CAUSADO_DEFECTO_O_ANOMALIA_CONGENITA'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'CONSECUENCIAS_REACCION', 'HAN_CAUSADO_LA_MUERTE_DEL_PACIENTE', 'HAN CAUSADO LA MUERTE DEL PACIENTE', 6, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'CONSECUENCIAS_REACCION' AND clave_objeto = 'HAN_CAUSADO_LA_MUERTE_DEL_PACIENTE'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'CONSECUENCIAS_REACCION', 'NO_HAN_CAUSADO_NADA_DE_LO_ANTERIOR_PERO_CONSIDERO_QUE_ES_GRAVE', 'NO HAN CAUSADO NADA DE LO ANTERIOR, PERO CONSIDERO QUE ES GRAVE', 7, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'CONSECUENCIAS_REACCION' AND clave_objeto = 'NO_HAN_CAUSADO_NADA_DE_LO_ANTERIOR_PERO_CONSIDERO_QUE_ES_GRAVE'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'CONSECUENCIAS_REACCION', 'NO_HAN_CAUSADO_NADA_DE_LO_ANTERIOR_PERO_CONSIDERO_QUE_NO_ES_GRAVE', 'NO HAN CAUSADO NADA DE LO ANTERIOR, PERO CONSIDERO QUE NO ES GRAVE', 8, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'CONSECUENCIAS_REACCION' AND clave_objeto = 'NO_HAN_CAUSADO_NADA_DE_LO_ANTERIOR_PERO_CONSIDERO_QUE_NO_ES_GRAVE'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'DESENLACE_REACCION', 'DESCONOCIDO', 'DESCONOCIDO', 1, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'DESENLACE_REACCION' AND clave_objeto = 'DESCONOCIDO'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'DESENLACE_REACCION', 'RECUPERADO_RESUELTO', 'RECUPERADO/RESUELTO', 2, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'DESENLACE_REACCION' AND clave_objeto = 'RECUPERADO_RESUELTO'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'DESENLACE_REACCION', 'EN_RECUPERACION_EN_RESOLUCION', 'EN RECUPERACION / EN RESOLUCION', 3, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'DESENLACE_REACCION' AND clave_objeto = 'EN_RECUPERACION_EN_RESOLUCION'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'DESENLACE_REACCION', 'NO_RECUPERADO_NO_RESUELTO', 'NO RECUPERADO / NO RESUELTO', 4, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'DESENLACE_REACCION' AND clave_objeto = 'NO_RECUPERADO_NO_RESUELTO'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'DESENLACE_REACCION', 'RECUPERADO_RESUELTO_CON_SECUELAS', 'RECUPERADO/RESUELTO CON SECUELAS', 5, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'DESENLACE_REACCION' AND clave_objeto = 'RECUPERADO_RESUELTO_CON_SECUELAS'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'DESENLACE_REACCION', 'MORTAL', 'MORTAL', 6, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'DESENLACE_REACCION' AND clave_objeto = 'MORTAL'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'ESTADO_REACCION', 'REGISTRADA', 'REGISTRADA', 1, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'ESTADO_REACCION' AND clave_objeto = 'REGISTRADA'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'ESTADO_REACCION', 'EN_ANALISIS', 'EN ANALISIS', 2, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'ESTADO_REACCION' AND clave_objeto = 'EN_ANALISIS'
);

INSERT INTO tbl_seg_objeto_acceso (tipo_objeto, clave_objeto, valor_objeto, orden, estado)
SELECT 'ESTADO_REACCION', 'CERRADA', 'CERRADA', 3, 'ACTIVO'
WHERE NOT EXISTS (
  SELECT 1 FROM tbl_seg_objeto_acceso WHERE tipo_objeto = 'ESTADO_REACCION' AND clave_objeto = 'CERRADA'
);
