-- MariaDB dump 10.19  Distrib 10.4.24-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sigpaf_DB
-- ------------------------------------------------------
-- Server version	10.4.24-MariaDB

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
-- Table structure for table `actividad`
--

DROP TABLE IF EXISTS `actividad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actividad` (
  `idActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombreActividad` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fechainicioActividad` date NOT NULL,
  `fechafinActividad` date NOT NULL,
  `cantSesionesPlanificada` int(4) NOT NULL,
  `objetivoActividad` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descActividad` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantPersoAtender` int(5) NOT NULL,
  `observacion` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idTipoActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idVertice` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idAreaE` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idEmpleado` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idUnidadMedida` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idGrupoEtnio` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idDocente` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idEstatus` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idHorario` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idEstDesarrollo` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idActividad`),
  KEY `idTipoActividad` (`idTipoActividad`),
  KEY `idVertice` (`idVertice`),
  KEY `idAreaE` (`idAreaE`),
  KEY `idEmpleado` (`idEmpleado`),
  KEY `idUnidadMedida` (`idUnidadMedida`),
  KEY `idGrupoEtnio` (`idGrupoEtnio`),
  KEY `idDocente` (`idDocente`),
  KEY `idEstatus` (`idEstatus`),
  KEY `idHorario` (`idHorario`),
  KEY `idEstDesarrollo` (`idEstDesarrollo`),
  CONSTRAINT `actividad_ibfk_1` FOREIGN KEY (`idTipoActividad`) REFERENCES `tipoactividad` (`idTipoActividad`),
  CONSTRAINT `actividad_ibfk_10` FOREIGN KEY (`idEstDesarrollo`) REFERENCES `estrategiadesarrollo` (`idEstDesarrollo`),
  CONSTRAINT `actividad_ibfk_2` FOREIGN KEY (`idVertice`) REFERENCES `vertice` (`idVertice`),
  CONSTRAINT `actividad_ibfk_3` FOREIGN KEY (`idAreaE`) REFERENCES `areaespecifica` (`idAreaE`),
  CONSTRAINT `actividad_ibfk_4` FOREIGN KEY (`idEmpleado`) REFERENCES `empleado` (`idEmpleado`),
  CONSTRAINT `actividad_ibfk_5` FOREIGN KEY (`idUnidadMedida`) REFERENCES `unidadmedida` (`idUnidadMedida`),
  CONSTRAINT `actividad_ibfk_6` FOREIGN KEY (`idGrupoEtnio`) REFERENCES `grupoetnio` (`idGrupoEtnio`),
  CONSTRAINT `actividad_ibfk_7` FOREIGN KEY (`idDocente`) REFERENCES `docente` (`idDocente`),
  CONSTRAINT `actividad_ibfk_8` FOREIGN KEY (`idEstatus`) REFERENCES `estatus` (`idEstatus`),
  CONSTRAINT `actividad_ibfk_9` FOREIGN KEY (`idHorario`) REFERENCES `horario` (`idHorario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actividad`
--

LOCK TABLES `actividad` WRITE;
/*!40000 ALTER TABLE `actividad` DISABLE KEYS */;
INSERT INTO `actividad` VALUES ('AC0001','dasdas','2026-10-25','2026-10-28',1,'saasasassasasas','sasasasasasasasas',22,'asasas','TA0010','VER0005','AE0002','EM0005','UM0001','GN0004','DC0003','ES0001','HO0002','ED0004');
/*!40000 ALTER TABLE `actividad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `areaespecifica`
--

DROP TABLE IF EXISTS `areaespecifica`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `areaespecifica` (
  `idAreaE` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomAreaE` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idAreaE`),
  UNIQUE KEY `nomAreaE` (`nomAreaE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `areaespecifica`
--

LOCK TABLES `areaespecifica` WRITE;
/*!40000 ALTER TABLE `areaespecifica` DISABLE KEYS */;
INSERT INTO `areaespecifica` VALUES ('AE0001','AE1'),('AE0002','AE2'),('AE0003','AE3'),('AE0004','AE4'),('AE0005','AE5');
/*!40000 ALTER TABLE `areaespecifica` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asistencia`
--

DROP TABLE IF EXISTS `asistencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asistencia` (
  `idAsistencia` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idSegActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idBeneficiario` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idAsistencia`),
  KEY `idSegActividad` (`idSegActividad`),
  KEY `idBeneficiario` (`idBeneficiario`),
  CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`idSegActividad`) REFERENCES `seguimientoactividad` (`idSegActividad`),
  CONSTRAINT `asistencia_ibfk_2` FOREIGN KEY (`idBeneficiario`) REFERENCES `beneficiario` (`idBeneficiario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencia`
--

LOCK TABLES `asistencia` WRITE;
/*!40000 ALTER TABLE `asistencia` DISABLE KEYS */;
/*!40000 ALTER TABLE `asistencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `beneficiario`
--

DROP TABLE IF EXISTS `beneficiario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `beneficiario` (
  `idBeneficiario` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombresBeneficiario` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidosBeneficiario` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cedulaBeneficiario` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nacionalidad` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fechaNacBeneficiario` date NOT NULL,
  `sexoBeneficiario` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idBeneficiario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `beneficiario`
--

LOCK TABLES `beneficiario` WRITE;
/*!40000 ALTER TABLE `beneficiario` DISABLE KEYS */;
/*!40000 ALTER TABLE `beneficiario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bitacora`
--

DROP TABLE IF EXISTS `bitacora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bitacora` (
  `idBitacora` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_id` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario_nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `accion` enum('LOGIN','LOGOUT','CREATE','UPDATE','DELETE','ACCESS_DENIED','GENERAR','EXPORTAR_BD','IMPORTAR_BD') COLLATE utf8mb4_unicode_ci NOT NULL,
  `modulo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registro_id` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idBitacora`),
  KEY `idx_bit_usuario` (`usuario_id`),
  KEY `idx_bit_fecha` (`fecha_hora`),
  KEY `idx_bit_accion` (`accion`),
  CONSTRAINT `bitacora_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
INSERT INTO `bitacora` VALUES ('BIT0001','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-23 05:53:46'),('BIT0002','Us0001','Super','UPDATE','Usuario','US0002','Actualizo el registro US0002 (andres)','::1','2026-10-23 05:55:23'),('BIT0003','Us0001','Super','LOGOUT','Sesion',NULL,'Cerro sesion en el sistema','::1','2026-10-23 05:55:28'),('BIT0004','US0002','andres','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-23 05:55:41'),('BIT0005','US0002','andres','LOGOUT','Sesion',NULL,'Cerro sesion en el sistema','::1','2026-10-23 06:09:25'),('BIT0006','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-23 06:09:35'),('BIT0007','Us0001','Super','','Configuracion',NULL,'Descargo un respaldo de la base de datos (sigpaf_DB)','::1','2026-10-23 06:09:58'),('BIT0008','Us0001','Super','','Configuracion',NULL,'Descargo un respaldo de la base de datos (sigpaf_DB)','::1','2026-10-23 06:18:26'),('BIT0009','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-23 12:03:38'),('BIT0010','Us0001','Super','','Configuracion',NULL,'Descargo un respaldo de la base de datos (sigpaf_DB)','::1','2026-10-23 12:08:28'),('BIT0011','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-25 02:24:05'),('BIT0012','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-25 02:45:45'),('BIT0013','Us0001','Super','GENERAR','Configuracion',NULL,'Generó PDF de Empleados','::1','2026-10-25 02:46:02'),('BIT0014','Us0001','Super','GENERAR','Configuracion',NULL,'Generó PDF de Empleados','::1','2026-10-25 02:46:16'),('BIT0015','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-26 03:06:32'),('BIT0016','Us0001','Super','CREATE','Estado','EST0002','Creo el registro EST0002 (Caracas)','::1','2026-10-26 03:43:58'),('BIT0017','Us0001','Super','CREATE','Cargo','CR0009','Creo el registro CR0009 (Analistaa)','::1','2026-10-26 03:50:46'),('BIT0018','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-26 13:31:42'),('BIT0019','Us0001','Super','','Configuracion',NULL,'Descargo un respaldo de la base de datos (sigpaf_DB)','::1','2026-10-26 13:35:22'),('BIT0020','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-26 14:04:56'),('BIT0021','Us0001','Super','CREATE','Actividad','AC0001','Creo el registro AC0001 (IHSNDHSNDS)','::1','2026-10-26 14:07:16'),('BIT0022','Us0001','Super','UPDATE','Actividad','AC0001','Actualizo el registro AC0001 (IHSNDHSNDS)','::1','2026-10-26 14:08:56'),('BIT0023','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-26 16:42:21'),('BIT0024','Us0001','Super','','Configuracion',NULL,'Restauro la base de datos desde un archivo .sql (89 sentencias ejecutadas)','::1','2026-10-26 17:11:28'),('BIT0025','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-26 17:55:41'),('BIT0026','Us0001','Super','','Configuracion',NULL,'Descargo un respaldo de la base de datos (sigpaf_DB) y se guardo una copia en respaldo/','::1','2026-10-26 17:55:45'),('BIT0027','Us0001','Super','EXPORTAR_BD','Configuracion',NULL,'Genero un respaldo de la base de datos (sigpaf_DB): sigpaf_respaldo_2026-10-26_14-34.sql','::1','2026-10-26 18:34:45'),('BIT0028','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-26 21:50:15'),('BIT0029','Us0001','Super','EXPORTAR_BD','Configuracion',NULL,'Genero un respaldo de la base de datos (sigpaf_DB): sigpaf_respaldo_2026-10-26_17-53.sql','::1','2026-10-26 21:53:36'),('BIT0030','Us0001','Super','EXPORTAR_BD','Configuracion',NULL,'Genero un respaldo de la base de datos (sigpaf_DB): sigpaf_respaldo_2026-10-26_17-53.sql','::1','2026-10-26 21:53:51'),('BIT0031','Us0001','Super','DELETE','Unidad de Medida','UM0006','Elimino el registro UM0006 (Unidad)','::1','2026-10-26 22:09:58'),('BIT0032','Us0001','Super','DELETE','Unidad de Medida','UM0005','Elimino el registro UM0005 (Informe)','::1','2026-10-26 22:10:11'),('BIT0033','Us0001','Super','GENERAR','Configuracion',NULL,'Generó PDF de Unidades de Medida','::1','2026-10-26 22:10:18'),('BIT0034','Us0001','Super','CREATE','Estrategia de Desarrollo','ED0008','Creo el registro ED0008 (1T Transformación Económica)','::1','2026-10-26 22:11:00'),('BIT0035','Us0001','Super','UPDATE','Actividad','AC0001','Actualizo el registro AC0001 (IHSNDHSNDS)','::1','2026-10-26 22:16:46'),('BIT0036','Us0001','Super','UPDATE','Actividad','AC0001','Actualizo el registro AC0001 (IHSNDHSNDS)','::1','2026-10-26 22:17:07'),('BIT0037','Us0001','Super','UPDATE','Actividad','AC0001','Actualizo el registro AC0001 (IHSNDHSNDS)','::1','2026-10-26 22:17:35'),('BIT0038','Us0001','Super','DELETE','Actividad','AC0001','Elimino el registro AC0001 (IHSNDHSNDS)','::1','2026-10-26 22:22:06'),('BIT0039','Us0001','Super','CREATE','Actividad','AC0001','Creo el registro AC0001 (dasdas)','::1','2026-10-26 22:23:28');
/*!40000 ALTER TABLE `bitacora` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cargo`
--

DROP TABLE IF EXISTS `cargo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cargo` (
  `idCargo` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombreCargo` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcionCargo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idCargo`),
  UNIQUE KEY `nombreCargo` (`nombreCargo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cargo`
--

LOCK TABLES `cargo` WRITE;
/*!40000 ALTER TABLE `cargo` DISABLE KEYS */;
INSERT INTO `cargo` VALUES ('CR0001','Super Usuario','Acceso total al sistema SIGPAF'),('CR0002','Analista','Responsable del análisis de datos, seguimiento de actividades y generación de reportes.'),('CR0003','Secretario','Encargado de la gestión administrativa, recepción de recaudos y archivo de documentación.'),('CR0004','Director','Supervisor general del área, encargado de la toma de decisiones estratégicas y aprobación de planes.'),('CR0005','Gerente','Coordinador operativo de los recursos, planificación de presupuestos y enlace institucional.'),('CR0006','Coordinador','Responsable directo de la ejecución de proyectos, asignación de tareas de campo y contacto comunitario.'),('CR0007','AAA','AAA'),('CR0008','asdakjsd','askjdasjkds'),('CR0009','Analistaa','sas');
/*!40000 ALTER TABLE `cargo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `docente`
--

DROP TABLE IF EXISTS `docente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docente` (
  `idDocente` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cedDocente` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nacionalidad` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombreDocente` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidoDocente` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telfDocente` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idDocente`),
  UNIQUE KEY `cedDocente` (`cedDocente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `docente`
--

LOCK TABLES `docente` WRITE;
/*!40000 ALTER TABLE `docente` DISABLE KEYS */;
INSERT INTO `docente` VALUES ('DC0001','V00000000','Venezolano','No','Aplica','0000-0000000'),('DC0002','V15421789','Venezolano','María Alejandra','Gómez Mendoza','0412-5551234'),('DC0003','V20114856','Venezolano','Carlos Eduardo','Rodríguez Plaza','0416-9994567'),('DC0004','E84215763','Extranjero','Jean Pierre','Dupond','0414-7778901'),('DC0005','V18956234','Venezolano','Yelitza Coromoto','Tovar Lovera','0424-3335566');
/*!40000 ALTER TABLE `docente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleado`
--

DROP TABLE IF EXISTS `empleado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `empleado` (
  `idEmpleado` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cedulaEmpleado` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nacionalidad` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fechaNacimiento` date NOT NULL,
  `telefonoEmpleado` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correoEmpleado` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idCargo` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idUnidadEjecutora` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idEmpleado`),
  UNIQUE KEY `cedulaEmpleado` (`cedulaEmpleado`),
  UNIQUE KEY `telefonoEmpleado` (`telefonoEmpleado`),
  UNIQUE KEY `correoEmpleado` (`correoEmpleado`),
  KEY `idCargo` (`idCargo`),
  KEY `idUnidadEjecutora` (`idUnidadEjecutora`),
  CONSTRAINT `empleado_ibfk_1` FOREIGN KEY (`idCargo`) REFERENCES `cargo` (`idCargo`),
  CONSTRAINT `empleado_ibfk_2` FOREIGN KEY (`idUnidadEjecutora`) REFERENCES `unidadejecutora` (`idUnidadEjecutora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleado`
--

LOCK TABLES `empleado` WRITE;
/*!40000 ALTER TABLE `empleado` DISABLE KEYS */;
INSERT INTO `empleado` VALUES ('EM0001','V00000000','Venezolano','Super','Usuario','1990-01-01','0000-0000000','super@usuario.com','CR0001','UNE0001'),('EM0002','18543210','Venezolano','Andrés Eloy','Blanco Ortega','1988-05-14','0412-1234567','andres.blanco@fundacite.gob.ve','CR0004','UNE0002'),('EM0003','20123456','Venezolano','Gabriela Valentina','Méndez Rojas','1992-10-22','0416-7654321','gabriela.mendez@fundacite.gob.ve','CR0006','UNE0002'),('EM0004','15987654','Venezolano','Francisco Javier','Sánchez Colmenares','1983-03-08','0414-9876543','francisco.sanchez@fundacite.gob.ve','CR0005','UNE0002'),('EM0005','22345678','Venezolano','Adriana Carolina','Pérez Sequera','1995-07-30','0424-4567890','adriana.perez@fundacite.gob.ve','CR0002','UNE0002'),('EM0006','24111222','Venezolano','José Gregorio','Giménez Castillo','1997-12-05','0412-0001122','jose.gimenez@fundacite.gob.ve','CR0003','UNE0003');
/*!40000 ALTER TABLE `empleado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `espacioutilizar`
--

DROP TABLE IF EXISTS `espacioutilizar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `espacioutilizar` (
  `idEspacioUtilizar` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombreEspacioUtilizar` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descEspacio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacidad` int(4) NOT NULL,
  PRIMARY KEY (`idEspacioUtilizar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `espacioutilizar`
--

LOCK TABLES `espacioutilizar` WRITE;
/*!40000 ALTER TABLE `espacioutilizar` DISABLE KEYS */;
INSERT INTO `espacioutilizar` VALUES ('EU0001','Auditorio Principal','Espacio para ponencias, charlas magistrales y eventos masivos',50),('EU0002','Laboratorio de Computación / Informática','Área equipada con computadoras para talleres prácticos y desarrollo de software',10),('EU0003','Salón de Usos Múltiples (SUM)','Espacio versátil para reuniones, mesas de trabajo y dinámicas grupales',40),('EU0004','Aula de Clases Estándar','Salón académico para formaciones teóricas y cursos breves',35),('EU0005','Laboratorio de Ciencias / Química','Espacio acondicionado para experimentos prácticos de química y biología',20),('EU0006','Patio Central / Espacio Abierto','Área al aire libre ideal para ferias de ciencia y exhibiciones de proyectos',60);
/*!40000 ALTER TABLE `espacioutilizar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estado`
--

DROP TABLE IF EXISTS `estado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estado` (
  `idEstado` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombreEstado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idEstado`),
  UNIQUE KEY `nombreEstado` (`nombreEstado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estado`
--

LOCK TABLES `estado` WRITE;
/*!40000 ALTER TABLE `estado` DISABLE KEYS */;
INSERT INTO `estado` VALUES ('EST0002','Caracas'),('EST0001','Yaracuy');
/*!40000 ALTER TABLE `estado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estatus`
--

DROP TABLE IF EXISTS `estatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estatus` (
  `idEstatus` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomEstatus` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descEstatus` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idEstatus`),
  UNIQUE KEY `nomEstatus` (`nomEstatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estatus`
--

LOCK TABLES `estatus` WRITE;
/*!40000 ALTER TABLE `estatus` DISABLE KEYS */;
INSERT INTO `estatus` VALUES ('ES0001','Planificada','La actividad ha sido registrada en la planificación inicial pero aún no ha iniciado'),('ES0002','En espera','La actividad se encuentra temporalmente pausada o aguardando condiciones para iniciar'),('ES0003','En ejecución','La actividad se encuentra actualmente en desarrollo y ejecución de sus sesiones'),('ES0004','Replanificada','La actividad sufrió modificaciones en sus fechas, lugar o planificación original'),('ES0005','Cancelada','La actividad fue suspendida definitivamente y no se llevará a cabo'),('ES0006','Finalizada','La actividad concluyó con éxito y se ejecutaron todas sus sesiones planeadas');
/*!40000 ALTER TABLE `estatus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estrategiadesarrollo`
--

DROP TABLE IF EXISTS `estrategiadesarrollo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estrategiadesarrollo` (
  `idEstDesarrollo` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomEstDesarrollo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descEstDesarrollo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idEstDesarrollo`),
  UNIQUE KEY `nomEstDesarrollo` (`nomEstDesarrollo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estrategiadesarrollo`
--

LOCK TABLES `estrategiadesarrollo` WRITE;
/*!40000 ALTER TABLE `estrategiadesarrollo` DISABLE KEYS */;
INSERT INTO `estrategiadesarrollo` VALUES ('ED0001','1T: Transformación Económica','Modernización de los métodos y técnicas de producción para lograr la diversificación económica.'),('ED0002','2T: Independencia Plena','Expansión de la doctrina bolivariana en sus dimensiones científica, tecnológica, educativa y cultural.'),('ED0003','3T: Paz, Seguridad e Integridad Territorial','Perfeccionamiento del modelo de convivencia ciudadana, garantía de la justicia y resguardo de la soberanía.'),('ED0004','4T: Transformación Social','Renovación total del modelo de protección humanista, garantizando el bienestar de los sectores más vulnerables.'),('ED0005','5T: Transformación Política','Consolidación de la democracia directa con ética republicana y fortalecimiento del Poder Popular.'),('ED0006','6T: Transformación Ecológica','Combate al cambio climático, preservación del ambiente, protección de la biodiversidad y equilibrio ecológico.'),('ED0007','7T: Geopolítica','Inserción y liderazgo de Venezuela en el nuevo proceso de integración de los países y el mundo pluripolar.'),('ED0008','1T Transformación Económica','');
/*!40000 ALTER TABLE `estrategiadesarrollo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupoetario`
--

DROP TABLE IF EXISTS `grupoetario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grupoetario` (
  `idGrupoEtareo` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomGrupoEtareo` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `edadMin` int(4) NOT NULL,
  `edadMax` int(4) NOT NULL,
  `descGrupoEtareo` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idGrupoEtareo`),
  UNIQUE KEY `nomGrupoEtareo` (`nomGrupoEtareo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupoetario`
--

LOCK TABLES `grupoetario` WRITE;
/*!40000 ALTER TABLE `grupoetario` DISABLE KEYS */;
INSERT INTO `grupoetario` VALUES ('GE0001','De 0 a 5 años',0,5,'Población infantil de 0 a 5 años de edad'),('GE0002','De 6 a 11 años',6,11,'Población infantil de 6 a 11 años de edad'),('GE0003','De 12 a 17 años',12,17,'Población adolescente de 12 a 17 años de edad'),('GE0004','De 18 a 23 años',18,23,'Población juvenil de 18 a 23 años de edad'),('GE0005','De 24 a 29 años',24,29,'Población joven/adulta de 24 a 29 años de edad'),('GE0006','De 30 a 35 años',30,35,'Población adulta de 30 a 35 años de edad'),('GE0007','De 36 a 41 años',36,41,'Población adulta de 36 a 41 años de edad'),('GE0008','De 42 a 47 años',42,47,'Población adulta de 42 a 47 años de edad'),('GE0009','De 48 a 53 años',48,53,'Población adulta de 48 a 53 años de edad'),('GE0010','De 54 a 59 años',54,59,'Población adulta de 54 a 59 años de edad'),('GE0011','De 60 a más',60,120,'Población adulta mayor de 60 años en adelante');
/*!40000 ALTER TABLE `grupoetario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupoetarioactividad`
--

DROP TABLE IF EXISTS `grupoetarioactividad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grupoetarioactividad` (
  `idGrupoEtareoActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idGrupoEtareo` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idGrupoEtareoActividad`),
  KEY `idGrupoEtareo` (`idGrupoEtareo`),
  KEY `idActividad` (`idActividad`),
  CONSTRAINT `grupoetarioactividad_ibfk_1` FOREIGN KEY (`idGrupoEtareo`) REFERENCES `grupoetario` (`idGrupoEtareo`),
  CONSTRAINT `grupoetarioactividad_ibfk_2` FOREIGN KEY (`idActividad`) REFERENCES `actividad` (`idActividad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupoetarioactividad`
--

LOCK TABLES `grupoetarioactividad` WRITE;
/*!40000 ALTER TABLE `grupoetarioactividad` DISABLE KEYS */;
INSERT INTO `grupoetarioactividad` VALUES ('GEA001','GE0001','AC0001','2026-10-26 22:23:28'),('GEA002','GE0002','AC0001','2026-10-26 22:23:28'),('GEA003','GE0003','AC0001','2026-10-26 22:23:28');
/*!40000 ALTER TABLE `grupoetarioactividad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupoetnio`
--

DROP TABLE IF EXISTS `grupoetnio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grupoetnio` (
  `idGrupoEtnio` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomGrupoEtnio` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desGrupoEtnio` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idGrupoEtnio`),
  UNIQUE KEY `nomGrupoEtnio` (`nomGrupoEtnio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupoetnio`
--

LOCK TABLES `grupoetnio` WRITE;
/*!40000 ALTER TABLE `grupoetnio` DISABLE KEYS */;
INSERT INTO `grupoetnio` VALUES ('GN0001','No aplica / Ninguno','Población general que no se autoidentifica con un grupo étnico específico'),('GN0002','Indígena','Población perteneciente a los pueblos y comunidades originarias'),('GN0003','Afrodescendiente','Población de herencia y descendencia africana en el territorio nacional'),('GN0004','Multiétnico / Otro','Comunidades con diversidad de raíces culturales u otros grupos étnicos específicos');
/*!40000 ALTER TABLE `grupoetnio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horario`
--

DROP TABLE IF EXISTS `horario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `horario` (
  `idHorario` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomHorario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idHorario`),
  UNIQUE KEY `nomHorario` (`nomHorario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horario`
--

LOCK TABLES `horario` WRITE;
/*!40000 ALTER TABLE `horario` DISABLE KEYS */;
INSERT INTO `horario` VALUES ('HO0003','01:00 PM - 02:00 PM'),('HO0009','01:00 PM - 04:00 PM'),('HO0004','02:00 PM - 04:00 PM'),('HO0001','08:00 AM - 10:00 AM'),('HO0008','08:00 AM - 12:00 PM'),('HO0002','10:00 AM - 12:00 PM'),('HO0010','12:00 PM - 01:00 PM');
/*!40000 ALTER TABLE `horario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lugaractividad`
--

DROP TABLE IF EXISTS `lugaractividad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lugaractividad` (
  `idLugarActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomLugarActividad` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desLugarActividad` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `esSede` tinyint(1) DEFAULT NULL,
  `idParroquia` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idLugarActividad`),
  UNIQUE KEY `nomLugarActividad` (`nomLugarActividad`),
  KEY `idParroquia` (`idParroquia`),
  CONSTRAINT `lugaractividad_ibfk_1` FOREIGN KEY (`idParroquia`) REFERENCES `parroquia` (`idParroquia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lugaractividad`
--

LOCK TABLES `lugaractividad` WRITE;
/*!40000 ALTER TABLE `lugaractividad` DISABLE KEYS */;
INSERT INTO `lugaractividad` VALUES ('LA0001','Sede Fundacite Yaracuy','Oficinas administrativas principales de Fundacite Yaracuy','Av. Intercomunal José Antonio Páez, sector El Samán',1,'PR0001'),('LA0002','U.E. Juan José de Maya','Institución educativa para actividades de formación y semilleros científicos','Av. Cartagena con Calle 19, San Felipe',0,'PR0001'),('LA0003','Liceo Arístides Bastidas','Liceo público de educación media general','Av. 2 entre Calles 11 y 12, San Felipe',0,'PR0001'),('LA0004','E.B. República de Nicaragua','Escuela básica del estado utilizada para rutas científicas','Av. Caracas entre Av. Yaracuy y Calle 15, San Felipe',0,'PR0001'),('LA0005','Complejo Tecnológico Yaracuy','Espacio de desarrollo e innovación tecnológica regional','Zona Industrial Agustín Rivero, San Felipe',0,'PR0001'),('LA0006','Plaza Bolívar de San Felipe','Espacio público abierto para ferias de divulgación científica','Av. Caracas entre Calles 6 y 7, San Felipe',0,'PR0001'),('LA0007','TANGAMANDAPIA','DASDS','ADSD',0,'PR0020');
/*!40000 ALTER TABLE `lugaractividad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lugarrealizaactividad`
--

DROP TABLE IF EXISTS `lugarrealizaactividad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lugarrealizaactividad` (
  `idReaActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idEspacioUtilizar` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idLugarActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idReaActividad`),
  KEY `idEspacioUtilizar` (`idEspacioUtilizar`),
  KEY `idLugarActividad` (`idLugarActividad`),
  KEY `idActividad` (`idActividad`),
  CONSTRAINT `lugarrealizaactividad_ibfk_1` FOREIGN KEY (`idEspacioUtilizar`) REFERENCES `espacioutilizar` (`idEspacioUtilizar`),
  CONSTRAINT `lugarrealizaactividad_ibfk_2` FOREIGN KEY (`idLugarActividad`) REFERENCES `lugaractividad` (`idLugarActividad`),
  CONSTRAINT `lugarrealizaactividad_ibfk_3` FOREIGN KEY (`idActividad`) REFERENCES `actividad` (`idActividad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lugarrealizaactividad`
--

LOCK TABLES `lugarrealizaactividad` WRITE;
/*!40000 ALTER TABLE `lugarrealizaactividad` DISABLE KEYS */;
INSERT INTO `lugarrealizaactividad` VALUES ('LR0001',NULL,'LA0004','AC0001','2026-10-26 22:23:28');
/*!40000 ALTER TABLE `lugarrealizaactividad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `municipio`
--

DROP TABLE IF EXISTS `municipio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `municipio` (
  `idMunicipio` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombreMunicipio` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idEstado` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idMunicipio`),
  UNIQUE KEY `nombreMunicipio` (`nombreMunicipio`),
  KEY `idEstado` (`idEstado`),
  CONSTRAINT `municipio_ibfk_1` FOREIGN KEY (`idEstado`) REFERENCES `estado` (`idEstado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `municipio`
--

LOCK TABLES `municipio` WRITE;
/*!40000 ALTER TABLE `municipio` DISABLE KEYS */;
INSERT INTO `municipio` VALUES ('MUN0001','San Felipe','EST0001'),('MUN0002','Independencia','EST0001'),('MUN0003','Cocorote','EST0001'),('MUN0004','Bruzual','EST0001'),('MUN0005','Nirgua','EST0001'),('MUN0006','Peña','EST0001'),('MUN0007','Arístides Bastidas','EST0001'),('MUN0008','Bolívar','EST0001'),('MUN0009','José Antonio Páez','EST0001'),('MUN0010','La Trinidad','EST0001'),('MUN0011','Manuel Monge','EST0001'),('MUN0012','Sucre','EST0001'),('MUN0013','Urachiche','EST0001'),('MUN0014','Veroes','EST0001');
/*!40000 ALTER TABLE `municipio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parroquia`
--

DROP TABLE IF EXISTS `parroquia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parroquia` (
  `idParroquia` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombreParroquia` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idMunicipio` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idParroquia`),
  UNIQUE KEY `nombreParroquia` (`nombreParroquia`),
  KEY `idMunicipio` (`idMunicipio`),
  CONSTRAINT `parroquia_ibfk_1` FOREIGN KEY (`idMunicipio`) REFERENCES `municipio` (`idMunicipio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parroquia`
--

LOCK TABLES `parroquia` WRITE;
/*!40000 ALTER TABLE `parroquia` DISABLE KEYS */;
INSERT INTO `parroquia` VALUES ('PR0001','San Felipe','MUN0001'),('PR0002','Albarico','MUN0001'),('PR0003','San Javier','MUN0001'),('PR0004','Independencia','MUN0002'),('PR0005','Cocorote','MUN0003'),('PR0006','Chivacoa','MUN0004'),('PR0007','Campo Elías','MUN0004'),('PR0008','Nirgua','MUN0005'),('PR0009','Salóm','MUN0005'),('PR0010','Temerla','MUN0005'),('PR0011','Yaritagua','MUN0006'),('PR0012','San Andrés','MUN0006'),('PR0013','San Pablo','MUN0007'),('PR0014','Aroa','MUN0008'),('PR0015','Sabana de Parra','MUN0009'),('PR0016','Boraure','MUN0010'),('PR0017','Yumare','MUN0011'),('PR0018','Guama','MUN0012'),('PR0019','Urachiche','MUN0013'),('PR0020','Farriar','MUN0014'),('PR0021','El Guayabo','MUN0014');
/*!40000 ALTER TABLE `parroquia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seguimientoactividad`
--

DROP TABLE IF EXISTS `seguimientoactividad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seguimientoactividad` (
  `idSegActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nroSesionPlanificada` int(4) NOT NULL,
  `fechaSesion` date NOT NULL,
  `logroActividad` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `observObstaculo` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idTipEntrega` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idSegActividad`),
  KEY `idActividad` (`idActividad`),
  KEY `idTipEntrega` (`idTipEntrega`),
  CONSTRAINT `seguimientoactividad_ibfk_1` FOREIGN KEY (`idActividad`) REFERENCES `actividad` (`idActividad`),
  CONSTRAINT `seguimientoactividad_ibfk_2` FOREIGN KEY (`idTipEntrega`) REFERENCES `tipoentrega` (`idTipEntrega`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seguimientoactividad`
--

LOCK TABLES `seguimientoactividad` WRITE;
/*!40000 ALTER TABLE `seguimientoactividad` DISABLE KEYS */;
/*!40000 ALTER TABLE `seguimientoactividad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones_activas`
--

DROP TABLE IF EXISTS `sesiones_activas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesiones_activas` (
  `idSesiones` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_inicio` datetime DEFAULT current_timestamp(),
  `ultima_actividad` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idSesiones`),
  UNIQUE KEY `uk_usuario` (`usuario_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_ultima_actividad` (`ultima_actividad`),
  CONSTRAINT `sesiones_activas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_activas`
--

LOCK TABLES `sesiones_activas` WRITE;
/*!40000 ALTER TABLE `sesiones_activas` DISABLE KEYS */;
INSERT INTO `sesiones_activas` VALUES (13,'Us0001','98oav1bfjt4q5b1j8tgcl1gke0','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-10-26 17:50:15','2026-10-26 20:45:44');
/*!40000 ALTER TABLE `sesiones_activas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipoactividad`
--

DROP TABLE IF EXISTS `tipoactividad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipoactividad` (
  `idTipoActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomTipoActividad` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descTipoActividad` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idTipoActividad`),
  UNIQUE KEY `nomTipoActividad` (`nomTipoActividad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipoactividad`
--

LOCK TABLES `tipoactividad` WRITE;
/*!40000 ALTER TABLE `tipoactividad` DISABLE KEYS */;
INSERT INTO `tipoactividad` VALUES ('TA0001','Abordaje','Visitas y acercamientos iniciales a comunidades o instituciones'),('TA0002','Acompañamiento','Asistencia continua en el desarrollo de proyectos o procesos'),('TA0003','Apoyo interinstitucional','Cooperación y soporte técnico/operativo a otras entidades públicas'),('TA0004','Asesorías','Orientación especializada en materia científica, tecnológica o de proyectos'),('TA0005','Capacitación','Talleres y cursos de formación técnica y profesional'),('TA0006','Caracterización','Estudios de diagnóstico y recolección de datos en unidades territoriales'),('TA0007','Cayapa heroica','Plan nacional de recuperación y mantenimiento de equipos médicos y tecnológicos'),('TA0008','Desarrollo de software','Diseño, programación e implementación de sistemas informáticos soberanos'),('TA0009','Divulgación y difusión','Actividades de socialización del conocimiento y eventos comunicacionales'),('TA0010','Elaboración de documento técnico','Redacción de manuales, informes especializados y guías metodológicas'),('TA0011','Ensayo','Pruebas técnicas y experimentación científica'),('TA0012','Evento','Organización de ferias, congresos, simposios o encuentros institucionales'),('TA0013','Formación','Procesos de enseñanza académica o comunitaria a largo plazo'),('TA0014','Implementación de sistema o aplicativo','Instalación y puesta en marcha de herramientas de software en entornos reales'),('TA0015','Investigación','Estudios y proyectos de desarrollo científico puro o aplicado'),('TA0016','Publicación realizada','Artículos, libros o notas de prensa científicas validadas'),('TA0017','Soporte tecnológico','Asistencia técnica en hardware, redes y conectividad'),('TA0018','Otro','Cualquier otra actividad no clasificada en los tipos anteriores');
/*!40000 ALTER TABLE `tipoactividad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipoentrega`
--

DROP TABLE IF EXISTS `tipoentrega`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipoentrega` (
  `idTipEntrega` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomTipEntrega` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idTipEntrega`),
  UNIQUE KEY `nomTipEntrega` (`nomTipEntrega`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipoentrega`
--

LOCK TABLES `tipoentrega` WRITE;
/*!40000 ALTER TABLE `tipoentrega` DISABLE KEYS */;
INSERT INTO `tipoentrega` VALUES ('TE0003','Certificado de Participación / Aprobación'),('TE0005','Equipo Tecnológico Recuperado (Cayapa Heroica)'),('TE0002','Kits de Robótica Educativa'),('TE0007','Material Divulgativo / Guía Técnica'),('TE0006','Servicio de Asesoría Técnica de Proyectos'),('TE0001','Servicio de Formación / Capacitación'),('TE0004','Servicio de Soporte y Mantenimiento Técnico');
/*!40000 ALTER TABLE `tipoentrega` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipousuario`
--

DROP TABLE IF EXISTS `tipousuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipousuario` (
  `idTipoUsuario` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rolUsuario` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idTipoUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipousuario`
--

LOCK TABLES `tipousuario` WRITE;
/*!40000 ALTER TABLE `tipousuario` DISABLE KEYS */;
INSERT INTO `tipousuario` VALUES ('Rol0001','Super Usuario'),('Rol0002','Administrador'),('Rol0003','Usuario Comun');
/*!40000 ALTER TABLE `tipousuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unidadejecutora`
--

DROP TABLE IF EXISTS `unidadejecutora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unidadejecutora` (
  `idUnidadEjecutora` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomUnidadEjecutora` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desUnidadEjecutora` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idUnidadEjecutora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unidadejecutora`
--

LOCK TABLES `unidadejecutora` WRITE;
/*!40000 ALTER TABLE `unidadejecutora` DISABLE KEYS */;
INSERT INTO `unidadejecutora` VALUES ('UNE0001','SuperU','Super Usuario Con Todos los Permisos'),('UNE0002','TIC','Unidad de Tecnologías de la Información y Comunicación'),('UNE0003','Admin y Finanzas','Departamento de Administración, Presupuesto, Compras y Gestión Financiera'),('UNE0004','Atención Ciudadana','Oficina de Vinculación Comunitaria, Atención al Ciudadano y Gestión Social'),('UNE0005','Talento Humano','Departamento de Recursos Humanos, Nómina y Bienestar Laboral');
/*!40000 ALTER TABLE `unidadejecutora` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unidadmedida`
--

DROP TABLE IF EXISTS `unidadmedida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unidadmedida` (
  `idUnidadMedida` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomUnidadMedida` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descUnidadMedida` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idUnidadMedida`),
  UNIQUE KEY `nomUnidadMedida` (`nomUnidadMedida`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unidadmedida`
--

LOCK TABLES `unidadmedida` WRITE;
/*!40000 ALTER TABLE `unidadmedida` DISABLE KEYS */;
INSERT INTO `unidadmedida` VALUES ('UM0001','Taller','Actividad de formación práctica o educativa grupal'),('UM0002','Reunión','Encuentro formal de trabajo o articulación con actores clave'),('UM0003','Inspección','Visita técnica de supervisión o verificación en campo'),('UM0004','Asistencia Técnica','Soporte directo brindado a usuarios, instituciones o productores'),('UM0007','Planilla','Fichas, registros o instrumentos de control completados');
/*!40000 ALTER TABLE `unidadmedida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `idUsuario` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombreUsuario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `idTipoUsuario` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idEmpleado` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idUsuario`),
  UNIQUE KEY `nombreUsuario` (`nombreUsuario`),
  KEY `idTipoUsuario` (`idTipoUsuario`),
  KEY `idEmpleado` (`idEmpleado`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`idTipoUsuario`) REFERENCES `tipousuario` (`idTipoUsuario`),
  CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`idEmpleado`) REFERENCES `empleado` (`idEmpleado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES ('Us0001','Super','$2a$12$6Ok0TmT9TLQm9Mo3j/B83.8DN0sR0GZkC/w4rqiT7Ma9pS38HNL9u','2026-09-27 02:56:41','Rol0001','EM0001'),('US0002','andres','$2y$10$6G.8j8dt9pseGQgiW8tn6.FyWz56xxXVta/jylVNuf4wWInu9ZW9y','2026-09-27 02:56:41','Rol0002','EM0002'),('US0003','gabriela_user','$2a$12$y/odPJdXnMdTnYAJXyULwuX.d94kPS3rEVWapyCrfCato1nZDMw0K','2026-09-27 02:56:41','Rol0003','EM0003');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vertice`
--

DROP TABLE IF EXISTS `vertice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vertice` (
  `idVertice` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombreVertice` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descVertice` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idVertice`),
  UNIQUE KEY `nombreVertice` (`nombreVertice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vertice`
--

LOCK TABLES `vertice` WRITE;
/*!40000 ALTER TABLE `vertice` DISABLE KEYS */;
INSERT INTO `vertice` VALUES ('VER0001','Siembra y preservación del talento científico nacional','Formación, capacitación y ejercicio ético-científico en niños, jóvenes y adultos para asegurar el relevo generacional'),('VER0002','Fortalecimiento de espacios para la investigación, desarrollo e innovación','Modernización y equipamiento de laboratorios, centros de investigación y espacios académicos para la producción científica'),('VER0003','Ciencia, tecnología e innovación para la producción y la economía','Uso y aplicación del conocimiento científico para potenciar los encadenamientos productivos y el desarrollo económico del país'),('VER0004','Venezuela hacia la transformación digital','Impulso y vanguardia en el desarrollo de software y hardware libre, ciberseguridad y soberanía tecnológica digital'),('VER0005','Cooperación y alianzas internacionales','Fomento de alianzas estratégicas y redes de innovación y de investigación científica para profundizar la cooperación global'),('VER0006','JJJ','NLNJJJJ');
/*!40000 ALTER TABLE `vertice` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-26 20:45:52
