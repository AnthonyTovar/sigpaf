SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `actividadhorario` (
  `idActividadHorario` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idHorario` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idActividadHorario`),
  UNIQUE KEY `uk_actividad_horario` (`idActividad`, `idHorario`),
  KEY `idHorario` (`idHorario`),
  CONSTRAINT `actividadhorario_ibfk_1` FOREIGN KEY (`idActividad`) REFERENCES `actividad` (`idActividad`) ON DELETE CASCADE,
  CONSTRAINT `actividadhorario_ibfk_2` FOREIGN KEY (`idHorario`) REFERENCES `horario` (`idHorario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `actividadempleado` (
  `idActividadEmpleado` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idEmpleado` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idActividadEmpleado`),
  UNIQUE KEY `uk_actividad_empleado` (`idActividad`, `idEmpleado`),
  KEY `idEmpleado` (`idEmpleado`),
  CONSTRAINT `actividadempleado_ibfk_1` FOREIGN KEY (`idActividad`) REFERENCES `actividad` (`idActividad`) ON DELETE CASCADE,
  CONSTRAINT `actividadempleado_ibfk_2` FOREIGN KEY (`idEmpleado`) REFERENCES `empleado` (`idEmpleado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `actividaddocente` (
  `idActividadDocente` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idActividad` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idDocente` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idActividadDocente`),
  UNIQUE KEY `uk_actividad_docente` (`idActividad`, `idDocente`),
  KEY `idDocente` (`idDocente`),
  CONSTRAINT `actividaddocente_ibfk_1` FOREIGN KEY (`idActividad`) REFERENCES `actividad` (`idActividad`) ON DELETE CASCADE,
  CONSTRAINT `actividaddocente_ibfk_2` FOREIGN KEY (`idDocente`) REFERENCES `docente` (`idDocente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `actividadhorario` (`idActividadHorario`, `idActividad`, `idHorario`)
SELECT CONCAT('AH', LPAD(CAST(SUBSTRING(a.`idActividad`, 3) AS UNSIGNED), 6, '0')),
       a.`idActividad`, a.`idHorario`
FROM `actividad` a;

INSERT IGNORE INTO `actividadempleado` (`idActividadEmpleado`, `idActividad`, `idEmpleado`)
SELECT CONCAT('AE', LPAD(CAST(SUBSTRING(a.`idActividad`, 3) AS UNSIGNED), 6, '0')),
       a.`idActividad`, a.`idEmpleado`
FROM `actividad` a;

INSERT IGNORE INTO `actividaddocente` (`idActividadDocente`, `idActividad`, `idDocente`)
SELECT CONCAT('AD', LPAD(CAST(SUBSTRING(a.`idActividad`, 3) AS UNSIGNED), 6, '0')),
       a.`idActividad`, a.`idDocente`
FROM `actividad` a;
