-- Agregar columnas para consecuencias a la tabla reacciones_adversas
-- Ejecutar este script en la base de datos MySQL

ALTER TABLE reacciones_adversas ADD COLUMN IF NOT EXISTS descripcion_consecuencia LONGTEXT NULL AFTER observaciones;
ALTER TABLE reacciones_adversas ADD COLUMN IF NOT EXISTS hospitalizacion VARCHAR(100) NULL AFTER descripcion_consecuencia;
ALTER TABLE reacciones_adversas ADD COLUMN IF NOT EXISTS gravedad ENUM('LEVE', 'MODERADA', 'GRAVE', 'MUY_GRAVE') NULL AFTER hospitalizacion;

-- Agregar índices para mejor búsqueda (opcional)
-- ALTER TABLE reacciones_adversas ADD INDEX idx_gravedad (gravedad);
