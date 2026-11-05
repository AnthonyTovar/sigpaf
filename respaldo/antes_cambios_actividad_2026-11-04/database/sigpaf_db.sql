
CREATE DATABASE IF NOT EXISTS sigpaf_DB 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sigpaf_DB;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ====================================================
--  NIVEL 1: CATÁLOGOS INDEPENDIENTES
-- ====================================================

-- Tabla: cargo
-- Cargos que puede ocupar un empleado
DROP TABLE IF EXISTS cargo;
CREATE TABLE cargo(
    idCargo VARCHAR(8) NOT NULL PRIMARY KEY,
    nombreCargo VARCHAR(15) NOT NULL UNIQUE,
    descripcionCargo VARCHAR(500) NULL
);

-- Tabla: unidadEjecutora
-- Unidades administrativas responsables de actividades
DROP TABLE IF EXISTS unidadEjecutora;
CREATE TABLE unidadEjecutora(
    idUnidadEjecutora VARCHAR(8) NOT NULL PRIMARY KEY,
    nomUnidadEjecutora VARCHAR(50) NOT NULL,
    desUnidadEjecutora VARCHAR(500) NULL
);

-- Tabla: tipoUsuario
-- Roles de acceso al sistema
DROP TABLE IF EXISTS tipoUsuario;
CREATE TABLE tipoUsuario(
    idTipoUsuario VARCHAR(8) NOT NULL PRIMARY KEY,
    rolUsuario VARCHAR(15) NOT NULL
);

-- Tabla: estado
-- Estados del país (división político-territorial)
DROP TABLE IF EXISTS estado;
CREATE TABLE estado(
    idEstado VARCHAR(8) NOT NULL PRIMARY KEY,
    nombreEstado VARCHAR(25) NOT NULL UNIQUE
);

-- Tabla: espacioUtilizar
-- Espacios físicos disponibles dentro de un lugar
DROP TABLE IF EXISTS espacioUtilizar;
CREATE TABLE espacioUtilizar(
    idEspacioUtilizar VARCHAR(8) NOT NULL PRIMARY KEY,
    nombreEspacioUtilizar VARCHAR(150) NOT NULL,
    descEspacio VARCHAR(255) NULL,
    capacidad INT(4) NOT NULL
);

-- Tabla: areaEspecifica
-- Áreas específicas de ejecución
DROP TABLE IF EXISTS areaEspecifica;
CREATE TABLE areaEspecifica(
    idAreaE VARCHAR(8) NOT NULL PRIMARY KEY,
    nomAreaE VARCHAR(50) NOT NULL UNIQUE
);

-- Tabla: vertice
-- Vértices del plan nacional de ciencia y tecnología
DROP TABLE IF EXISTS vertice;
CREATE TABLE vertice(
    idVertice VARCHAR(8) NOT NULL PRIMARY KEY,
    nombreVertice VARCHAR(150) NOT NULL UNIQUE,
    descVertice VARCHAR(250) NOT NULL
);

-- Tabla: grupoEtario
-- Rangos de edad de la población atendida
DROP TABLE IF EXISTS grupoEtario;
CREATE TABLE grupoEtario(
    idGrupoEtareo VARCHAR(8) NOT NULL PRIMARY KEY,
    nomGrupoEtareo VARCHAR(25) NOT NULL UNIQUE,
    edadMin INT(4) NOT NULL,
    edadMax INT(4) NOT NULL,
    descGrupoEtareo VARCHAR(250) NULL    
);

-- Tabla: tipoActividad
-- Clasificación de las actividades
DROP TABLE IF EXISTS tipoActividad;
CREATE TABLE tipoActividad(
    idTipoActividad VARCHAR(8) NOT NULL PRIMARY KEY,
    nomTipoActividad VARCHAR(50) NOT NULL UNIQUE,
    descTipoActividad VARCHAR(250) NULL
);

-- Tabla: unidadMedida
-- Unidades para cuantificar las entregas
DROP TABLE IF EXISTS unidadMedida;
CREATE TABLE unidadMedida(
    idUnidadMedida VARCHAR(8) NOT NULL PRIMARY KEY,
    nomUnidadMedida VARCHAR(50) NOT NULL UNIQUE,
    descUnidadMedida VARCHAR(250) NULL
);

-- Tabla: estatus
-- Ciclo de vida de una actividad
DROP TABLE IF EXISTS estatus;
CREATE TABLE estatus(
    idEstatus VARCHAR(8) NOT NULL PRIMARY KEY,
    nomEstatus VARCHAR(50) NOT NULL UNIQUE,
    descEstatus VARCHAR(250) NULL
);

-- Tabla: horario
-- Bloques de horario disponibles
DROP TABLE IF EXISTS horario;
CREATE TABLE horario(
    idHorario VARCHAR(8) NOT NULL PRIMARY KEY,
    nomHorario VARCHAR(50) NOT NULL UNIQUE
);

-- Tabla: tipoEntrega
-- Tipos de entrega o producto de una sesión
DROP TABLE IF EXISTS tipoEntrega;
CREATE TABLE tipoEntrega(
    idTipEntrega VARCHAR(8) NOT NULL PRIMARY KEY,
    nomTipEntrega VARCHAR(50) NOT NULL UNIQUE
);

-- Tabla: grupoEtnio
-- Grupos étnicos de la población atendida
DROP TABLE IF EXISTS grupoEtnio;
CREATE TABLE grupoEtnio(
    idGrupoEtnio VARCHAR(8) NOT NULL PRIMARY KEY,
    nomGrupoEtnio VARCHAR(50) NOT NULL UNIQUE,
    desGrupoEtnio VARCHAR(500) NULL
);

-- Tabla: docente
-- Docentes o facilitadores de las actividades
DROP TABLE IF EXISTS docente;
CREATE TABLE docente(
    idDocente VARCHAR(8) NOT NULL PRIMARY KEY,
    cedDocente VARCHAR(9) NOT NULL UNIQUE,
    nacionalidad VARCHAR(15) NOT NULL,
    nombreDocente VARCHAR(50) NOT NULL,
    apellidoDocente VARCHAR(50) NOT NULL,
    telfDocente VARCHAR(20) NULL
);

