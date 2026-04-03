-- Elimina tablas estandar de Laravel que no se usan en este proyecto.
-- Ejecutar manualmente en la base de datos hospitalescuela.

USE hospitalescuela;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `cache_locks`;
DROP TABLE IF EXISTS `cache`;
DROP TABLE IF EXISTS `failed_jobs`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `job_batches`;
DROP TABLE IF EXISTS `migrations`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- Verificacion rapida
SHOW TABLES LIKE 'cache';
SHOW TABLES LIKE 'cache_locks';
SHOW TABLES LIKE 'failed_jobs';
SHOW TABLES LIKE 'jobs';
SHOW TABLES LIKE 'job_batches';
SHOW TABLES LIKE 'migrations';
SHOW TABLES LIKE 'password_reset_tokens';
SHOW TABLES LIKE 'sessions';
SHOW TABLES LIKE 'users';
