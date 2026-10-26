SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `sigpaf_DB` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sigpaf_DB`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `actividad`;
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

INSERT INTO `actividad` (`idActividad`, `nombreActividad`, `fechainicioActividad`, `fechafinActividad`, `cantSesionesPlanificada`, `objetivoActividad`, `descActividad`, `cantPersoAtender`, `observacion`, `idTipoActividad`, `idVertice`, `idAreaE`, `idEmpleado`, `idUnidadMedida`, `idGrupoEtnio`, `idDocente`, `idEstatus`, `idHorario`, `idEstDesarrollo`) VALUES
('AC0001','IHSNDHSNDS','2026-10-28','2026-10-31','1','SJNDJSnDKJsnd','asbjX jX \\S XHS','11','AJDAKJDS','TA0011','VER0003','AE0002','EM0002','UM0007','GN0002','DC0004','ES0001','HO0002','ED0006');


DROP TABLE IF EXISTS `areaespecifica`;
CREATE TABLE `areaespecifica` (
  `idAreaE` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomAreaE` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idAreaE`),
  UNIQUE KEY `nomAreaE` (`nomAreaE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `areaespecifica` (`idAreaE`, `nomAreaE`) VALUES
('AE0001','AE1'),
('AE0002','AE2'),
('AE0003','AE3'),
('AE0004','AE4'),
('AE0005','AE5');


DROP TABLE IF EXISTS `asistencia`;
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


DROP TABLE IF EXISTS `beneficiario`;
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