-- Tabla: estrategiaDesarrollo
-- Estrategias del plan de desarrollo nacional
DROP TABLE IF EXISTS estrategiaDesarrollo;
CREATE TABLE estrategiaDesarrollo(
    idEstDesarrollo VARCHAR(8) NOT NULL PRIMARY KEY,
    nomEstDesarrollo VARCHAR(50) NOT NULL UNIQUE,
    descEstDesarrollo VARCHAR(500) NULL
);

-- Tabla: beneficiario
-- Personas que participan en las actividades
DROP TABLE IF EXISTS beneficiario;
CREATE TABLE beneficiario(
    idBeneficiario VARCHAR(8) NOT NULL PRIMARY KEY,
    nombresBeneficiario VARCHAR(250) NOT NULL,
    apellidosBeneficiario VARCHAR(250) NOT NULL,
    cedulaBeneficiario VARCHAR(9) NOT NULL,
    nacionalidad VARCHAR(15) NOT NULL,
    fechaNacBeneficiario DATE NOT NULL,
    sexoBeneficiario VARCHAR(4) NOT NULL
);


-- ====================================================
--  NIVEL 2: TABLAS CON DEPENDENCIAS SIMPLES
-- ====================================================

-- Tabla: empleado
-- Personal de la institución (depende de cargo y unidadEjecutora)
DROP TABLE IF EXISTS empleado;
CREATE TABLE empleado(
    idEmpleado VARCHAR(8) NOT NULL PRIMARY KEY,
    cedulaEmpleado VARCHAR(9) NOT NULL UNIQUE,
    nacionalidad VARCHAR(15) NOT NULL, 
    nombres VARCHAR(40) NOT NULL,
    apellidos VARCHAR(40) NOT NULL,
    fechaNacimiento DATE NOT NULL,
    telefonoEmpleado VARCHAR(12) NULL UNIQUE,
    correoEmpleado VARCHAR(150) NULL UNIQUE,
    idCargo VARCHAR(8) NOT NULL,
    idUnidadEjecutora VARCHAR(8) NOT NULL,
    
    FOREIGN KEY (idCargo) REFERENCES cargo(idCargo),
    FOREIGN KEY (idUnidadEjecutora) REFERENCES unidadEjecutora(idUnidadEjecutora)
);

-- Tabla: usuarios
-- Cuentas de acceso al sistema (depende de tipoUsuario y empleado)
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios(
    idUsuario VARCHAR(8) NOT NULL PRIMARY KEY,
    nombreUsuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    idTipoUsuario VARCHAR(8) NOT NULL,
    idEmpleado VARCHAR(8) NOT NULL,

    FOREIGN KEY (idTipoUsuario) REFERENCES tipoUsuario(idTipoUsuario),
    FOREIGN KEY (idEmpleado) REFERENCES empleado(idEmpleado)
);

-- Tabla: municipio
-- Municipios de un estado (depende de estado)
DROP TABLE IF EXISTS municipio;
CREATE TABLE municipio(
    idMunicipio VARCHAR(8) NOT NULL PRIMARY KEY,
    nombreMunicipio VARCHAR(25) NOT NULL,
    idEstado VARCHAR(8) NOT NULL,

    FOREIGN KEY (idEstado) REFERENCES estado(idEstado)
);

-- Tabla: parroquia
-- Parroquias de un municipio (depende de municipio)
DROP TABLE IF EXISTS parroquia;
CREATE TABLE parroquia(
    idParroquia VARCHAR(8) NOT NULL PRIMARY KEY,
    nombreParroquia VARCHAR(25) NOT NULL,
    idMunicipio VARCHAR(8) NOT NULL,

    FOREIGN KEY (idMunicipio) REFERENCES municipio(idMunicipio)
);

-- Tabla: lugarActividad
-- Lugares donde se realizan las actividades (depende de parroquia)
DROP TABLE IF EXISTS lugarActividad;
CREATE TABLE lugarActividad(
    idLugarActividad VARCHAR(8) NOT NULL PRIMARY KEY,
    nomLugarActividad VARCHAR(100) NOT NULL UNIQUE,
    desLugarActividad VARCHAR(255) NULL,
    direccion VARCHAR(255) NOT NULL,
    esSede BOOLEAN,
    idParroquia VARCHAR(8) NOT NULL,

    FOREIGN KEY (idParroquia) REFERENCES parroquia(idParroquia)
);


-- ====================================================
--  NIVEL 3: TABLA CENTRAL
-- ====================================================

-- Tabla: actividad
-- Actividades planificadas, tabla central del sistema
DROP TABLE IF EXISTS actividad;
CREATE TABLE actividad(
    idActividad VARCHAR(8) NOT NULL PRIMARY KEY,
    nombreActividad VARCHAR(250) NOT NULL,
    fechainicioActividad DATE NOT NULL,
    fechafinActividad DATE NOT NULL,
    cantSesionesPlanificada INT(4) NOT NULL,
    objetivoActividad VARCHAR(250) NOT NULL,
    descActividad VARCHAR(250) NOT NULL,
    cantPersoAtender INT(5) NOT NULL,
    observacion VARCHAR(500) NOT NULL,    
    idTipoActividad VARCHAR(8) NOT NULL,
    idVertice VARCHAR(8) NOT NULL,
    idAreaE VARCHAR(8) NOT NULL,
    idEmpleado VARCHAR(8) NOT NULL,
    idUnidadMedida VARCHAR(8) NOT NULL,
    idGrupoEtnio VARCHAR(8) NOT NULL,   
    idDocente VARCHAR(8) NOT NULL,    
    idEstatus VARCHAR(8) NOT NULL,
    idHorario VARCHAR(8) NOT NULL,    
    idEstDesarrollo VARCHAR(8) NOT NULL,

    FOREIGN KEY (idTipoActividad) REFERENCES tipoActividad(idTipoActividad),
    FOREIGN KEY (idVertice) REFERENCES vertice(idVertice),
    FOREIGN KEY (idAreaE) REFERENCES areaEspecifica(idAreaE),
    FOREIGN KEY (idEmpleado) REFERENCES empleado(idEmpleado),
    FOREIGN KEY (idUnidadMedida) REFERENCES unidadMedida(idUnidadMedida),
    FOREIGN KEY (idGrupoEtnio) REFERENCES grupoEtnio(idGrupoEtnio),   
    FOREIGN KEY (idDocente) REFERENCES docente(idDocente),    
    FOREIGN KEY (idEstatus) REFERENCES estatus(idEstatus),
    FOREIGN KEY (idHorario) REFERENCES horario(idHorario),    
    FOREIGN KEY (idEstDesarrollo) REFERENCES estrategiaDesarrollo(idEstDesarrollo)
);


