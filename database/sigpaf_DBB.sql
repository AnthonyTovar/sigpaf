CREATE DATABASE IF NOT EXISTS sigpaf_DB 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sigpaf_DB;

-- ====================================================
-- SIGPAF - FUNDACITE Yaracuy
-- Script canónico de la base de datos (formato original:
-- CREATE DATABASE + CREATE TABLE + INSERTs con banners)
-- Generado: 15/10/2026 02:34 AM desde la base de datos viva
-- ====================================================

CREATE TABLE cargo(
    idCargo VARCHAR(8) NOT NULL PRIMARY KEY,
    nombreCargo VARCHAR (15) NOT NULL UNIQUE,
    descripcionCargo VARCHAR (500) NULL
);

CREATE TABLE unidadEjecutora(
    idUnidadEjecutora VARCHAR(8) NOT NULL PRIMARY KEY,
    nomUnidadEjecutora VARCHAR(50) NOT NULL,
    desUnidadEjecutora VARCHAR (500) NULL
);

CREATE TABLE empleado(
    idEmpleado VARCHAR(8) NOT NULL PRIMARY KEY,
    cedulaEmpleado VARCHAR(9) NOT NULL UNIQUE,
    nacionalidad VARCHAR(15) NOT NULL, 
    nombres VARCHAR (40) NOT NULL,
    apellidos VARCHAR (40) NOT NULL,
    fechaNacimiento DATE NOT NULL,
    telefonoEmpleado VARCHAR(12) NULL UNIQUE,
    correoEmpleado VARCHAR(150) NULL UNIQUE,
    idCargo VARCHAR(8) NOT NULL,
    idUnidadEjecutora VARCHAR(8) NOT NULL,
    
    FOREIGN KEY (idCargo) REFERENCES cargo(idCargo),
    FOREIGN KEY (idUnidadEjecutora) REFERENCES unidadEjecutora(idUnidadEjecutora)
);

CREATE TABLE tipoUsuario(
    idTipoUsuario VARCHAR(8) NOT NULL PRIMARY KEY,
    rolUsuario VARCHAR(15) NOT NULL
);

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

CREATE TABLE estado(
    idEstado VARCHAR(8) NOT NULL PRIMARY KEY,
    nombreEstado VARCHAR(25) NOT NULL UNIQUE
);

CREATE TABLE municipio(
    idMunicipio VARCHAR(8) NOT NULL PRIMARY KEY,
    nombreMunicipio VARCHAR(25) NOT NULL UNIQUE,
    idEstado VARCHAR(8) NOT NULL,

    FOREIGN KEY (idEstado) REFERENCES estado(idEstado)
);

CREATE TABLE parroquia (
    idParroquia VARCHAR(8) NOT NULL PRIMARY KEY,
    nombreParroquia VARCHAR(25) NOT NULL UNIQUE,
    idMunicipio VARCHAR(8) NOT NULL,

    FOREIGN KEY (idMunicipio) REFERENCES municipio(idMunicipio)
);

CREATE TABLE lugarActividad(
    idLugarActividad VARCHAR(8) NOT NULL PRIMARY KEY,
    nomLugarActividad VARCHAR(100) NOT NULL UNIQUE,
    desLugarActividad VARCHAR(255) NULL,
    direccion VARCHAR(255) NOT NULL,
    esSede BOOLEAN,
    idParroquia VARCHAR(8) NOT NULL,

    FOREIGN KEY (idParroquia) REFERENCES parroquia(idParroquia)
);

CREATE TABLE espacioUtilizar(
    idEspacioUtilizar VARCHAR(8) NOT NULL PRIMARY KEY,
    nombreEspacioUtilizar VARCHAR(150) NOT NULL,
    descEspacio VARCHAR(255) NULL,
    capacidad INT(4) NOT NULL
);

CREATE TABLE areaEspecifica(
    idAreaE VARCHAR(8) NOT NULL PRIMARY KEY,
    nomAreaE VARCHAR(50) NOT NULL UNIQUE
    );

CREATE TABLE vertice(
    idVertice VARCHAR(8) NOT NULL PRIMARY KEY,
    nombreVertice VARCHAR(150) NOT NULL UNIQUE,
    descVertice VARCHAR(250) NOT NULL
);

CREATE TABLE grupoEtario(
    idGrupoEtareo VARCHAR(8) NOT NULL PRIMARY KEY,
    nomGrupoEtareo VARCHAR(25) NOT NULL UNIQUE,
    edadMin INT(4) NOT NULL,
    edadMax INT(4) NOT NULL,
    descGrupoEtareo VARCHAR(250) NULL    
);

CREATE TABLE tipoActividad(
    idTipoActividad VARCHAR(8) NOT NULL PRIMARY KEY,
    nomTipoActividad VARCHAR(50) NOT NULL UNIQUE,
    descTipoActividad VARCHAR(250) NULL
);

CREATE TABLE unidadMedida(
    idUnidadMedida VARCHAR(8) NOT NULL PRIMARY KEY,
    nomUnidadMedida VARCHAR(50) NOT NULL UNIQUE,
    descUnidadMedida VARCHAR(250) NULL
);

CREATE TABLE estatus(
    idEstatus VARCHAR(8) NOT NULL PRIMARY KEY,
    nomEstatus VARCHAR(50) NOT NULL UNIQUE,
    descEstatus VARCHAR(250) NULL
);

