-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: hospitalescuela
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `tbl_bitacora_auditoria`
--

DROP TABLE IF EXISTS `tbl_bitacora_auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_bitacora_auditoria` (
  `id_bitacora` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `usuario` varchar(10) NOT NULL,
  `id_tipo_accion` int(11) NOT NULL,
  `id_formulario` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ruta` varchar(200) NOT NULL,
  `direccion_ip` varchar(45) DEFAULT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_bitacora`),
  KEY `idx_bitacora_fecha` (`fecha_hora`),
  KEY `idx_bitacora_usuario_fecha` (`id_usuario`,`fecha_hora`),
  KEY `idx_bitacora_usuario` (`id_usuario`),
  KEY `idx_bitacora_tipo_accion` (`id_tipo_accion`),
  KEY `idx_bitacora_formulario` (`id_formulario`),
  KEY `idx_bitacora_completo` (`id_usuario`,`id_tipo_accion`,`id_formulario`),
  CONSTRAINT `tbl_bitacora_auditoria_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_seg_usuario` (`id_usuario`),
  CONSTRAINT `tbl_bitacora_auditoria_ibfk_2` FOREIGN KEY (`id_tipo_accion`) REFERENCES `tbl_seg_tipo_accion` (`id_tipo_accion`),
  CONSTRAINT `tbl_bitacora_auditoria_ibfk_3` FOREIGN KEY (`id_formulario`) REFERENCES `tbl_seg_formulario` (`id_formulario`)
) ENGINE=InnoDB AUTO_INCREMENT=403 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_bitacora_auditoria`
--

LOCK TABLES `tbl_bitacora_auditoria` WRITE;
/*!40000 ALTER TABLE `tbl_bitacora_auditoria` DISABLE KEYS */;
INSERT INTO `tbl_bitacora_auditoria` VALUES (294,15,'MENTA',1,5,'CONSULTAR en BITACORA (GET /api/bitacora)','','::1','2026-03-17 23:32:40'),(295,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD (GET /api/perfil)','','::1','2026-03-17 23:32:40'),(296,15,'MENTA',5,7,'CONSULTAR en USUARIOS (GET /api/usuarios/15)','','::1','2026-03-17 23:32:40'),(297,15,'MENTA',1,5,'CONSULTAR en BITACORA','/api/bitacora','::1','2026-03-17 23:34:29'),(298,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:34:29'),(299,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:34:29'),(300,15,'MENTA',3,6,'Cierre de sesion del usuario MENTA','/api/logout','::1','2026-03-17 23:34:42'),(301,15,'MENTA',4,6,'Inicio de sesion exitoso del usuario MENTA','/api/login','::1','2026-03-17 23:34:47'),(302,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:34:47'),(303,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:34:47'),(304,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:34:47'),(305,15,'MENTA',1,5,'CONSULTAR en BITACORA','/api/bitacora','::1','2026-03-17 23:34:48'),(306,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:34:48'),(307,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:34:48'),(308,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios','::1','2026-03-17 23:35:20'),(309,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:35:20'),(310,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios','::1','2026-03-17 23:35:51'),(311,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:35:51'),(312,15,'MENTA',1,5,'CONSULTAR en BITACORA','/api/bitacora','::1','2026-03-17 23:36:00'),(313,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:36:00'),(314,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:36:00'),(315,15,'MENTA',8,9,'CONSULTAR en PACIENTES','/api/pacientes','::1','2026-03-17 23:36:23'),(316,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:36:23'),(317,15,'MENTA',9,3,'CONSULTAR en REACCIONES ADVERSAS','/api/reacciones-adversas','::1','2026-03-17 23:36:25'),(318,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:36:25'),(319,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:36:25'),(320,15,'MENTA',8,9,'CONSULTAR en PACIENTES','/api/pacientes','::1','2026-03-17 23:36:30'),(321,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:36:30'),(322,15,'MENTA',9,3,'CONSULTAR en REACCIONES ADVERSAS','/api/reacciones-adversas','::1','2026-03-17 23:36:42'),(323,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:36:42'),(324,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:36:42'),(325,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:37:22'),(326,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:37:22'),(327,15,'MENTA',9,3,'CONSULTAR en REACCIONES ADVERSAS','/api/reacciones-adversas','::1','2026-03-17 23:37:24'),(328,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:37:24'),(329,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:37:24'),(330,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:37:25'),(331,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:37:25'),(332,15,'MENTA',8,9,'CONSULTAR en PACIENTES','/api/pacientes','::1','2026-03-17 23:37:26'),(333,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:37:26'),(334,15,'MENTA',9,3,'CONSULTAR en REACCIONES ADVERSAS','/api/reacciones-adversas','::1','2026-03-17 23:37:28'),(335,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:37:28'),(336,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:37:28'),(337,15,'MENTA',9,3,'CONSULTAR en REACCIONES ADVERSAS','/api/reacciones-adversas','::1','2026-03-17 23:42:15'),(338,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:42:16'),(339,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:42:16'),(340,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:42:17'),(341,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:42:17'),(342,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:42:24'),(343,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:42:24'),(344,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios','::1','2026-03-17 23:45:11'),(345,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:45:11'),(346,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/24','::1','2026-03-17 23:45:14'),(347,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:45:14'),(348,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/24/permisos','::1','2026-03-17 23:45:14'),(349,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios','::1','2026-03-17 23:45:16'),(350,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:45:16'),(351,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:47:43'),(352,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:47:43'),(353,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15/permisos','::1','2026-03-17 23:47:43'),(354,15,'MENTA',11,7,'CREAR en USUARIOS','/api/usuarios/15/permisos','::1','2026-03-17 23:48:05'),(355,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios','::1','2026-03-17 23:48:05'),(356,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:48:05'),(357,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios','::1','2026-03-17 23:51:41'),(358,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:51:41'),(359,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios','::1','2026-03-17 23:51:42'),(360,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:51:42'),(361,15,'MENTA',1,5,'CONSULTAR en BITACORA','/api/bitacora','::1','2026-03-17 23:51:47'),(362,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:51:47'),(363,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:51:47'),(364,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios','::1','2026-03-17 23:51:49'),(365,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:51:49'),(366,15,'MENTA',1,5,'CONSULTAR en BITACORA','/api/bitacora','::1','2026-03-17 23:51:50'),(367,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:51:50'),(368,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:51:50'),(369,15,'MENTA',12,10,'CONSULTAR en RESPALDOS','/api/backups','::1','2026-03-17 23:51:52'),(370,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:51:52'),(371,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios','::1','2026-03-17 23:51:59'),(372,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:51:59'),(373,15,'MENTA',1,5,'CONSULTAR en BITACORA','/api/bitacora','::1','2026-03-17 23:51:59'),(374,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:51:59'),(375,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:51:59'),(376,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios','::1','2026-03-17 23:52:54'),(377,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:52:54'),(378,15,'MENTA',8,9,'CONSULTAR en PACIENTES','/api/pacientes','::1','2026-03-17 23:52:55'),(379,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:52:55'),(380,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios','::1','2026-03-17 23:52:57'),(381,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:52:57'),(382,15,'MENTA',1,5,'CONSULTAR en BITACORA','/api/bitacora','::1','2026-03-17 23:53:00'),(383,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:53:00'),(384,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:53:00'),(385,15,'MENTA',8,9,'CONSULTAR en PACIENTES','/api/pacientes','::1','2026-03-17 23:53:01'),(386,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:53:01'),(387,15,'MENTA',9,3,'CONSULTAR en REACCIONES ADVERSAS','/api/reacciones-adversas','::1','2026-03-17 23:53:02'),(388,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:53:02'),(389,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:53:02'),(390,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:53:04'),(391,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:53:04'),(392,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios','::1','2026-03-17 23:53:05'),(393,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:53:05'),(394,15,'MENTA',1,5,'CONSULTAR en BITACORA','/api/bitacora','::1','2026-03-17 23:53:13'),(395,15,'MENTA',2,6,'CONSULTAR en SEGURIDAD','/api/perfil','::1','2026-03-17 23:53:13'),(396,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:53:13'),(397,15,'MENTA',12,10,'CONSULTAR en RESPALDOS','/api/backups','::1','2026-03-17 23:53:17'),(398,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:53:17'),(399,15,'MENTA',13,10,'CREAR en RESPALDOS','/api/backups','::1','2026-03-17 23:53:21'),(400,15,'MENTA',12,10,'CONSULTAR en RESPALDOS','/api/backups','::1','2026-03-17 23:53:21'),(401,15,'MENTA',5,7,'CONSULTAR en USUARIOS','/api/usuarios/15','::1','2026-03-17 23:53:21'),(402,15,'MENTA',12,10,'CONSULTAR en RESPALDOS','/api/backups/hospitalescuela_backup_20260317_235321.sql/download','::1','2026-03-17 23:53:24');
/*!40000 ALTER TABLE `tbl_bitacora_auditoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_far_lote`
--

DROP TABLE IF EXISTS `tbl_far_lote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_far_lote` (
  `id_lote` int(11) NOT NULL AUTO_INCREMENT,
  `id_medicamento` int(11) DEFAULT NULL,
  `numero_lote` varchar(50) NOT NULL,
  `fecha_expiracion` date DEFAULT NULL,
  `cantidad_inicial` int(11) DEFAULT NULL,
  `cantidad_actual` int(11) DEFAULT NULL,
  `estado` enum('ACTIVO','VENCIDO','AGOTADO') DEFAULT 'ACTIVO',
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_lote`),
  UNIQUE KEY `numero_lote` (`numero_lote`),
  KEY `id_medicamento` (`id_medicamento`),
  KEY `usuario_creacion` (`usuario_creacion`),
  KEY `idx_lote_expiracion` (`fecha_expiracion`),
  KEY `idx_lote_estado` (`estado`),
  CONSTRAINT `tbl_far_lote_ibfk_1` FOREIGN KEY (`id_medicamento`) REFERENCES `tbl_far_medicamento` (`id_medicamento`),
  CONSTRAINT `tbl_far_lote_ibfk_2` FOREIGN KEY (`usuario_creacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_far_lote`
--

LOCK TABLES `tbl_far_lote` WRITE;
/*!40000 ALTER TABLE `tbl_far_lote` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_far_lote` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_far_medicamento`
--

DROP TABLE IF EXISTS `tbl_far_medicamento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_far_medicamento` (
  `id_medicamento` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_comercial` varchar(255) NOT NULL,
  `principio_activo` varchar(255) DEFAULT NULL,
  `laboratorio_fabricante` varchar(255) DEFAULT NULL,
  `registro_sanitario` varchar(100) DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_medicamento`),
  KEY `usuario_creacion` (`usuario_creacion`),
  KEY `idx_medicamento_nombre` (`nombre_comercial`),
  CONSTRAINT `tbl_far_medicamento_ibfk_1` FOREIGN KEY (`usuario_creacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_far_medicamento`
--

LOCK TABLES `tbl_far_medicamento` WRITE;
/*!40000 ALTER TABLE `tbl_far_medicamento` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_far_medicamento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_far_medico`
--

DROP TABLE IF EXISTS `tbl_far_medico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_far_medico` (
  `id_medico` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `nombre_completo` varchar(150) DEFAULT NULL,
  `numero_colegiacion` varchar(50) NOT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  `usuario_creacion` varchar(50) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_medico`),
  UNIQUE KEY `numero_colegiacion` (`numero_colegiacion`),
  KEY `id_usuario` (`id_usuario`),
  KEY `usuario_creacion` (`usuario_creacion`),
  CONSTRAINT `tbl_far_medico_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_far_medico`
--

LOCK TABLES `tbl_far_medico` WRITE;
/*!40000 ALTER TABLE `tbl_far_medico` DISABLE KEYS */;
INSERT INTO `tbl_far_medico` VALUES (1,1,'DR. ANTHONY MARTÍNEZ','COL-12345','MEDICINA GENERAL','ACTIVO','ADMIN_SISTEMA','2026-03-16 19:36:20');
/*!40000 ALTER TABLE `tbl_far_medico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_far_paciente`
--

DROP TABLE IF EXISTS `tbl_far_paciente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_far_paciente` (
  `id_paciente` int(11) NOT NULL AUTO_INCREMENT,
  `numero_expediente` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `edad` int(11) DEFAULT NULL,
  `sexo` enum('M','F','Otro') DEFAULT NULL,
  `sala` varchar(100) DEFAULT NULL,
  `numero_cama` varchar(20) DEFAULT NULL,
  `diagnostico` varchar(255) DEFAULT NULL,
  `id_medico` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_paciente`),
  KEY `usuario_creacion` (`usuario_creacion`),
  KEY `fk_paciente_medico` (`id_medico`),
  CONSTRAINT `fk_paciente_medico` FOREIGN KEY (`id_medico`) REFERENCES `tbl_far_medico` (`id_medico`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `tbl_far_paciente_ibfk_1` FOREIGN KEY (`usuario_creacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_far_paciente`
--

LOCK TABLES `tbl_far_paciente` WRITE;
/*!40000 ALTER TABLE `tbl_far_paciente` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_far_paciente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_far_prescripcion`
--

DROP TABLE IF EXISTS `tbl_far_prescripcion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_far_prescripcion` (
  `id_prescripcion` int(11) NOT NULL AUTO_INCREMENT,
  `id_paciente` int(11) DEFAULT NULL,
  `id_medico` int(11) DEFAULT NULL,
  `fecha_prescripcion` datetime DEFAULT current_timestamp(),
  `fecha_despacho` datetime DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado_receta` enum('PENDIENTE','DESPACHADA','CANCELADA','PARCIAL') DEFAULT 'PENDIENTE',
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_despacho` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_prescripcion`),
  KEY `id_medico` (`id_medico`),
  KEY `usuario_creacion` (`usuario_creacion`),
  KEY `usuario_despacho` (`usuario_despacho`),
  KEY `idx_prescripcion_estado` (`estado_receta`),
  KEY `idx_prescripcion_paciente` (`id_paciente`),
  CONSTRAINT `tbl_far_prescripcion_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `tbl_far_paciente` (`id_paciente`),
  CONSTRAINT `tbl_far_prescripcion_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `tbl_far_medico` (`id_medico`),
  CONSTRAINT `tbl_far_prescripcion_ibfk_3` FOREIGN KEY (`usuario_creacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`),
  CONSTRAINT `tbl_far_prescripcion_ibfk_4` FOREIGN KEY (`usuario_despacho`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_far_prescripcion`
--

LOCK TABLES `tbl_far_prescripcion` WRITE;
/*!40000 ALTER TABLE `tbl_far_prescripcion` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_far_prescripcion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_far_prescripcion_detalle`
--

DROP TABLE IF EXISTS `tbl_far_prescripcion_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_far_prescripcion_detalle` (
  `id_detalle_presc` int(11) NOT NULL AUTO_INCREMENT,
  `id_prescripcion` int(11) DEFAULT NULL,
  `id_medicamento` int(11) DEFAULT NULL,
  `id_lote` int(11) DEFAULT NULL,
  `cantidad_prescrita` int(11) DEFAULT NULL,
  `cantidad_despachada` int(11) DEFAULT 0,
  `dosis_instrucciones` varchar(255) DEFAULT NULL,
  `duracion_tratamiento` varchar(50) DEFAULT NULL,
  `estado` enum('PENDIENTE','DESPACHADO','CANCELADO') DEFAULT 'PENDIENTE',
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_detalle_presc`),
  KEY `id_prescripcion` (`id_prescripcion`),
  KEY `id_medicamento` (`id_medicamento`),
  KEY `id_lote` (`id_lote`),
  KEY `usuario_creacion` (`usuario_creacion`),
  CONSTRAINT `tbl_far_prescripcion_detalle_ibfk_1` FOREIGN KEY (`id_prescripcion`) REFERENCES `tbl_far_prescripcion` (`id_prescripcion`),
  CONSTRAINT `tbl_far_prescripcion_detalle_ibfk_2` FOREIGN KEY (`id_medicamento`) REFERENCES `tbl_far_medicamento` (`id_medicamento`),
  CONSTRAINT `tbl_far_prescripcion_detalle_ibfk_3` FOREIGN KEY (`id_lote`) REFERENCES `tbl_far_lote` (`id_lote`),
  CONSTRAINT `tbl_far_prescripcion_detalle_ibfk_4` FOREIGN KEY (`usuario_creacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_far_prescripcion_detalle`
--

LOCK TABLES `tbl_far_prescripcion_detalle` WRITE;
/*!40000 ALTER TABLE `tbl_far_prescripcion_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_far_prescripcion_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_far_reaccion_adversa`
--

DROP TABLE IF EXISTS `tbl_far_reaccion_adversa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_far_reaccion_adversa` (
  `id_reaccion` int(11) NOT NULL AUTO_INCREMENT,
  `id_paciente` int(11) NOT NULL,
  `id_medico` int(11) NOT NULL,
  `descripcion_reaccion` text DEFAULT NULL,
  `fecha_inicio_reaccion` date DEFAULT NULL,
  `fecha_fin_reaccion` date DEFAULT NULL,
  `desenlace` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('REGISTRADA','EN_ANALISIS','CERRADA') DEFAULT 'REGISTRADA',
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_modificacion` int(11) DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_reaccion`),
  KEY `usuario_creacion` (`usuario_creacion`),
  KEY `usuario_modificacion` (`usuario_modificacion`),
  KEY `idx_reaccion_paciente` (`id_paciente`),
  KEY `idx_reaccion_medico` (`id_medico`),
  KEY `idx_reaccion_estado` (`estado`),
  KEY `idx_reaccion_fecha` (`fecha_inicio_reaccion`),
  CONSTRAINT `tbl_far_reaccion_adversa_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `tbl_far_paciente` (`id_paciente`),
  CONSTRAINT `tbl_far_reaccion_adversa_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `tbl_far_medico` (`id_medico`),
  CONSTRAINT `tbl_far_reaccion_adversa_ibfk_3` FOREIGN KEY (`usuario_creacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`),
  CONSTRAINT `tbl_far_reaccion_adversa_ibfk_4` FOREIGN KEY (`usuario_modificacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_far_reaccion_adversa`
--

LOCK TABLES `tbl_far_reaccion_adversa` WRITE;
/*!40000 ALTER TABLE `tbl_far_reaccion_adversa` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_far_reaccion_adversa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_far_reaccion_consecuencia`
--

DROP TABLE IF EXISTS `tbl_far_reaccion_consecuencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_far_reaccion_consecuencia` (
  `id_consecuencia` int(11) NOT NULL AUTO_INCREMENT,
  `id_reaccion` int(11) NOT NULL,
  `descripcion_consecuencia` varchar(150) NOT NULL,
  `gravedad` enum('LEVE','MODERADA','GRAVE') DEFAULT 'LEVE',
  `usuario_creacion` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_consecuencia`),
  KEY `id_reaccion` (`id_reaccion`),
  KEY `usuario_creacion` (`usuario_creacion`),
  CONSTRAINT `tbl_far_reaccion_consecuencia_ibfk_1` FOREIGN KEY (`id_reaccion`) REFERENCES `tbl_far_reaccion_adversa` (`id_reaccion`) ON DELETE CASCADE,
  CONSTRAINT `tbl_far_reaccion_consecuencia_ibfk_2` FOREIGN KEY (`usuario_creacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_far_reaccion_consecuencia`
--

LOCK TABLES `tbl_far_reaccion_consecuencia` WRITE;
/*!40000 ALTER TABLE `tbl_far_reaccion_consecuencia` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_far_reaccion_consecuencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_far_reaccion_detalle`
--

DROP TABLE IF EXISTS `tbl_far_reaccion_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_far_reaccion_detalle` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_reaccion` int(11) NOT NULL,
  `id_medicamento` int(11) DEFAULT NULL,
  `id_lote` int(11) DEFAULT NULL,
  `dosis_posologia` varchar(100) DEFAULT NULL,
  `via_administracion` varchar(100) DEFAULT NULL,
  `fecha_inicio_uso` date DEFAULT NULL,
  `fecha_fin_uso` date DEFAULT NULL,
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_detalle`),
  KEY `id_reaccion` (`id_reaccion`),
  KEY `id_medicamento` (`id_medicamento`),
  KEY `id_lote` (`id_lote`),
  KEY `usuario_creacion` (`usuario_creacion`),
  CONSTRAINT `tbl_far_reaccion_detalle_ibfk_1` FOREIGN KEY (`id_reaccion`) REFERENCES `tbl_far_reaccion_adversa` (`id_reaccion`) ON DELETE CASCADE,
  CONSTRAINT `tbl_far_reaccion_detalle_ibfk_2` FOREIGN KEY (`id_medicamento`) REFERENCES `tbl_far_medicamento` (`id_medicamento`),
  CONSTRAINT `tbl_far_reaccion_detalle_ibfk_3` FOREIGN KEY (`id_lote`) REFERENCES `tbl_far_lote` (`id_lote`),
  CONSTRAINT `tbl_far_reaccion_detalle_ibfk_4` FOREIGN KEY (`usuario_creacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_far_reaccion_detalle`
--

LOCK TABLES `tbl_far_reaccion_detalle` WRITE;
/*!40000 ALTER TABLE `tbl_far_reaccion_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_far_reaccion_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_far_reaccion_foto`
--

DROP TABLE IF EXISTS `tbl_far_reaccion_foto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_far_reaccion_foto` (
  `id_foto` int(11) NOT NULL AUTO_INCREMENT,
  `id_reaccion` int(11) NOT NULL,
  `nombre_archivo` varchar(255) DEFAULT NULL,
  `tipo_archivo` varchar(50) DEFAULT NULL,
  `ruta_archivo` varchar(500) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `usuario_creacion` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_foto`),
  KEY `id_reaccion` (`id_reaccion`),
  KEY `usuario_creacion` (`usuario_creacion`),
  CONSTRAINT `tbl_far_reaccion_foto_ibfk_1` FOREIGN KEY (`id_reaccion`) REFERENCES `tbl_far_reaccion_adversa` (`id_reaccion`) ON DELETE CASCADE,
  CONSTRAINT `tbl_far_reaccion_foto_ibfk_2` FOREIGN KEY (`usuario_creacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_far_reaccion_foto`
--

LOCK TABLES `tbl_far_reaccion_foto` WRITE;
/*!40000 ALTER TABLE `tbl_far_reaccion_foto` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_far_reaccion_foto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_inv_ajuste_inventario`
--

DROP TABLE IF EXISTS `tbl_inv_ajuste_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_inv_ajuste_inventario` (
  `id_ajuste` int(11) NOT NULL AUTO_INCREMENT,
  `id_lote` int(11) DEFAULT NULL,
  `cantidad_anterior` int(11) DEFAULT NULL,
  `cantidad_nueva` int(11) DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_ajuste`),
  KEY `usuario_creacion` (`usuario_creacion`),
  KEY `idx_ajuste_lote` (`id_lote`),
  CONSTRAINT `tbl_inv_ajuste_inventario_ibfk_1` FOREIGN KEY (`id_lote`) REFERENCES `tbl_far_lote` (`id_lote`),
  CONSTRAINT `tbl_inv_ajuste_inventario_ibfk_2` FOREIGN KEY (`usuario_creacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_inv_ajuste_inventario`
--

LOCK TABLES `tbl_inv_ajuste_inventario` WRITE;
/*!40000 ALTER TABLE `tbl_inv_ajuste_inventario` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_inv_ajuste_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_inv_configuracion_stock`
--

DROP TABLE IF EXISTS `tbl_inv_configuracion_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_inv_configuracion_stock` (
  `id_config` int(11) NOT NULL AUTO_INCREMENT,
  `id_medicamento` int(11) DEFAULT NULL,
  `stock_minimo` int(11) DEFAULT 10,
  `stock_maximo` int(11) DEFAULT NULL,
  `alerta_vencimiento_dias` int(11) DEFAULT 30,
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_config`),
  UNIQUE KEY `uk_medicamento_config` (`id_medicamento`),
  KEY `usuario_creacion` (`usuario_creacion`),
  KEY `idx_config_medicamento` (`id_medicamento`),
  CONSTRAINT `tbl_inv_configuracion_stock_ibfk_1` FOREIGN KEY (`id_medicamento`) REFERENCES `tbl_far_medicamento` (`id_medicamento`),
  CONSTRAINT `tbl_inv_configuracion_stock_ibfk_2` FOREIGN KEY (`usuario_creacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_inv_configuracion_stock`
--

LOCK TABLES `tbl_inv_configuracion_stock` WRITE;
/*!40000 ALTER TABLE `tbl_inv_configuracion_stock` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_inv_configuracion_stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_inv_movimiento`
--

DROP TABLE IF EXISTS `tbl_inv_movimiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_inv_movimiento` (
  `id_movimiento` int(11) NOT NULL AUTO_INCREMENT,
  `id_lote` int(11) DEFAULT NULL,
  `tipo_movimiento` enum('ENTRADA','SALIDA','AJUSTE','DESPACHO_RECETA','TRASLADO','DEVOLUCION') DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_movimiento` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_movimiento`),
  KEY `usuario_creacion` (`usuario_creacion`),
  KEY `idx_movimiento_tipo_fecha` (`tipo_movimiento`,`fecha_movimiento`),
  KEY `idx_movimiento_lote` (`id_lote`),
  CONSTRAINT `tbl_inv_movimiento_ibfk_1` FOREIGN KEY (`id_lote`) REFERENCES `tbl_far_lote` (`id_lote`),
  CONSTRAINT `tbl_inv_movimiento_ibfk_2` FOREIGN KEY (`usuario_creacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_inv_movimiento`
--

LOCK TABLES `tbl_inv_movimiento` WRITE;
/*!40000 ALTER TABLE `tbl_inv_movimiento` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_inv_movimiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_seg_formulario`
--

DROP TABLE IF EXISTS `tbl_seg_formulario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_seg_formulario` (
  `id_formulario` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `codigo_formulario` varchar(10) NOT NULL,
  `descripcion` varchar(25) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `usuario_creacion` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_formulario`),
  UNIQUE KEY `codigo_formulario` (`codigo_formulario`),
  KEY `idx_formulario_usuario` (`id_usuario`),
  KEY `idx_formulario_usuario_creacion` (`usuario_creacion`),
  CONSTRAINT `tbl_seg_formulario_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_seg_usuario` (`id_usuario`),
  CONSTRAINT `tbl_seg_formulario_ibfk_2` FOREIGN KEY (`usuario_creacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_seg_formulario`
--

LOCK TABLES `tbl_seg_formulario` WRITE;
/*!40000 ALTER TABLE `tbl_seg_formulario` DISABLE KEYS */;
INSERT INTO `tbl_seg_formulario` VALUES (1,1,'INV-01','INVENTARIO',1,1,'2026-03-16 19:36:20'),(2,1,'PAC-01','REGISTRO PACIENTE',1,1,'2026-03-16 19:36:20'),(3,1,'REA-01','REACCIONES ADVERSAS',1,1,'2026-03-16 19:36:20'),(4,1,'USU-01','MANTENIMIENTO DE USUARIO',1,1,'2026-03-16 19:36:20'),(5,1,'BIT-01','BITACORA',1,1,'2026-03-16 19:36:20'),(6,24,'SEGURIDAD','SEGURIDAD',1,24,'2026-03-17 22:36:18'),(7,15,'USUARIOS','USUARIOS',1,15,'2026-03-17 22:41:08'),(8,15,'ROLES','ROLES',1,15,'2026-03-17 22:41:14'),(9,24,'PACIENTES','PACIENTES',1,24,'2026-03-17 22:49:41'),(10,15,'RESPALDOS','RESPALDOS',1,15,'2026-03-17 23:51:52');
/*!40000 ALTER TABLE `tbl_seg_formulario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_seg_parametro`
--

DROP TABLE IF EXISTS `tbl_seg_parametro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_seg_parametro` (
  `id_parametro` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_parametro` varchar(50) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `valor` int(11) DEFAULT NULL,
  `intentos_fallidos` int(11) DEFAULT 0,
  `ultimo_sesion` datetime DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `usuario_modificacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_parametro`),
  UNIQUE KEY `nombre_parametro` (`nombre_parametro`),
  KEY `idx_param_usuario_creacion` (`usuario_creacion`),
  KEY `idx_param_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `tbl_seg_parametro_ibfk_1` FOREIGN KEY (`usuario_creacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`),
  CONSTRAINT `tbl_seg_parametro_ibfk_2` FOREIGN KEY (`usuario_modificacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_seg_parametro`
--

LOCK TABLES `tbl_seg_parametro` WRITE;
/*!40000 ALTER TABLE `tbl_seg_parametro` DISABLE KEYS */;
INSERT INTO `tbl_seg_parametro` VALUES (1,'ADMIN_INTENTOS_INVALIDOS','Máximo de intentos fallidos de login permitidos',3,3,NULL,NULL,'ACTIVO','2026-02-23 14:45:13',NULL,'2026-03-15 20:34:06',NULL),(2,'MIN_CONTRASENA','Longitud mínima de contraseña',5,0,NULL,NULL,'ACTIVO','2026-03-15 20:34:31',NULL,'2026-03-15 20:34:31',NULL),(3,'MAX_CONTRASENA','Longitud máxima de contraseña',10,0,NULL,NULL,'ACTIVO','2026-03-15 20:34:40',NULL,'2026-03-15 20:34:40',NULL);
/*!40000 ALTER TABLE `tbl_seg_parametro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_seg_permisos`
--

DROP TABLE IF EXISTS `tbl_seg_permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_seg_permisos` (
  `id_permiso` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_formulario` int(11) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_permiso`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_seg_permisos`
--

LOCK TABLES `tbl_seg_permisos` WRITE;
/*!40000 ALTER TABLE `tbl_seg_permisos` DISABLE KEYS */;
INSERT INTO `tbl_seg_permisos` VALUES (33,1,2,'VISUALIZAR','2026-03-18 00:50:42'),(34,1,2,'GUARDAR','2026-03-18 00:50:42'),(35,1,2,'ACTUALIZAR','2026-03-18 00:50:42'),(36,1,2,'ELIMINAR','2026-03-18 00:50:42'),(37,1,3,'VISUALIZAR','2026-03-18 00:50:42'),(38,1,3,'GUARDAR','2026-03-18 00:50:42'),(39,1,3,'ACTUALIZAR','2026-03-18 00:50:42'),(40,1,3,'ELIMINAR','2026-03-18 00:50:42'),(41,1,4,'VISUALIZAR','2026-03-18 00:50:42'),(42,1,4,'GUARDAR','2026-03-18 00:50:42'),(43,1,4,'ACTUALIZAR','2026-03-18 00:50:42'),(44,1,4,'ELIMINAR','2026-03-18 00:50:42'),(45,1,5,'VISUALIZAR','2026-03-18 00:50:42'),(46,1,5,'GUARDAR','2026-03-18 00:50:42'),(47,1,5,'ACTUALIZAR','2026-03-18 00:50:42'),(48,1,5,'ELIMINAR','2026-03-18 00:50:42'),(53,24,1,'VISUALIZAR','2026-03-18 03:37:01'),(54,15,1,'VISUALIZAR','2026-03-18 05:48:05'),(55,15,1,'GUARDAR','2026-03-18 05:48:05'),(56,15,1,'ACTUALIZAR','2026-03-18 05:48:05'),(57,15,1,'ELIMINAR','2026-03-18 05:48:05'),(58,15,2,'VISUALIZAR','2026-03-18 05:48:05'),(59,15,2,'GUARDAR','2026-03-18 05:48:05'),(60,15,2,'ACTUALIZAR','2026-03-18 05:48:05'),(61,15,2,'ELIMINAR','2026-03-18 05:48:05');
/*!40000 ALTER TABLE `tbl_seg_permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_seg_pregunta_usuario`
--

DROP TABLE IF EXISTS `tbl_seg_pregunta_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_seg_pregunta_usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `respuesta` varchar(255) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` varchar(50) DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_seg_pregunta_usuario`
--

LOCK TABLES `tbl_seg_pregunta_usuario` WRITE;
/*!40000 ALTER TABLE `tbl_seg_pregunta_usuario` DISABLE KEYS */;
INSERT INTO `tbl_seg_pregunta_usuario` VALUES (5,'SARA',2,'$2y$12$3MD1ZaYNOGPvqqoabZ52sOPQ0Zo1.WrcgdFKO2ZqP0tmbt22arD6u','2026-03-17 14:14:32',NULL,'ACTIVO'),(6,'SARA',9,'$2y$12$7M7EndS/7Umdjlcghyeeeerx/9Uqx9NKxrVRrI9vgjw2rCk8YZi0q','2026-03-17 14:14:32',NULL,'ACTIVO'),(7,'ADMIN',2,'$2y$12$SjwzfAkbj5oteMhy4gkb6emBKKngEcIXz18ZP1jg6uWVBpSNRWFAG','2026-03-17 15:05:17',NULL,'ACTIVO'),(8,'ADMIN',9,'$2y$12$wGAO.ne623Jn27IhWCKlLuCqdsApa8hhmm9kykaA8F.kQs.Q4v5.W','2026-03-17 15:05:17',NULL,'ACTIVO'),(9,'NERU',2,'$2y$12$.kcDu8TvljpZoLkWbYz6tulsSH.iQnfRN5bAaU7m9xd2H29pb6vBK','2026-03-17 16:16:45',NULL,'ACTIVO'),(10,'NERU',3,'$2y$12$T6MfCs2847TtbIM5yB5eouPXzh8PrA5qZEWyvdQKQK8OuQZ5YRVd.','2026-03-17 16:16:45',NULL,'ACTIVO');
/*!40000 ALTER TABLE `tbl_seg_pregunta_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_seg_preguntas`
--

DROP TABLE IF EXISTS `tbl_seg_preguntas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_seg_preguntas` (
  `id_pregunta` int(11) NOT NULL AUTO_INCREMENT,
  `pregunta` varchar(255) NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_pregunta`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_seg_preguntas`
--

LOCK TABLES `tbl_seg_preguntas` WRITE;
/*!40000 ALTER TABLE `tbl_seg_preguntas` DISABLE KEYS */;
INSERT INTO `tbl_seg_preguntas` VALUES (1,'¿Cuál es el nombre de tu primera mascota?','ACTIVO'),(2,'¿En qué ciudad naciste?','ACTIVO'),(3,'¿Cuál es tu comida favorita?','ACTIVO'),(4,'¿Cuál fue el nombre de tu primera escuela?','ACTIVO'),(5,'¿Cuál es el segundo nombre de tu madre?','ACTIVO'),(6,'¿Cuál es el nombre de tu mejor amigo de la infancia?','ACTIVO'),(7,'¿Cuál fue tu primer trabajo?','ACTIVO'),(8,'¿Cuál es tu película favorita?','ACTIVO'),(9,'¿Cuál es tu deporte favorito?','ACTIVO'),(10,'¿Cuál es el nombre de tu abuelo materno?','ACTIVO');
/*!40000 ALTER TABLE `tbl_seg_preguntas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_seg_rol`
--

DROP TABLE IF EXISTS `tbl_seg_rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_seg_rol` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_seg_rol`
--

LOCK TABLES `tbl_seg_rol` WRITE;
/*!40000 ALTER TABLE `tbl_seg_rol` DISABLE KEYS */;
INSERT INTO `tbl_seg_rol` VALUES (1,'ADMINISTRADOR','Acceso total','ACTIVO'),(2,'MEDICO','Atiende pacientes','ACTIVO'),(3,'FARMACEUTICO','Gestiona medicamentos','ACTIVO'),(4,'ENFERMERO','Apoyo clínico','ACTIVO');
/*!40000 ALTER TABLE `tbl_seg_rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_seg_tipo_accion`
--

DROP TABLE IF EXISTS `tbl_seg_tipo_accion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_seg_tipo_accion` (
  `id_tipo_accion` int(11) NOT NULL AUTO_INCREMENT,
  `id_formulario` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `accion` varchar(25) NOT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `estado` enum('NUEVO','ACTIVO','INACTIVO') DEFAULT 'NUEVO',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `usuario_modificacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_tipo_accion`),
  KEY `usuario_modificacion` (`usuario_modificacion`),
  KEY `idx_tipo_accion_formulario` (`id_formulario`),
  KEY `idx_tipo_accion_usuario` (`id_usuario`),
  KEY `idx_tipo_accion_usuario_creacion` (`usuario_creacion`),
  CONSTRAINT `tbl_seg_tipo_accion_ibfk_1` FOREIGN KEY (`id_formulario`) REFERENCES `tbl_seg_formulario` (`id_formulario`),
  CONSTRAINT `tbl_seg_tipo_accion_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `tbl_seg_usuario` (`id_usuario`),
  CONSTRAINT `tbl_seg_tipo_accion_ibfk_3` FOREIGN KEY (`usuario_creacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`),
  CONSTRAINT `tbl_seg_tipo_accion_ibfk_4` FOREIGN KEY (`usuario_modificacion`) REFERENCES `tbl_seg_usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_seg_tipo_accion`
--

LOCK TABLES `tbl_seg_tipo_accion` WRITE;
/*!40000 ALTER TABLE `tbl_seg_tipo_accion` DISABLE KEYS */;
INSERT INTO `tbl_seg_tipo_accion` VALUES (1,5,24,'CONSULTAR','ACCION CONSULTAR','ACTIVO','2026-03-17 22:36:18',24,NULL,NULL),(2,6,24,'CONSULTAR','ACCION CONSULTAR','ACTIVO','2026-03-17 22:36:18',24,NULL,NULL),(3,6,24,'CERRAR SESION','ACCION CERRAR SESION','ACTIVO','2026-03-17 22:37:17',24,NULL,NULL),(4,6,15,'INICIAR SESION','ACCION INICIAR SESION','ACTIVO','2026-03-17 22:41:08',15,NULL,NULL),(5,7,15,'CONSULTAR','ACCION CONSULTAR','ACTIVO','2026-03-17 22:41:08',15,NULL,NULL),(6,8,15,'CONSULTAR','ACCION CONSULTAR','ACTIVO','2026-03-17 22:41:14',15,NULL,NULL),(7,7,15,'ACTUALIZAR','ACCION ACTUALIZAR','ACTIVO','2026-03-17 22:41:34',15,NULL,NULL),(8,9,24,'CONSULTAR','ACCION CONSULTAR','ACTIVO','2026-03-17 22:49:41',24,NULL,NULL),(9,3,24,'CONSULTAR','ACCION CONSULTAR','ACTIVO','2026-03-17 22:49:42',24,NULL,NULL),(10,6,15,'CREAR','ACCION CREAR','ACTIVO','2026-03-17 23:17:53',15,NULL,NULL),(11,7,15,'CREAR','ACCION CREAR','ACTIVO','2026-03-17 23:48:05',15,NULL,NULL),(12,10,15,'CONSULTAR','ACCION CONSULTAR','ACTIVO','2026-03-17 23:51:52',15,NULL,NULL),(13,10,15,'CREAR','ACCION CREAR','ACTIVO','2026-03-17 23:53:21',15,NULL,NULL);
/*!40000 ALTER TABLE `tbl_seg_tipo_accion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_seg_usuario`
--

DROP TABLE IF EXISTS `tbl_seg_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbl_seg_usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `correo` varchar(20) NOT NULL,
  `contrasena_hash` varchar(255) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `apellido` varchar(50) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `estado` enum('NUEVO','ACTIVO','INACTIVO','BLOQUEADO') DEFAULT 'NUEVO',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `usuario_modificacion` int(11) DEFAULT NULL,
  `id_rol` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `usuario` (`usuario`),
  UNIQUE KEY `correo` (`correo`),
  KEY `idx_usuario_estado` (`estado`),
  KEY `fk_usuario_rol` (`id_rol`),
  KEY `usuario_2` (`usuario`),
  KEY `correo_2` (`correo`),
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `tbl_seg_rol` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_seg_usuario`
--

LOCK TABLES `tbl_seg_usuario` WRITE;
/*!40000 ALTER TABLE `tbl_seg_usuario` DISABLE KEYS */;
INSERT INTO `tbl_seg_usuario` VALUES (1,'ADMIN','admin@example.com','$2b$10$1CjquoEK3vdWLf4lAeZ4kenDUDdlef7iuwjGMXAlEJ8iGiR.M4QSC','Administrador','General','9999-9999','ACTIVO','2026-02-23 14:45:05',NULL,'2026-03-17 18:00:29',NULL,1),(15,'MENTA','rammze00@gmail.com','$2b$10$ejjKtiMc87SFYsXR6FDtaueBlz3TLZBkDqjzDVVnCLVRF3wQ9WSEC','Alejandro','Ávila',NULL,'ACTIVO','2026-02-28 16:30:15',NULL,'2026-03-17 23:21:32',NULL,2),(24,'BAITY','mrneruu@gmail.com','$2b$10$zDDIj/2PntYuZ0RVcTEv/ek9wFCODzo/MmCSXyGpg/lPwmNOi8uZi','Sara','AVILA',NULL,'ACTIVO','2026-03-17 13:18:33',NULL,'2026-03-17 23:21:07',NULL,2);
/*!40000 ALTER TABLE `tbl_seg_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'hospitalescuela'
--

--
-- Dumping routines for database 'hospitalescuela'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_ActualizarPaciente` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_ActualizarPaciente`(
    IN p_id_paciente INT,
    IN p_numero_expediente VARCHAR(20),
    IN p_nombre VARCHAR(100),
    IN p_edad INT,
    IN p_sexo VARCHAR(20),
    IN p_sala VARCHAR(50),
    IN p_numero_cama VARCHAR(10),
    IN p_diagnostico TEXT,
    IN p_id_medico INT
)
BEGIN

    UPDATE tbl_far_paciente 
    SET numero_expediente = p_numero_expediente,
        nombre = p_nombre,
        edad = p_edad,
        sexo = p_sexo,
        sala = p_sala,
        numero_cama = p_numero_cama,
        diagnostico = p_diagnostico,
        id_medico = p_id_medico
    WHERE id_paciente = p_id_paciente;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_ActualizarReaccionAdversa` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_ActualizarReaccionAdversa`( 

    IN p_id_reaccion INT, 

    IN p_id_paciente INT, 

    IN p_id_medico INT, 

    IN p_descripcion_reaccion TEXT, 

    IN p_fecha_inicio_reaccion DATE, 

    IN p_fecha_fin_reaccion DATE, 

    IN p_desenlace VARCHAR(100), 

    IN p_observaciones TEXT, 

    IN p_estado VARCHAR(20), 

    IN p_usuario_modificacion INT 

)
BEGIN 

    UPDATE tbl_far_reaccion_adversa 

    SET 

        id_paciente = p_id_paciente, 

        id_medico = p_id_medico, 

        descripcion_reaccion = UPPER(p_descripcion_reaccion), 

        fecha_inicio_reaccion = p_fecha_inicio_reaccion, 

        fecha_fin_reaccion = p_fecha_fin_reaccion, 

        desenlace = UPPER(p_desenlace), 

        observaciones = UPPER(p_observaciones), 

        estado = UPPER(p_estado), 

        usuario_modificacion = p_usuario_modificacion, 

        fecha_modificacion = NOW() 

    WHERE id_reaccion = p_id_reaccion; 

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_EliminarConsecuenciaReaccion` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_EliminarConsecuenciaReaccion`( 

    IN p_id_consecuencia INT 

)
BEGIN 

    DELETE FROM tbl_far_reaccion_consecuencia 

    WHERE id_consecuencia = p_id_consecuencia; 

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_EliminarDetalleReaccion` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_EliminarDetalleReaccion`( 

    IN p_id_detalle INT 

)
BEGIN 

    DELETE FROM tbl_far_reaccion_detalle 

    WHERE id_detalle = p_id_detalle; 

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_EliminarPaciente` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_EliminarPaciente`(IN _id INT)
BEGIN
    DELETE FROM tbl_far_paciente 
    WHERE id_paciente = _id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_EliminarReaccionAdversa` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_EliminarReaccionAdversa`( 

    IN p_id_reaccion INT 

)
BEGIN 

    DELETE FROM tbl_far_reaccion_consecuencia 

    WHERE id_reaccion = p_id_reaccion; 

 

    DELETE FROM tbl_far_reaccion_detalle 

    WHERE id_reaccion = p_id_reaccion; 

 

    DELETE FROM tbl_far_reaccion_adversa 

    WHERE id_reaccion = p_id_reaccion; 

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_InsertarConsecuenciaReaccion` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_InsertarConsecuenciaReaccion`( 

    IN p_id_reaccion INT, 

    IN p_descripcion_consecuencia TEXT, 

    IN p_gravedad VARCHAR(20), 

    IN p_usuario_creacion INT 

)
BEGIN 

    INSERT INTO tbl_far_reaccion_consecuencia ( 

        id_reaccion, 

        descripcion_consecuencia, 

        gravedad, 

        usuario_creacion, 

        fecha_creacion 

    ) 

    VALUES ( 

        p_id_reaccion, 

        UPPER(p_descripcion_consecuencia), 

        UPPER(p_gravedad), 

        p_usuario_creacion, 

        NOW() 

    ); 

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_InsertarDetalleReaccion` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_InsertarDetalleReaccion`( 

    IN p_id_reaccion INT, 

    IN p_id_medicamento INT, 

    IN p_id_lote INT, 

    IN p_dosis_posologia VARCHAR(100), 

    IN p_via_administracion VARCHAR(100), 

    IN p_fecha_inicio_uso DATE, 

    IN p_fecha_fin_uso DATE, 

    IN p_usuario_creacion INT 

)
BEGIN 

    INSERT INTO tbl_far_reaccion_detalle ( 

        id_reaccion, 

        id_medicamento, 

        id_lote, 

        dosis_posologia, 

        via_administracion, 

        fecha_inicio_uso, 

        fecha_fin_uso, 

        usuario_creacion, 

        fecha_creacion 

    ) 

    VALUES ( 

        p_id_reaccion, 

        p_id_medicamento, 

        p_id_lote, 

        UPPER(p_dosis_posologia), 

        UPPER(p_via_administracion), 

        p_fecha_inicio_uso, 

        p_fecha_fin_uso, 

        p_usuario_creacion, 

        NOW() 

    ); 

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_InsertarPaciente` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_InsertarPaciente`(
    IN p_nombre VARCHAR(100),
    IN p_edad INT,
    IN p_sexo VARCHAR(20),
    IN p_sala VARCHAR(50),
    IN p_numero_cama VARCHAR(10),
    IN p_diagnostico TEXT,
    IN p_id_medico INT,
    IN p_usuario_creacion INT
)
BEGIN

    DECLARE v_numero_expediente VARCHAR(20);
    DECLARE v_conteo INT;

    SELECT COUNT(*) + 1 INTO v_conteo 
    FROM tbl_far_paciente;

    SET v_numero_expediente = CONCAT('PAC-', YEAR(CURDATE()), '-', LPAD(v_conteo, 4, '0'));

    INSERT INTO tbl_far_paciente (
        numero_expediente, 
        nombre, 
        edad, 
        sexo, 
        sala, 
        numero_cama, 
        diagnostico, 
        id_medico, 
        usuario_creacion, 
        fecha_creacion
    ) VALUES (
        v_numero_expediente, 
        p_nombre, 
        p_edad, 
        p_sexo, 
        p_sala, 
        p_numero_cama, 
        p_diagnostico, 
        p_id_medico, 
        p_usuario_creacion, 
        NOW()
    );

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_InsertarReaccionAdversa` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_InsertarReaccionAdversa`( 

    IN p_id_paciente INT, 

    IN p_id_medico INT, 

    IN p_descripcion_reaccion TEXT, 

    IN p_fecha_inicio_reaccion DATE, 

    IN p_fecha_fin_reaccion DATE, 

    IN p_desenlace VARCHAR(100), 

    IN p_observaciones TEXT, 

    IN p_estado VARCHAR(20), 

    IN p_usuario_creacion INT 

)
BEGIN 

    INSERT INTO tbl_far_reaccion_adversa ( 

        id_paciente, 

        id_medico, 

        descripcion_reaccion, 

        fecha_inicio_reaccion, 

        fecha_fin_reaccion, 

        desenlace, 

        observaciones, 

        estado, 

        usuario_creacion, 

        fecha_creacion 

    ) 

    VALUES ( 

        p_id_paciente, 

        p_id_medico, 

        UPPER(p_descripcion_reaccion), 

        p_fecha_inicio_reaccion, 

        p_fecha_fin_reaccion, 

        UPPER(p_desenlace), 

        UPPER(p_observaciones), 

        UPPER(p_estado), 

        p_usuario_creacion, 

        NOW() 

    ); 

 

    SELECT LAST_INSERT_ID() AS id_reaccion; 

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_ObtenerConsecuenciasReaccion` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_ObtenerConsecuenciasReaccion`( 

    IN p_id_reaccion INT 

)
BEGIN 

    SELECT 

        rc.id_consecuencia, 

        rc.id_reaccion, 

        rc.descripcion_consecuencia, 

        rc.gravedad, 

        rc.usuario_creacion, 

        rc.fecha_creacion 

    FROM tbl_far_reaccion_consecuencia rc 

    WHERE rc.id_reaccion = p_id_reaccion 

    ORDER BY rc.id_consecuencia ASC; 

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_ObtenerDetallesReaccion` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_ObtenerDetallesReaccion`( 

    IN p_id_reaccion INT 

)
BEGIN 

    SELECT 

        rd.id_detalle, 

        rd.id_reaccion, 

        rd.id_medicamento, 

        m.nombre_comercial, 

        rd.id_lote, 

        l.numero_lote, 

        rd.dosis_posologia, 

        rd.via_administracion, 

        rd.fecha_inicio_uso, 

        rd.fecha_fin_uso 

    FROM tbl_far_reaccion_detalle rd 

    LEFT JOIN tbl_far_medicamento m ON m.id_medicamento = rd.id_medicamento 

    LEFT JOIN tbl_far_lote l ON l.id_lote = rd.id_lote 

    WHERE rd.id_reaccion = p_id_reaccion 

    ORDER BY rd.id_detalle ASC; 

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_ObtenerPacientePorId` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_ObtenerPacientePorId`(IN p_id_paciente INT)
BEGIN
    SELECT * 
    FROM tbl_far_paciente
    WHERE id_paciente = p_id_paciente;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_ObtenerPacientesGeneral` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_ObtenerPacientesGeneral`(IN p_busqueda VARCHAR(255))
BEGIN

    IF p_busqueda = '' OR p_busqueda IS NULL THEN
    
        SELECT * 
        FROM tbl_far_paciente 
        ORDER BY id_paciente DESC;

    ELSE

        SELECT * 
        FROM tbl_far_paciente
        WHERE UPPER(nombre) LIKE CONCAT('%', UPPER(p_busqueda), '%')
           OR UPPER(numero_expediente) LIKE CONCAT('%', UPPER(p_busqueda), '%')
        ORDER BY id_paciente DESC;

    END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_ObtenerReaccionAdversaPorId` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_ObtenerReaccionAdversaPorId`( 

    IN p_id_reaccion INT 

)
BEGIN 

    SELECT 

        ra.id_reaccion, 

        ra.id_paciente, 

        p.numero_expediente, 

        p.nombre AS nombre_completo, 
        p.edad, 

        p.sexo, 

        p.sala, 

        p.numero_cama, 

        p.diagnostico, 

        ra.id_medico, 

        m.nombre_completo AS nombre_medico, 

        m.numero_colegiacion, 

        ra.descripcion_reaccion, 

        ra.fecha_inicio_reaccion, 

        ra.fecha_fin_reaccion, 

        ra.desenlace, 

        ra.observaciones, 

        ra.estado, 

        ra.usuario_creacion, 

        ra.fecha_creacion, 

        ra.usuario_modificacion, 

        ra.fecha_modificacion 

    FROM tbl_far_reaccion_adversa ra 

    INNER JOIN tbl_far_paciente p ON p.id_paciente = ra.id_paciente 

    INNER JOIN tbl_far_medico m ON m.id_medico = ra.id_medico 

    WHERE ra.id_reaccion = p_id_reaccion; 

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_ObtenerReaccionCompleta` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_ObtenerReaccionCompleta`( 
IN p_id_reaccion INT 
)
BEGIN 
SELECT 
ra.id_reaccion, 
ra.id_paciente, 

p.numero_expediente, 
p.nombre AS nombre_completo, 
ra.id_medico, 
m.nombre_completo AS nombre_medico, 
m.numero_colegiacion, 
ra.descripcion_reaccion, 
ra.fecha_inicio_reaccion, 
ra.fecha_fin_reaccion, 
ra.desenlace, 
ra.observaciones, 
ra.estado, 
ra.usuario_creacion, 
ra.fecha_creacion, 
ra.usuario_modificacion, 
ra.fecha_modificacion 
FROM tbl_far_reaccion_adversa ra 
INNER JOIN tbl_far_paciente p ON p.id_paciente = ra.id_paciente 
INNER JOIN tbl_far_medico m ON m.id_medico = ra.id_medico 
WHERE ra.id_reaccion = p_id_reaccion; 
 
SELECT 
rd.id_detalle, 
rd.id_reaccion, 
rd.id_medicamento, 
m.nombre_comercial, 
rd.id_lote, 
l.numero_lote, 
rd.dosis_posologia, 
rd.via_administracion, 
rd.fecha_inicio_uso, 
rd.fecha_fin_uso 
FROM tbl_far_reaccion_detalle rd 
LEFT JOIN tbl_far_medicamento m ON m.id_medicamento = rd.id_medicamento 
LEFT JOIN tbl_far_lote l ON l.id_lote = rd.id_lote 
WHERE rd.id_reaccion = p_id_reaccion 
ORDER BY rd.id_detalle ASC; 
 
SELECT 
rc.id_consecuencia, 
rc.id_reaccion, 
rc.descripcion_consecuencia, 
rc.gravedad 
FROM tbl_far_reaccion_consecuencia rc 
WHERE rc.id_reaccion = p_id_reaccion 
ORDER BY rc.id_consecuencia ASC; 
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_ObtenerReaccionesAdversas` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_ObtenerReaccionesAdversas`()
BEGIN 
SELECT 
ra.id_reaccion, 
ra.id_paciente, 

p.numero_expediente, 
p.nombre AS nombre_completo, 
ra.id_medico, 
m.nombre_completo AS nombre_medico, 
m.numero_colegiacion, 
ra.descripcion_reaccion, 
ra.fecha_inicio_reaccion, 
ra.fecha_fin_reaccion, 
ra.desenlace, 
ra.observaciones, 
ra.estado, 
ra.fecha_creacion 
FROM tbl_far_reaccion_adversa ra 
INNER JOIN tbl_far_paciente p ON p.id_paciente = ra.id_paciente 
INNER JOIN tbl_far_medico m ON m.id_medico = ra.id_medico 
ORDER BY ra.id_reaccion DESC; 
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-17 23:54:08