-- ====================================================
--  NIVEL 4: TABLAS PUENTE Y DE SEGUIMIENTO
-- ====================================================

-- Tabla: actividadHorario (puente)
-- Horarios asignados a cada actividad (relación N:M)
DROP TABLE IF EXISTS actividadHorario;
CREATE TABLE actividadHorario(
    idActividadHorario VARCHAR(8) NOT NULL PRIMARY KEY,
    idActividad VARCHAR(8) NOT NULL,
    idHorario VARCHAR(8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (idActividad, idHorario),
    FOREIGN KEY (idActividad) REFERENCES actividad(idActividad) ON DELETE CASCADE,
    FOREIGN KEY (idHorario) REFERENCES horario(idHorario)
);

-- Tabla: actividadEmpleado (puente)
-- Empleados responsables de cada actividad (relación N:M)
DROP TABLE IF EXISTS actividadEmpleado;
CREATE TABLE actividadEmpleado(
    idActividadEmpleado VARCHAR(8) NOT NULL PRIMARY KEY,
    idActividad VARCHAR(8) NOT NULL,
    idEmpleado VARCHAR(8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (idActividad, idEmpleado),
    FOREIGN KEY (idActividad) REFERENCES actividad(idActividad) ON DELETE CASCADE,
    FOREIGN KEY (idEmpleado) REFERENCES empleado(idEmpleado)
);

-- Tabla: actividadDocente (puente)
-- Docentes asignados a cada actividad (relación N:M)
DROP TABLE IF EXISTS actividadDocente;
CREATE TABLE actividadDocente(
    idActividadDocente VARCHAR(8) NOT NULL PRIMARY KEY,
    idActividad VARCHAR(8) NOT NULL,
    idDocente VARCHAR(8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (idActividad, idDocente),
    FOREIGN KEY (idActividad) REFERENCES actividad(idActividad) ON DELETE CASCADE,
    FOREIGN KEY (idDocente) REFERENCES docente(idDocente)
);

-- Tabla: lugarRealizaActividad (puente)
-- Lugar y espacio donde se ejecuta cada actividad
DROP TABLE IF EXISTS lugarRealizaActividad;
CREATE TABLE lugarRealizaActividad(
    idReaActividad VARCHAR(8) NOT NULL PRIMARY KEY,
    idEspacioUtilizar VARCHAR(8) NULL,
    idLugarActividad VARCHAR(8) NOT NULL,
    idActividad VARCHAR(8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (idEspacioUtilizar) REFERENCES espacioUtilizar(idEspacioUtilizar),
    FOREIGN KEY (idLugarActividad) REFERENCES lugarActividad(idLugarActividad),
    FOREIGN KEY (idActividad) REFERENCES actividad(idActividad)
);

-- Tabla: grupoEtarioActividad (puente)
-- Grupos etarios atendidos por cada actividad (relación N:M)
DROP TABLE IF EXISTS grupoEtarioActividad;
CREATE TABLE grupoEtarioActividad(
    idGrupoEtareoActividad VARCHAR(8) NOT NULL PRIMARY KEY,
    idGrupoEtareo VARCHAR(8) NOT NULL,
    idActividad VARCHAR(8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (idGrupoEtareo) REFERENCES grupoEtario(idGrupoEtareo),
    FOREIGN KEY (idActividad) REFERENCES actividad(idActividad)
);

-- Tabla: seguimientoActividad
-- Registro de cada sesión ejecutada de una actividad
DROP TABLE IF EXISTS seguimientoActividad;
CREATE TABLE seguimientoActividad(
    idSegActividad VARCHAR(8) NOT NULL PRIMARY KEY,
    nroSesionPlanificada INT(4) NOT NULL,
    fechaSesion DATE NOT NULL,
    logroActividad VARCHAR(500) NOT NULL,
    observObstaculo VARCHAR(500) NOT NULL,
    idActividad VARCHAR(8) NOT NULL,
    idTipEntrega VARCHAR(8) NOT NULL,

    FOREIGN KEY (idActividad) REFERENCES actividad(idActividad),
    FOREIGN KEY (idTipEntrega) REFERENCES tipoEntrega(idTipEntrega)
);

-- Tabla: asistencia (puente)
-- Beneficiarios presentes en cada sesión de seguimiento
DROP TABLE IF EXISTS asistencia;
CREATE TABLE asistencia(
    idAsistencia VARCHAR(8) NOT NULL PRIMARY KEY,
    idSegActividad VARCHAR(8) NOT NULL,
    idBeneficiario VARCHAR(8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (idSegActividad) REFERENCES seguimientoActividad(idSegActividad),
    FOREIGN KEY (idBeneficiario) REFERENCES beneficiario(idBeneficiario)
);


-- ====================================================
--  NIVEL 5: TABLAS DEL SISTEMA
-- ====================================================

-- Tabla: sesiones_activas
-- Control de sesión única por usuario
DROP TABLE IF EXISTS sesiones_activas;
CREATE TABLE sesiones_activas(
    idSesiones INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id VARCHAR(8) NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultima_actividad DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_usuario (usuario_id),
    INDEX idx_session_id (session_id),
    INDEX idx_ultima_actividad (ultima_actividad),

    FOREIGN KEY (usuario_id) REFERENCES usuarios(idUsuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: bitacora
-- Registro de auditoría de todas las acciones del sistema
DROP TABLE IF EXISTS bitacora;
CREATE TABLE bitacora(
    idBitacora VARCHAR(8) NOT NULL PRIMARY KEY,
    usuario_id VARCHAR(8) NULL,
    usuario_nombre VARCHAR(50) NOT NULL,
    accion ENUM('LOGIN','LOGOUT','CREATE','UPDATE','DELETE','ACCESS_DENIED','GENERAR','EXPORTAR_BD','IMPORTAR_BD') NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    registro_id VARCHAR(8) NULL,
    descripcion VARCHAR(500) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_bit_usuario (usuario_id),
    INDEX idx_bit_fecha (fecha_hora),
    INDEX idx_bit_accion (accion),

    FOREIGN KEY (usuario_id) REFERENCES usuarios(idUsuario)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  DATOS INICIALES
-- ============================================================

-- ====================================================
-- INSERTS PARA LA TABLA: tipoUsuario
-- ====================================================

-- Roles del sistema (jerarquía de Fundacite)
INSERT INTO tipoUsuario (idTipoUsuario, rolUsuario) VALUES 
('Rol0001', 'Super Usuario'),
('Rol0002', 'Administrador'),
('Rol0003', 'Usuario Comun');

-- ====================================================
-- INSERTS PARA LA TABLA: cargo
-- ====================================================

INSERT INTO cargo (idCargo, nombreCargo, descripcionCargo) VALUES 
('CR0001', 'Super Usuario', 'Acceso total al sistema SIGPAF'),
('CR0002', 'Analista', 'Responsable del análisis de datos, seguimiento de actividades y generación de reportes.'),
('CR0003', 'Secretario', 'Encargado de la gestión administrativa, recepción de recaudos y archivo de documentación.'),
('CR0004', 'Director', 'Supervisor general del área, encargado de la toma de decisiones estratégicas y aprobación de planes.'),
('CR0005', 'Gerente', 'Coordinador operativo de los recursos, planificación de presupuestos y enlace institucional.'),
('CR0006', 'Coordinador', 'Responsable directo de la ejecución de proyectos, asignación de tareas de campo y contacto comunitario.'),
('CR0007', 'AAA', 'AAA'),
('CR0008', 'asdakjsd', 'askjdasjkds'),
('CR0009', 'Analistaa', 'sas');

-- ====================================================
-- INSERTS PARA LA TABLA: unidadEjecutora
-- ====================================================

INSERT INTO unidadEjecutora (idUnidadEjecutora, nomUnidadEjecutora, desUnidadEjecutora) VALUES 
('UNE0001', 'SuperU', 'Super Usuario Con Todos los Permisos'),
('UNE0002', 'TIC', 'Unidad de Tecnologías de la Información y Comunicación'),
('UNE0003', 'Admin y Finanzas', 'Departamento de Administración, Presupuesto, Compras y Gestión Financiera'),
('UNE0004', 'Atención Ciudadana', 'Oficina de Vinculación Comunitaria, Atención al Ciudadano y Gestión Social'),
('UNE0005', 'Talento Humano', 'Departamento de Recursos Humanos, Nómina y Bienestar Laboral');

-- ====================================================
-- INSERTS PARA LA TABLA: estado
-- ====================================================

INSERT INTO estado (idEstado, nombreEstado) VALUES 
('EST0001', 'Yaracuy'),
('EST0002', 'Caracas');

-- ====================================================
-- INSERTS PARA LA TABLA: municipio
-- ====================================================

INSERT INTO municipio (idMunicipio, nombreMunicipio, idEstado) VALUES 
('MUN0001', 'San Felipe', 'EST0001'),
('MUN0002', 'Independencia', 'EST0001'),
('MUN0003', 'Cocorote', 'EST0001'),
('MUN0004', 'Bruzual', 'EST0001'),
('MUN0005', 'Nirgua', 'EST0001'),
('MUN0006', 'Peña', 'EST0001'),
('MUN0007', 'Arístides Bastidas', 'EST0001'),
('MUN0008', 'Bolívar', 'EST0001'),
('MUN0009', 'José Antonio Páez', 'EST0001'),
('MUN0010', 'La Trinidad', 'EST0001'),
('MUN0011', 'Manuel Monge', 'EST0001'),
('MUN0012', 'Sucre', 'EST0001'),
('MUN0013', 'Urachiche', 'EST0001'),
('MUN0014', 'Veroes', 'EST0001');

-- ====================================================
-- INSERTS PARA LA TABLA: parroquia
-- ====================================================

-- Parroquias del Municipio San Felipe (MUN0001)
INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES 
('PR0001', 'San Felipe', 'MUN0001'),
('PR0002', 'Albarico', 'MUN0001'),
('PR0003', 'San Javier', 'MUN0001');

-- Parroquias del Municipio Independencia (MUN0002)
INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES 
('PR0004', 'Independencia', 'MUN0002');

-- Parroquias del Municipio Cocorote (MUN0003)
INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES 
('PR0005', 'Cocorote', 'MUN0003');

-- Parroquias del Municipio Bruzual (MUN0004)
INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES 
('PR0006', 'Chivacoa', 'MUN0004'),
('PR0007', 'Campo Elías', 'MUN0004');

-- Parroquias del Municipio Nirgua (MUN0005)
INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES 
('PR0008', 'Nirgua', 'MUN0005'),
('PR0009', 'Salóm', 'MUN0005'),
('PR0010', 'Temerla', 'MUN0005');

-- Parroquias del Municipio Peña (MUN0006)
INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES 
('PR0011', 'Yaritagua', 'MUN0006'),
('PR0012', 'San Andrés', 'MUN0006');

-- Parroquias del Municipio Arístides Bastidas (MUN0007)
INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES 
('PR0013', 'San Pablo', 'MUN0007');

-- Parroquias del Municipio Bolívar (MUN0008)
INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES 
('PR0014', 'Aroa', 'MUN0008');

-- Parroquias del Municipio José Antonio Páez (MUN0009)
INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES 
('PR0015', 'Sabana de Parra', 'MUN0009');

-- Parroquias del Municipio La Trinidad (MUN0010)
INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES 
('PR0016', 'Boraure', 'MUN0010');

-- Parroquias del Municipio Manuel Monge (MUN0011)
INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES 
('PR0017', 'Yumare', 'MUN0011');

-- Parroquias del Municipio Sucre (MUN0012)
INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES 
('PR0018', 'Guama', 'MUN0012');

-- Parroquias del Municipio Urachiche (MUN0013)
INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES 
('PR0019', 'Urachiche', 'MUN0013');

-- Parroquias del Municipio Veroes (MUN0014)
INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES 
('PR0020', 'Farriar', 'MUN0014'),
('PR0021', 'El Guayabo', 'MUN0014');

-- ====================================================
-- INSERTS PARA LA TABLA: espacioUtilizar
-- ====================================================

INSERT INTO espacioUtilizar (idEspacioUtilizar, nombreEspacioUtilizar, descEspacio, capacidad) VALUES 
('EU0001', 'Auditorio Principal', 'Espacio para ponencias, charlas magistrales y eventos masivos', 50),
('EU0002', 'Laboratorio de Computación / Informática', 'Área equipada con computadoras para talleres prácticos y desarrollo de software', 10),
('EU0003', 'Salón de Usos Múltiples (SUM)', 'Espacio versátil para reuniones, mesas de trabajo y dinámicas grupales', 40),
('EU0004', 'Aula de Clases Estándar', 'Salón académico para formaciones teóricas y cursos breves', 35),
('EU0005', 'Laboratorio de Ciencias / Química', 'Espacio acondicionado para experimentos prácticos de química y biología', 20),
('EU0006', 'Patio Central / Espacio Abierto', 'Área al aire libre ideal para ferias de ciencia y exhibiciones de proyectos', 60);

-- ====================================================
-- INSERTS PARA LA TABLA: areaEspecifica
-- ====================================================

INSERT INTO areaEspecifica (idAreaE, nomAreaE) VALUES 
('AE0001', 'AE1'),
('AE0002', 'AE2'),
('AE0003', 'AE3'),
('AE0004', 'AE4'),
('AE0005', 'AE5');

-- ====================================================
-- INSERTS PARA LA TABLA: vertice
-- ====================================================

INSERT INTO vertice (idVertice, nombreVertice, descVertice) VALUES 
('VER0001', 'Siembra y preservación del talento científico nacional', 'Formación, capacitación y ejercicio ético-científico en niños, jóvenes y adultos para asegurar el relevo generacional'),
('VER0002', 'Fortalecimiento de espacios para la investigación, desarrollo e innovación', 'Modernización y equipamiento de laboratorios, centros de investigación y espacios académicos para la producción científica'),
('VER0003', 'Ciencia, tecnología e innovación para la producción y la economía', 'Uso y aplicación del conocimiento científico para potenciar los encadenamientos productivos y el desarrollo económico del país'),
('VER0004', 'Venezuela hacia la transformación digital', 'Impulso y vanguardia en el desarrollo de software y hardware libre, ciberseguridad y soberanía tecnológica digital'),
('VER0005', 'Cooperación y alianzas internacionales', 'Fomento de alianzas estratégicas y redes de innovación y de investigación científica para profundizar la cooperación global'),
('VER0006', 'JJJ', 'NLNJJJJ');

-- ====================================================
-- INSERTS PARA LA TABLA: grupoEtario
-- ====================================================

INSERT INTO grupoEtario (idGrupoEtareo, nomGrupoEtareo, edadMin, edadMax, descGrupoEtareo) VALUES 
('GE0001', 'De 0 a 5 años', 0, 5, 'Población infantil de 0 a 5 años de edad'),
('GE0002', 'De 6 a 11 años', 6, 11, 'Población infantil de 6 a 11 años de edad'),
('GE0003', 'De 12 a 17 años', 12, 17, 'Población adolescente de 12 a 17 años de edad'),
('GE0004', 'De 18 a 23 años', 18, 23, 'Población juvenil de 18 a 23 años de edad'),
('GE0005', 'De 24 a 29 años', 24, 29, 'Población joven/adulta de 24 a 29 años de edad'),
('GE0006', 'De 30 a 35 años', 30, 35, 'Población adulta de 30 a 35 años de edad'),
('GE0007', 'De 36 a 41 años', 36, 41, 'Población adulta de 36 a 41 años de edad'),
('GE0008', 'De 42 a 47 años', 42, 47, 'Población adulta de 42 a 47 años de edad'),
('GE0009', 'De 48 a 53 años', 48, 53, 'Población adulta de 48 a 53 años de edad'),
('GE0010', 'De 54 a 59 años', 54, 59, 'Población adulta de 54 a 59 años de edad'),
('GE0011', 'De 60 a más', 60, 120, 'Población adulta mayor de 60 años en adelante');

-- ====================================================
-- INSERTS PARA LA TABLA: tipoActividad
-- ====================================================

INSERT INTO tipoActividad (idTipoActividad, nomTipoActividad, descTipoActividad) VALUES 
('TA0001', 'Abordaje', 'Visitas y acercamientos iniciales a comunidades o instituciones'),
('TA0002', 'Acompañamiento', 'Asistencia continua en el desarrollo de proyectos o procesos'),
('TA0003', 'Apoyo interinstitucional', 'Cooperación y soporte técnico/operativo a otras entidades públicas'),
('TA0004', 'Asesorías', 'Orientación especializada en materia científica, tecnológica o de proyectos'),
('TA0005', 'Capacitación', 'Talleres y cursos de formación técnica y profesional'),
('TA0006', 'Caracterización', 'Estudios de diagnóstico y recolección de datos en unidades territoriales'),
('TA0007', 'Cayapa heroica', 'Plan nacional de recuperación y mantenimiento de equipos médicos y tecnológicos'),
('TA0008', 'Desarrollo de software', 'Diseño, programación e implementación de sistemas informáticos soberanos'),
('TA0009', 'Divulgación y difusión', 'Actividades de socialización del conocimiento y eventos comunicacionales'),
('TA0010', 'Elaboración de documento técnico', 'Redacción de manuales, informes especializados y guías metodológicas'),
('TA0011', 'Ensayo', 'Pruebas técnicas y experimentación científica'),
('TA0012', 'Evento', 'Organización de ferias, congresos, simposios o encuentros institucionales'),
('TA0013', 'Formación', 'Procesos de enseñanza académica o comunitaria a largo plazo'),
('TA0014', 'Implementación de sistema o aplicativo', 'Instalación y puesta en marcha de herramientas de software en entornos reales'),
('TA0015', 'Investigación', 'Estudios y proyectos de desarrollo científico puro o aplicado'),
('TA0016', 'Publicación realizada', 'Artículos, libros o notas de prensa científicas validadas'),
('TA0017', 'Soporte tecnológico', 'Asistencia técnica en hardware, redes y conectividad'),
('TA0018', 'Otro', 'Cualquier otra actividad no clasificada en los tipos anteriores');

-- ====================================================
-- INSERTS PARA LA TABLA: unidadMedida
-- ====================================================

INSERT INTO unidadMedida (idUnidadMedida, nomUnidadMedida, descUnidadMedida) VALUES 
('UM0001', 'Taller', 'Actividad de formación práctica o educativa grupal'),
('UM0002', 'Reunión', 'Encuentro formal de trabajo o articulación con actores clave'),
('UM0003', 'Inspección', 'Visita técnica de supervisión o verificación en campo'),
('UM0004', 'Asistencia Técnica', 'Soporte directo brindado a usuarios, instituciones o productores'),
('UM0005', 'Informe', 'Documento técnico, operativo o administrativo generado'),
('UM0006', 'Unidad', 'Medida general para cuantificar entregas de bienes o sistemas'),
('UM0007', 'Planilla', 'Fichas, registros o instrumentos de control completados');

-- ====================================================
-- INSERTS PARA LA TABLA: estatus
-- ====================================================

INSERT INTO estatus (idEstatus, nomEstatus, descEstatus) VALUES 
('ES0001', 'Planificada', 'La actividad ha sido registrada en la planificación inicial pero aún no ha iniciado'),
('ES0002', 'En espera', 'La actividad se encuentra temporalmente pausada o aguardando condiciones para iniciar'),
('ES0003', 'En ejecución', 'La actividad se encuentra actualmente en desarrollo y ejecución de sus sesiones'),
('ES0004', 'Replanificada', 'La actividad sufrió modificaciones en sus fechas, lugar o planificación original'),
('ES0005', 'Cancelada', 'La actividad fue suspendida definitivamente y no se llevará a cabo'),
('ES0006', 'Finalizada', 'La actividad concluyó con éxito y se ejecutaron todas sus sesiones planeadas');

-- ====================================================
-- INSERTS PARA LA TABLA: horario
-- ====================================================

INSERT INTO horario (idHorario, nomHorario) VALUES 
('HO0001', '08:00 AM - 10:00 AM'),
('HO0002', '10:00 AM - 12:00 PM'),
('HO0003', '01:00 PM - 02:00 PM'),
('HO0004', '02:00 PM - 04:00 PM'),
('HO0008', '08:00 AM - 12:00 PM'),
('HO0009', '01:00 PM - 04:00 PM'),
('HO0010', '12:00 PM - 01:00 PM');

-- ====================================================
-- INSERTS PARA LA TABLA: tipoEntrega
-- ====================================================

INSERT INTO tipoEntrega (idTipEntrega, nomTipEntrega) VALUES 
('TE0001', 'Servicio de Formación / Capacitación'),
('TE0002', 'Kits de Robótica Educativa'),
('TE0003', 'Certificado de Participación / Aprobación'),
('TE0004', 'Servicio de Soporte y Mantenimiento Técnico'),
('TE0005', 'Equipo Tecnológico Recuperado (Cayapa Heroica)'),
('TE0006', 'Servicio de Asesoría Técnica de Proyectos'),
('TE0007', 'Material Divulgativo / Guía Técnica');

-- ====================================================
-- INSERTS PARA LA TABLA: grupoEtnio
-- ====================================================

INSERT INTO grupoEtnio (idGrupoEtnio, nomGrupoEtnio, desGrupoEtnio) VALUES 
('GN0001', 'No aplica / Ninguno', 'Población general que no se autoidentifica con un grupo étnico específico'),
('GN0002', 'Indígena', 'Población perteneciente a los pueblos y comunidades originarias'),
('GN0003', 'Afrodescendiente', 'Población de herencia y descendencia africana en el territorio nacional'),
('GN0004', 'Multiétnico / Otro', 'Comunidades con diversidad de raíces culturales u otros grupos étnicos específicos');

-- ====================================================
-- INSERTS PARA LA TABLA: docente
-- ====================================================

INSERT INTO docente (idDocente, cedDocente, nacionalidad, nombreDocente, apellidoDocente, telfDocente) VALUES 
('DC0001', 'V00000000', 'Venezolano', 'No', 'Aplica', '0000-0000000'),
('DC0002', 'V15421789', 'Venezolano', 'María Alejandra', 'Gómez Mendoza', '0412-5551234'),
('DC0003', 'V20114856', 'Venezolano', 'Carlos Eduardo', 'Rodríguez Plaza', '0416-9994567'),
('DC0004', 'E84215763', 'Extranjero', 'Jean Pierre', 'Dupond', '0414-7778901'),
('DC0005', 'V18956234', 'Venezolano', 'Yelitza Coromoto', 'Tovar Lovera', '0424-3335566');

-- ====================================================
-- INSERTS PARA LA TABLA: estrategiaDesarrollo
-- ====================================================

INSERT INTO estrategiaDesarrollo (idEstDesarrollo, nomEstDesarrollo, descEstDesarrollo) VALUES 
('ED0001', '1T: Transformación Económica', 'Modernización de los métodos y técnicas de producción para lograr la diversificación económica.'),
('ED0002', '2T: Independencia Plena', 'Expansión de la doctrina bolivariana en sus dimensiones científica, tecnológica, educativa y cultural.'),
('ED0003', '3T: Paz, Seguridad e Integridad Territorial', 'Perfeccionamiento del modelo de convivencia ciudadana, garantía de la justicia y resguardo de la soberanía.'),
('ED0004', '4T: Transformación Social', 'Renovación total del modelo de protección humanista, garantizando el bienestar de los sectores más vulnerables.'),
('ED0005', '5T: Transformación Política', 'Consolidación de la democracia directa con ética republicana y fortalecimiento del Poder Popular.'),
('ED0006', '6T: Transformación Ecológica', 'Combate al cambio climático, preservación del ambiente, protección de la biodiversidad y equilibrio ecológico.'),
('ED0007', '7T: Geopolítica', 'Inserción y liderazgo de Venezuela en el nuevo proceso de integración de los países y el mundo pluripolar.');


-- ====================================================
-- INSERTS PARA LA TABLA: lugarActividad
-- ====================================================

INSERT INTO lugarActividad (idLugarActividad, nomLugarActividad, desLugarActividad, direccion, esSede, idParroquia) VALUES 
('LA0001', 'Sede Fundacite Yaracuy', 'Oficinas administrativas principales de Fundacite Yaracuy', 'Av. Intercomunal José Antonio Páez, sector El Samán', TRUE, 'PR0001'),
('LA0002', 'U.E. Juan José de Maya', 'Institución educativa para actividades de formación y semilleros científicos', 'Av. Cartagena con Calle 19, San Felipe', FALSE, 'PR0001'),
('LA0003', 'Liceo Arístides Bastidas', 'Liceo público de educación media general', 'Av. 2 entre Calles 11 y 12, San Felipe', FALSE, 'PR0001'),
('LA0004', 'E.B. República de Nicaragua', 'Escuela básica del estado utilizada para rutas científicas', 'Av. Caracas entre Av. Yaracuy y Calle 15, San Felipe', FALSE, 'PR0001'),
('LA0005', 'Complejo Tecnológico Yaracuy', 'Espacio de desarrollo e innovación tecnológica regional', 'Zona Industrial Agustín Rivero, San Felipe', FALSE, 'PR0001'),
('LA0006', 'Plaza Bolívar de San Felipe', 'Espacio público abierto para ferias de divulgación científica', 'Av. Caracas entre Calles 6 y 7, San Felipe', FALSE, 'PR0001'),
('LA0007', 'TANGAMANDAPIA', 'DASDS', 'ADSD', FALSE, 'PR0020');

-- ====================================================
-- INSERTS PARA LA TABLA: empleado
-- ====================================================

-- Empleado asociado al Super Usuario
INSERT INTO empleado (idEmpleado, cedulaEmpleado, nacionalidad, nombres, apellidos, fechaNacimiento, telefonoEmpleado, correoEmpleado, idCargo, idUnidadEjecutora) VALUES 
('EM0001', 'V00000000', 'Venezolano', 'Super', 'Usuario', '1990-01-01', '0000-0000000', 'super@usuario.com', 'CR0001', 'UNE0001'),
('EM0002', '18543210', 'Venezolano', 'Andrés Eloy', 'Blanco Ortega', '1988-05-14', '0412-1234567', 'andres.blanco@fundacite.gob.ve', 'CR0004', 'UNE0002'),
('EM0003', '20123456', 'Venezolano', 'Gabriela Valentina', 'Méndez Rojas', '1992-10-22', '0416-7654321', 'gabriela.mendez@fundacite.gob.ve', 'CR0006', 'UNE0002'),
('EM0004', '15987654', 'Venezolano', 'Francisco Javier', 'Sánchez Colmenares', '1983-03-08', '0414-9876543', 'francisco.sanchez@fundacite.gob.ve', 'CR0005', 'UNE0002'),
('EM0005', '22345678', 'Venezolano', 'Adriana Carolina', 'Pérez Sequera', '1995-07-30', '0424-4567890', 'adriana.perez@fundacite.gob.ve', 'CR0002', 'UNE0002'),
('EM0006', '24111222', 'Venezolano', 'José Gregorio', 'Giménez Castillo', '1997-12-05', '0412-0001122', 'jose.gimenez@fundacite.gob.ve', 'CR0003', 'UNE0003');

-- ====================================================
-- INSERTS PARA LA TABLA: usuarios
-- ====================================================

-- Usuario Super (Login: Super / Pass: SuperU2026)
INSERT INTO usuarios (idUsuario, nombreUsuario, contrasena, created_at, idTipoUsuario, idEmpleado) VALUES 
('Us0001', 'Super', '$2a$12$6Ok0TmT9TLQm9Mo3j/B83.8DN0sR0GZkC/w4rqiT7Ma9pS38HNL9u', '2026-09-26 22:56:41', 'Rol0001', 'EM0001'),
('US0002', 'andres', '$2y$10$6G.8j8dt9pseGQgiW8tn6.FyWz56xxXVta/jylVNuf4wWInu9ZW9y', '2026-09-26 22:56:41', 'Rol0002', 'EM0002'),
('US0003', 'gabriela_user', '$2a$12$y/odPJdXnMdTnYAJXyULwuX.d94kPS3rEVWapyCrfCato1nZDMw0K', '2026-09-26 22:56:41', 'Rol0003', 'EM0003');

-- ====================================================
-- INSERTS PARA LA TABLA: actividad
-- ====================================================

INSERT INTO actividad (idActividad, nombreActividad, fechainicioActividad, fechafinActividad, cantSesionesPlanificada, objetivoActividad, descActividad, cantPersoAtender, observacion, idTipoActividad, idVertice, idAreaE, idEmpleado, idUnidadMedida, idGrupoEtnio, idDocente, idEstatus, idHorario, idEstDesarrollo) VALUES 
('AC0001', 'IHSNDHSNDS', '2026-10-28', '2026-10-31', 1, 'SJNDJSnDKJsnd', 'asbjX jX \\S XHS', 11, 'AJDAKJDS', 'TA0011', 'VER0003', 'AE0002', 'EM0002', 'UM0007', 'GN0002', 'DC0004', 'ES0001', 'HO0002', 'ED0006');

-- ====================================================
-- INSERTS PARA LAS TABLAS PUENTE DE ACTIVIDAD
-- (Relaciones generadas por la migración)
-- ====================================================

INSERT INTO actividadHorario (idActividadHorario, idActividad, idHorario) VALUES 
('AH000001', 'AC0001', 'HO0002');

INSERT INTO actividadEmpleado (idActividadEmpleado, idActividad, idEmpleado) VALUES 
('AE000001', 'AC0001', 'EM0002');

INSERT INTO actividadDocente (idActividadDocente, idActividad, idDocente) VALUES 
('AD000001', 'AC0001', 'DC0004');

-- ====================================================
-- INSERTS PARA LA TABLA: lugarRealizaActividad
-- ====================================================

INSERT INTO lugarRealizaActividad (idReaActividad, idEspacioUtilizar, idLugarActividad, idActividad, created_at) VALUES 
('LR0001', NULL, 'LA0004', 'AC0001', '2026-10-26 10:07:16');

-- ====================================================
-- INSERTS PARA LA TABLA: grupoEtarioActividad
-- ====================================================

INSERT INTO grupoEtarioActividad (idGrupoEtareoActividad, idGrupoEtareo, idActividad, created_at) VALUES 
('GEA001', 'GE0003', 'AC0001', '2026-10-26 10:08:56');

-- ====================================================
-- INSERTS PARA LA TABLA: sesiones_activas
-- ====================================================

INSERT INTO sesiones_activas (idSesiones, usuario_id, session_id, ip_address, user_agent, fecha_inicio, ultima_actividad) VALUES 
(13, 'Us0001', '98oav1bfjt4q5b1j8tgcl1gke0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-26 17:50:15', '2026-10-26 17:53:51');

-- ====================================================
-- INSERTS PARA LA TABLA: bitacora
-- ====================================================

INSERT INTO bitacora (idBitacora, usuario_id, usuario_nombre, accion, modulo, registro_id, descripcion, ip, fecha_hora) VALUES 
('BIT0001', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-23 01:53:46'),
('BIT0002', 'Us0001', 'Super', 'UPDATE', 'Usuario', 'US0002', 'Actualizo el registro US0002 (andres)', '::1', '2026-10-23 01:55:23'),
('BIT0003', 'Us0001', 'Super', 'LOGOUT', 'Sesion', NULL, 'Cerro sesion en el sistema', '::1', '2026-10-23 01:55:28'),
('BIT0004', 'US0002', 'andres', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-23 01:55:41'),
('BIT0005', 'US0002', 'andres', 'LOGOUT', 'Sesion', NULL, 'Cerro sesion en el sistema', '::1', '2026-10-23 02:09:25'),
('BIT0006', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-23 02:09:35'),
('BIT0007', 'Us0001', 'Super', '', 'Configuracion', NULL, 'Descargo un respaldo de la base de datos (sigpaf_DB)', '::1', '2026-10-23 02:09:58'),
('BIT0008', 'Us0001', 'Super', '', 'Configuracion', NULL, 'Descargo un respaldo de la base de datos (sigpaf_DB)', '::1', '2026-10-23 02:18:26'),
('BIT0009', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-23 08:03:38'),
('BIT0010', 'Us0001', 'Super', '', 'Configuracion', NULL, 'Descargo un respaldo de la base de datos (sigpaf_DB)', '::1', '2026-10-23 08:08:28'),
('BIT0011', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-24 22:24:05'),
('BIT0012', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-24 22:45:45'),
('BIT0013', 'Us0001', 'Super', 'GENERAR', 'Configuracion', NULL, 'Generó PDF de Empleados', '::1', '2026-10-24 22:46:02'),
('BIT0014', 'Us0001', 'Super', 'GENERAR', 'Configuracion', NULL, 'Generó PDF de Empleados', '::1', '2026-10-24 22:46:16'),
('BIT0015', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-25 23:06:32'),
('BIT0016', 'Us0001', 'Super', 'CREATE', 'Estado', 'EST0002', 'Creo el registro EST0002 (Caracas)', '::1', '2026-10-25 23:43:58'),
('BIT0017', 'Us0001', 'Super', 'CREATE', 'Cargo', 'CR0009', 'Creo el registro CR0009 (Analistaa)', '::1', '2026-10-25 23:50:46'),
('BIT0018', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-26 09:31:42'),
('BIT0019', 'Us0001', 'Super', '', 'Configuracion', NULL, 'Descargo un respaldo de la base de datos (sigpaf_DB)', '::1', '2026-10-26 09:35:22'),
('BIT0020', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-26 10:04:56'),
('BIT0021', 'Us0001', 'Super', 'CREATE', 'Actividad', 'AC0001', 'Creo el registro AC0001 (IHSNDHSNDS)', '::1', '2026-10-26 10:07:16'),
('BIT0022', 'Us0001', 'Super', 'UPDATE', 'Actividad', 'AC0001', 'Actualizo el registro AC0001 (IHSNDHSNDS)', '::1', '2026-10-26 10:08:56'),
('BIT0023', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-26 12:42:21'),
('BIT0024', 'Us0001', 'Super', '', 'Configuracion', NULL, 'Restauro la base de datos desde un archivo .sql (89 sentencias ejecutadas)', '::1', '2026-10-26 13:11:28'),
('BIT0025', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-26 13:55:41'),
('BIT0026', 'Us0001', 'Super', '', 'Configuracion', NULL, 'Descargo un respaldo de la base de datos (sigpaf_DB) y se guardo una copia en respaldo/', '::1', '2026-10-26 13:55:45'),
('BIT0027', 'Us0001', 'Super', 'EXPORTAR_BD', 'Configuracion', NULL, 'Genero un respaldo de la base de datos (sigpaf_DB): sigpaf_respaldo_2026-10-26_14-34.sql', '::1', '2026-10-26 14:34:45'),
('BIT0028', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-26 17:50:15'),
('BIT0029', 'Us0001', 'Super', 'EXPORTAR_BD', 'Configuracion', NULL, 'Genero un respaldo de la base de datos (sigpaf_DB): sigpaf_respaldo_2026-10-26_17-53.sql', '::1', '2026-10-26 17:53:36');