CREATE TABLE horario(
    idHorario VARCHAR(8) NOT NULL PRIMARY KEY,
    nomHorario VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE tipoEntrega(
    idTipEntrega VARCHAR(8) NOT NULL PRIMARY KEY,
    nomTipEntrega VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE grupoEtnio(
    idGrupoEtnio VARCHAR(8) NOT NULL PRIMARY KEY,
    nomGrupoEtnio VARCHAR(50) NOT NULL UNIQUE,
    desGrupoEtnio VARCHAR(500) NULL
);

CREATE TABLE docente(
    idDocente VARCHAR(8) NOT NULL PRIMARY KEY,
    cedDocente VARCHAR(9) NOT NULL UNIQUE,
    nacionalidad VARCHAR(15) NOT NULL,
    nombreDocente VARCHAR(50) NOT NULL,
    apellidoDocente VARCHAR(50) NOT NULL,
    telfDocente VARCHAR(20) NULL
);

CREATE TABLE estrategiaDesarrollo(
    idEstDesarrollo VARCHAR(8) NOT NULL PRIMARY KEY,
    nomEstDesarrollo VARCHAR(50) NOT NULL UNIQUE,
    descEstDesarrollo VARCHAR(500) NULL
);

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

CREATE TABLE grupoEtarioActividad(
    idGrupoEtareoActividad VARCHAR(8) NOT NULL PRIMARY KEY,
    idGrupoEtareo VARCHAR(8) NOT NULL,
    idActividad VARCHAR(8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (idGrupoEtareo) REFERENCES grupoEtario(idGrupoEtareo),
    FOREIGN KEY (idActividad) REFERENCES actividad(idActividad)
);

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

CREATE TABLE beneficiario(
    idBeneficiario VARCHAR(8) NOT NULL PRIMARY KEY,
    nombresBeneficiario VARCHAR(250) NOT NULL,
    apellidosBeneficiario VARCHAR(250) NOT NULL,
    cedulaBeneficiario VARCHAR(9) NOT NULL,
    nacionalidad VARCHAR(15) NOT NULL,
    fechaNacBeneficiario DATE NOT NULL,
    sexoBeneficiario VARCHAR(4) NOT NULL

);

CREATE TABLE Asistencia(
    idAsistencia VARCHAR(8) NOT NULL PRIMARY KEY,
    idSegActividad VARCHAR(8) NOT NULL,
    idBeneficiario VARCHAR(8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (idSegActividad) REFERENCES seguimientoActividad(idSegActividad),
    FOREIGN KEY (idBeneficiario) REFERENCES beneficiario(idBeneficiario)
);

CREATE TABLE sesiones_activas (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: bitacora
-- Registro de auditoría del sistema (módulo Bitácora).
-- No existía en el formato original: se creó para el sistema y
-- queda vinculada a usuarios (ON DELETE SET NULL conserva el
-- historial aunque se elimine un usuario).
-- ============================================

CREATE TABLE bitacora(
    idBitacora VARCHAR(8) NOT NULL PRIMARY KEY,
    usuario_id VARCHAR(8) NULL,
    usuario_nombre VARCHAR(50) NOT NULL,
    accion ENUM('LOGIN','LOGOUT','CREATE','UPDATE','DELETE','ACCESS_DENIED','GENERAR') NOT NULL,
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

-- ====================================================
-- DATOS
-- ====================================================

-- ====================================================
-- INSERTS PARA LA TABLA: ROLES (jerarquía de Fundacite)
-- (3 registros)
-- ====================================================

INSERT INTO tipoUsuario (idTipoUsuario, rolUsuario) VALUES
('Rol0001', 'Super Usuario'),
('Rol0002', 'Administrador'),
('Rol0003', 'Usuario Comun');

-- ====================================================
-- INSERTS PARA LA TABLA: cargo
-- (8 registros)
-- ====================================================

INSERT INTO cargo (idCargo, nombreCargo, descripcionCargo) VALUES
('CR0001', 'Super Usuario', 'Acceso total al sistema SIGPAF'),
('CR0002', 'Analista', 'Responsable del análisis de datos, seguimiento de actividades y generación de reportes.'),
('CR0003', 'Secretario', 'Encargado de la gestión administrativa, recepción de recaudos y archivo de documentación.'),
('CR0004', 'Director', 'Supervisor general del área, encargado de la toma de decisiones estratégicas y aprobación de planes.'),
('CR0005', 'Gerente', 'Coordinador operativo de los recursos, planificación de presupuestos y enlace institucional.'),
('CR0006', 'Coordinador', 'Responsable directo de la ejecución de proyectos, asignación de tareas de campo y contacto comunitario.'),
('CR0007', 'AAA', 'AAA'),
('CR0008', 'asdakjsd', 'askjdasjkds');

-- ====================================================
-- INSERTS PARA LA TABLA: unidadEjecutora
-- (6 registros)
-- ====================================================

INSERT INTO unidadEjecutora (idUnidadEjecutora, nomUnidadEjecutora, desUnidadEjecutora) VALUES
('UNE0001', 'SuperU', 'Super Usuario Con Todos los Permisos'),
('UNE0002', 'TIC', 'Unidad de Tecnologías de la Información y Comunicación'),
('UNE0003', 'Admin y Finanzas', 'Departamento de Administración, Presupuesto, Compras y Gestión Financiera'),
('UNE0004', 'Atención Ciudadana', 'Oficina de Vinculación Comunitaria, Atención al Ciudadano y Gestión Social'),
('UNE0005', 'Talento Humano', 'Departamento de Recursos Humanos, Nómina y Bienestar Laboral'),
('UNE0006', 'I+D+I', 'Unidad de Investigación, Desarrollo, Innovación y Proyectos Socio-Tecnológicos');

-- ====================================================
-- INSERTS PARA LA TABLA: Estado
-- (1 registros)
-- ====================================================

INSERT INTO estado (idEstado, nombreEstado) VALUES
('EST0001', 'Yaracuy');

-- ====================================================
-- INSERTS PARA LA TABLA: Municipio
-- (14 registros)
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
-- INSERTS PARA LA TABLA: Parroquia
-- (21 registros)
-- ====================================================

INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES
('PR0001', 'San Felipe', 'MUN0001'),
('PR0002', 'Albarico', 'MUN0001'),
('PR0003', 'San Javier', 'MUN0001'),
('PR0004', 'Independencia', 'MUN0002'),
('PR0005', 'Cocorote', 'MUN0003'),
('PR0006', 'Chivacoa', 'MUN0004'),
('PR0007', 'Campo Elías', 'MUN0004'),
('PR0008', 'Nirgua', 'MUN0005'),
('PR0009', 'Salóm', 'MUN0005'),
('PR0010', 'Temerla', 'MUN0005'),
('PR0011', 'Yaritagua', 'MUN0006'),
('PR0012', 'San Andrés', 'MUN0006'),
('PR0013', 'San Pablo', 'MUN0007'),
('PR0014', 'Aroa', 'MUN0008'),
('PR0015', 'Sabana de Parra', 'MUN0009'),
('PR0016', 'Boraure', 'MUN0010'),
('PR0017', 'Yumare', 'MUN0011'),
('PR0018', 'Guama', 'MUN0012'),
('PR0019', 'Urachiche', 'MUN0013'),
('PR0020', 'Farriar', 'MUN0014');

INSERT INTO parroquia (idParroquia, nombreParroquia, idMunicipio) VALUES
('PR0021', 'El Guayabo', 'MUN0014');

-- ====================================================
-- INSERTS PARA LA TABLA: lugarActividad
-- (7 registros)
-- ====================================================

INSERT INTO lugarActividad (idLugarActividad, nomLugarActividad, desLugarActividad, direccion, esSede, idParroquia) VALUES
('LA0001', 'Sede Fundacite Yaracuy', 'Oficinas administrativas principales de Fundacite Yaracuy', 'Av. Intercomunal José Antonio Páez, sector El Samán', 1, 'PR0001'),
('LA0002', 'U.E. Juan José de Maya', 'Institución educativa para actividades de formación y semilleros científicos', 'Av. Cartagena con Calle 19, San Felipe', 0, 'PR0001'),
('LA0003', 'Liceo Arístides Bastidas', 'Liceo público de educación media general', 'Av. 2 entre Calles 11 y 12, San Felipe', 0, 'PR0001'),
('LA0004', 'E.B. República de Nicaragua', 'Escuela básica del estado utilizada para rutas científicas', 'Av. Caracas entre Av. Yaracuy y Calle 15, San Felipe', 0, 'PR0001'),
('LA0005', 'Complejo Tecnológico Yaracuy', 'Espacio de desarrollo e innovación tecnológica regional', 'Zona Industrial Agustín Rivero, San Felipe', 0, 'PR0001'),
('LA0006', 'Plaza Bolívar de San Felipe', 'Espacio público abierto para ferias de divulgación científica', 'Av. Caracas entre Calles 6 y 7, San Felipe', 0, 'PR0001'),
('LA0007', 'TANGAMANDAPIA', 'DASDS', 'ADSD', 0, 'PR0020');

-- ====================================================
-- INSERTS PARA LA TABLA: espacioUtilizar
-- (6 registros)
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
-- (5 registros)
-- ====================================================

INSERT INTO areaEspecifica (idAreaE, nomAreaE) VALUES
('AE0001', 'AE1'),
('AE0002', 'AE2'),
('AE0003', 'AE3'),
('AE0004', 'AE4'),
('AE0005', 'AE5');

-- ====================================================
-- INSERTS PARA LA TABLA: vertice
-- (6 registros)
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
-- (11 registros)
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
-- (18 registros)
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
-- (7 registros)
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
-- (6 registros)
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
-- (7 registros)
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
-- (7 registros)
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
-- (4 registros)
-- ====================================================

INSERT INTO grupoEtnio (idGrupoEtnio, nomGrupoEtnio, desGrupoEtnio) VALUES
('GN0001', 'No aplica / Ninguno', 'Población general que no se autoidentifica con un grupo étnico específico'),
('GN0002', 'Indígena', 'Población perteneciente a los pueblos y comunidades originarias'),
('GN0003', 'Afrodescendiente', 'Población de herencia y descendencia africana en el territorio nacional'),
('GN0004', 'Multiétnico / Otro', 'Comunidades con diversidad de raíces culturales u otros grupos étnicos específicos');

-- ====================================================
-- INSERTS PARA LA TABLA: docente
-- (5 registros)
-- ====================================================

INSERT INTO docente (idDocente, cedDocente, nacionalidad, nombreDocente, apellidoDocente, telfDocente) VALUES
('DC0001', 'V00000000', 'Venezolano', 'No', 'Aplica', '0000-0000000'),
('DC0002', 'V15421789', 'Venezolano', 'María Alejandra', 'Gómez Mendoza', '0412-5551234'),
('DC0003', 'V20114856', 'Venezolano', 'Carlos Eduardo', 'Rodríguez Plaza', '0416-9994567'),
('DC0004', 'E84215763', 'Extranjero', 'Jean Pierre', 'Dupond', '0414-7778901'),
('DC0005', 'V18956234', 'Venezolano', 'Yelitza Coromoto', 'Tovar Lovera', '0424-3335566');

-- ====================================================
-- INSERTS PARA LA TABLA: estrategiaDesarrollo
-- (7 registros)
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
-- INSERTS PARA LA TABLA: empleado
-- (6 registros)
-- ====================================================

INSERT INTO empleado (idEmpleado, cedulaEmpleado, nacionalidad, nombres, apellidos, fechaNacimiento, telefonoEmpleado, correoEmpleado, idCargo, idUnidadEjecutora) VALUES
('EM0001', 'V00000000', 'Venezolano', 'Super', 'Usuario', '1990-01-01', '0000-0000000', 'super@usuario.com', 'CR0001', 'UNE0001'),
('EM0002', 18543210, 'Venezolano', 'Andrés Eloy', 'Blanco Ortega', '1988-05-14', '0412-1234567', 'andres.blanco@fundacite.gob.ve', 'CR0004', 'UNE0002'),
('EM0003', 20123456, 'Venezolano', 'Gabriela Valentina', 'Méndez Rojas', '1992-10-22', '0416-7654321', 'gabriela.mendez@fundacite.gob.ve', 'CR0006', 'UNE0002'),
('EM0004', 15987654, 'Venezolano', 'Francisco Javier', 'Sánchez Colmenares', '1983-03-08', '0414-9876543', 'francisco.sanchez@fundacite.gob.ve', 'CR0005', 'UNE0002'),
('EM0005', 22345678, 'Venezolano', 'Adriana Carolina', 'Pérez Sequera', '1995-07-30', '0424-4567890', 'adriana.perez@fundacite.gob.ve', 'CR0002', 'UNE0002'),
('EM0006', 24111222, 'Venezolano', 'José Gregorio', 'Giménez Castillo', '1997-12-05', '0412-0001122', 'jose.gimenez@fundacite.gob.ve', 'CR0003', 'UNE0003');

-- ====================================================
-- INSERTS PARA LA TABLA: usuarios
-- (3 registros)
-- ====================================================

INSERT INTO usuarios (idUsuario, nombreUsuario, contrasena, created_at, idTipoUsuario, idEmpleado) VALUES
('Us0001', 'Super', '$2a$12$6Ok0TmT9TLQm9Mo3j/B83.8DN0sR0GZkC/w4rqiT7Ma9pS38HNL9u', '2026-09-26 22:56:41', 'Rol0001', 'EM0001'),
('US0002', 'andres', '$2y$10$4zcrk0H599zAyDBmd65oeOmR68OB9VyXfWWNRnj9TI4u1cjsIQyt6', '2026-09-26 22:56:41', 'Rol0003', 'EM0002'),
('US0003', 'gabriela_user', '$2a$12$y/odPJdXnMdTnYAJXyULwuX.d94kPS3rEVWapyCrfCato1nZDMw0K', '2026-09-26 22:56:41', 'Rol0003', 'EM0003');

-- ====================================================
-- INSERTS PARA LA TABLA: actividad
-- (37 registros)
-- ====================================================

INSERT INTO actividad (idActividad, nombreActividad, fechainicioActividad, fechafinActividad, cantSesionesPlanificada, objetivoActividad, descActividad, cantPersoAtender, observacion, idTipoActividad, idVertice, idAreaE, idEmpleado, idUnidadMedida, idGrupoEtnio, idDocente, idEstatus, idHorario, idEstDesarrollo) VALUES
('AC0001', 'JASJDJSAJDSADKJADKJASND', '2026-10-05', '2026-10-08', 1, 'ASHDBASKDBKASD', 'ASHBDHAKS', 11, 11, 'TA0010', 'VER0002', 'AE0003', 'EM0005', 'UM0002', 'GN0003', 'DC0003', 'ES0001', 'HO0001', 'ED0002'),
('AC0002', 'DADADSAD', '2026-10-01', '2026-10-10', 1, 'ASDASDKJAS', 'AJKSDKAS', 22, 'JJKKJ', 'TA0007', 'VER0002', 'AE0002', 'EM0005', 'UM0003', 'GN0002', 'DC0003', 'ES0001', 'HO0001', 'ED0002'),
('AC0101', 'Taller de robótica educativa', '2025-11-20', '2025-11-25', 2, 'Formar multiplicadores comunitarios en el uso pedagógico de las TIC', 'Espacio de encuentro y aprendizaje colaborativo con proyectos de aplicación real', 21, 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'TA0001', 'VER0005', 'AE0001', 'EM0005', 'UM0003', 'GN0002', 'DC0002', 'ES0006', 'HO0001', 'ED0001'),
('AC0102', 'Formación en programación básica', '2025-11-20', '2025-11-23', 2, 'Impulsar la innovación y el emprendimiento tecnológico en la comunidad', 'Espacio de encuentro y aprendizaje colaborativo con proyectos de aplicación real', 24, '', 'TA0004', 'VER0002', 'AE0003', 'EM0005', 'UM0005', 'GN0003', 'DC0004', 'ES0006', 'HO0004', 'ED0003'),
('AC0103', 'Jornada de alfabetización tecnológica', '2025-11-11', '2025-11-18', 4, 'Impulsar la innovación y el emprendimiento tecnológico en la comunidad', 'Espacio de encuentro y aprendizaje colaborativo con proyectos de aplicación real', 42, 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'TA0007', 'VER0002', 'AE0003', 'EM0003', 'UM0003', 'GN0002', 'DC0004', 'ES0005', 'HO0002', 'ED0001'),
('AC0104', 'Club de ciencias para jóvenes', '2025-12-20', '2025-12-27', 1, 'Impulsar la innovación y el emprendimiento tecnológico en la comunidad', 'Actividad presencial con aprendizaje basado en práctica, dictada en las instalaciones de FUNDACITI', 45, 'Falla eléctrica momentánea, se continuó con la parte teórica hasta restablecerse el servicio', 'TA0009', 'VER0002', 'AE0002', 'EM0001', 'UM0004', 'GN0002', 'DC0003', 'ES0006', 'HO0002', 'ED0001'),
('AC0105', 'Taller de diseño gráfico comunitario', '2025-12-02', '2025-12-09', 4, 'Promover la vocación científica y tecnológica en niños, niñas y jóvenes del municipio', 'Jornada de formación con estaciones de trabajo y acompañamiento personalizado', 17, '', 'TA0009', 'VER0003', 'AE0003', 'EM0002', 'UM0005', 'GN0003', 'DC0004', 'ES0006', 'HO0002', 'ED0003'),
('AC0106', 'Formación de instructores en TIC', '2025-12-03', '2025-12-10', 1, 'Impulsar la innovación y el emprendimiento tecnológico en la comunidad', 'Taller teórico-práctico dirigido a la comunidad, con material didáctico propio', 52, 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'TA0009', 'VER0005', 'AE0003', 'EM0004', 'UM0003', 'GN0003', 'DC0005', 'ES0003', 'HO0003', 'ED0002'),
('AC0107', 'Jornada científica escolar', '2026-01-22', '2026-01-28', 3, 'Impulsar la innovación y el emprendimiento tecnológico en la comunidad', 'Espacio de encuentro y aprendizaje colaborativo con proyectos de aplicación real', 39, '', 'TA0005', 'VER0005', 'AE0001', 'EM0002', 'UM0004', 'GN0003', 'DC0005', 'ES0003', 'HO0003', 'ED0003'),
('AC0108', 'Taller de impresión 3D', '2026-01-22', '2026-01-28', 3, 'Fortalecer las competencias digitales de docentes y comunidad educativa', 'Actividad de formación continua con evaluación por desempeño práctico', 37, 'Falla eléctrica momentánea, se continuó con la parte teórica hasta restablecerse el servicio', 'TA0005', 'VER0002', 'AE0001', 'EM0005', 'UM0005', 'GN0003', 'DC0002', 'ES0006', 'HO0002', 'ED0001'),
('AC0109', 'Curso de mantenimiento de computadoras', '2026-01-01', '2026-01-03', 3, 'Promover la vocación científica y tecnológica en niños, niñas y jóvenes del municipio', 'Jornada de formación con estaciones de trabajo y acompañamiento personalizado', 43, 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'TA0008', 'VER0002', 'AE0001', 'EM0003', 'UM0004', 'GN0004', 'DC0002', 'ES0006', 'HO0003', 'ED0002'),
('AC0111', 'Taller de videojuegos educativos', '2026-02-11', '2026-02-14', 1, 'Contribuir a la reducción de la brecha digital en la región yaracuyana', 'Actividad presencial con aprendizaje basado en práctica, dictada en las instalaciones de FUNDACITI', 36, 'Algunos participantes llegaron tarde por dificultades de transporte', 'TA0008', 'VER0003', 'AE0003', 'EM0006', 'UM0005', 'GN0004', 'DC0002', 'ES0006', 'HO0002', 'ED0001'),
('AC0112', 'Formación en herramientas ofimáticas', '2026-02-14', '2026-02-21', 1, 'Promover la vocación científica y tecnológica en niños, niñas y jóvenes del municipio', 'Actividad de formación continua con evaluación por desempeño práctico', 47, 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'TA0005', 'VER0002', 'AE0002', 'EM0002', 'UM0005', 'GN0002', 'DC0005', 'ES0006', 'HO0001', 'ED0003'),
('AC0113', 'Taller de programación con Arduino', '2026-03-15', '2026-03-21', 3, 'Capacitar a la comunidad en el uso de herramientas tecnológicas para su desarrollo personal y profesional', 'Jornada de formación con estaciones de trabajo y acompañamiento personalizado', 15, 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'TA0004', 'VER0002', 'AE0003', 'EM0004', 'UM0004', 'GN0002', 'DC0005', 'ES0006', 'HO0004', 'ED0002'),
('AC0114', 'Jornada de divulgación científica', '2026-03-10', '2026-03-12', 1, 'Promover la vocación científica y tecnológica en niños, niñas y jóvenes del municipio', 'Actividad presencial con aprendizaje basado en práctica, dictada en las instalaciones de FUNDACITI', 49, 'Algunos participantes llegaron tarde por dificultades de transporte', 'TA0002', 'VER0002', 'AE0001', 'EM0001', 'UM0004', 'GN0004', 'DC0001', 'ES0005', 'HO0003', 'ED0002'),
('AC0115', 'Curso de redes sociales para emprendedores', '2026-03-12', '2026-03-16', 2, 'Formar multiplicadores comunitarios en el uso pedagógico de las TIC', 'Espacio de encuentro y aprendizaje colaborativo con proyectos de aplicación real', 37, 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'TA0002', 'VER0003', 'AE0001', 'EM0004', 'UM0005', 'GN0003', 'DC0005', 'ES0005', 'HO0004', 'ED0001'),
('AC0116', 'Taller de fotografía digital', '2026-04-21', '2026-04-25', 2, 'Fortalecer las competencias digitales de docentes y comunidad educativa', 'Jornada de formación con estaciones de trabajo y acompañamiento personalizado', 31, 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'TA0002', 'VER0002', 'AE0002', 'EM0004', 'UM0005', 'GN0003', 'DC0003', 'ES0006', 'HO0002', 'ED0003'),
('AC0117', 'Formación en hojas de cálculo', '2026-04-13', '2026-04-17', 1, 'Formar multiplicadores comunitarios en el uso pedagógico de las TIC', 'Jornada de formación con estaciones de trabajo y acompañamiento personalizado', 60, '', 'TA0007', 'VER0003', 'AE0003', 'EM0004', 'UM0003', 'GN0004', 'DC0003', 'ES0006', 'HO0003', 'ED0003'),
('AC0118', 'Club de matemáticas recreativas', '2026-04-21', '2026-04-26', 1, 'Desarrollar habilidades prácticas de programación y robótica en los participantes', 'Espacio de encuentro y aprendizaje colaborativo con proyectos de aplicación real', 19, 'Falla eléctrica momentánea, se continuó con la parte teórica hasta restablecerse el servicio', 'TA0008', 'VER0002', 'AE0003', 'EM0001', 'UM0004', 'GN0002', 'DC0001', 'ES0006', 'HO0004', 'ED0001'),
('AC0119', 'Taller de electrónica básica', '2026-05-10', '2026-05-16', 3, 'Contribuir a la reducción de la brecha digital en la región yaracuyana', 'Actividad de formación continua con evaluación por desempeño práctico', 20, '', 'TA0007', 'VER0005', 'AE0002', 'EM0002', 'UM0004', 'GN0002', 'DC0004', 'ES0003', 'HO0001', 'ED0001');

INSERT INTO actividad (idActividad, nombreActividad, fechainicioActividad, fechafinActividad, cantSesionesPlanificada, objetivoActividad, descActividad, cantPersoAtender, observacion, idTipoActividad, idVertice, idAreaE, idEmpleado, idUnidadMedida, idGrupoEtnio, idDocente, idEstatus, idHorario, idEstDesarrollo) VALUES
('AC0120', 'Jornada de capacitación docente en TIC', '2026-05-17', '2026-05-23', 3, 'Promover la vocación científica y tecnológica en niños, niñas y jóvenes del municipio', 'Jornada de formación con estaciones de trabajo y acompañamiento personalizado', 18, '', 'TA0008', 'VER0003', 'AE0003', 'EM0001', 'UM0004', 'GN0004', 'DC0005', 'ES0006', 'HO0002', 'ED0001'),
('AC0121', 'Taller de animación digital', '2026-05-16', '2026-05-18', 4, 'Contribuir a la reducción de la brecha digital en la región yaracuyana', 'Taller teórico-práctico dirigido a la comunidad, con material didáctico propio', 44, '', 'TA0001', 'VER0002', 'AE0001', 'EM0002', 'UM0003', 'GN0004', 'DC0003', 'ES0006', 'HO0003', 'ED0001'),
('AC0122', 'Formación en internet seguro', '2026-06-03', '2026-06-06', 3, 'Contribuir a la reducción de la brecha digital en la región yaracuyana', 'Espacio de encuentro y aprendizaje colaborativo con proyectos de aplicación real', 37, 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'TA0002', 'VER0005', 'AE0003', 'EM0003', 'UM0004', 'GN0004', 'DC0002', 'ES0006', 'HO0004', 'ED0003'),
('AC0123', 'Curso de reparación de teléfonos móviles', '2026-06-12', '2026-06-16', 3, 'Impulsar la innovación y el emprendimiento tecnológico en la comunidad', 'Actividad de formación continua con evaluación por desempeño práctico', 42, 'Algunos participantes llegaron tarde por dificultades de transporte', 'TA0008', 'VER0003', 'AE0002', 'EM0005', 'UM0003', 'GN0002', 'DC0002', 'ES0005', 'HO0002', 'ED0002'),
('AC0124', 'Taller de química experimental', '2026-06-16', '2026-06-22', 2, 'Impulsar la innovación y el emprendimiento tecnológico en la comunidad', 'Actividad presencial con aprendizaje basado en práctica, dictada en las instalaciones de FUNDACITI', 23, '', 'TA0003', 'VER0005', 'AE0003', 'EM0005', 'UM0005', 'GN0002', 'DC0004', 'ES0006', 'HO0002', 'ED0002'),
('AC0125', 'Encuentro de lectura y tecnología', '2026-07-17', '2026-07-19', 4, 'Fortalecer las competencias digitales de docentes y comunidad educativa', 'Jornada de formación con estaciones de trabajo y acompañamiento personalizado', 59, '', 'TA0006', 'VER0005', 'AE0002', 'EM0004', 'UM0003', 'GN0002', 'DC0005', 'ES0006', 'HO0004', 'ED0003'),
('AC0126', 'Formación en bases de datos', '2026-07-18', '2026-07-21', 4, 'Acercar las tecnologías emergentes a sectores vulnerables de la población', 'Jornada de formación con estaciones de trabajo y acompañamiento personalizado', 15, 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'TA0002', 'VER0005', 'AE0003', 'EM0004', 'UM0003', 'GN0002', 'DC0004', 'ES0005', 'HO0004', 'ED0002'),
('AC0127', 'Taller de realidad virtual', '2026-07-20', '2026-07-28', 4, 'Formar multiplicadores comunitarios en el uso pedagógico de las TIC', 'Actividad presencial con aprendizaje basado en práctica, dictada en las instalaciones de FUNDACITI', 22, 'Algunos participantes llegaron tarde por dificultades de transporte', 'TA0006', 'VER0003', 'AE0002', 'EM0003', 'UM0003', 'GN0004', 'DC0003', 'ES0006', 'HO0002', 'ED0003'),
('AC0128', 'Jornada de inclusión digital', '2026-08-07', '2026-08-14', 3, 'Fortalecer las competencias digitales de docentes y comunidad educativa', 'Taller teórico-práctico dirigido a la comunidad, con material didáctico propio', 26, '', 'TA0001', 'VER0003', 'AE0002', 'EM0003', 'UM0005', 'GN0004', 'DC0004', 'ES0005', 'HO0001', 'ED0003'),
('AC0129', 'Curso de contabilidad básica digital', '2026-08-03', '2026-08-08', 3, 'Capacitar a la comunidad en el uso de herramientas tecnológicas para su desarrollo personal y profesional', 'Actividad de formación continua con evaluación por desempeño práctico', 26, '', 'TA0009', 'VER0002', 'AE0003', 'EM0004', 'UM0004', 'GN0003', 'DC0002', 'ES0006', 'HO0004', 'ED0003'),
('AC0130', 'Taller de podcast educativo', '2026-08-17', '2026-08-25', 3, 'Capacitar a la comunidad en el uso de herramientas tecnológicas para su desarrollo personal y profesional', 'Actividad de formación continua con evaluación por desempeño práctico', 48, '', 'TA0001', 'VER0003', 'AE0001', 'EM0002', 'UM0004', 'GN0004', 'DC0002', 'ES0006', 'HO0004', 'ED0001'),
('AC0131', 'Formación en ciberseguridad', '2026-09-02', '2026-09-07', 4, 'Formar multiplicadores comunitarios en el uso pedagógico de las TIC', 'Jornada de formación con estaciones de trabajo y acompañamiento personalizado', 21, '', 'TA0006', 'VER0003', 'AE0001', 'EM0004', 'UM0004', 'GN0003', 'DC0003', 'ES0006', 'HO0003', 'ED0003'),
('AC0132', 'Taller de astronomía práctica', '2026-09-12', '2026-09-16', 1, 'Contribuir a la reducción de la brecha digital en la región yaracuyana', 'Taller teórico-práctico dirigido a la comunidad, con material didáctico propio', 58, 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'TA0009', 'VER0005', 'AE0002', 'EM0001', 'UM0005', 'GN0002', 'DC0003', 'ES0006', 'HO0004', 'ED0002'),
('AC0133', 'Jornada de feria tecnológica', '2026-09-13', '2026-09-16', 4, 'Acercar las tecnologías emergentes a sectores vulnerables de la población', 'Actividad presencial con aprendizaje basado en práctica, dictada en las instalaciones de FUNDACITI', 51, '', 'TA0008', 'VER0005', 'AE0003', 'EM0003', 'UM0005', 'GN0002', 'DC0001', 'ES0004', 'HO0003', 'ED0002'),
('AC0134', 'Formación en edición de video', '2026-10-05', '2026-10-11', 4, 'Fortalecer las competencias digitales de docentes y comunidad educativa', 'Taller teórico-práctico dirigido a la comunidad, con material didáctico propio', 58, '', 'TA0006', 'VER0003', 'AE0002', 'EM0004', 'UM0005', 'GN0003', 'DC0005', 'ES0002', 'HO0003', 'ED0002'),
('AC0135', 'Taller de inteligencia artificial', '2026-10-05', '2026-10-07', 4, 'Impulsar la innovación y el emprendimiento tecnológico en la comunidad', 'Espacio de encuentro y aprendizaje colaborativo con proyectos de aplicación real', 29, 'Falla eléctrica momentánea, se continuó con la parte teórica hasta restablecerse el servicio', 'TA0003', 'VER0002', 'AE0001', 'EM0004', 'UM0004', 'GN0003', 'DC0005', 'ES0003', 'HO0003', 'ED0001'),
('AC0136', 'Curso de comercio electrónico', '2026-10-07', '2026-10-12', 2, 'Fortalecer las competencias digitales de docentes y comunidad educativa', 'Taller teórico-práctico dirigido a la comunidad, con material didáctico propio', 51, 'Algunos participantes llegaron tarde por dificultades de transporte', 'TA0003', 'VER0003', 'AE0003', 'EM0003', 'UM0004', 'GN0002', 'DC0002', 'ES0001', 'HO0002', 'ED0001');

-- ====================================================
-- INSERTS PARA LA TABLA: lugarRealizaActividad
-- (37 registros)
-- ====================================================

INSERT INTO lugarRealizaActividad (idReaActividad, idEspacioUtilizar, idLugarActividad, idActividad, created_at) VALUES
('LR0001', NULL, 'LA0004', 'AC0001', '2026-10-12 21:23:29'),
('LR0002', NULL, 'LA0004', 'AC0002', '2026-10-12 23:00:32'),
('LR0100', NULL, 'LA0003', 'AC0101', '2026-10-13 20:51:18'),
('LR0101', NULL, 'LA0004', 'AC0102', '2026-10-13 20:51:18'),
('LR0102', NULL, 'LA0002', 'AC0103', '2026-10-13 20:51:18'),
('LR0103', NULL, 'LA0001', 'AC0104', '2026-10-13 20:51:18'),
('LR0104', NULL, 'LA0003', 'AC0105', '2026-10-13 20:51:18'),
('LR0105', NULL, 'LA0003', 'AC0106', '2026-10-13 20:51:18'),
('LR0106', NULL, 'LA0004', 'AC0107', '2026-10-13 20:51:18'),
('LR0107', NULL, 'LA0002', 'AC0108', '2026-10-13 20:51:18'),
('LR0108', NULL, 'LA0001', 'AC0109', '2026-10-13 20:51:18'),
('LR0110', NULL, 'LA0004', 'AC0111', '2026-10-13 20:51:18'),
('LR0111', NULL, 'LA0001', 'AC0112', '2026-10-13 20:51:18'),
('LR0112', NULL, 'LA0003', 'AC0113', '2026-10-13 20:51:18'),
('LR0113', NULL, 'LA0003', 'AC0114', '2026-10-13 20:51:18'),
('LR0114', NULL, 'LA0003', 'AC0115', '2026-10-13 20:51:18'),
('LR0115', NULL, 'LA0003', 'AC0116', '2026-10-13 20:51:18'),
('LR0116', NULL, 'LA0001', 'AC0117', '2026-10-13 20:51:18'),
('LR0117', NULL, 'LA0001', 'AC0118', '2026-10-13 20:51:18'),
('LR0118', NULL, 'LA0002', 'AC0119', '2026-10-13 20:51:18');

INSERT INTO lugarRealizaActividad (idReaActividad, idEspacioUtilizar, idLugarActividad, idActividad, created_at) VALUES
('LR0119', NULL, 'LA0004', 'AC0120', '2026-10-13 20:51:18'),
('LR0120', NULL, 'LA0001', 'AC0121', '2026-10-13 20:51:18'),
('LR0121', NULL, 'LA0004', 'AC0122', '2026-10-13 20:51:18'),
('LR0122', NULL, 'LA0004', 'AC0123', '2026-10-13 20:51:18'),
('LR0123', NULL, 'LA0002', 'AC0124', '2026-10-13 20:51:18'),
('LR0124', NULL, 'LA0002', 'AC0125', '2026-10-13 20:51:18'),
('LR0125', NULL, 'LA0004', 'AC0126', '2026-10-13 20:51:18'),
('LR0126', NULL, 'LA0001', 'AC0127', '2026-10-13 20:51:18'),
('LR0127', NULL, 'LA0002', 'AC0128', '2026-10-13 20:51:18'),
('LR0128', NULL, 'LA0003', 'AC0129', '2026-10-13 20:51:18'),
('LR0129', NULL, 'LA0001', 'AC0130', '2026-10-13 20:51:18'),
('LR0130', NULL, 'LA0001', 'AC0131', '2026-10-13 20:51:18'),
('LR0131', NULL, 'LA0002', 'AC0132', '2026-10-13 20:51:18'),
('LR0132', NULL, 'LA0001', 'AC0133', '2026-10-13 20:51:18'),
('LR0133', NULL, 'LA0002', 'AC0134', '2026-10-13 20:51:18'),
('LR0134', NULL, 'LA0001', 'AC0135', '2026-10-13 20:51:18'),
('LR0135', NULL, 'LA0004', 'AC0136', '2026-10-13 20:51:18');

-- ====================================================
-- INSERTS PARA LA TABLA: grupoEtarioActividad
-- (37 registros)
-- ====================================================

INSERT INTO grupoEtarioActividad (idGrupoEtareoActividad, idGrupoEtareo, idActividad, created_at) VALUES
('GA0100', 'GE0005', 'AC0101', '2026-10-13 20:51:18'),
('GA0101', 'GE0003', 'AC0102', '2026-10-13 20:51:18'),
('GA0102', 'GE0001', 'AC0103', '2026-10-13 20:51:18'),
('GA0103', 'GE0004', 'AC0104', '2026-10-13 20:51:18'),
('GA0104', 'GE0004', 'AC0105', '2026-10-13 20:51:18'),
('GA0105', 'GE0005', 'AC0106', '2026-10-13 20:51:18'),
('GA0106', 'GE0001', 'AC0107', '2026-10-13 20:51:18'),
('GA0107', 'GE0003', 'AC0108', '2026-10-13 20:51:18'),
('GA0108', 'GE0006', 'AC0109', '2026-10-13 20:51:18'),
('GA0110', 'GE0005', 'AC0111', '2026-10-13 20:51:18'),
('GA0111', 'GE0003', 'AC0112', '2026-10-13 20:51:18'),
('GA0112', 'GE0001', 'AC0113', '2026-10-13 20:51:18'),
('GA0113', 'GE0005', 'AC0114', '2026-10-13 20:51:18'),
('GA0114', 'GE0001', 'AC0115', '2026-10-13 20:51:18'),
('GA0115', 'GE0003', 'AC0116', '2026-10-13 20:51:18'),
('GA0116', 'GE0001', 'AC0117', '2026-10-13 20:51:18'),
('GA0117', 'GE0001', 'AC0118', '2026-10-13 20:51:18'),
('GA0118', 'GE0005', 'AC0119', '2026-10-13 20:51:18'),
('GA0119', 'GE0005', 'AC0120', '2026-10-13 20:51:18'),
('GA0120', 'GE0005', 'AC0121', '2026-10-13 20:51:18');

INSERT INTO grupoEtarioActividad (idGrupoEtareoActividad, idGrupoEtareo, idActividad, created_at) VALUES
('GA0121', 'GE0005', 'AC0122', '2026-10-13 20:51:18'),
('GA0122', 'GE0004', 'AC0123', '2026-10-13 20:51:18'),
('GA0123', 'GE0006', 'AC0124', '2026-10-13 20:51:18'),
('GA0124', 'GE0006', 'AC0125', '2026-10-13 20:51:18'),
('GA0125', 'GE0001', 'AC0126', '2026-10-13 20:51:18'),
('GA0126', 'GE0006', 'AC0127', '2026-10-13 20:51:18'),
('GA0127', 'GE0006', 'AC0128', '2026-10-13 20:51:18'),
('GA0128', 'GE0006', 'AC0129', '2026-10-13 20:51:18'),
('GA0129', 'GE0001', 'AC0130', '2026-10-13 20:51:18'),
('GA0130', 'GE0004', 'AC0131', '2026-10-13 20:51:18'),
('GA0131', 'GE0001', 'AC0132', '2026-10-13 20:51:18'),
('GA0132', 'GE0004', 'AC0133', '2026-10-13 20:51:18'),
('GA0133', 'GE0005', 'AC0134', '2026-10-13 20:51:18'),
('GA0134', 'GE0004', 'AC0135', '2026-10-13 20:51:18'),
('GA0135', 'GE0004', 'AC0136', '2026-10-13 20:51:18'),
('GEA001', 'GE0005', 'AC0001', '2026-10-12 21:23:29'),
('GEA002', 'GE0005', 'AC0002', '2026-10-12 23:00:32');

-- ====================================================
-- INSERTS PARA LA TABLA: seguimientoActividad
-- (95 registros)
-- ====================================================

INSERT INTO seguimientoActividad (idSegActividad, nroSesionPlanificada, fechaSesion, logroActividad, observObstaculo, idActividad, idTipEntrega) VALUES
('SA0001', 1, '2026-10-05', 'HJJHJBNJKJKJKJKJKJKJKKJ', 'BJJBJB', 'AC0001', 'TE0002'),
('SA0002', 1, '2026-10-01', 'HHKHHJHJHJHJHJ', 'HBJHBJHBHJ', 'AC0002', 'TE0001'),
('SA0101', 1, '2025-11-20', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', '', 'AC0101', 'TE0004'),
('SA0102', 2, '2025-11-25', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', 'Algunos participantes llegaron tarde por dificultades de transporte', 'AC0101', 'TE0003'),
('SA0103', 1, '2025-11-20', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'AC0102', 'TE0002'),
('SA0104', 2, '2025-11-23', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', '', 'AC0102', 'TE0001'),
('SA0105', 1, '2025-11-11', '', '', 'AC0103', 'TE0004'),
('SA0106', 2, '2025-11-13', '', '', 'AC0103', 'TE0003'),
('SA0107', 3, '2025-11-15', '', '', 'AC0103', 'TE0004'),
('SA0108', 4, '2025-11-17', '', '', 'AC0103', 'TE0004'),
('SA0109', 1, '2025-12-20', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', '', 'AC0104', 'TE0003'),
('SA0110', 1, '2025-12-02', 'La sesión cumplió su objetivo; los asistentes presentaron sus avances de proyecto', 'Algunos participantes llegaron tarde por dificultades de transporte', 'AC0105', 'TE0001'),
('SA0111', 2, '2025-12-04', 'La sesión cumplió su objetivo; los asistentes presentaron sus avances de proyecto', 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'AC0105', 'TE0002'),
('SA0112', 3, '2025-12-06', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', '', 'AC0105', 'TE0003'),
('SA0113', 4, '2025-12-08', 'Los participantes ejecutaron las prácticas de forma autónoma demostrando dominio de los contenidos', 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'AC0105', 'TE0002'),
('SA0114', 1, '2025-12-03', '', '', 'AC0106', 'TE0001'),
('SA0115', 1, '2026-01-22', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', '', 'AC0107', 'TE0003'),
('SA0116', 2, '2026-01-25', '', '', 'AC0107', 'TE0004'),
('SA0117', 3, '2026-01-28', '', '', 'AC0107', 'TE0003'),
('SA0118', 1, '2026-01-22', 'La sesión cumplió su objetivo; los asistentes presentaron sus avances de proyecto', 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'AC0108', 'TE0001');

INSERT INTO seguimientoActividad (idSegActividad, nroSesionPlanificada, fechaSesion, logroActividad, observObstaculo, idActividad, idTipEntrega) VALUES
('SA0119', 2, '2026-01-25', 'Se fortalecieron las competencias trabajadas mediante actividades colaborativas de aplicación real', '', 'AC0108', 'TE0001'),
('SA0120', 3, '2026-01-28', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', '', 'AC0108', 'TE0001'),
('SA0121', 1, '2026-01-01', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', '', 'AC0109', 'TE0002'),
('SA0122', 2, '2026-01-02', 'La sesión cumplió su objetivo; los asistentes presentaron sus avances de proyecto', '', 'AC0109', 'TE0003'),
('SA0123', 3, '2026-01-03', 'La sesión cumplió su objetivo; los asistentes presentaron sus avances de proyecto', '', 'AC0109', 'TE0001'),
('SA0125', 1, '2026-02-11', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', '', 'AC0111', 'TE0003'),
('SA0126', 1, '2026-02-14', 'Se fortalecieron las competencias trabajadas mediante actividades colaborativas de aplicación real', '', 'AC0112', 'TE0001'),
('SA0127', 1, '2026-03-15', 'Los participantes ejecutaron las prácticas de forma autónoma demostrando dominio de los contenidos', '', 'AC0113', 'TE0003'),
('SA0128', 2, '2026-03-18', 'Los participantes ejecutaron las prácticas de forma autónoma demostrando dominio de los contenidos', 'Falla eléctrica momentánea, se continuó con la parte teórica hasta restablecerse el servicio', 'AC0113', 'TE0001'),
('SA0129', 3, '2026-03-21', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', '', 'AC0113', 'TE0003'),
('SA0130', 1, '2026-03-10', '', '', 'AC0114', 'TE0004'),
('SA0131', 1, '2026-03-12', '', '', 'AC0115', 'TE0004'),
('SA0132', 2, '2026-03-16', '', '', 'AC0115', 'TE0001'),
('SA0133', 1, '2026-04-21', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', '', 'AC0116', 'TE0004'),
('SA0134', 2, '2026-04-25', 'La sesión cumplió su objetivo; los asistentes presentaron sus avances de proyecto', '', 'AC0116', 'TE0003'),
('SA0135', 1, '2026-04-13', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'AC0117', 'TE0002'),
('SA0136', 1, '2026-04-21', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'AC0118', 'TE0003'),
('SA0137', 1, '2026-05-10', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'AC0119', 'TE0002'),
('SA0138', 2, '2026-05-13', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', 'Algunos participantes llegaron tarde por dificultades de transporte', 'AC0119', 'TE0002'),
('SA0139', 3, '2026-05-16', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', '', 'AC0119', 'TE0002');

INSERT INTO seguimientoActividad (idSegActividad, nroSesionPlanificada, fechaSesion, logroActividad, observObstaculo, idActividad, idTipEntrega) VALUES
('SA0140', 1, '2026-05-17', 'Los participantes ejecutaron las prácticas de forma autónoma demostrando dominio de los contenidos', 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'AC0120', 'TE0004'),
('SA0141', 2, '2026-05-20', 'La sesión cumplió su objetivo; los asistentes presentaron sus avances de proyecto', 'Falla eléctrica momentánea, se continuó con la parte teórica hasta restablecerse el servicio', 'AC0120', 'TE0004'),
('SA0142', 3, '2026-05-23', 'Los participantes ejecutaron las prácticas de forma autónoma demostrando dominio de los contenidos', 'Falla eléctrica momentánea, se continuó con la parte teórica hasta restablecerse el servicio', 'AC0120', 'TE0004'),
('SA0143', 1, '2026-05-16', 'Los participantes ejecutaron las prácticas de forma autónoma demostrando dominio de los contenidos', 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'AC0121', 'TE0001'),
('SA0144', 2, '2026-05-17', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'AC0121', 'TE0003'),
('SA0145', 3, '2026-05-18', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', 'Falla eléctrica momentánea, se continuó con la parte teórica hasta restablecerse el servicio', 'AC0121', 'TE0003'),
('SA0146', 4, '2026-05-18', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', 'Falla eléctrica momentánea, se continuó con la parte teórica hasta restablecerse el servicio', 'AC0121', 'TE0002'),
('SA0147', 1, '2026-06-03', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', '', 'AC0122', 'TE0001'),
('SA0148', 2, '2026-06-04', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', '', 'AC0122', 'TE0004'),
('SA0149', 3, '2026-06-05', 'Los participantes ejecutaron las prácticas de forma autónoma demostrando dominio de los contenidos', '', 'AC0122', 'TE0003'),
('SA0150', 1, '2026-06-12', '', '', 'AC0123', 'TE0001'),
('SA0151', 2, '2026-06-14', '', '', 'AC0123', 'TE0003'),
('SA0152', 3, '2026-06-16', '', '', 'AC0123', 'TE0001'),
('SA0153', 1, '2026-06-16', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', '', 'AC0124', 'TE0001'),
('SA0154', 2, '2026-06-22', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', '', 'AC0124', 'TE0002'),
('SA0155', 1, '2026-07-17', 'La sesión cumplió su objetivo; los asistentes presentaron sus avances de proyecto', 'Algunos participantes llegaron tarde por dificultades de transporte', 'AC0125', 'TE0001'),
('SA0156', 2, '2026-07-18', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'AC0125', 'TE0002'),
('SA0157', 3, '2026-07-19', 'Los participantes ejecutaron las prácticas de forma autónoma demostrando dominio de los contenidos', 'Falla eléctrica momentánea, se continuó con la parte teórica hasta restablecerse el servicio', 'AC0125', 'TE0002'),
('SA0158', 4, '2026-07-19', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', '', 'AC0125', 'TE0004'),
('SA0159', 1, '2026-07-18', '', '', 'AC0126', 'TE0001');

INSERT INTO seguimientoActividad (idSegActividad, nroSesionPlanificada, fechaSesion, logroActividad, observObstaculo, idActividad, idTipEntrega) VALUES
('SA0160', 2, '2026-07-19', '', '', 'AC0126', 'TE0004'),
('SA0161', 3, '2026-07-20', '', '', 'AC0126', 'TE0003'),
('SA0162', 4, '2026-07-21', '', '', 'AC0126', 'TE0003'),
('SA0163', 1, '2026-07-20', 'La sesión cumplió su objetivo; los asistentes presentaron sus avances de proyecto', 'Algunos participantes llegaron tarde por dificultades de transporte', 'AC0127', 'TE0001'),
('SA0164', 2, '2026-07-22', 'La sesión cumplió su objetivo; los asistentes presentaron sus avances de proyecto', '', 'AC0127', 'TE0003'),
('SA0165', 3, '2026-07-24', 'La sesión cumplió su objetivo; los asistentes presentaron sus avances de proyecto', '', 'AC0127', 'TE0001'),
('SA0166', 4, '2026-07-26', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', '', 'AC0127', 'TE0001'),
('SA0167', 1, '2026-08-07', '', '', 'AC0128', 'TE0003'),
('SA0168', 2, '2026-08-10', '', '', 'AC0128', 'TE0004'),
('SA0169', 3, '2026-08-13', '', '', 'AC0128', 'TE0001'),
('SA0170', 1, '2026-08-03', 'Se fortalecieron las competencias trabajadas mediante actividades colaborativas de aplicación real', 'Falla eléctrica momentánea, se continuó con la parte teórica hasta restablecerse el servicio', 'AC0129', 'TE0003'),
('SA0171', 2, '2026-08-05', 'Los participantes ejecutaron las prácticas de forma autónoma demostrando dominio de los contenidos', '', 'AC0129', 'TE0001'),
('SA0172', 3, '2026-08-07', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', '', 'AC0129', 'TE0004'),
('SA0173', 1, '2026-08-17', 'Se fortalecieron las competencias trabajadas mediante actividades colaborativas de aplicación real', '', 'AC0130', 'TE0004'),
('SA0174', 2, '2026-08-21', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', '', 'AC0130', 'TE0001'),
('SA0175', 3, '2026-08-25', 'La sesión cumplió su objetivo; los asistentes presentaron sus avances de proyecto', '', 'AC0130', 'TE0002'),
('SA0176', 1, '2026-09-02', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', 'Falla eléctrica momentánea, se continuó con la parte teórica hasta restablecerse el servicio', 'AC0131', 'TE0001'),
('SA0177', 2, '2026-09-03', 'Se fortalecieron las competencias trabajadas mediante actividades colaborativas de aplicación real', 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'AC0131', 'TE0004'),
('SA0178', 3, '2026-09-04', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', 'Falla eléctrica momentánea, se continuó con la parte teórica hasta restablecerse el servicio', 'AC0131', 'TE0002'),
('SA0179', 4, '2026-09-05', 'Se fortalecieron las competencias trabajadas mediante actividades colaborativas de aplicación real', '', 'AC0131', 'TE0003');

INSERT INTO seguimientoActividad (idSegActividad, nroSesionPlanificada, fechaSesion, logroActividad, observObstaculo, idActividad, idTipEntrega) VALUES
('SA0180', 1, '2026-09-12', 'Los participantes ejecutaron las prácticas de forma autónoma demostrando dominio de los contenidos', 'Algunos participantes llegaron tarde por dificultades de transporte', 'AC0132', 'TE0002'),
('SA0181', 1, '2026-09-13', '', '', 'AC0133', 'TE0003'),
('SA0182', 2, '2026-09-14', '', '', 'AC0133', 'TE0003'),
('SA0183', 3, '2026-09-15', '', '', 'AC0133', 'TE0004'),
('SA0184', 4, '2026-09-16', '', '', 'AC0133', 'TE0001'),
('SA0185', 1, '2026-10-05', '', '', 'AC0134', 'TE0002'),
('SA0186', 2, '2026-10-07', '', '', 'AC0134', 'TE0004'),
('SA0187', 3, '2026-10-09', '', '', 'AC0134', 'TE0001'),
('SA0188', 4, '2026-10-11', '', '', 'AC0134', 'TE0002'),
('SA0189', 1, '2026-10-05', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', '', 'AC0135', 'TE0004'),
('SA0190', 2, '2026-10-06', 'Se completó el temario previsto con ejercicios prácticos y retroalimentación grupal', 'Retraso de 30 minutos por lluvia en la zona, se recuperó el tiempo alargando la jornada', 'AC0135', 'TE0001'),
('SA0191', 3, '2026-10-07', 'Se desarrolló la sesión con alta participación, cumpliendo el contenido planificado y los objetivos de aprendizaje', 'Algunos participantes llegaron tarde por dificultades de transporte', 'AC0135', 'TE0002'),
('SA0192', 4, '2026-10-07', '', '', 'AC0135', 'TE0003'),
('SA0193', 1, '2026-10-07', '', '', 'AC0136', 'TE0002'),
('SA0194', 2, '2026-10-12', '', '', 'AC0136', 'TE0001');

-- ====================================================
-- INSERTS PARA LA TABLA: beneficiario
-- (26 registros)
-- ====================================================

INSERT INTO beneficiario (idBeneficiario, nombresBeneficiario, apellidosBeneficiario, cedulaBeneficiario, nacionalidad, fechaNacBeneficiario, sexoBeneficiario) VALUES
('BEN0001', 'LUIS ALEJANDRO', 'SALAZAR AGUILAR', 27166779, 'Venezolano', '1999-09-11', 'M'),
('BEN0002', 'LORIANNY YURLEYVIC', 'MARTINEZ GUTIETRREZ', 27529482, 'Venezolano', '1999-05-12', 'F'),
('BEN0010', 'MARIA', 'GONZALEZ', 'V21000000', 'Venezolano', '1980-01-01', 'F'),
('BEN0011', 'JOSE', 'PEREZ', 'V21137913', 'Venezolano', '1981-02-02', 'M'),
('BEN0012', 'ANA', 'RODRIGUEZ', 'V21275826', 'Venezolano', '1982-03-03', 'F'),
('BEN0013', 'LUIS', 'HERNANDEZ', 'V21413739', 'Venezolano', '1983-04-04', 'M'),
('BEN0014', 'CARLA', 'MARTINEZ', 'V21551652', 'Venezolano', '1984-05-05', 'F'),
('BEN0015', 'PEDRO', 'LOPEZ', 'V21689565', 'Venezolano', '1985-06-06', 'M'),
('BEN0016', 'LUZ', 'GUTIERREZ', 'V21827478', 'Venezolano', '1986-07-07', 'F'),
('BEN0017', 'DANIEL', 'SANCHEZ', 'V21965391', 'Venezolano', '1987-08-08', 'M'),
('BEN0018', 'VALENTINA', 'RAMIREZ', 'V22103304', 'Venezolano', '1988-09-09', 'F'),
('BEN0019', 'CARLOS', 'TORRES', 'V22241217', 'Venezolano', '1989-10-10', 'M'),
('BEN0020', 'ESTEFANIA', 'VARGAS', 'V22379130', 'Venezolano', '1990-11-11', 'F'),
('BEN0021', 'MIGUEL', 'CASTRO', 'V22517043', 'Venezolano', '1991-12-12', 'M'),
('BEN0022', 'SOFIA', 'RUIZ', 'V22654956', 'Venezolano', '1992-01-13', 'F'),
('BEN0023', 'ANDRES', 'ALVAREZ', 'V22792869', 'Venezolano', '1993-02-14', 'M'),
('BEN0024', 'CAMILA', 'MORENO', 'V22930782', 'Venezolano', '1994-03-15', 'F'),
('BEN0025', 'JAVIER', 'GUERRERO', 'V23068695', 'Venezolano', '1995-04-16', 'M'),
('BEN0026', 'ISABELLA', 'MEDINA', 'V23206608', 'Venezolano', '1996-05-17', 'F'),
('BEN0027', 'FRANCISCO', 'CASTILLO', 'V23344521', 'Venezolano', '1997-06-18', 'M');

INSERT INTO beneficiario (idBeneficiario, nombresBeneficiario, apellidosBeneficiario, cedulaBeneficiario, nacionalidad, fechaNacBeneficiario, sexoBeneficiario) VALUES
('BEN0028', 'ANTONELLA', 'SOSA', 'V23482434', 'Venezolano', '1998-07-19', 'F'),
('BEN0029', 'DIEGO', 'CARRILLO', 'V23620347', 'Venezolano', '1999-08-20', 'M'),
('BEN0030', 'SAMANTA', 'FLORES', 'V23758260', 'Venezolano', '1980-09-21', 'F'),
('BEN0031', 'RICARDO', 'ACOSTA', 'V23896173', 'Venezolano', '1981-10-22', 'M'),
('BEN0032', 'MELANIE', 'MOLINA', 'V24034086', 'Venezolano', '1982-11-23', 'F'),
('BEN0033', 'EDUARDO', 'DELGADO', 'V24171999', 'Venezolano', '1983-12-24', 'M');

-- ====================================================
-- INSERTS PARA LA TABLA: Asistencia
-- (818 registros)
-- ====================================================

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000002', 'SA0001', 'BEN0001', '2026-10-13 19:07:57'),
('AS000003', 'SA0001', 'BEN0002', '2026-10-13 19:07:57'),
('AS000004', 'SA0002', 'BEN0002', '2026-10-13 19:58:02'),
('AS000005', 'SA0002', 'BEN0001', '2026-10-13 19:58:02'),
('AS000006', 'SA0101', 'BEN0031', '2026-10-13 20:51:18'),
('AS000007', 'SA0101', 'BEN0015', '2026-10-13 20:51:18'),
('AS000008', 'SA0101', 'BEN0011', '2026-10-13 20:51:18'),
('AS000009', 'SA0101', 'BEN0021', '2026-10-13 20:51:18'),
('AS000010', 'SA0101', 'BEN0024', '2026-10-13 20:51:18'),
('AS000011', 'SA0101', 'BEN0033', '2026-10-13 20:51:18'),
('AS000012', 'SA0101', 'BEN0016', '2026-10-13 20:51:18'),
('AS000013', 'SA0101', 'BEN0010', '2026-10-13 20:51:18'),
('AS000014', 'SA0101', 'BEN0022', '2026-10-13 20:51:18'),
('AS000015', 'SA0101', 'BEN0025', '2026-10-13 20:51:18'),
('AS000016', 'SA0101', 'BEN0013', '2026-10-13 20:51:18'),
('AS000017', 'SA0101', 'BEN0018', '2026-10-13 20:51:18'),
('AS000018', 'SA0102', 'BEN0015', '2026-10-13 20:51:18'),
('AS000019', 'SA0102', 'BEN0024', '2026-10-13 20:51:18'),
('AS000020', 'SA0102', 'BEN0020', '2026-10-13 20:51:18'),
('AS000021', 'SA0102', 'BEN0026', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000022', 'SA0102', 'BEN0031', '2026-10-13 20:51:18'),
('AS000023', 'SA0102', 'BEN0010', '2026-10-13 20:51:18'),
('AS000024', 'SA0102', 'BEN0027', '2026-10-13 20:51:18'),
('AS000025', 'SA0102', 'BEN0023', '2026-10-13 20:51:18'),
('AS000026', 'SA0103', 'BEN0029', '2026-10-13 20:51:18'),
('AS000027', 'SA0103', 'BEN0010', '2026-10-13 20:51:18'),
('AS000028', 'SA0103', 'BEN0021', '2026-10-13 20:51:18'),
('AS000029', 'SA0103', 'BEN0025', '2026-10-13 20:51:18'),
('AS000030', 'SA0103', 'BEN0020', '2026-10-13 20:51:18'),
('AS000031', 'SA0103', 'BEN0032', '2026-10-13 20:51:18'),
('AS000032', 'SA0103', 'BEN0028', '2026-10-13 20:51:18'),
('AS000033', 'SA0103', 'BEN0011', '2026-10-13 20:51:18'),
('AS000034', 'SA0103', 'BEN0017', '2026-10-13 20:51:18'),
('AS000035', 'SA0103', 'BEN0027', '2026-10-13 20:51:18'),
('AS000036', 'SA0103', 'BEN0018', '2026-10-13 20:51:18'),
('AS000037', 'SA0103', 'BEN0015', '2026-10-13 20:51:18'),
('AS000038', 'SA0103', 'BEN0022', '2026-10-13 20:51:18'),
('AS000039', 'SA0103', 'BEN0026', '2026-10-13 20:51:18'),
('AS000040', 'SA0103', 'BEN0013', '2026-10-13 20:51:18'),
('AS000041', 'SA0103', 'BEN0012', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000042', 'SA0103', 'BEN0030', '2026-10-13 20:51:18'),
('AS000043', 'SA0103', 'BEN0023', '2026-10-13 20:51:18'),
('AS000044', 'SA0104', 'BEN0022', '2026-10-13 20:51:18'),
('AS000045', 'SA0104', 'BEN0033', '2026-10-13 20:51:18'),
('AS000046', 'SA0104', 'BEN0032', '2026-10-13 20:51:18'),
('AS000047', 'SA0104', 'BEN0016', '2026-10-13 20:51:18'),
('AS000048', 'SA0104', 'BEN0021', '2026-10-13 20:51:18'),
('AS000049', 'SA0104', 'BEN0025', '2026-10-13 20:51:18'),
('AS000050', 'SA0104', 'BEN0026', '2026-10-13 20:51:18'),
('AS000051', 'SA0104', 'BEN0031', '2026-10-13 20:51:18'),
('AS000052', 'SA0104', 'BEN0024', '2026-10-13 20:51:18'),
('AS000053', 'SA0104', 'BEN0014', '2026-10-13 20:51:18'),
('AS000054', 'SA0104', 'BEN0017', '2026-10-13 20:51:18'),
('AS000055', 'SA0109', 'BEN0033', '2026-10-13 20:51:18'),
('AS000056', 'SA0109', 'BEN0019', '2026-10-13 20:51:18'),
('AS000057', 'SA0109', 'BEN0026', '2026-10-13 20:51:18'),
('AS000058', 'SA0109', 'BEN0015', '2026-10-13 20:51:18'),
('AS000059', 'SA0109', 'BEN0020', '2026-10-13 20:51:18'),
('AS000060', 'SA0109', 'BEN0032', '2026-10-13 20:51:18'),
('AS000061', 'SA0109', 'BEN0021', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000062', 'SA0109', 'BEN0028', '2026-10-13 20:51:18'),
('AS000063', 'SA0109', 'BEN0029', '2026-10-13 20:51:18'),
('AS000064', 'SA0109', 'BEN0012', '2026-10-13 20:51:18'),
('AS000065', 'SA0109', 'BEN0016', '2026-10-13 20:51:18'),
('AS000066', 'SA0109', 'BEN0010', '2026-10-13 20:51:18'),
('AS000067', 'SA0109', 'BEN0011', '2026-10-13 20:51:18'),
('AS000068', 'SA0109', 'BEN0018', '2026-10-13 20:51:18'),
('AS000069', 'SA0109', 'BEN0023', '2026-10-13 20:51:18'),
('AS000070', 'SA0109', 'BEN0014', '2026-10-13 20:51:18'),
('AS000071', 'SA0109', 'BEN0024', '2026-10-13 20:51:18'),
('AS000072', 'SA0109', 'BEN0017', '2026-10-13 20:51:18'),
('AS000073', 'SA0110', 'BEN0032', '2026-10-13 20:51:18'),
('AS000074', 'SA0110', 'BEN0018', '2026-10-13 20:51:18'),
('AS000075', 'SA0110', 'BEN0026', '2026-10-13 20:51:18'),
('AS000076', 'SA0110', 'BEN0022', '2026-10-13 20:51:18'),
('AS000077', 'SA0110', 'BEN0029', '2026-10-13 20:51:18'),
('AS000078', 'SA0110', 'BEN0017', '2026-10-13 20:51:18'),
('AS000079', 'SA0110', 'BEN0021', '2026-10-13 20:51:18'),
('AS000080', 'SA0110', 'BEN0031', '2026-10-13 20:51:18'),
('AS000081', 'SA0110', 'BEN0015', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000082', 'SA0110', 'BEN0016', '2026-10-13 20:51:18'),
('AS000083', 'SA0110', 'BEN0025', '2026-10-13 20:51:18'),
('AS000084', 'SA0110', 'BEN0024', '2026-10-13 20:51:18'),
('AS000085', 'SA0111', 'BEN0012', '2026-10-13 20:51:18'),
('AS000086', 'SA0111', 'BEN0022', '2026-10-13 20:51:18'),
('AS000087', 'SA0111', 'BEN0010', '2026-10-13 20:51:18'),
('AS000088', 'SA0111', 'BEN0027', '2026-10-13 20:51:18'),
('AS000089', 'SA0111', 'BEN0011', '2026-10-13 20:51:18'),
('AS000090', 'SA0111', 'BEN0032', '2026-10-13 20:51:18'),
('AS000091', 'SA0111', 'BEN0020', '2026-10-13 20:51:18'),
('AS000092', 'SA0111', 'BEN0026', '2026-10-13 20:51:18'),
('AS000093', 'SA0111', 'BEN0019', '2026-10-13 20:51:18'),
('AS000094', 'SA0112', 'BEN0029', '2026-10-13 20:51:18'),
('AS000095', 'SA0112', 'BEN0031', '2026-10-13 20:51:18'),
('AS000096', 'SA0112', 'BEN0010', '2026-10-13 20:51:18'),
('AS000097', 'SA0112', 'BEN0020', '2026-10-13 20:51:18'),
('AS000098', 'SA0112', 'BEN0017', '2026-10-13 20:51:18'),
('AS000099', 'SA0112', 'BEN0015', '2026-10-13 20:51:18'),
('AS000100', 'SA0112', 'BEN0028', '2026-10-13 20:51:18'),
('AS000101', 'SA0112', 'BEN0033', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000102', 'SA0112', 'BEN0027', '2026-10-13 20:51:18'),
('AS000103', 'SA0112', 'BEN0022', '2026-10-13 20:51:18'),
('AS000104', 'SA0112', 'BEN0025', '2026-10-13 20:51:18'),
('AS000105', 'SA0112', 'BEN0024', '2026-10-13 20:51:18'),
('AS000106', 'SA0112', 'BEN0032', '2026-10-13 20:51:18'),
('AS000107', 'SA0112', 'BEN0023', '2026-10-13 20:51:18'),
('AS000108', 'SA0112', 'BEN0018', '2026-10-13 20:51:18'),
('AS000109', 'SA0112', 'BEN0012', '2026-10-13 20:51:18'),
('AS000110', 'SA0112', 'BEN0019', '2026-10-13 20:51:18'),
('AS000111', 'SA0112', 'BEN0030', '2026-10-13 20:51:18'),
('AS000112', 'SA0113', 'BEN0016', '2026-10-13 20:51:18'),
('AS000113', 'SA0113', 'BEN0026', '2026-10-13 20:51:18'),
('AS000114', 'SA0113', 'BEN0027', '2026-10-13 20:51:18'),
('AS000115', 'SA0113', 'BEN0032', '2026-10-13 20:51:18'),
('AS000116', 'SA0113', 'BEN0013', '2026-10-13 20:51:18'),
('AS000117', 'SA0113', 'BEN0017', '2026-10-13 20:51:18'),
('AS000118', 'SA0113', 'BEN0029', '2026-10-13 20:51:18'),
('AS000119', 'SA0113', 'BEN0010', '2026-10-13 20:51:18'),
('AS000120', 'SA0113', 'BEN0011', '2026-10-13 20:51:18'),
('AS000121', 'SA0115', 'BEN0031', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000122', 'SA0115', 'BEN0028', '2026-10-13 20:51:18'),
('AS000123', 'SA0115', 'BEN0016', '2026-10-13 20:51:18'),
('AS000124', 'SA0115', 'BEN0030', '2026-10-13 20:51:18'),
('AS000125', 'SA0115', 'BEN0015', '2026-10-13 20:51:18'),
('AS000126', 'SA0115', 'BEN0025', '2026-10-13 20:51:18'),
('AS000127', 'SA0115', 'BEN0032', '2026-10-13 20:51:18'),
('AS000128', 'SA0115', 'BEN0018', '2026-10-13 20:51:18'),
('AS000129', 'SA0118', 'BEN0013', '2026-10-13 20:51:18'),
('AS000130', 'SA0118', 'BEN0023', '2026-10-13 20:51:18'),
('AS000131', 'SA0118', 'BEN0026', '2026-10-13 20:51:18'),
('AS000132', 'SA0118', 'BEN0017', '2026-10-13 20:51:18'),
('AS000133', 'SA0118', 'BEN0027', '2026-10-13 20:51:18'),
('AS000134', 'SA0118', 'BEN0032', '2026-10-13 20:51:18'),
('AS000135', 'SA0118', 'BEN0028', '2026-10-13 20:51:18'),
('AS000136', 'SA0118', 'BEN0022', '2026-10-13 20:51:18'),
('AS000137', 'SA0118', 'BEN0021', '2026-10-13 20:51:18'),
('AS000138', 'SA0118', 'BEN0012', '2026-10-13 20:51:18'),
('AS000139', 'SA0118', 'BEN0015', '2026-10-13 20:51:18'),
('AS000140', 'SA0118', 'BEN0025', '2026-10-13 20:51:18'),
('AS000141', 'SA0118', 'BEN0014', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000142', 'SA0118', 'BEN0019', '2026-10-13 20:51:18'),
('AS000143', 'SA0118', 'BEN0016', '2026-10-13 20:51:18'),
('AS000144', 'SA0118', 'BEN0024', '2026-10-13 20:51:18'),
('AS000145', 'SA0119', 'BEN0026', '2026-10-13 20:51:18'),
('AS000146', 'SA0119', 'BEN0032', '2026-10-13 20:51:18'),
('AS000147', 'SA0119', 'BEN0025', '2026-10-13 20:51:18'),
('AS000148', 'SA0119', 'BEN0022', '2026-10-13 20:51:18'),
('AS000149', 'SA0119', 'BEN0010', '2026-10-13 20:51:18'),
('AS000150', 'SA0119', 'BEN0017', '2026-10-13 20:51:18'),
('AS000151', 'SA0119', 'BEN0033', '2026-10-13 20:51:18'),
('AS000152', 'SA0119', 'BEN0020', '2026-10-13 20:51:18'),
('AS000153', 'SA0119', 'BEN0015', '2026-10-13 20:51:18'),
('AS000154', 'SA0119', 'BEN0016', '2026-10-13 20:51:18'),
('AS000155', 'SA0119', 'BEN0014', '2026-10-13 20:51:18'),
('AS000156', 'SA0119', 'BEN0018', '2026-10-13 20:51:18'),
('AS000157', 'SA0119', 'BEN0023', '2026-10-13 20:51:18'),
('AS000158', 'SA0119', 'BEN0021', '2026-10-13 20:51:18'),
('AS000159', 'SA0119', 'BEN0029', '2026-10-13 20:51:18'),
('AS000160', 'SA0119', 'BEN0031', '2026-10-13 20:51:18'),
('AS000161', 'SA0119', 'BEN0024', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000162', 'SA0119', 'BEN0019', '2026-10-13 20:51:18'),
('AS000163', 'SA0119', 'BEN0028', '2026-10-13 20:51:18'),
('AS000164', 'SA0120', 'BEN0010', '2026-10-13 20:51:18'),
('AS000165', 'SA0120', 'BEN0030', '2026-10-13 20:51:18'),
('AS000166', 'SA0120', 'BEN0012', '2026-10-13 20:51:18'),
('AS000167', 'SA0120', 'BEN0017', '2026-10-13 20:51:18'),
('AS000168', 'SA0120', 'BEN0028', '2026-10-13 20:51:18'),
('AS000169', 'SA0120', 'BEN0015', '2026-10-13 20:51:18'),
('AS000170', 'SA0120', 'BEN0026', '2026-10-13 20:51:18'),
('AS000171', 'SA0121', 'BEN0018', '2026-10-13 20:51:18'),
('AS000172', 'SA0121', 'BEN0014', '2026-10-13 20:51:18'),
('AS000173', 'SA0121', 'BEN0028', '2026-10-13 20:51:18'),
('AS000174', 'SA0121', 'BEN0011', '2026-10-13 20:51:18'),
('AS000175', 'SA0121', 'BEN0010', '2026-10-13 20:51:18'),
('AS000176', 'SA0121', 'BEN0021', '2026-10-13 20:51:18'),
('AS000177', 'SA0121', 'BEN0025', '2026-10-13 20:51:18'),
('AS000178', 'SA0121', 'BEN0020', '2026-10-13 20:51:18'),
('AS000179', 'SA0121', 'BEN0017', '2026-10-13 20:51:18'),
('AS000180', 'SA0121', 'BEN0012', '2026-10-13 20:51:18'),
('AS000181', 'SA0121', 'BEN0029', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000182', 'SA0121', 'BEN0024', '2026-10-13 20:51:18'),
('AS000183', 'SA0121', 'BEN0030', '2026-10-13 20:51:18'),
('AS000184', 'SA0121', 'BEN0033', '2026-10-13 20:51:18'),
('AS000185', 'SA0121', 'BEN0015', '2026-10-13 20:51:18'),
('AS000186', 'SA0121', 'BEN0026', '2026-10-13 20:51:18'),
('AS000187', 'SA0122', 'BEN0033', '2026-10-13 20:51:18'),
('AS000188', 'SA0122', 'BEN0028', '2026-10-13 20:51:18'),
('AS000189', 'SA0122', 'BEN0021', '2026-10-13 20:51:18'),
('AS000190', 'SA0122', 'BEN0023', '2026-10-13 20:51:18'),
('AS000191', 'SA0122', 'BEN0019', '2026-10-13 20:51:18'),
('AS000192', 'SA0122', 'BEN0024', '2026-10-13 20:51:18'),
('AS000193', 'SA0122', 'BEN0022', '2026-10-13 20:51:18'),
('AS000194', 'SA0122', 'BEN0011', '2026-10-13 20:51:18'),
('AS000195', 'SA0122', 'BEN0013', '2026-10-13 20:51:18'),
('AS000196', 'SA0122', 'BEN0017', '2026-10-13 20:51:18'),
('AS000197', 'SA0122', 'BEN0029', '2026-10-13 20:51:18'),
('AS000198', 'SA0122', 'BEN0025', '2026-10-13 20:51:18'),
('AS000199', 'SA0122', 'BEN0030', '2026-10-13 20:51:18'),
('AS000200', 'SA0122', 'BEN0016', '2026-10-13 20:51:18'),
('AS000201', 'SA0122', 'BEN0010', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000202', 'SA0122', 'BEN0026', '2026-10-13 20:51:18'),
('AS000203', 'SA0122', 'BEN0014', '2026-10-13 20:51:18'),
('AS000204', 'SA0122', 'BEN0018', '2026-10-13 20:51:18'),
('AS000205', 'SA0122', 'BEN0031', '2026-10-13 20:51:18'),
('AS000206', 'SA0122', 'BEN0015', '2026-10-13 20:51:18'),
('AS000207', 'SA0123', 'BEN0013', '2026-10-13 20:51:18'),
('AS000208', 'SA0123', 'BEN0024', '2026-10-13 20:51:18'),
('AS000209', 'SA0123', 'BEN0033', '2026-10-13 20:51:18'),
('AS000210', 'SA0123', 'BEN0012', '2026-10-13 20:51:18'),
('AS000211', 'SA0123', 'BEN0029', '2026-10-13 20:51:18'),
('AS000212', 'SA0123', 'BEN0030', '2026-10-13 20:51:18'),
('AS000213', 'SA0123', 'BEN0010', '2026-10-13 20:51:18'),
('AS000214', 'SA0123', 'BEN0019', '2026-10-13 20:51:18'),
('AS000215', 'SA0123', 'BEN0031', '2026-10-13 20:51:18'),
('AS000216', 'SA0123', 'BEN0018', '2026-10-13 20:51:18'),
('AS000217', 'SA0123', 'BEN0021', '2026-10-13 20:51:18'),
('AS000218', 'SA0123', 'BEN0017', '2026-10-13 20:51:18'),
('AS000219', 'SA0123', 'BEN0025', '2026-10-13 20:51:18'),
('AS000220', 'SA0123', 'BEN0014', '2026-10-13 20:51:18'),
('AS000221', 'SA0123', 'BEN0027', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000222', 'SA0123', 'BEN0023', '2026-10-13 20:51:18'),
('AS000223', 'SA0123', 'BEN0032', '2026-10-13 20:51:18'),
('AS000224', 'SA0123', 'BEN0026', '2026-10-13 20:51:18'),
('AS000225', 'SA0123', 'BEN0028', '2026-10-13 20:51:18'),
('AS000239', 'SA0125', 'BEN0031', '2026-10-13 20:51:18'),
('AS000240', 'SA0125', 'BEN0026', '2026-10-13 20:51:18'),
('AS000241', 'SA0125', 'BEN0033', '2026-10-13 20:51:18'),
('AS000242', 'SA0125', 'BEN0020', '2026-10-13 20:51:18'),
('AS000243', 'SA0125', 'BEN0013', '2026-10-13 20:51:18'),
('AS000244', 'SA0125', 'BEN0018', '2026-10-13 20:51:18'),
('AS000245', 'SA0126', 'BEN0030', '2026-10-13 20:51:18'),
('AS000246', 'SA0126', 'BEN0015', '2026-10-13 20:51:18'),
('AS000247', 'SA0126', 'BEN0024', '2026-10-13 20:51:18'),
('AS000248', 'SA0126', 'BEN0022', '2026-10-13 20:51:18'),
('AS000249', 'SA0126', 'BEN0016', '2026-10-13 20:51:18'),
('AS000250', 'SA0126', 'BEN0023', '2026-10-13 20:51:18'),
('AS000251', 'SA0126', 'BEN0018', '2026-10-13 20:51:18'),
('AS000252', 'SA0126', 'BEN0010', '2026-10-13 20:51:18'),
('AS000253', 'SA0126', 'BEN0026', '2026-10-13 20:51:18'),
('AS000254', 'SA0126', 'BEN0033', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000255', 'SA0126', 'BEN0019', '2026-10-13 20:51:18'),
('AS000256', 'SA0126', 'BEN0011', '2026-10-13 20:51:18'),
('AS000257', 'SA0126', 'BEN0013', '2026-10-13 20:51:18'),
('AS000258', 'SA0127', 'BEN0013', '2026-10-13 20:51:18'),
('AS000259', 'SA0127', 'BEN0027', '2026-10-13 20:51:18'),
('AS000260', 'SA0127', 'BEN0029', '2026-10-13 20:51:18'),
('AS000261', 'SA0127', 'BEN0017', '2026-10-13 20:51:18'),
('AS000262', 'SA0127', 'BEN0015', '2026-10-13 20:51:18'),
('AS000263', 'SA0127', 'BEN0018', '2026-10-13 20:51:18'),
('AS000264', 'SA0127', 'BEN0030', '2026-10-13 20:51:18'),
('AS000265', 'SA0127', 'BEN0026', '2026-10-13 20:51:18'),
('AS000266', 'SA0127', 'BEN0025', '2026-10-13 20:51:18'),
('AS000267', 'SA0127', 'BEN0020', '2026-10-13 20:51:18'),
('AS000268', 'SA0127', 'BEN0033', '2026-10-13 20:51:18'),
('AS000269', 'SA0127', 'BEN0019', '2026-10-13 20:51:18'),
('AS000270', 'SA0127', 'BEN0022', '2026-10-13 20:51:18'),
('AS000271', 'SA0127', 'BEN0032', '2026-10-13 20:51:18'),
('AS000272', 'SA0127', 'BEN0024', '2026-10-13 20:51:18'),
('AS000273', 'SA0127', 'BEN0016', '2026-10-13 20:51:18'),
('AS000274', 'SA0128', 'BEN0023', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000275', 'SA0128', 'BEN0012', '2026-10-13 20:51:18'),
('AS000276', 'SA0128', 'BEN0014', '2026-10-13 20:51:18'),
('AS000277', 'SA0128', 'BEN0015', '2026-10-13 20:51:18'),
('AS000278', 'SA0128', 'BEN0018', '2026-10-13 20:51:18'),
('AS000279', 'SA0128', 'BEN0029', '2026-10-13 20:51:18'),
('AS000280', 'SA0128', 'BEN0017', '2026-10-13 20:51:18'),
('AS000281', 'SA0128', 'BEN0033', '2026-10-13 20:51:18'),
('AS000282', 'SA0128', 'BEN0025', '2026-10-13 20:51:18'),
('AS000283', 'SA0128', 'BEN0024', '2026-10-13 20:51:18'),
('AS000284', 'SA0128', 'BEN0010', '2026-10-13 20:51:18'),
('AS000285', 'SA0128', 'BEN0013', '2026-10-13 20:51:18'),
('AS000286', 'SA0128', 'BEN0011', '2026-10-13 20:51:18'),
('AS000287', 'SA0128', 'BEN0030', '2026-10-13 20:51:18'),
('AS000288', 'SA0128', 'BEN0016', '2026-10-13 20:51:18'),
('AS000289', 'SA0129', 'BEN0022', '2026-10-13 20:51:18'),
('AS000290', 'SA0129', 'BEN0032', '2026-10-13 20:51:18'),
('AS000291', 'SA0129', 'BEN0010', '2026-10-13 20:51:18'),
('AS000292', 'SA0129', 'BEN0017', '2026-10-13 20:51:18'),
('AS000293', 'SA0129', 'BEN0024', '2026-10-13 20:51:18'),
('AS000294', 'SA0133', 'BEN0026', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000295', 'SA0133', 'BEN0020', '2026-10-13 20:51:18'),
('AS000296', 'SA0133', 'BEN0021', '2026-10-13 20:51:18'),
('AS000297', 'SA0133', 'BEN0012', '2026-10-13 20:51:18'),
('AS000298', 'SA0133', 'BEN0022', '2026-10-13 20:51:18'),
('AS000299', 'SA0133', 'BEN0029', '2026-10-13 20:51:18'),
('AS000300', 'SA0133', 'BEN0032', '2026-10-13 20:51:18'),
('AS000301', 'SA0133', 'BEN0011', '2026-10-13 20:51:18'),
('AS000302', 'SA0133', 'BEN0014', '2026-10-13 20:51:18'),
('AS000303', 'SA0133', 'BEN0024', '2026-10-13 20:51:18'),
('AS000304', 'SA0133', 'BEN0018', '2026-10-13 20:51:18'),
('AS000305', 'SA0133', 'BEN0031', '2026-10-13 20:51:18'),
('AS000306', 'SA0133', 'BEN0016', '2026-10-13 20:51:18'),
('AS000307', 'SA0133', 'BEN0030', '2026-10-13 20:51:18'),
('AS000308', 'SA0133', 'BEN0033', '2026-10-13 20:51:18'),
('AS000309', 'SA0133', 'BEN0017', '2026-10-13 20:51:18'),
('AS000310', 'SA0133', 'BEN0019', '2026-10-13 20:51:18'),
('AS000311', 'SA0134', 'BEN0026', '2026-10-13 20:51:18'),
('AS000312', 'SA0134', 'BEN0012', '2026-10-13 20:51:18'),
('AS000313', 'SA0134', 'BEN0032', '2026-10-13 20:51:18'),
('AS000314', 'SA0134', 'BEN0029', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000315', 'SA0134', 'BEN0018', '2026-10-13 20:51:18'),
('AS000316', 'SA0134', 'BEN0027', '2026-10-13 20:51:18'),
('AS000317', 'SA0135', 'BEN0016', '2026-10-13 20:51:18'),
('AS000318', 'SA0135', 'BEN0027', '2026-10-13 20:51:18'),
('AS000319', 'SA0135', 'BEN0015', '2026-10-13 20:51:18'),
('AS000320', 'SA0135', 'BEN0032', '2026-10-13 20:51:18'),
('AS000321', 'SA0135', 'BEN0018', '2026-10-13 20:51:18'),
('AS000322', 'SA0135', 'BEN0026', '2026-10-13 20:51:18'),
('AS000323', 'SA0135', 'BEN0030', '2026-10-13 20:51:18'),
('AS000324', 'SA0135', 'BEN0031', '2026-10-13 20:51:18'),
('AS000325', 'SA0136', 'BEN0028', '2026-10-13 20:51:18'),
('AS000326', 'SA0136', 'BEN0015', '2026-10-13 20:51:18'),
('AS000327', 'SA0136', 'BEN0031', '2026-10-13 20:51:18'),
('AS000328', 'SA0136', 'BEN0023', '2026-10-13 20:51:18'),
('AS000329', 'SA0136', 'BEN0026', '2026-10-13 20:51:18'),
('AS000330', 'SA0136', 'BEN0033', '2026-10-13 20:51:18'),
('AS000331', 'SA0136', 'BEN0016', '2026-10-13 20:51:18'),
('AS000332', 'SA0136', 'BEN0018', '2026-10-13 20:51:18'),
('AS000333', 'SA0136', 'BEN0020', '2026-10-13 20:51:18'),
('AS000334', 'SA0136', 'BEN0032', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000335', 'SA0136', 'BEN0010', '2026-10-13 20:51:18'),
('AS000336', 'SA0136', 'BEN0021', '2026-10-13 20:51:18'),
('AS000337', 'SA0136', 'BEN0029', '2026-10-13 20:51:18'),
('AS000338', 'SA0136', 'BEN0025', '2026-10-13 20:51:18'),
('AS000339', 'SA0136', 'BEN0017', '2026-10-13 20:51:18'),
('AS000340', 'SA0136', 'BEN0024', '2026-10-13 20:51:18'),
('AS000341', 'SA0136', 'BEN0027', '2026-10-13 20:51:18'),
('AS000342', 'SA0136', 'BEN0013', '2026-10-13 20:51:18'),
('AS000343', 'SA0136', 'BEN0012', '2026-10-13 20:51:18'),
('AS000344', 'SA0137', 'BEN0027', '2026-10-13 20:51:18'),
('AS000345', 'SA0137', 'BEN0028', '2026-10-13 20:51:18'),
('AS000346', 'SA0137', 'BEN0031', '2026-10-13 20:51:18'),
('AS000347', 'SA0137', 'BEN0024', '2026-10-13 20:51:18'),
('AS000348', 'SA0137', 'BEN0029', '2026-10-13 20:51:18'),
('AS000349', 'SA0137', 'BEN0011', '2026-10-13 20:51:18'),
('AS000350', 'SA0137', 'BEN0013', '2026-10-13 20:51:18'),
('AS000351', 'SA0137', 'BEN0015', '2026-10-13 20:51:18'),
('AS000352', 'SA0137', 'BEN0014', '2026-10-13 20:51:18'),
('AS000353', 'SA0137', 'BEN0025', '2026-10-13 20:51:18'),
('AS000354', 'SA0137', 'BEN0022', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000355', 'SA0137', 'BEN0010', '2026-10-13 20:51:18'),
('AS000356', 'SA0137', 'BEN0021', '2026-10-13 20:51:18'),
('AS000357', 'SA0137', 'BEN0023', '2026-10-13 20:51:18'),
('AS000358', 'SA0137', 'BEN0018', '2026-10-13 20:51:18'),
('AS000359', 'SA0137', 'BEN0020', '2026-10-13 20:51:18'),
('AS000360', 'SA0137', 'BEN0026', '2026-10-13 20:51:18'),
('AS000361', 'SA0137', 'BEN0033', '2026-10-13 20:51:18'),
('AS000362', 'SA0137', 'BEN0030', '2026-10-13 20:51:18'),
('AS000363', 'SA0138', 'BEN0023', '2026-10-13 20:51:18'),
('AS000364', 'SA0138', 'BEN0022', '2026-10-13 20:51:18'),
('AS000365', 'SA0138', 'BEN0010', '2026-10-13 20:51:18'),
('AS000366', 'SA0138', 'BEN0020', '2026-10-13 20:51:18'),
('AS000367', 'SA0138', 'BEN0019', '2026-10-13 20:51:18'),
('AS000368', 'SA0138', 'BEN0024', '2026-10-13 20:51:18'),
('AS000369', 'SA0138', 'BEN0015', '2026-10-13 20:51:18'),
('AS000370', 'SA0138', 'BEN0030', '2026-10-13 20:51:18'),
('AS000371', 'SA0138', 'BEN0016', '2026-10-13 20:51:18'),
('AS000372', 'SA0139', 'BEN0015', '2026-10-13 20:51:18'),
('AS000373', 'SA0139', 'BEN0021', '2026-10-13 20:51:18'),
('AS000374', 'SA0139', 'BEN0033', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000375', 'SA0139', 'BEN0025', '2026-10-13 20:51:18'),
('AS000376', 'SA0139', 'BEN0031', '2026-10-13 20:51:18'),
('AS000377', 'SA0139', 'BEN0024', '2026-10-13 20:51:18'),
('AS000378', 'SA0139', 'BEN0020', '2026-10-13 20:51:18'),
('AS000379', 'SA0139', 'BEN0012', '2026-10-13 20:51:18'),
('AS000380', 'SA0139', 'BEN0022', '2026-10-13 20:51:18'),
('AS000381', 'SA0139', 'BEN0028', '2026-10-13 20:51:18'),
('AS000382', 'SA0140', 'BEN0018', '2026-10-13 20:51:18'),
('AS000383', 'SA0140', 'BEN0010', '2026-10-13 20:51:18'),
('AS000384', 'SA0140', 'BEN0028', '2026-10-13 20:51:18'),
('AS000385', 'SA0140', 'BEN0011', '2026-10-13 20:51:18'),
('AS000386', 'SA0140', 'BEN0032', '2026-10-13 20:51:18'),
('AS000387', 'SA0141', 'BEN0015', '2026-10-13 20:51:18'),
('AS000388', 'SA0141', 'BEN0022', '2026-10-13 20:51:18'),
('AS000389', 'SA0141', 'BEN0027', '2026-10-13 20:51:18'),
('AS000390', 'SA0141', 'BEN0010', '2026-10-13 20:51:18'),
('AS000391', 'SA0141', 'BEN0019', '2026-10-13 20:51:18'),
('AS000392', 'SA0142', 'BEN0022', '2026-10-13 20:51:18'),
('AS000393', 'SA0142', 'BEN0024', '2026-10-13 20:51:18'),
('AS000394', 'SA0142', 'BEN0011', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000395', 'SA0142', 'BEN0018', '2026-10-13 20:51:18'),
('AS000396', 'SA0142', 'BEN0012', '2026-10-13 20:51:18'),
('AS000397', 'SA0142', 'BEN0032', '2026-10-13 20:51:18'),
('AS000398', 'SA0142', 'BEN0030', '2026-10-13 20:51:18'),
('AS000399', 'SA0142', 'BEN0017', '2026-10-13 20:51:18'),
('AS000400', 'SA0142', 'BEN0020', '2026-10-13 20:51:18'),
('AS000401', 'SA0142', 'BEN0028', '2026-10-13 20:51:18'),
('AS000402', 'SA0142', 'BEN0033', '2026-10-13 20:51:18'),
('AS000403', 'SA0142', 'BEN0019', '2026-10-13 20:51:18'),
('AS000404', 'SA0142', 'BEN0026', '2026-10-13 20:51:18'),
('AS000405', 'SA0142', 'BEN0010', '2026-10-13 20:51:18'),
('AS000406', 'SA0142', 'BEN0014', '2026-10-13 20:51:18'),
('AS000407', 'SA0142', 'BEN0013', '2026-10-13 20:51:18'),
('AS000408', 'SA0142', 'BEN0023', '2026-10-13 20:51:18'),
('AS000409', 'SA0142', 'BEN0015', '2026-10-13 20:51:18'),
('AS000410', 'SA0142', 'BEN0016', '2026-10-13 20:51:18'),
('AS000411', 'SA0142', 'BEN0021', '2026-10-13 20:51:18'),
('AS000412', 'SA0143', 'BEN0012', '2026-10-13 20:51:18'),
('AS000413', 'SA0143', 'BEN0029', '2026-10-13 20:51:18'),
('AS000414', 'SA0143', 'BEN0021', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000415', 'SA0143', 'BEN0022', '2026-10-13 20:51:18'),
('AS000416', 'SA0143', 'BEN0014', '2026-10-13 20:51:18'),
('AS000417', 'SA0143', 'BEN0020', '2026-10-13 20:51:18'),
('AS000418', 'SA0143', 'BEN0016', '2026-10-13 20:51:18'),
('AS000419', 'SA0143', 'BEN0018', '2026-10-13 20:51:18'),
('AS000420', 'SA0143', 'BEN0011', '2026-10-13 20:51:18'),
('AS000421', 'SA0143', 'BEN0028', '2026-10-13 20:51:18'),
('AS000422', 'SA0143', 'BEN0031', '2026-10-13 20:51:18'),
('AS000423', 'SA0144', 'BEN0012', '2026-10-13 20:51:18'),
('AS000424', 'SA0144', 'BEN0014', '2026-10-13 20:51:18'),
('AS000425', 'SA0144', 'BEN0030', '2026-10-13 20:51:18'),
('AS000426', 'SA0144', 'BEN0026', '2026-10-13 20:51:18'),
('AS000427', 'SA0144', 'BEN0031', '2026-10-13 20:51:18'),
('AS000428', 'SA0144', 'BEN0029', '2026-10-13 20:51:18'),
('AS000429', 'SA0144', 'BEN0013', '2026-10-13 20:51:18'),
('AS000430', 'SA0144', 'BEN0015', '2026-10-13 20:51:18'),
('AS000431', 'SA0144', 'BEN0024', '2026-10-13 20:51:18'),
('AS000432', 'SA0144', 'BEN0032', '2026-10-13 20:51:18'),
('AS000433', 'SA0144', 'BEN0011', '2026-10-13 20:51:18'),
('AS000434', 'SA0144', 'BEN0017', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000435', 'SA0144', 'BEN0023', '2026-10-13 20:51:18'),
('AS000436', 'SA0144', 'BEN0033', '2026-10-13 20:51:18'),
('AS000437', 'SA0144', 'BEN0018', '2026-10-13 20:51:18'),
('AS000438', 'SA0144', 'BEN0027', '2026-10-13 20:51:18'),
('AS000439', 'SA0144', 'BEN0016', '2026-10-13 20:51:18'),
('AS000440', 'SA0145', 'BEN0021', '2026-10-13 20:51:18'),
('AS000441', 'SA0145', 'BEN0016', '2026-10-13 20:51:18'),
('AS000442', 'SA0145', 'BEN0012', '2026-10-13 20:51:18'),
('AS000443', 'SA0145', 'BEN0026', '2026-10-13 20:51:18'),
('AS000444', 'SA0145', 'BEN0023', '2026-10-13 20:51:18'),
('AS000445', 'SA0145', 'BEN0011', '2026-10-13 20:51:18'),
('AS000446', 'SA0145', 'BEN0028', '2026-10-13 20:51:18'),
('AS000447', 'SA0145', 'BEN0030', '2026-10-13 20:51:18'),
('AS000448', 'SA0145', 'BEN0027', '2026-10-13 20:51:18'),
('AS000449', 'SA0145', 'BEN0018', '2026-10-13 20:51:18'),
('AS000450', 'SA0145', 'BEN0024', '2026-10-13 20:51:18'),
('AS000451', 'SA0145', 'BEN0019', '2026-10-13 20:51:18'),
('AS000452', 'SA0145', 'BEN0031', '2026-10-13 20:51:18'),
('AS000453', 'SA0145', 'BEN0014', '2026-10-13 20:51:18'),
('AS000454', 'SA0145', 'BEN0032', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000455', 'SA0145', 'BEN0025', '2026-10-13 20:51:18'),
('AS000456', 'SA0145', 'BEN0033', '2026-10-13 20:51:18'),
('AS000457', 'SA0145', 'BEN0017', '2026-10-13 20:51:18'),
('AS000458', 'SA0146', 'BEN0026', '2026-10-13 20:51:18'),
('AS000459', 'SA0146', 'BEN0018', '2026-10-13 20:51:18'),
('AS000460', 'SA0146', 'BEN0021', '2026-10-13 20:51:18'),
('AS000461', 'SA0146', 'BEN0028', '2026-10-13 20:51:18'),
('AS000462', 'SA0146', 'BEN0032', '2026-10-13 20:51:18'),
('AS000463', 'SA0146', 'BEN0014', '2026-10-13 20:51:18'),
('AS000464', 'SA0147', 'BEN0033', '2026-10-13 20:51:18'),
('AS000465', 'SA0147', 'BEN0021', '2026-10-13 20:51:18'),
('AS000466', 'SA0147', 'BEN0030', '2026-10-13 20:51:18'),
('AS000467', 'SA0147', 'BEN0032', '2026-10-13 20:51:18'),
('AS000468', 'SA0147', 'BEN0010', '2026-10-13 20:51:18'),
('AS000469', 'SA0147', 'BEN0028', '2026-10-13 20:51:18'),
('AS000470', 'SA0148', 'BEN0014', '2026-10-13 20:51:18'),
('AS000471', 'SA0148', 'BEN0012', '2026-10-13 20:51:18'),
('AS000472', 'SA0148', 'BEN0030', '2026-10-13 20:51:18'),
('AS000473', 'SA0148', 'BEN0024', '2026-10-13 20:51:18'),
('AS000474', 'SA0148', 'BEN0023', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000475', 'SA0148', 'BEN0028', '2026-10-13 20:51:18'),
('AS000476', 'SA0148', 'BEN0022', '2026-10-13 20:51:18'),
('AS000477', 'SA0148', 'BEN0029', '2026-10-13 20:51:18'),
('AS000478', 'SA0148', 'BEN0016', '2026-10-13 20:51:18'),
('AS000479', 'SA0148', 'BEN0017', '2026-10-13 20:51:18'),
('AS000480', 'SA0148', 'BEN0019', '2026-10-13 20:51:18'),
('AS000481', 'SA0148', 'BEN0018', '2026-10-13 20:51:18'),
('AS000482', 'SA0148', 'BEN0011', '2026-10-13 20:51:18'),
('AS000483', 'SA0148', 'BEN0026', '2026-10-13 20:51:18'),
('AS000484', 'SA0148', 'BEN0010', '2026-10-13 20:51:18'),
('AS000485', 'SA0148', 'BEN0031', '2026-10-13 20:51:18'),
('AS000486', 'SA0148', 'BEN0021', '2026-10-13 20:51:18'),
('AS000487', 'SA0148', 'BEN0025', '2026-10-13 20:51:18'),
('AS000488', 'SA0148', 'BEN0033', '2026-10-13 20:51:18'),
('AS000489', 'SA0148', 'BEN0015', '2026-10-13 20:51:18'),
('AS000490', 'SA0149', 'BEN0010', '2026-10-13 20:51:18'),
('AS000491', 'SA0149', 'BEN0017', '2026-10-13 20:51:18'),
('AS000492', 'SA0149', 'BEN0025', '2026-10-13 20:51:18'),
('AS000493', 'SA0149', 'BEN0020', '2026-10-13 20:51:18'),
('AS000494', 'SA0149', 'BEN0011', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000495', 'SA0149', 'BEN0022', '2026-10-13 20:51:18'),
('AS000496', 'SA0149', 'BEN0012', '2026-10-13 20:51:18'),
('AS000497', 'SA0149', 'BEN0030', '2026-10-13 20:51:18'),
('AS000498', 'SA0149', 'BEN0029', '2026-10-13 20:51:18'),
('AS000499', 'SA0149', 'BEN0018', '2026-10-13 20:51:18'),
('AS000500', 'SA0153', 'BEN0025', '2026-10-13 20:51:18'),
('AS000501', 'SA0153', 'BEN0031', '2026-10-13 20:51:18'),
('AS000502', 'SA0153', 'BEN0032', '2026-10-13 20:51:18'),
('AS000503', 'SA0153', 'BEN0013', '2026-10-13 20:51:18'),
('AS000504', 'SA0153', 'BEN0010', '2026-10-13 20:51:18'),
('AS000505', 'SA0153', 'BEN0024', '2026-10-13 20:51:18'),
('AS000506', 'SA0153', 'BEN0017', '2026-10-13 20:51:18'),
('AS000507', 'SA0153', 'BEN0028', '2026-10-13 20:51:18'),
('AS000508', 'SA0153', 'BEN0026', '2026-10-13 20:51:18'),
('AS000509', 'SA0153', 'BEN0019', '2026-10-13 20:51:18'),
('AS000510', 'SA0153', 'BEN0029', '2026-10-13 20:51:18'),
('AS000511', 'SA0153', 'BEN0033', '2026-10-13 20:51:18'),
('AS000512', 'SA0153', 'BEN0015', '2026-10-13 20:51:18'),
('AS000513', 'SA0153', 'BEN0012', '2026-10-13 20:51:18'),
('AS000514', 'SA0153', 'BEN0021', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000515', 'SA0153', 'BEN0014', '2026-10-13 20:51:18'),
('AS000516', 'SA0153', 'BEN0020', '2026-10-13 20:51:18'),
('AS000517', 'SA0153', 'BEN0027', '2026-10-13 20:51:18'),
('AS000518', 'SA0153', 'BEN0023', '2026-10-13 20:51:18'),
('AS000519', 'SA0153', 'BEN0030', '2026-10-13 20:51:18'),
('AS000520', 'SA0154', 'BEN0015', '2026-10-13 20:51:18'),
('AS000521', 'SA0154', 'BEN0024', '2026-10-13 20:51:18'),
('AS000522', 'SA0154', 'BEN0011', '2026-10-13 20:51:18'),
('AS000523', 'SA0154', 'BEN0014', '2026-10-13 20:51:18'),
('AS000524', 'SA0154', 'BEN0033', '2026-10-13 20:51:18'),
('AS000525', 'SA0154', 'BEN0021', '2026-10-13 20:51:18'),
('AS000526', 'SA0154', 'BEN0018', '2026-10-13 20:51:18'),
('AS000527', 'SA0154', 'BEN0013', '2026-10-13 20:51:18'),
('AS000528', 'SA0154', 'BEN0020', '2026-10-13 20:51:18'),
('AS000529', 'SA0154', 'BEN0030', '2026-10-13 20:51:18'),
('AS000530', 'SA0154', 'BEN0026', '2026-10-13 20:51:18'),
('AS000531', 'SA0154', 'BEN0028', '2026-10-13 20:51:18'),
('AS000532', 'SA0154', 'BEN0025', '2026-10-13 20:51:18'),
('AS000533', 'SA0154', 'BEN0032', '2026-10-13 20:51:18'),
('AS000534', 'SA0154', 'BEN0022', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000535', 'SA0155', 'BEN0012', '2026-10-13 20:51:18'),
('AS000536', 'SA0155', 'BEN0016', '2026-10-13 20:51:18'),
('AS000537', 'SA0155', 'BEN0028', '2026-10-13 20:51:18'),
('AS000538', 'SA0155', 'BEN0033', '2026-10-13 20:51:18'),
('AS000539', 'SA0155', 'BEN0021', '2026-10-13 20:51:18'),
('AS000540', 'SA0155', 'BEN0017', '2026-10-13 20:51:18'),
('AS000541', 'SA0155', 'BEN0023', '2026-10-13 20:51:18'),
('AS000542', 'SA0155', 'BEN0013', '2026-10-13 20:51:18'),
('AS000543', 'SA0155', 'BEN0010', '2026-10-13 20:51:18'),
('AS000544', 'SA0155', 'BEN0011', '2026-10-13 20:51:18'),
('AS000545', 'SA0155', 'BEN0018', '2026-10-13 20:51:18'),
('AS000546', 'SA0156', 'BEN0032', '2026-10-13 20:51:18'),
('AS000547', 'SA0156', 'BEN0031', '2026-10-13 20:51:18'),
('AS000548', 'SA0156', 'BEN0011', '2026-10-13 20:51:18'),
('AS000549', 'SA0156', 'BEN0018', '2026-10-13 20:51:18'),
('AS000550', 'SA0156', 'BEN0014', '2026-10-13 20:51:18'),
('AS000551', 'SA0156', 'BEN0026', '2026-10-13 20:51:18'),
('AS000552', 'SA0156', 'BEN0022', '2026-10-13 20:51:18'),
('AS000553', 'SA0156', 'BEN0015', '2026-10-13 20:51:18'),
('AS000554', 'SA0156', 'BEN0033', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000555', 'SA0156', 'BEN0010', '2026-10-13 20:51:18'),
('AS000556', 'SA0156', 'BEN0016', '2026-10-13 20:51:18'),
('AS000557', 'SA0156', 'BEN0024', '2026-10-13 20:51:18'),
('AS000558', 'SA0156', 'BEN0012', '2026-10-13 20:51:18'),
('AS000559', 'SA0156', 'BEN0023', '2026-10-13 20:51:18'),
('AS000560', 'SA0156', 'BEN0013', '2026-10-13 20:51:18'),
('AS000561', 'SA0156', 'BEN0029', '2026-10-13 20:51:18'),
('AS000562', 'SA0156', 'BEN0027', '2026-10-13 20:51:18'),
('AS000563', 'SA0156', 'BEN0019', '2026-10-13 20:51:18'),
('AS000564', 'SA0156', 'BEN0025', '2026-10-13 20:51:18'),
('AS000565', 'SA0157', 'BEN0025', '2026-10-13 20:51:18'),
('AS000566', 'SA0157', 'BEN0022', '2026-10-13 20:51:18'),
('AS000567', 'SA0157', 'BEN0018', '2026-10-13 20:51:18'),
('AS000568', 'SA0157', 'BEN0030', '2026-10-13 20:51:18'),
('AS000569', 'SA0157', 'BEN0023', '2026-10-13 20:51:18'),
('AS000570', 'SA0157', 'BEN0033', '2026-10-13 20:51:18'),
('AS000571', 'SA0157', 'BEN0015', '2026-10-13 20:51:18'),
('AS000572', 'SA0157', 'BEN0011', '2026-10-13 20:51:18'),
('AS000573', 'SA0157', 'BEN0020', '2026-10-13 20:51:18'),
('AS000574', 'SA0158', 'BEN0033', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000575', 'SA0158', 'BEN0027', '2026-10-13 20:51:18'),
('AS000576', 'SA0158', 'BEN0023', '2026-10-13 20:51:18'),
('AS000577', 'SA0158', 'BEN0017', '2026-10-13 20:51:18'),
('AS000578', 'SA0158', 'BEN0012', '2026-10-13 20:51:18'),
('AS000579', 'SA0158', 'BEN0011', '2026-10-13 20:51:18'),
('AS000580', 'SA0158', 'BEN0010', '2026-10-13 20:51:18'),
('AS000581', 'SA0158', 'BEN0014', '2026-10-13 20:51:18'),
('AS000582', 'SA0158', 'BEN0018', '2026-10-13 20:51:18'),
('AS000583', 'SA0158', 'BEN0030', '2026-10-13 20:51:18'),
('AS000584', 'SA0158', 'BEN0032', '2026-10-13 20:51:18'),
('AS000585', 'SA0158', 'BEN0015', '2026-10-13 20:51:18'),
('AS000586', 'SA0158', 'BEN0019', '2026-10-13 20:51:18'),
('AS000587', 'SA0158', 'BEN0025', '2026-10-13 20:51:18'),
('AS000588', 'SA0158', 'BEN0026', '2026-10-13 20:51:18'),
('AS000589', 'SA0163', 'BEN0011', '2026-10-13 20:51:18'),
('AS000590', 'SA0163', 'BEN0025', '2026-10-13 20:51:18'),
('AS000591', 'SA0163', 'BEN0028', '2026-10-13 20:51:18'),
('AS000592', 'SA0163', 'BEN0016', '2026-10-13 20:51:18'),
('AS000593', 'SA0163', 'BEN0018', '2026-10-13 20:51:18'),
('AS000594', 'SA0163', 'BEN0012', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000595', 'SA0163', 'BEN0024', '2026-10-13 20:51:18'),
('AS000596', 'SA0163', 'BEN0022', '2026-10-13 20:51:18'),
('AS000597', 'SA0163', 'BEN0017', '2026-10-13 20:51:18'),
('AS000598', 'SA0163', 'BEN0013', '2026-10-13 20:51:18'),
('AS000599', 'SA0163', 'BEN0014', '2026-10-13 20:51:18'),
('AS000600', 'SA0163', 'BEN0033', '2026-10-13 20:51:18'),
('AS000601', 'SA0164', 'BEN0012', '2026-10-13 20:51:18'),
('AS000602', 'SA0164', 'BEN0018', '2026-10-13 20:51:18'),
('AS000603', 'SA0164', 'BEN0020', '2026-10-13 20:51:18'),
('AS000604', 'SA0164', 'BEN0010', '2026-10-13 20:51:18'),
('AS000605', 'SA0164', 'BEN0021', '2026-10-13 20:51:18'),
('AS000606', 'SA0164', 'BEN0033', '2026-10-13 20:51:18'),
('AS000607', 'SA0164', 'BEN0011', '2026-10-13 20:51:18'),
('AS000608', 'SA0164', 'BEN0019', '2026-10-13 20:51:18'),
('AS000609', 'SA0164', 'BEN0027', '2026-10-13 20:51:18'),
('AS000610', 'SA0164', 'BEN0022', '2026-10-13 20:51:18'),
('AS000611', 'SA0164', 'BEN0013', '2026-10-13 20:51:18'),
('AS000612', 'SA0164', 'BEN0030', '2026-10-13 20:51:18'),
('AS000613', 'SA0164', 'BEN0032', '2026-10-13 20:51:18'),
('AS000614', 'SA0164', 'BEN0015', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000615', 'SA0164', 'BEN0025', '2026-10-13 20:51:18'),
('AS000616', 'SA0164', 'BEN0014', '2026-10-13 20:51:18'),
('AS000617', 'SA0164', 'BEN0031', '2026-10-13 20:51:18'),
('AS000618', 'SA0165', 'BEN0024', '2026-10-13 20:51:18'),
('AS000619', 'SA0165', 'BEN0027', '2026-10-13 20:51:18'),
('AS000620', 'SA0165', 'BEN0028', '2026-10-13 20:51:18'),
('AS000621', 'SA0165', 'BEN0016', '2026-10-13 20:51:18'),
('AS000622', 'SA0165', 'BEN0019', '2026-10-13 20:51:18'),
('AS000623', 'SA0165', 'BEN0020', '2026-10-13 20:51:18'),
('AS000624', 'SA0165', 'BEN0025', '2026-10-13 20:51:18'),
('AS000625', 'SA0165', 'BEN0013', '2026-10-13 20:51:18'),
('AS000626', 'SA0165', 'BEN0011', '2026-10-13 20:51:18'),
('AS000627', 'SA0165', 'BEN0023', '2026-10-13 20:51:18'),
('AS000628', 'SA0166', 'BEN0023', '2026-10-13 20:51:18'),
('AS000629', 'SA0166', 'BEN0011', '2026-10-13 20:51:18'),
('AS000630', 'SA0166', 'BEN0026', '2026-10-13 20:51:18'),
('AS000631', 'SA0166', 'BEN0012', '2026-10-13 20:51:18'),
('AS000632', 'SA0166', 'BEN0020', '2026-10-13 20:51:18'),
('AS000633', 'SA0166', 'BEN0015', '2026-10-13 20:51:18'),
('AS000634', 'SA0166', 'BEN0024', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000635', 'SA0166', 'BEN0030', '2026-10-13 20:51:18'),
('AS000636', 'SA0166', 'BEN0016', '2026-10-13 20:51:18'),
('AS000637', 'SA0166', 'BEN0018', '2026-10-13 20:51:18'),
('AS000638', 'SA0166', 'BEN0027', '2026-10-13 20:51:18'),
('AS000639', 'SA0166', 'BEN0014', '2026-10-13 20:51:18'),
('AS000640', 'SA0166', 'BEN0010', '2026-10-13 20:51:18'),
('AS000641', 'SA0166', 'BEN0021', '2026-10-13 20:51:18'),
('AS000642', 'SA0166', 'BEN0031', '2026-10-13 20:51:18'),
('AS000643', 'SA0166', 'BEN0022', '2026-10-13 20:51:18'),
('AS000644', 'SA0166', 'BEN0032', '2026-10-13 20:51:18'),
('AS000645', 'SA0166', 'BEN0033', '2026-10-13 20:51:18'),
('AS000646', 'SA0166', 'BEN0019', '2026-10-13 20:51:18'),
('AS000647', 'SA0166', 'BEN0028', '2026-10-13 20:51:18'),
('AS000648', 'SA0170', 'BEN0027', '2026-10-13 20:51:18'),
('AS000649', 'SA0170', 'BEN0032', '2026-10-13 20:51:18'),
('AS000650', 'SA0170', 'BEN0013', '2026-10-13 20:51:18'),
('AS000651', 'SA0170', 'BEN0025', '2026-10-13 20:51:18'),
('AS000652', 'SA0170', 'BEN0029', '2026-10-13 20:51:18'),
('AS000653', 'SA0170', 'BEN0024', '2026-10-13 20:51:18'),
('AS000654', 'SA0170', 'BEN0022', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000655', 'SA0170', 'BEN0019', '2026-10-13 20:51:18'),
('AS000656', 'SA0170', 'BEN0028', '2026-10-13 20:51:18'),
('AS000657', 'SA0171', 'BEN0020', '2026-10-13 20:51:18'),
('AS000658', 'SA0171', 'BEN0023', '2026-10-13 20:51:18'),
('AS000659', 'SA0171', 'BEN0018', '2026-10-13 20:51:18'),
('AS000660', 'SA0171', 'BEN0025', '2026-10-13 20:51:18'),
('AS000661', 'SA0171', 'BEN0011', '2026-10-13 20:51:18'),
('AS000662', 'SA0171', 'BEN0013', '2026-10-13 20:51:18'),
('AS000663', 'SA0171', 'BEN0028', '2026-10-13 20:51:18'),
('AS000664', 'SA0171', 'BEN0029', '2026-10-13 20:51:18'),
('AS000665', 'SA0171', 'BEN0031', '2026-10-13 20:51:18'),
('AS000666', 'SA0171', 'BEN0019', '2026-10-13 20:51:18'),
('AS000667', 'SA0171', 'BEN0027', '2026-10-13 20:51:18'),
('AS000668', 'SA0171', 'BEN0021', '2026-10-13 20:51:18'),
('AS000669', 'SA0171', 'BEN0017', '2026-10-13 20:51:18'),
('AS000670', 'SA0171', 'BEN0024', '2026-10-13 20:51:18'),
('AS000671', 'SA0171', 'BEN0015', '2026-10-13 20:51:18'),
('AS000672', 'SA0171', 'BEN0033', '2026-10-13 20:51:18'),
('AS000673', 'SA0171', 'BEN0010', '2026-10-13 20:51:18'),
('AS000674', 'SA0172', 'BEN0021', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000675', 'SA0172', 'BEN0018', '2026-10-13 20:51:18'),
('AS000676', 'SA0172', 'BEN0026', '2026-10-13 20:51:18'),
('AS000677', 'SA0172', 'BEN0025', '2026-10-13 20:51:18'),
('AS000678', 'SA0172', 'BEN0011', '2026-10-13 20:51:18'),
('AS000679', 'SA0172', 'BEN0033', '2026-10-13 20:51:18'),
('AS000680', 'SA0172', 'BEN0019', '2026-10-13 20:51:18'),
('AS000681', 'SA0172', 'BEN0029', '2026-10-13 20:51:18'),
('AS000682', 'SA0172', 'BEN0012', '2026-10-13 20:51:18'),
('AS000683', 'SA0173', 'BEN0019', '2026-10-13 20:51:18'),
('AS000684', 'SA0173', 'BEN0027', '2026-10-13 20:51:18'),
('AS000685', 'SA0173', 'BEN0022', '2026-10-13 20:51:18'),
('AS000686', 'SA0173', 'BEN0021', '2026-10-13 20:51:18'),
('AS000687', 'SA0173', 'BEN0012', '2026-10-13 20:51:18'),
('AS000688', 'SA0173', 'BEN0017', '2026-10-13 20:51:18'),
('AS000689', 'SA0173', 'BEN0015', '2026-10-13 20:51:18'),
('AS000690', 'SA0173', 'BEN0024', '2026-10-13 20:51:18'),
('AS000691', 'SA0173', 'BEN0013', '2026-10-13 20:51:18'),
('AS000692', 'SA0173', 'BEN0030', '2026-10-13 20:51:18'),
('AS000693', 'SA0173', 'BEN0033', '2026-10-13 20:51:18'),
('AS000694', 'SA0173', 'BEN0018', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000695', 'SA0173', 'BEN0016', '2026-10-13 20:51:18'),
('AS000696', 'SA0173', 'BEN0011', '2026-10-13 20:51:18'),
('AS000697', 'SA0173', 'BEN0028', '2026-10-13 20:51:18'),
('AS000698', 'SA0173', 'BEN0026', '2026-10-13 20:51:18'),
('AS000699', 'SA0173', 'BEN0023', '2026-10-13 20:51:18'),
('AS000700', 'SA0173', 'BEN0014', '2026-10-13 20:51:18'),
('AS000701', 'SA0173', 'BEN0025', '2026-10-13 20:51:18'),
('AS000702', 'SA0173', 'BEN0031', '2026-10-13 20:51:18'),
('AS000703', 'SA0174', 'BEN0030', '2026-10-13 20:51:18'),
('AS000704', 'SA0174', 'BEN0026', '2026-10-13 20:51:18'),
('AS000705', 'SA0174', 'BEN0020', '2026-10-13 20:51:18'),
('AS000706', 'SA0174', 'BEN0018', '2026-10-13 20:51:18'),
('AS000707', 'SA0174', 'BEN0029', '2026-10-13 20:51:18'),
('AS000708', 'SA0174', 'BEN0021', '2026-10-13 20:51:18'),
('AS000709', 'SA0174', 'BEN0023', '2026-10-13 20:51:18'),
('AS000710', 'SA0174', 'BEN0016', '2026-10-13 20:51:18'),
('AS000711', 'SA0174', 'BEN0025', '2026-10-13 20:51:18'),
('AS000712', 'SA0174', 'BEN0015', '2026-10-13 20:51:18'),
('AS000713', 'SA0174', 'BEN0010', '2026-10-13 20:51:18'),
('AS000714', 'SA0174', 'BEN0012', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000715', 'SA0175', 'BEN0015', '2026-10-13 20:51:18'),
('AS000716', 'SA0175', 'BEN0021', '2026-10-13 20:51:18'),
('AS000717', 'SA0175', 'BEN0033', '2026-10-13 20:51:18'),
('AS000718', 'SA0175', 'BEN0019', '2026-10-13 20:51:18'),
('AS000719', 'SA0175', 'BEN0028', '2026-10-13 20:51:18'),
('AS000720', 'SA0175', 'BEN0024', '2026-10-13 20:51:18'),
('AS000721', 'SA0175', 'BEN0032', '2026-10-13 20:51:18'),
('AS000722', 'SA0175', 'BEN0020', '2026-10-13 20:51:18'),
('AS000723', 'SA0175', 'BEN0010', '2026-10-13 20:51:18'),
('AS000724', 'SA0175', 'BEN0018', '2026-10-13 20:51:18'),
('AS000725', 'SA0175', 'BEN0016', '2026-10-13 20:51:18'),
('AS000726', 'SA0175', 'BEN0029', '2026-10-13 20:51:18'),
('AS000727', 'SA0175', 'BEN0022', '2026-10-13 20:51:18'),
('AS000728', 'SA0175', 'BEN0012', '2026-10-13 20:51:18'),
('AS000729', 'SA0175', 'BEN0023', '2026-10-13 20:51:18'),
('AS000730', 'SA0175', 'BEN0025', '2026-10-13 20:51:18'),
('AS000731', 'SA0175', 'BEN0031', '2026-10-13 20:51:18'),
('AS000732', 'SA0175', 'BEN0013', '2026-10-13 20:51:18'),
('AS000733', 'SA0176', 'BEN0033', '2026-10-13 20:51:18'),
('AS000734', 'SA0176', 'BEN0018', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000735', 'SA0176', 'BEN0013', '2026-10-13 20:51:18'),
('AS000736', 'SA0176', 'BEN0010', '2026-10-13 20:51:18'),
('AS000737', 'SA0176', 'BEN0024', '2026-10-13 20:51:18'),
('AS000738', 'SA0176', 'BEN0016', '2026-10-13 20:51:18'),
('AS000739', 'SA0176', 'BEN0029', '2026-10-13 20:51:18'),
('AS000740', 'SA0176', 'BEN0027', '2026-10-13 20:51:18'),
('AS000741', 'SA0176', 'BEN0026', '2026-10-13 20:51:18'),
('AS000742', 'SA0176', 'BEN0014', '2026-10-13 20:51:18'),
('AS000743', 'SA0176', 'BEN0025', '2026-10-13 20:51:18'),
('AS000744', 'SA0176', 'BEN0031', '2026-10-13 20:51:18'),
('AS000745', 'SA0176', 'BEN0012', '2026-10-13 20:51:18'),
('AS000746', 'SA0176', 'BEN0015', '2026-10-13 20:51:18'),
('AS000747', 'SA0176', 'BEN0020', '2026-10-13 20:51:18'),
('AS000748', 'SA0176', 'BEN0028', '2026-10-13 20:51:18'),
('AS000749', 'SA0176', 'BEN0011', '2026-10-13 20:51:18'),
('AS000750', 'SA0176', 'BEN0022', '2026-10-13 20:51:18'),
('AS000751', 'SA0177', 'BEN0026', '2026-10-13 20:51:18'),
('AS000752', 'SA0177', 'BEN0012', '2026-10-13 20:51:18'),
('AS000753', 'SA0177', 'BEN0013', '2026-10-13 20:51:18'),
('AS000754', 'SA0177', 'BEN0022', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000755', 'SA0177', 'BEN0018', '2026-10-13 20:51:18'),
('AS000756', 'SA0177', 'BEN0027', '2026-10-13 20:51:18'),
('AS000757', 'SA0177', 'BEN0021', '2026-10-13 20:51:18'),
('AS000758', 'SA0177', 'BEN0031', '2026-10-13 20:51:18'),
('AS000759', 'SA0177', 'BEN0028', '2026-10-13 20:51:18'),
('AS000760', 'SA0177', 'BEN0032', '2026-10-13 20:51:18'),
('AS000761', 'SA0177', 'BEN0033', '2026-10-13 20:51:18'),
('AS000762', 'SA0177', 'BEN0017', '2026-10-13 20:51:18'),
('AS000763', 'SA0177', 'BEN0029', '2026-10-13 20:51:18'),
('AS000764', 'SA0177', 'BEN0019', '2026-10-13 20:51:18'),
('AS000765', 'SA0177', 'BEN0014', '2026-10-13 20:51:18'),
('AS000766', 'SA0177', 'BEN0020', '2026-10-13 20:51:18'),
('AS000767', 'SA0177', 'BEN0016', '2026-10-13 20:51:18'),
('AS000768', 'SA0178', 'BEN0029', '2026-10-13 20:51:18'),
('AS000769', 'SA0178', 'BEN0033', '2026-10-13 20:51:18'),
('AS000770', 'SA0178', 'BEN0030', '2026-10-13 20:51:18'),
('AS000771', 'SA0178', 'BEN0016', '2026-10-13 20:51:18'),
('AS000772', 'SA0178', 'BEN0017', '2026-10-13 20:51:18'),
('AS000773', 'SA0178', 'BEN0023', '2026-10-13 20:51:18'),
('AS000774', 'SA0178', 'BEN0011', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000775', 'SA0178', 'BEN0020', '2026-10-13 20:51:18'),
('AS000776', 'SA0178', 'BEN0031', '2026-10-13 20:51:18'),
('AS000777', 'SA0178', 'BEN0028', '2026-10-13 20:51:18'),
('AS000778', 'SA0179', 'BEN0020', '2026-10-13 20:51:18'),
('AS000779', 'SA0179', 'BEN0016', '2026-10-13 20:51:18'),
('AS000780', 'SA0179', 'BEN0019', '2026-10-13 20:51:18'),
('AS000781', 'SA0179', 'BEN0024', '2026-10-13 20:51:18'),
('AS000782', 'SA0179', 'BEN0025', '2026-10-13 20:51:18'),
('AS000783', 'SA0179', 'BEN0011', '2026-10-13 20:51:18'),
('AS000784', 'SA0179', 'BEN0017', '2026-10-13 20:51:18'),
('AS000785', 'SA0179', 'BEN0012', '2026-10-13 20:51:18'),
('AS000786', 'SA0179', 'BEN0021', '2026-10-13 20:51:18'),
('AS000787', 'SA0179', 'BEN0029', '2026-10-13 20:51:18'),
('AS000788', 'SA0179', 'BEN0023', '2026-10-13 20:51:18'),
('AS000789', 'SA0179', 'BEN0028', '2026-10-13 20:51:18'),
('AS000790', 'SA0179', 'BEN0010', '2026-10-13 20:51:18'),
('AS000791', 'SA0179', 'BEN0033', '2026-10-13 20:51:18'),
('AS000792', 'SA0179', 'BEN0018', '2026-10-13 20:51:18'),
('AS000793', 'SA0179', 'BEN0031', '2026-10-13 20:51:18'),
('AS000794', 'SA0179', 'BEN0022', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000795', 'SA0179', 'BEN0027', '2026-10-13 20:51:18'),
('AS000796', 'SA0179', 'BEN0026', '2026-10-13 20:51:18'),
('AS000797', 'SA0179', 'BEN0032', '2026-10-13 20:51:18'),
('AS000798', 'SA0180', 'BEN0020', '2026-10-13 20:51:18'),
('AS000799', 'SA0180', 'BEN0022', '2026-10-13 20:51:18'),
('AS000800', 'SA0180', 'BEN0015', '2026-10-13 20:51:18'),
('AS000801', 'SA0180', 'BEN0024', '2026-10-13 20:51:18'),
('AS000802', 'SA0180', 'BEN0018', '2026-10-13 20:51:18'),
('AS000803', 'SA0180', 'BEN0029', '2026-10-13 20:51:18'),
('AS000804', 'SA0180', 'BEN0031', '2026-10-13 20:51:18'),
('AS000805', 'SA0180', 'BEN0025', '2026-10-13 20:51:18'),
('AS000806', 'SA0180', 'BEN0016', '2026-10-13 20:51:18'),
('AS000807', 'SA0180', 'BEN0033', '2026-10-13 20:51:18'),
('AS000808', 'SA0180', 'BEN0010', '2026-10-13 20:51:18'),
('AS000809', 'SA0180', 'BEN0013', '2026-10-13 20:51:18'),
('AS000810', 'SA0189', 'BEN0021', '2026-10-13 20:51:18'),
('AS000811', 'SA0189', 'BEN0029', '2026-10-13 20:51:18'),
('AS000812', 'SA0189', 'BEN0015', '2026-10-13 20:51:18'),
('AS000813', 'SA0189', 'BEN0012', '2026-10-13 20:51:18'),
('AS000814', 'SA0189', 'BEN0020', '2026-10-13 20:51:18');

INSERT INTO Asistencia (idAsistencia, idSegActividad, idBeneficiario, created_at) VALUES
('AS000815', 'SA0189', 'BEN0016', '2026-10-13 20:51:18'),
('AS000816', 'SA0189', 'BEN0011', '2026-10-13 20:51:18'),
('AS000817', 'SA0189', 'BEN0032', '2026-10-13 20:51:18'),
('AS000818', 'SA0189', 'BEN0024', '2026-10-13 20:51:18'),
('AS000819', 'SA0189', 'BEN0026', '2026-10-13 20:51:18'),
('AS000820', 'SA0189', 'BEN0028', '2026-10-13 20:51:18'),
('AS000821', 'SA0189', 'BEN0030', '2026-10-13 20:51:18'),
('AS000822', 'SA0190', 'BEN0012', '2026-10-13 20:51:18'),
('AS000823', 'SA0190', 'BEN0028', '2026-10-13 20:51:18'),
('AS000824', 'SA0190', 'BEN0020', '2026-10-13 20:51:18'),
('AS000825', 'SA0190', 'BEN0022', '2026-10-13 20:51:18'),
('AS000826', 'SA0190', 'BEN0019', '2026-10-13 20:51:18'),
('AS000827', 'SA0190', 'BEN0017', '2026-10-13 20:51:18'),
('AS000828', 'SA0191', 'BEN0020', '2026-10-13 20:51:18'),
('AS000829', 'SA0191', 'BEN0021', '2026-10-13 20:51:18'),
('AS000830', 'SA0191', 'BEN0023', '2026-10-13 20:51:18'),
('AS000831', 'SA0191', 'BEN0031', '2026-10-13 20:51:18'),
('AS000832', 'SA0191', 'BEN0015', '2026-10-13 20:51:18');

-- ====================================================
-- INSERTS PARA LA TABLA: sesiones_activas
-- (1 registros)
-- ====================================================

INSERT INTO sesiones_activas (idSesiones, usuario_id, session_id, ip_address, user_agent, fecha_inicio, ultima_actividad) VALUES
(32, 'Us0001', 'ie9r8p5c83ntbq5nnlvg087q29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-15 02:05:13', '2026-10-15 02:34:06');

-- ====================================================
-- INSERTS PARA LA TABLA: bitacora
-- (126 registros)
-- ====================================================

INSERT INTO bitacora (idBitacora, usuario_id, usuario_nombre, accion, modulo, registro_id, descripcion, ip, fecha_hora) VALUES
('BIT0001', NULL, 'Anonimo', 'ACCESS_DENIED', 'cargos', NULL, 'Usuario desconocido intento acceder al sistema sin credenciales', '::1', '2026-09-28 23:19:55'),
('BIT0002', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-09-28 23:20:07'),
('BIT0003', 'Us0001', 'Super', 'LOGOUT', 'Sesion', NULL, 'Sesion cerrada automaticamente por exceder el tiempo limite de inactividad', '::1', '2026-09-28 23:51:05'),
('BIT0004', 'Us0001', 'Super', 'LOGOUT', 'Sesion', NULL, 'Sesion cerrada automaticamente por exceder el tiempo limite de inactividad', '::1', '2026-09-28 23:51:05'),
('BIT0005', NULL, 'Anonimo', 'ACCESS_DENIED', 'cargos', NULL, 'Usuario desconocido intento acceder al sistema sin credenciales', '::1', '2026-09-29 00:05:54'),
('BIT0006', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-09-29 00:06:07'),
('BIT0007', 'Us0001', 'Super', 'LOGOUT', 'Sesion', NULL, 'Cerro sesion en el sistema', '::1', '2026-09-29 00:06:08'),
('BIT0008', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-09-29 00:06:20'),
('BIT0009', 'Us0001', 'Super', 'CREATE', 'Actividad', 'AC0001', 'Creo el registro AC0001 (asdasd)', '::1', '2026-09-29 00:10:51'),
('BIT0010', 'Us0001', 'Super', 'LOGOUT', 'Sesion', NULL, 'Cerro sesion en el sistema', '::1', '2026-09-29 00:24:37'),
('BIT0011', NULL, 'Anonimo', 'ACCESS_DENIED', 'cargos', NULL, 'Usuario desconocido intento acceder al sistema sin credenciales', '::1', '2026-09-29 22:37:24'),
('BIT0012', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-09-29 22:37:32'),
('BIT0013', 'Us0001', 'Super', 'CREATE', 'Actividad', 'AC0002', 'Creo el registro AC0002 (ASAS)', '::1', '2026-09-29 22:48:06'),
('BIT0014', 'Us0001', 'Super', 'CREATE', 'Lugar de Actividad', 'LA0007', 'Creo el registro LA0007 (TANGAMANDAPIA)', '::1', '2026-09-29 22:49:53'),
('BIT0015', 'Us0001', 'Super', 'CREATE', 'Actividad', 'AC0003', 'Creo el registro AC0003 (IOHIIO)', '::1', '2026-09-29 22:50:16'),
('BIT0016', 'Us0001', 'Super', 'CREATE', 'Actividad', 'AC0004', 'Creo el registro AC0004 (KJNJN)', '::1', '2026-09-29 22:51:52'),
('BIT0017', 'Us0001', 'Super', 'CREATE', 'Actividad', 'AC0005', 'Creo el registro AC0005 (DASD)', '::1', '2026-09-29 22:53:24'),
('BIT0018', NULL, 'Anonimo', 'ACCESS_DENIED', 'cargos', NULL, 'Usuario desconocido intento acceder al sistema sin credenciales', '::1', '2026-10-11 23:06:28'),
('BIT0019', NULL, 'Anonimo', 'ACCESS_DENIED', 'cargos', NULL, 'Usuario desconocido intento acceder al sistema sin credenciales', '::1', '2026-10-11 23:18:03'),
('BIT0020', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-11 23:18:13');

INSERT INTO bitacora (idBitacora, usuario_id, usuario_nombre, accion, modulo, registro_id, descripcion, ip, fecha_hora) VALUES
('BIT0021', 'Us0001', 'Super', 'LOGOUT', 'Sesion', NULL, 'Cerro sesion en el sistema', '::1', '2026-10-11 23:19:29'),
('BIT0022', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-11 23:19:52'),
('BIT0023', 'Us0001', 'Super', 'DELETE', 'Actividad', 'AC0005', 'Elimino el registro AC0005 (DASD)', '::1', '2026-10-11 23:48:41'),
('BIT0024', 'Us0001', 'Super', 'DELETE', 'Actividad', 'AC0004', 'Elimino el registro AC0004 (KJNJN)', '::1', '2026-10-11 23:48:44'),
('BIT0025', 'Us0001', 'Super', 'DELETE', 'Actividad', 'AC0001', 'Elimino el registro AC0001 (asdasd)', '::1', '2026-10-11 23:48:47'),
('BIT0026', 'Us0001', 'Super', 'DELETE', 'Actividad', 'AC0003', 'Elimino el registro AC0003 (IOHIIO)', '::1', '2026-10-11 23:48:49'),
('BIT0027', 'Us0001', 'Super', 'DELETE', 'Actividad', 'AC0002', 'Elimino el registro AC0002 (ASAS)', '::1', '2026-10-11 23:48:51'),
('BIT0028', 'Us0001', 'Super', 'CREATE', 'Actividad', 'AC0001', 'Creo el registro AC0001 (asdasd)', '::1', '2026-10-11 23:52:49'),
('BIT0029', 'Us0001', 'Super', 'LOGOUT', 'Sesion', NULL, 'Cerro sesion en el sistema', '::1', '2026-10-12 00:18:24'),
('BIT0030', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-12 12:15:07'),
('BIT0031', 'Us0001', 'Super', 'UPDATE', 'Actividad', 'AC0001', 'Actualizo el registro AC0001 (asdasd)', '::1', '2026-10-12 12:16:09'),
('BIT0032', 'Us0001', 'Super', 'LOGOUT', 'Sesion', NULL, 'Sesion invalidada: inicio en otro dispositivo, cierre del navegador o inactividad prolongada', '::1', '2026-10-12 18:48:11'),
('BIT0033', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-12 18:48:27'),
('BIT0034', 'Us0001', 'Super', 'DELETE', 'Actividad', 'AC0001', 'Elimino el registro AC0001 (asdasd)', '::1', '2026-10-12 19:00:32'),
('BIT0035', 'Us0001', 'Super', 'CREATE', 'Vertice', 'VER0006', 'Creo el registro VER0006 (JJJ)', '::1', '2026-10-12 19:48:06'),
('BIT0036', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-12 21:12:07'),
('BIT0037', 'Us0001', 'Super', 'CREATE', 'Cargo', 'CR0007', 'Creo el registro CR0007 (AAA)', '::1', '2026-10-12 21:18:36'),
('BIT0038', 'Us0001', 'Super', 'CREATE', 'Grupo Etnio', 'GN0005', 'Creo el registro GN0005 (0202)', '::1', '2026-10-12 21:20:29'),
('BIT0039', 'Us0001', 'Super', 'DELETE', 'Grupo Etnio', 'GN0005', 'Elimino el registro GN0005 (0202)', '::1', '2026-10-12 21:20:36'),
('BIT0040', 'Us0001', 'Super', 'CREATE', 'Grupo Etnio', 'GN0005', 'Creo el registro GN0005 (ASDSAD)', '::1', '2026-10-12 21:20:43');

INSERT INTO bitacora (idBitacora, usuario_id, usuario_nombre, accion, modulo, registro_id, descripcion, ip, fecha_hora) VALUES
('BIT0041', 'Us0001', 'Super', 'DELETE', 'Grupo Etnio', 'GN0005', 'Elimino el registro GN0005 (ASDSAD)', '::1', '2026-10-12 21:20:53'),
('BIT0042', 'Us0001', 'Super', 'CREATE', 'Actividad', 'AC0001', 'Creo el registro AC0001 (JASJDJSAJDSADKJADKJASND)', '::1', '2026-10-12 21:23:29'),
('BIT0043', NULL, 'Super Admin', 'LOGOUT', 'Sesion', NULL, 'Sesion invalidada: inicio en otro dispositivo, cierre del navegador o inactividad prolongada', '127.0.0.1', '2026-10-12 22:27:15'),
('BIT0044', NULL, 'Super Admin', 'LOGOUT', 'Sesion', NULL, 'Sesion invalidada: inicio en otro dispositivo, cierre del navegador o inactividad prolongada', '127.0.0.1', '2026-10-12 22:28:13'),
('BIT0045', NULL, 'Anonimo', 'ACCESS_DENIED', 'obtenerSesionesSeguimiento', NULL, 'Usuario desconocido intento acceder al sistema sin credenciales', '127.0.0.1', '2026-10-12 22:28:57'),
('BIT0046', NULL, 'Super Admin', 'LOGOUT', 'Sesion', NULL, 'Sesion invalidada: inicio en otro dispositivo, cierre del navegador o inactividad prolongada', '127.0.0.1', '2026-10-12 22:35:34'),
('BIT0047', NULL, 'Super Admin', 'LOGOUT', 'Sesion', NULL, 'Sesion invalidada: inicio en otro dispositivo, cierre del navegador o inactividad prolongada', '127.0.0.1', '2026-10-12 22:36:38'),
('BIT0048', 'Us0001', 'Super', 'LOGOUT', 'Sesion', NULL, 'Sesion invalidada: inicio en otro dispositivo, cierre del navegador o inactividad prolongada', '127.0.0.1', '2026-10-12 22:41:06'),
('BIT0049', NULL, 'Anonimo', 'UPDATE', 'Seguimiento', 'SA9998', 'Actualizo el registro SA9998 (Sesión 1)', 'desconocida', '2026-10-12 22:49:46'),
('BIT0050', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-12 22:59:15'),
('BIT0051', 'Us0001', 'Super', 'CREATE', 'Actividad', 'AC0002', 'Creo el registro AC0002 (DADADSAD)', '::1', '2026-10-12 23:00:32'),
('BIT0052', NULL, 'Anonimo', 'ACCESS_DENIED', 'cargos', NULL, 'Usuario desconocido intento acceder al sistema sin credenciales', '::1', '2026-10-13 11:09:04'),
('BIT0053', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-13 11:09:14'),
('BIT0054', 'Us0001', 'Super', 'CREATE', 'Beneficiario', 'BEN0001', 'Creo el registro BEN0001 (LUIS ALEJANDRO SALAZAR AGUILAR)', '::1', '2026-10-13 11:25:49'),
('BIT0055', 'Us0001', 'Super', 'UPDATE', 'Seguimiento', 'SA0002', 'Actualizo el registro SA0002 (Sesión 1)', '::1', '2026-10-13 11:26:03'),
('BIT0056', 'Us0001', 'Super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades (2 actividades)', '::1', '2026-10-13 12:41:30'),
('BIT0057', 'Us0001', 'Super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades de octubre 2026 (1 actividades)', '::1', '2026-10-13 13:29:02'),
('BIT0058', 'Us0001', 'Super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades de octubre 2026 (1 actividades)', '::1', '2026-10-13 13:53:35'),
('BIT0059', NULL, 'Anonimo', 'ACCESS_DENIED', 'cargos', NULL, 'Usuario desconocido intento acceder al sistema sin credenciales', '::1', '2026-10-13 19:03:10'),
('BIT0060', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-13 19:03:58');

INSERT INTO bitacora (idBitacora, usuario_id, usuario_nombre, accion, modulo, registro_id, descripcion, ip, fecha_hora) VALUES
('BIT0061', 'Us0001', 'Super', 'UPDATE', 'Seguimiento', 'SA0002', 'Actualizo el registro SA0002 (Sesión 1)', '::1', '2026-10-13 19:05:59'),
('BIT0062', 'Us0001', 'Super', 'CREATE', 'Beneficiario', 'BEN0002', 'Creo el registro BEN0002 (LORIANNY YURLEYVIC MARTINEZ GUTIETRREZ)', '::1', '2026-10-13 19:07:37'),
('BIT0063', 'Us0001', 'Super', 'UPDATE', 'Seguimiento', 'SA0001', 'Actualizo el registro SA0001 (Sesión 1)', '::1', '2026-10-13 19:07:57'),
('BIT0064', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-13 19:50:45'),
('BIT0065', 'Us0001', 'Super', 'UPDATE', 'Seguimiento', 'SA0002', 'Actualizo el registro SA0002 (Sesión 1)', '::1', '2026-10-13 19:58:02'),
('BIT0066', 'Us0001', 'Super', 'UPDATE', 'Actividad', 'AC0002', 'Actualizo el registro AC0002 (DADADSAD → estatus: Cancelada)', '::1', '2026-10-13 20:05:40'),
('BIT0067', 'Us0001', 'Super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades de octubre 2026 (0 actividades)', '::1', '2026-10-13 20:06:02'),
('BIT0068', 'Us0001', 'Super', 'UPDATE', 'Actividad', 'AC0002', 'Actualizo el registro AC0002 (DADADSAD → estatus: Finalizada)', '::1', '2026-10-13 20:06:27'),
('BIT0069', 'Us0001', 'Super', 'UPDATE', 'Actividad', 'AC0001', 'Actualizo el registro AC0001 (JASJDJSAJDSADKJADKJASND → estatus: Finalizada)', '::1', '2026-10-13 20:06:40'),
('BIT0070', 'Us0001', 'Super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades de octubre 2026 (1 actividades)', '::1', '2026-10-13 20:07:15'),
('BIT0071', 'Us0001', 'Super', 'UPDATE', 'Actividad', 'AC0001', 'Actualizo el registro AC0001 (JASJDJSAJDSADKJADKJASND → estatus: En espera)', '::1', '2026-10-13 20:18:25'),
('BIT0072', 'Us0001', 'Super', 'UPDATE', 'Actividad', 'AC0001', 'Actualizo el registro AC0001 (JASJDJSAJDSADKJADKJASND → estatus: Finalizada)', '::1', '2026-10-13 20:18:46'),
('BIT0073', 'Us0001', 'Super', 'UPDATE', 'Usuario', 'US0002', 'Actualizo el registro US0002 (andres)', '::1', '2026-10-13 20:34:30'),
('BIT0074', 'Us0001', 'Super', 'LOGOUT', 'Sesion', NULL, 'Cerro sesion en el sistema', '::1', '2026-10-13 20:34:51'),
('BIT0075', 'US0002', 'andres', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-13 20:34:57'),
('BIT0076', 'US0002', 'andres', 'LOGOUT', 'Sesion', NULL, 'Cerro sesion en el sistema', '::1', '2026-10-13 20:35:18'),
('BIT0077', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-13 20:36:03'),
('BIT0078', 'Us0001', 'Super', 'DELETE', 'Actividad', 'AC0134', 'Elimino el registro AC0134 (Formación en edición de video)', '::1', '2026-10-13 20:46:23'),
('BIT0079', 'Us0001', 'Super', 'DELETE', 'Actividad', 'AC0136', 'Elimino el registro AC0136 (Curso de comercio electrónico)', '::1', '2026-10-13 20:46:29'),
('BIT0080', 'Us0001', 'Super', 'UPDATE', 'Actividad', 'AC0001', 'Actualizo el registro AC0001 (JASJDJSAJDSADKJADKJASND → estatus: Planificada)', '::1', '2026-10-13 20:46:53');

INSERT INTO bitacora (idBitacora, usuario_id, usuario_nombre, accion, modulo, registro_id, descripcion, ip, fecha_hora) VALUES
('BIT0081', 'Us0001', 'Super', 'UPDATE', 'Actividad', 'AC0002', 'Actualizo el registro AC0002 (DADADSAD → estatus: Planificada)', '::1', '2026-10-13 20:47:05'),
('BIT0082', 'Us0001', 'Super', 'DELETE', 'Actividad', 'AC0133', 'Elimino el registro AC0133 (Jornada de feria tecnológica)', '::1', '2026-10-13 20:47:28'),
('BIT0083', 'Us0001', 'Super', 'UPDATE', 'Seguimiento', 'SA0192', 'Actualizo el registro SA0192 (Sesión 4)', '::1', '2026-10-13 20:50:01'),
('BIT0084', 'Us0001', 'Super', 'UPDATE', 'Actividad', 'AC0135', 'Actualizo el registro AC0135 (Taller de inteligencia artificial → estatus: Finalizada)', '::1', '2026-10-13 20:50:10'),
('BIT0085', 'Us0001', 'Super', 'CREATE', 'Actividad', 'AC0137', 'Creo el registro AC0137 (TALLER FORANEO CULTIVO)', '::1', '2026-10-13 20:57:15'),
('BIT0086', 'Us0001', 'Super', 'CREATE', 'Tipo de Usuario', 'TU0001', 'Creo el registro TU0001 (SADASSSSSSSSSSS)', '::1', '2026-10-13 20:59:08'),
('BIT0087', 'Us0001', 'Super', 'DELETE', 'Tipo de Usuario', 'TU0001', 'Elimino el registro TU0001 (SADASSSSSSSSSSS)', '::1', '2026-10-13 20:59:15'),
('BIT0088', 'Us0001', 'Super', 'CREATE', 'Unidad de Medida', 'UM0008', 'Creo el registro UM0008 (ASDASD)', '::1', '2026-10-13 20:59:40'),
('BIT0089', 'Us0001', 'Super', 'DELETE', 'Unidad de Medida', 'UM0008', 'Elimino el registro UM0008 (ASDASD)', '::1', '2026-10-13 20:59:44'),
('BIT0090', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-14 12:31:10'),
('BIT0091', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-14 20:34:50'),
('BIT0092', NULL, 'prueba_cli', '', 'Configuracion', NULL, 'Exportó PDF del módulo Listado de Cargos (7 registros)', 'desconocida', '2026-10-14 23:20:50'),
('BIT0093', NULL, 'prueba_cli', '', 'Configuracion', NULL, 'Exportó PDF del módulo Listado de Empleados (6 registros)', 'desconocida', '2026-10-14 23:20:51'),
('BIT0094', NULL, 'prueba_cli', '', 'Configuracion', NULL, 'Exportó PDF del módulo Listado de Lugares de Actividad (7 registros)', 'desconocida', '2026-10-14 23:20:52'),
('BIT0095', 'Us0001', 'Super', '', 'Configuracion', NULL, 'Exportó PDF del módulo Listado de Cargos (7 registros)', '::1', '2026-10-14 23:32:41'),
('BIT0096', 'Us0001', 'Super', '', 'Configuracion', NULL, 'Exportó PDF del módulo Listado de Estrategias de Desarrollo (7 registros)', '::1', '2026-10-14 23:33:31'),
('BIT0097', 'Us0001', 'Super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades de octubre 2026 (0 actividades)', '::1', '2026-10-15 00:10:18'),
('BIT0098', 'Us0001', 'Super', 'CREATE', 'Cargo', 'CR0008', 'Creo el registro CR0008 (asdakjsd)', '::1', '2026-10-15 00:12:42'),
('BIT0099', 'Us0001', 'Super', '', 'Configuracion', NULL, 'Exportó PDF del módulo Listado de Cargos (8 registros)', '::1', '2026-10-15 00:13:24'),
('BIT0100', 'Us0001', 'Super', '', 'Configuracion', NULL, 'Exportó PDF del módulo Listado de Horarios (7 registros)', '::1', '2026-10-15 00:18:52');

INSERT INTO bitacora (idBitacora, usuario_id, usuario_nombre, accion, modulo, registro_id, descripcion, ip, fecha_hora) VALUES
('BIT0101', 'US0001', 'super', '', 'Configuracion', NULL, 'Descargó PDF del módulo Listado de Cargos', 'desconocida', '2026-10-15 00:42:59'),
('BIT0102', 'US0001', 'super', '', 'Configuracion', NULL, 'Descargó PDF del módulo Listado de Empleados', 'desconocida', '2026-10-15 00:43:00'),
('BIT0103', 'US0001', 'super', '', 'Configuracion', NULL, 'Descargó PDF del módulo Listado de Usuarios del Sistema', 'desconocida', '2026-10-15 00:43:01'),
('BIT0104', 'Us0001', 'Super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades de octubre 2026 (0 actividades)', '::1', '2026-10-15 00:48:19'),
('BIT0105', 'Us0001', 'Super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades de febrero 2026 (3 actividades)', '::1', '2026-10-15 00:48:38'),
('BIT0106', 'US0001', 'super', '', 'Configuracion', NULL, 'Generó PDF de Cargos', 'desconocida', '2026-10-15 01:11:46'),
('BIT0107', 'US0001', 'super', '', 'Configuracion', NULL, 'Generó PDF de Empleados', 'desconocida', '2026-10-15 01:11:47'),
('BIT0108', 'US0001', 'super', '', 'Configuracion', NULL, 'Generó PDF de Usuarios del Sistema', 'desconocida', '2026-10-15 01:11:47'),
('BIT0109', 'Us0001', 'Super', '', 'Configuracion', NULL, 'Generó PDF de Usuarios del Sistema', '::1', '2026-10-15 01:16:04'),
('BIT0110', 'Us0001', 'Super', '', 'Configuracion', NULL, 'Generó PDF de Usuarios del Sistema', '::1', '2026-10-15 01:16:16'),
('BIT0111', 'Us0001', 'Super', '', 'Configuracion', NULL, 'Generó PDF de Tipos de Entrega', '::1', '2026-10-15 01:16:23'),
('BIT0112', 'Us0001', 'Super', '', 'Configuracion', NULL, 'Generó PDF de Cargos', '::1', '2026-10-15 01:20:43'),
('BIT0113', 'Us0001', 'Super', '', 'Configuracion', NULL, 'Generó PDF de Cargos', '::1', '2026-10-15 01:20:48'),
('BIT0114', 'Us0001', 'Super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades de octubre 2026 (0 actividades)', '::1', '2026-10-15 01:22:06'),
('BIT0115', 'US0001', 'super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades de octubre 2026 (0 actividades)', 'desconocida', '2026-10-15 01:29:19'),
('BIT0116', 'US0001', 'super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades de septiembre 2026 (2 actividades)', 'desconocida', '2026-10-15 01:30:04'),
('BIT0117', 'Us0001', 'Super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades de octubre 2026 (0 actividades)', '::1', '2026-10-15 01:32:47'),
('BIT0118', 'Us0001', 'Super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades de octubre 2026 (0 actividades)', '::1', '2026-10-15 01:32:56'),
('BIT0119', 'US0001', 'super', '', 'Actividades', NULL, 'Genero PDF de actividades (39 actividades)', 'desconocida', '2026-10-15 01:36:23'),
('BIT0120', 'Us0001', 'Super', '', 'Actividades', NULL, 'Genero PDF de actividades (39 actividades)', '::1', '2026-10-15 01:38:39');

INSERT INTO bitacora (idBitacora, usuario_id, usuario_nombre, accion, modulo, registro_id, descripcion, ip, fecha_hora) VALUES
('BIT0121', 'Us0001', 'Super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades de octubre 2026 (0 actividades)', '::1', '2026-10-15 01:42:38'),
('BIT0122', 'Us0001', 'Super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades de octubre 2026 (0 actividades)', '::1', '2026-10-15 01:46:17'),
('BIT0123', 'Us0001', 'Super', '', 'Reportes', NULL, 'Genero reporte PDF de actividades de agosto 2026 (2 actividades)', '::1', '2026-10-15 01:46:43'),
('BIT0124', 'Us0001', 'Super', 'DELETE', 'Actividad', 'AC0137', 'Elimino el registro AC0137 (TALLER FORANEO CULTIVO)', '::1', '2026-10-15 01:58:14'),
('BIT0125', 'Us0001', 'Super', 'DELETE', 'Actividad', 'AC0110', 'Elimino el registro AC0110 (Encuentro de innovación tecnológica)', '::1', '2026-10-15 01:58:19'),
('BIT0126', 'Us0001', 'Super', 'LOGIN', 'Sesion', NULL, 'Inicio sesion en el sistema', '::1', '2026-10-15 02:05:13');