DROP TABLE IF EXISTS `bitacora`;
CREATE TABLE `bitacora` (
  `idBitacora` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_id` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario_nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `accion` enum('LOGIN','LOGOUT','CREATE','UPDATE','DELETE','ACCESS_DENIED','GENERAR') COLLATE utf8mb4_unicode_ci NOT NULL,
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

INSERT INTO `bitacora` (`idBitacora`, `usuario_id`, `usuario_nombre`, `accion`, `modulo`, `registro_id`, `descripcion`, `ip`, `fecha_hora`) VALUES
('BIT0001','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-23 01:53:46'),
('BIT0002','Us0001','Super','UPDATE','Usuario','US0002','Actualizo el registro US0002 (andres)','::1','2026-10-23 01:55:23'),
('BIT0003','Us0001','Super','LOGOUT','Sesion',NULL,'Cerro sesion en el sistema','::1','2026-10-23 01:55:28'),
('BIT0004','US0002','andres','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-23 01:55:41'),
('BIT0005','US0002','andres','LOGOUT','Sesion',NULL,'Cerro sesion en el sistema','::1','2026-10-23 02:09:25'),
('BIT0006','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-23 02:09:35'),
('BIT0007','Us0001','Super','','Configuracion',NULL,'Descargo un respaldo de la base de datos (sigpaf_DB)','::1','2026-10-23 02:09:58'),
('BIT0008','Us0001','Super','','Configuracion',NULL,'Descargo un respaldo de la base de datos (sigpaf_DB)','::1','2026-10-23 02:18:26'),
('BIT0009','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-23 08:03:38'),
('BIT0010','Us0001','Super','','Configuracion',NULL,'Descargo un respaldo de la base de datos (sigpaf_DB)','::1','2026-10-23 08:08:28'),
('BIT0011','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-24 22:24:05'),
('BIT0012','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-24 22:45:45'),
('BIT0013','Us0001','Super','GENERAR','Configuracion',NULL,'Generó PDF de Empleados','::1','2026-10-24 22:46:02'),
('BIT0014','Us0001','Super','GENERAR','Configuracion',NULL,'Generó PDF de Empleados','::1','2026-10-24 22:46:16'),
('BIT0015','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-25 23:06:32'),
('BIT0016','Us0001','Super','CREATE','Estado','EST0002','Creo el registro EST0002 (Caracas)','::1','2026-10-25 23:43:58'),
('BIT0017','Us0001','Super','CREATE','Cargo','CR0009','Creo el registro CR0009 (Analistaa)','::1','2026-10-25 23:50:46'),
('BIT0018','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-26 09:31:42'),
('BIT0019','Us0001','Super','','Configuracion',NULL,'Descargo un respaldo de la base de datos (sigpaf_DB)','::1','2026-10-26 09:35:22'),
('BIT0020','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-26 10:04:56'),
('BIT0021','Us0001','Super','CREATE','Actividad','AC0001','Creo el registro AC0001 (IHSNDHSNDS)','::1','2026-10-26 10:07:16'),
('BIT0022','Us0001','Super','UPDATE','Actividad','AC0001','Actualizo el registro AC0001 (IHSNDHSNDS)','::1','2026-10-26 10:08:56'),
('BIT0023','Us0001','Super','LOGIN','Sesion',NULL,'Inicio sesion en el sistema','::1','2026-10-26 12:42:21'),
('BIT0024','Us0001','Super','','Configuracion',NULL,'Descargo un respaldo de la base de datos (sigpaf_DB) y se guardo una copia en respaldo/','::1','2026-10-26 12:42:27');


DROP TABLE IF EXISTS `cargo`;
CREATE TABLE `cargo` (
  `idCargo` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombreCargo` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcionCargo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idCargo`),
  UNIQUE KEY `nombreCargo` (`nombreCargo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cargo` (`idCargo`, `nombreCargo`, `descripcionCargo`) VALUES
('CR0001','Super Usuario','Acceso total al sistema SIGPAF'),
('CR0002','Analista','Responsable del análisis de datos, seguimiento de actividades y generación de reportes.'),
('CR0003','Secretario','Encargado de la gestión administrativa, recepción de recaudos y archivo de documentación.'),
('CR0004','Director','Supervisor general del área, encargado de la toma de decisiones estratégicas y aprobación de planes.'),
('CR0005','Gerente','Coordinador operativo de los recursos, planificación de presupuestos y enlace institucional.'),
('CR0006','Coordinador','Responsable directo de la ejecución de proyectos, asignación de tareas de campo y contacto comunitario.'),
('CR0007','AAA','AAA'),
('CR0008','asdakjsd','askjdasjkds'),
('CR0009','Analistaa','sas');


DROP TABLE IF EXISTS `docente`;
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

INSERT INTO `docente` (`idDocente`, `cedDocente`, `nacionalidad`, `nombreDocente`, `apellidoDocente`, `telfDocente`) VALUES
('DC0001','V00000000','Venezolano','No','Aplica','0000-0000000'),
('DC0002','V15421789','Venezolano','María Alejandra','Gómez Mendoza','0412-5551234'),
('DC0003','V20114856','Venezolano','Carlos Eduardo','Rodríguez Plaza','0416-9994567'),
('DC0004','E84215763','Extranjero','Jean Pierre','Dupond','0414-7778901'),
('DC0005','V18956234','Venezolano','Yelitza Coromoto','Tovar Lovera','0424-3335566');


DROP TABLE IF EXISTS `empleado`;
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

INSERT INTO `empleado` (`idEmpleado`, `cedulaEmpleado`, `nacionalidad`, `nombres`, `apellidos`, `fechaNacimiento`, `telefonoEmpleado`, `correoEmpleado`, `idCargo`, `idUnidadEjecutora`) VALUES
('EM0001','V00000000','Venezolano','Super','Usuario','1990-01-01','0000-0000000','super@usuario.com','CR0001','UNE0001'),
('EM0002','18543210','Venezolano','Andrés Eloy','Blanco Ortega','1988-05-14','0412-1234567','andres.blanco@fundacite.gob.ve','CR0004','UNE0002'),
('EM0003','20123456','Venezolano','Gabriela Valentina','Méndez Rojas','1992-10-22','0416-7654321','gabriela.mendez@fundacite.gob.ve','CR0006','UNE0002'),
('EM0004','15987654','Venezolano','Francisco Javier','Sánchez Colmenares','1983-03-08','0414-9876543','francisco.sanchez@fundacite.gob.ve','CR0005','UNE0002'),
('EM0005','22345678','Venezolano','Adriana Carolina','Pérez Sequera','1995-07-30','0424-4567890','adriana.perez@fundacite.gob.ve','CR0002','UNE0002'),
('EM0006','24111222','Venezolano','José Gregorio','Giménez Castillo','1997-12-05','0412-0001122','jose.gimenez@fundacite.gob.ve','CR0003','UNE0003');


DROP TABLE IF EXISTS `espacioutilizar`;
CREATE TABLE `espacioutilizar` (
  `idEspacioUtilizar` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombreEspacioUtilizar` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descEspacio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacidad` int(4) NOT NULL,
  PRIMARY KEY (`idEspacioUtilizar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `espacioutilizar` (`idEspacioUtilizar`, `nombreEspacioUtilizar`, `descEspacio`, `capacidad`) VALUES
('EU0001','Auditorio Principal','Espacio para ponencias, charlas magistrales y eventos masivos','50'),
('EU0002','Laboratorio de Computación / Informática','Área equipada con computadoras para talleres prácticos y desarrollo de software','10'),
('EU0003','Salón de Usos Múltiples (SUM)','Espacio versátil para reuniones, mesas de trabajo y dinámicas grupales','40'),
('EU0004','Aula de Clases Estándar','Salón académico para formaciones teóricas y cursos breves','35'),
('EU0005','Laboratorio de Ciencias / Química','Espacio acondicionado para experimentos prácticos de química y biología','20'),
('EU0006','Patio Central / Espacio Abierto','Área al aire libre ideal para ferias de ciencia y exhibiciones de proyectos','60');


DROP TABLE IF EXISTS `estado`;
CREATE TABLE `estado` (
  `idEstado` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombreEstado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idEstado`),
  UNIQUE KEY `nombreEstado` (`nombreEstado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `estado` (`idEstado`, `nombreEstado`) VALUES
('EST0002','Caracas'),
('EST0001','Yaracuy');


DROP TABLE IF EXISTS `estatus`;
CREATE TABLE `estatus` (
  `idEstatus` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomEstatus` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descEstatus` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idEstatus`),
  UNIQUE KEY `nomEstatus` (`nomEstatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `estatus` (`idEstatus`, `nomEstatus`, `descEstatus`) VALUES
('ES0001','Planificada','La actividad ha sido registrada en la planificación inicial pero aún no ha iniciado'),
('ES0002','En espera','La actividad se encuentra temporalmente pausada o aguardando condiciones para iniciar'),
('ES0003','En ejecución','La actividad se encuentra actualmente en desarrollo y ejecución de sus sesiones'),
('ES0004','Replanificada','La actividad sufrió modificaciones en sus fechas, lugar o planificación original'),
('ES0005','Cancelada','La actividad fue suspendida definitivamente y no se llevará a cabo'),
('ES0006','Finalizada','La actividad concluyó con éxito y se ejecutaron todas sus sesiones planeadas');


DROP TABLE IF EXISTS `estrategiadesarrollo`;
CREATE TABLE `estrategiadesarrollo` (
  `idEstDesarrollo` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomEstDesarrollo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descEstDesarrollo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idEstDesarrollo`),
  UNIQUE KEY `nomEstDesarrollo` (`nomEstDesarrollo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `estrategiadesarrollo` (`idEstDesarrollo`, `nomEstDesarrollo`, `descEstDesarrollo`) VALUES
('ED0001','1T: Transformación Económica','Modernización de los métodos y técnicas de producción para lograr la diversificación económica.'),
('ED0002','2T: Independencia Plena','Expansión de la doctrina bolivariana en sus dimensiones científica, tecnológica, educativa y cultural.'),
('ED0003','3T: Paz, Seguridad e Integridad Territorial','Perfeccionamiento del modelo de convivencia ciudadana, garantía de la justicia y resguardo de la soberanía.'),
('ED0004','4T: Transformación Social','Renovación total del modelo de protección humanista, garantizando el bienestar de los sectores más vulnerables.'),
('ED0005','5T: Transformación Política','Consolidación de la democracia directa con ética republicana y fortalecimiento del Poder Popular.'),
('ED0006','6T: Transformación Ecológica','Combate al cambio climático, preservación del ambiente, protección de la biodiversidad y equilibrio ecológico.'),
('ED0007','7T: Geopolítica','Inserción y liderazgo de Venezuela en el nuevo proceso de integración de los países y el mundo pluripolar.');


DROP TABLE IF EXISTS `grupoetario`;
CREATE TABLE `grupoetario` (
  `idGrupoEtareo` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomGrupoEtareo` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `edadMin` int(4) NOT NULL,
  `edadMax` int(4) NOT NULL,
  `descGrupoEtareo` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idGrupoEtareo`),
  UNIQUE KEY `nomGrupoEtareo` (`nomGrupoEtareo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `grupoetario` (`idGrupoEtareo`, `nomGrupoEtareo`, `edadMin`, `edadMax`, `descGrupoEtareo`) VALUES
('GE0001','De 0 a 5 años','0','5','Población infantil de 0 a 5 años de edad'),
('GE0002','De 6 a 11 años','6','11','Población infantil de 6 a 11 años de edad'),
('GE0003','De 12 a 17 años','12','17','Población adolescente de 12 a 17 años de edad'),
('GE0004','De 18 a 23 años','18','23','Población juvenil de 18 a 23 años de edad'),
('GE0005','De 24 a 29 años','24','29','Población joven/adulta de 24 a 29 años de edad'),
('GE0006','De 30 a 35 años','30','35','Población adulta de 30 a 35 años de edad'),
('GE0007','De 36 a 41 años','36','41','Población adulta de 36 a 41 años de edad'),
('GE0008','De 42 a 47 años','42','47','Población adulta de 42 a 47 años de edad'),
('GE0009','De 48 a 53 años','48','53','Población adulta de 48 a 53 años de edad'),
('GE0010','De 54 a 59 años','54','59','Población adulta de 54 a 59 años de edad'),
('GE0011','De 60 a más','60','120','Población adulta mayor de 60 años en adelante');


DROP TABLE IF EXISTS `grupoetarioactividad`;
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

INSERT INTO `grupoetarioactividad` (`idGrupoEtareoActividad`, `idGrupoEtareo`, `idActividad`, `created_at`) VALUES
('GEA001','GE0003','AC0001','2026-10-26 10:08:56');


DROP TABLE IF EXISTS `grupoetnio`;
CREATE TABLE `grupoetnio` (
  `idGrupoEtnio` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomGrupoEtnio` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desGrupoEtnio` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idGrupoEtnio`),
  UNIQUE KEY `nomGrupoEtnio` (`nomGrupoEtnio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `grupoetnio` (`idGrupoEtnio`, `nomGrupoEtnio`, `desGrupoEtnio`) VALUES
('GN0001','No aplica / Ninguno','Población general que no se autoidentifica con un grupo étnico específico'),
('GN0002','Indígena','Población perteneciente a los pueblos y comunidades originarias'),
('GN0003','Afrodescendiente','Población de herencia y descendencia africana en el territorio nacional'),
('GN0004','Multiétnico / Otro','Comunidades con diversidad de raíces culturales u otros grupos étnicos específicos');


DROP TABLE IF EXISTS `horario`;
CREATE TABLE `horario` (
  `idHorario` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomHorario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idHorario`),
  UNIQUE KEY `nomHorario` (`nomHorario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `horario` (`idHorario`, `nomHorario`) VALUES
('HO0003','01:00 PM - 02:00 PM'),
('HO0009','01:00 PM - 04:00 PM'),
('HO0004','02:00 PM - 04:00 PM'),
('HO0001','08:00 AM - 10:00 AM'),
('HO0008','08:00 AM - 12:00 PM'),
('HO0002','10:00 AM - 12:00 PM'),
('HO0010','12:00 PM - 01:00 PM');


DROP TABLE IF EXISTS `lugaractividad`;
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

INSERT INTO `lugaractividad` (`idLugarActividad`, `nomLugarActividad`, `desLugarActividad`, `direccion`, `esSede`, `idParroquia`) VALUES
('LA0001','Sede Fundacite Yaracuy','Oficinas administrativas principales de Fundacite Yaracuy','Av. Intercomunal José Antonio Páez, sector El Samán','1','PR0001'),
('LA0002','U.E. Juan José de Maya','Institución educativa para actividades de formación y semilleros científicos','Av. Cartagena con Calle 19, San Felipe','0','PR0001'),
('LA0003','Liceo Arístides Bastidas','Liceo público de educación media general','Av. 2 entre Calles 11 y 12, San Felipe','0','PR0001'),
('LA0004','E.B. República de Nicaragua','Escuela básica del estado utilizada para rutas científicas','Av. Caracas entre Av. Yaracuy y Calle 15, San Felipe','0','PR0001'),
('LA0005','Complejo Tecnológico Yaracuy','Espacio de desarrollo e innovación tecnológica regional','Zona Industrial Agustín Rivero, San Felipe','0','PR0001'),
('LA0006','Plaza Bolívar de San Felipe','Espacio público abierto para ferias de divulgación científica','Av. Caracas entre Calles 6 y 7, San Felipe','0','PR0001'),
('LA0007','TANGAMANDAPIA','DASDS','ADSD','0','PR0020');


DROP TABLE IF EXISTS `lugarrealizaactividad`;
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

INSERT INTO `lugarrealizaactividad` (`idReaActividad`, `idEspacioUtilizar`, `idLugarActividad`, `idActividad`, `created_at`) VALUES
('LR0001',NULL,'LA0004','AC0001','2026-10-26 10:07:16');


DROP TABLE IF EXISTS `municipio`;
CREATE TABLE `municipio` (
  `idMunicipio` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombreMunicipio` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idEstado` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idMunicipio`),
  UNIQUE KEY `nombreMunicipio` (`nombreMunicipio`),
  KEY `idEstado` (`idEstado`),
  CONSTRAINT `municipio_ibfk_1` FOREIGN KEY (`idEstado`) REFERENCES `estado` (`idEstado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `municipio` (`idMunicipio`, `nombreMunicipio`, `idEstado`) VALUES
('MUN0001','San Felipe','EST0001'),
('MUN0002','Independencia','EST0001'),
('MUN0003','Cocorote','EST0001'),
('MUN0004','Bruzual','EST0001'),
('MUN0005','Nirgua','EST0001'),
('MUN0006','Peña','EST0001'),
('MUN0007','Arístides Bastidas','EST0001'),
('MUN0008','Bolívar','EST0001'),
('MUN0009','José Antonio Páez','EST0001'),
('MUN0010','La Trinidad','EST0001'),
('MUN0011','Manuel Monge','EST0001'),
('MUN0012','Sucre','EST0001'),
('MUN0013','Urachiche','EST0001'),
('MUN0014','Veroes','EST0001');


DROP TABLE IF EXISTS `parroquia`;
CREATE TABLE `parroquia` (
  `idParroquia` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombreParroquia` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idMunicipio` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idParroquia`),
  UNIQUE KEY `nombreParroquia` (`nombreParroquia`),
  KEY `idMunicipio` (`idMunicipio`),
  CONSTRAINT `parroquia_ibfk_1` FOREIGN KEY (`idMunicipio`) REFERENCES `municipio` (`idMunicipio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `parroquia` (`idParroquia`, `nombreParroquia`, `idMunicipio`) VALUES
('PR0001','San Felipe','MUN0001'),
('PR0002','Albarico','MUN0001'),
('PR0003','San Javier','MUN0001'),
('PR0004','Independencia','MUN0002'),
('PR0005','Cocorote','MUN0003'),
('PR0006','Chivacoa','MUN0004'),
('PR0007','Campo Elías','MUN0004'),
('PR0008','Nirgua','MUN0005'),
('PR0009','Salóm','MUN0005'),
('PR0010','Temerla','MUN0005'),
('PR0011','Yaritagua','MUN0006'),
('PR0012','San Andrés','MUN0006'),
('PR0013','San Pablo','MUN0007'),
('PR0014','Aroa','MUN0008'),
('PR0015','Sabana de Parra','MUN0009'),
('PR0016','Boraure','MUN0010'),
('PR0017','Yumare','MUN0011'),
('PR0018','Guama','MUN0012'),
('PR0019','Urachiche','MUN0013'),
('PR0020','Farriar','MUN0014'),
('PR0021','El Guayabo','MUN0014');


DROP TABLE IF EXISTS `seguimientoactividad`;
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


DROP TABLE IF EXISTS `sesiones_activas`;
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sesiones_activas` (`idSesiones`, `usuario_id`, `session_id`, `ip_address`, `user_agent`, `fecha_inicio`, `ultima_actividad`) VALUES
('10','Us0001','3pnfe92j7uq0uc89nvujor4fj6','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-10-26 12:42:21','2026-10-26 12:43:22');


DROP TABLE IF EXISTS `tipoactividad`;
CREATE TABLE `tipoactividad` (
  `idTipoActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomTipoActividad` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descTipoActividad` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idTipoActividad`),
  UNIQUE KEY `nomTipoActividad` (`nomTipoActividad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tipoactividad` (`idTipoActividad`, `nomTipoActividad`, `descTipoActividad`) VALUES
('TA0001','Abordaje','Visitas y acercamientos iniciales a comunidades o instituciones'),
('TA0002','Acompañamiento','Asistencia continua en el desarrollo de proyectos o procesos'),
('TA0003','Apoyo interinstitucional','Cooperación y soporte técnico/operativo a otras entidades públicas'),
('TA0004','Asesorías','Orientación especializada en materia científica, tecnológica o de proyectos'),
('TA0005','Capacitación','Talleres y cursos de formación técnica y profesional'),
('TA0006','Caracterización','Estudios de diagnóstico y recolección de datos en unidades territoriales'),
('TA0007','Cayapa heroica','Plan nacional de recuperación y mantenimiento de equipos médicos y tecnológicos'),
('TA0008','Desarrollo de software','Diseño, programación e implementación de sistemas informáticos soberanos'),
('TA0009','Divulgación y difusión','Actividades de socialización del conocimiento y eventos comunicacionales'),
('TA0010','Elaboración de documento técnico','Redacción de manuales, informes especializados y guías metodológicas'),
('TA0011','Ensayo','Pruebas técnicas y experimentación científica'),
('TA0012','Evento','Organización de ferias, congresos, simposios o encuentros institucionales'),
('TA0013','Formación','Procesos de enseñanza académica o comunitaria a largo plazo'),
('TA0014','Implementación de sistema o aplicativo','Instalación y puesta en marcha de herramientas de software en entornos reales'),
('TA0015','Investigación','Estudios y proyectos de desarrollo científico puro o aplicado'),
('TA0016','Publicación realizada','Artículos, libros o notas de prensa científicas validadas'),
('TA0017','Soporte tecnológico','Asistencia técnica en hardware, redes y conectividad'),
('TA0018','Otro','Cualquier otra actividad no clasificada en los tipos anteriores');


DROP TABLE IF EXISTS `tipoentrega`;
CREATE TABLE `tipoentrega` (
  `idTipEntrega` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomTipEntrega` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idTipEntrega`),
  UNIQUE KEY `nomTipEntrega` (`nomTipEntrega`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tipoentrega` (`idTipEntrega`, `nomTipEntrega`) VALUES
('TE0003','Certificado de Participación / Aprobación'),
('TE0005','Equipo Tecnológico Recuperado (Cayapa Heroica)'),
('TE0002','Kits de Robótica Educativa'),
('TE0007','Material Divulgativo / Guía Técnica'),
('TE0006','Servicio de Asesoría Técnica de Proyectos'),
('TE0001','Servicio de Formación / Capacitación'),
('TE0004','Servicio de Soporte y Mantenimiento Técnico');


DROP TABLE IF EXISTS `tipousuario`;
CREATE TABLE `tipousuario` (
  `idTipoUsuario` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rolUsuario` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idTipoUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tipousuario` (`idTipoUsuario`, `rolUsuario`) VALUES
('Rol0001','Super Usuario'),
('Rol0002','Administrador'),
('Rol0003','Usuario Comun');


DROP TABLE IF EXISTS `unidadejecutora`;
CREATE TABLE `unidadejecutora` (
  `idUnidadEjecutora` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomUnidadEjecutora` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desUnidadEjecutora` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idUnidadEjecutora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `unidadejecutora` (`idUnidadEjecutora`, `nomUnidadEjecutora`, `desUnidadEjecutora`) VALUES
('UNE0001','SuperU','Super Usuario Con Todos los Permisos'),
('UNE0002','TIC','Unidad de Tecnologías de la Información y Comunicación'),
('UNE0003','Admin y Finanzas','Departamento de Administración, Presupuesto, Compras y Gestión Financiera'),
('UNE0004','Atención Ciudadana','Oficina de Vinculación Comunitaria, Atención al Ciudadano y Gestión Social'),
('UNE0005','Talento Humano','Departamento de Recursos Humanos, Nómina y Bienestar Laboral');


DROP TABLE IF EXISTS `unidadmedida`;
CREATE TABLE `unidadmedida` (
  `idUnidadMedida` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomUnidadMedida` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descUnidadMedida` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idUnidadMedida`),
  UNIQUE KEY `nomUnidadMedida` (`nomUnidadMedida`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `unidadmedida` (`idUnidadMedida`, `nomUnidadMedida`, `descUnidadMedida`) VALUES
('UM0001','Taller','Actividad de formación práctica o educativa grupal'),
('UM0002','Reunión','Encuentro formal de trabajo o articulación con actores clave'),
('UM0003','Inspección','Visita técnica de supervisión o verificación en campo'),
('UM0004','Asistencia Técnica','Soporte directo brindado a usuarios, instituciones o productores'),
('UM0005','Informe','Documento técnico, operativo o administrativo generado'),
('UM0006','Unidad','Medida general para cuantificar entregas de bienes o sistemas'),
('UM0007','Planilla','Fichas, registros o instrumentos de control completados');


DROP TABLE IF EXISTS `usuarios`;
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

INSERT INTO `usuarios` (`idUsuario`, `nombreUsuario`, `contrasena`, `created_at`, `idTipoUsuario`, `idEmpleado`) VALUES
('Us0001','Super','$2a$12$6Ok0TmT9TLQm9Mo3j/B83.8DN0sR0GZkC/w4rqiT7Ma9pS38HNL9u','2026-09-26 22:56:41','Rol0001','EM0001'),
('US0002','andres','$2y$10$6G.8j8dt9pseGQgiW8tn6.FyWz56xxXVta/jylVNuf4wWInu9ZW9y','2026-09-26 22:56:41','Rol0002','EM0002'),
('US0003','gabriela_user','$2a$12$y/odPJdXnMdTnYAJXyULwuX.d94kPS3rEVWapyCrfCato1nZDMw0K','2026-09-26 22:56:41','Rol0003','EM0003');


DROP TABLE IF EXISTS `vertice`;
CREATE TABLE `vertice` (
  `idVertice` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombreVertice` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descVertice` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idVertice`),
  UNIQUE KEY `nombreVertice` (`nombreVertice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `vertice` (`idVertice`, `nombreVertice`, `descVertice`) VALUES
('VER0001','Siembra y preservación del talento científico nacional','Formación, capacitación y ejercicio ético-científico en niños, jóvenes y adultos para asegurar el relevo generacional'),
('VER0002','Fortalecimiento de espacios para la investigación, desarrollo e innovación','Modernización y equipamiento de laboratorios, centros de investigación y espacios académicos para la producción científica'),
('VER0003','Ciencia, tecnología e innovación para la producción y la economía','Uso y aplicación del conocimiento científico para potenciar los encadenamientos productivos y el desarrollo económico del país'),
('VER0004','Venezuela hacia la transformación digital','Impulso y vanguardia en el desarrollo de software y hardware libre, ciberseguridad y soberanía tecnológica digital'),
('VER0005','Cooperación y alianzas internacionales','Fomento de alianzas estratégicas y redes de innovación y de investigación científica para profundizar la cooperación global'),
('VER0006','JJJ','NLNJJJJ');


SET FOREIGN_KEY_CHECKS=1;
