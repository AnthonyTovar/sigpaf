CREATE DATABASE IF NOT EXISTS sigpaf_DB 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sigpaf_DB;

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

-- ====================================================
-- INSERTS PARA LA TABLA: tipoUsuario
-- ====================================================

-- INSERTAR LOS ROLES (jerarquía de Fundacite)
INSERT INTO tipoUsuario (idTipoUsuario, rolUsuario) VALUES ('Rol0001', 'Super Usuario');
INSERT INTO tipoUsuario (idTipoUsuario, rolUsuario) VALUES ('Rol0002', 'Administrador');
INSERT INTO tipoUsuario (idTipoUsuario, rolUsuario) VALUES ('Rol0003', 'Usuario Comun');

-- ====================================================
-- INSERTS PARA LA TABLA: cargo
-- ====================================================

INSERT INTO cargo (idCargo, nombreCargo, descripcionCargo) 
VALUES ('CR0001', 'Super Usuario', 'Acceso total al sistema SIGPAF');

INSERT INTO cargo (idCargo, nombreCargo, descripcionCargo) 
VALUES ('CR0002', 'Analista', 'Responsable del análisis de datos, seguimiento de actividades y generación de reportes.');

INSERT INTO cargo (idCargo, nombreCargo, descripcionCargo) 
VALUES ('CR0003', 'Secretario', 'Encargado de la gestión administrativa, recepción de recaudos y archivo de documentación.');

INSERT INTO cargo (idCargo, nombreCargo, descripcionCargo) 
VALUES ('CR0004', 'Director', 'Supervisor general del área, encargado de la toma de decisiones estratégicas y aprobación de planes.');

INSERT INTO cargo (idCargo, nombreCargo, descripcionCargo) 
VALUES ('CR0005', 'Gerente', 'Coordinador operativo de los recursos, planificación de presupuestos y enlace institucional.');

INSERT INTO cargo (idCargo, nombreCargo, descripcionCargo) 
VALUES ('CR0006', 'Coordinador', 'Responsable directo de la ejecución de proyectos, asignación de tareas de campo y contacto comunitario.');

-- ====================================================
-- INSERTS PARA LA TABLA: unidadEjecutora
-- ====================================================

INSERT INTO unidadEjecutora (idUnidadEjecutora, nomUnidadEjecutora, desUnidadEjecutora) 
VALUES ('UNE0001', 'SuperU', 'Super Usuario Con Todos los Permisos');

INSERT INTO unidadEjecutora (idUnidadEjecutora, nomUnidadEjecutora, desUnidadEjecutora) 
VALUES ('UNE0002', 'TIC', 'Unidad de Tecnologías de la Información y Comunicación');

INSERT INTO unidadEjecutora (idUnidadEjecutora, nomUnidadEjecutora, desUnidadEjecutora) 
VALUES ('UNE0003', 'Admin y Finanzas', 'Departamento de Administración, Presupuesto, Compras y Gestión Financiera');

INSERT INTO unidadEjecutora (idUnidadEjecutora, nomUnidadEjecutora, desUnidadEjecutora) 
VALUES ('UNE0004', 'Atención Ciudadana', 'Oficina de Vinculación Comunitaria, Atención al Ciudadano y Gestión Social');

INSERT INTO unidadEjecutora (idUnidadEjecutora, nomUnidadEjecutora, desUnidadEjecutora) 
VALUES ('UNE0005', 'Talento Humano', 'Departamento de Recursos Humanos, Nómina y Bienestar Laboral');

INSERT INTO unidadEjecutora (idUnidadEjecutora, nomUnidadEjecutora, desUnidadEjecutora) 
VALUES ('UNE0006', 'I+D+I', 'Unidad de Investigación, Desarrollo, Innovación y Proyectos Socio-Tecnológicos');

-- ====================================================
-- INSERTS PARA LA TABLA: Estado
-- ====================================================

INSERT INTO estado (idEstado, nombreEstado) 
VALUES ('EST0001', 'Yaracuy');

-- ====================================================
-- INSERTS PARA LA TABLA: Municipio
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
('VER0005', 'Cooperación y alianzas internacionales', 'Fomento de alianzas estratégicas y redes de innovación y de investigación científica para profundizar la cooperación global');

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
-- INSERTS PARA LA TABLA: lugarActividad
-- ====================================================
INSERT INTO lugarActividad (idLugarActividad, nomLugarActividad, desLugarActividad, direccion, esSede, idParroquia) VALUES 
('LA0001', 'Sede Fundacite Yaracuy', 'Oficinas administrativas principales de Fundacite Yaracuy', 'Av. Intercomunal José Antonio Páez, sector El Samán', TRUE, 'PR0001'),
('LA0002', 'U.E. Juan José de Maya', 'Institución educativa para actividades de formación y semilleros científicos', 'Av. Cartagena con Calle 19, San Felipe', FALSE, 'PR0001'),
('LA0003', 'Liceo Arístides Bastidas', 'Liceo público de educación media general', 'Av. 2 entre Calles 11 y 12, San Felipe', FALSE, 'PR0001'),
('LA0004', 'E.B. República de Nicaragua', 'Escuela básica del estado utilizada para rutas científicas', 'Av. Caracas entre Av. Yaracuy y Calle 15, San Felipe', FALSE, 'PR0001'),
('LA0005', 'Complejo Tecnológico Yaracuy', 'Espacio de desarrollo e innovación tecnológica regional', 'Zona Industrial Agustín Rivero, San Felipe', FALSE, 'PR0001'),
('LA0006', 'Plaza Bolívar de San Felipe', 'Espacio público abierto para ferias de divulgación científica', 'Av. Caracas entre Calles 6 y 7, San Felipe', FALSE, 'PR0001');

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
('EST0001', 'Planificada', 'La actividad ha sido registrada en la planificación inicial pero aún no ha iniciado'),
('EST0002', 'En espera', 'La actividad se encuentra temporalmente pausada o aguardando condiciones para iniciar'),
('EST0003', 'En ejecución', 'La actividad se encuentra actualmente en desarrollo y ejecución de sus sesiones'),
('EST0004', 'Replanificada', 'La actividad sufrió modificaciones en sus fechas, lugar o planificación original'),
('EST0005', 'Cancelada', 'La actividad fue suspendida definitivamente y no se llevará a cabo'),
('EST0006', 'Finalizada', 'La actividad concluyó con éxito y se ejecutaron todas sus sesiones planeadas');

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
-- INSERTS PARA LA TABLA: empleado
-- ====================================================

-- INSERTAR EL EMPLEADO (Asociado al Super Usuario)
INSERT INTO empleado (idEmpleado, cedulaEmpleado, nacionalidad, nombres, apellidos, fechaNacimiento, telefonoEmpleado, correoEmpleado, idCargo, idUnidadEjecutora) 
VALUES ('EM0001', 'V00000000', 'Venezolano', 'Super', 'Usuario', '1990-01-01', '0000-0000000', 'super@usuario.com', 'CR0001', 'UNE0001');

INSERT INTO empleado (idEmpleado, cedulaEmpleado, nacionalidad, nombres, apellidos, fechaNacimiento, telefonoEmpleado, correoEmpleado, idCargo, idUnidadEjecutora) VALUES 
('EM0002', '18543210', 'Venezolano', 'Andrés Eloy', 'Blanco Ortega', '1988-05-14', '0412-1234567', 'andres.blanco@fundacite.gob.ve', 'CR0004', 'UNE0002'),
('EM0003', '20123456', 'Venezolano', 'Gabriela Valentina', 'Méndez Rojas', '1992-10-22', '0416-7654321', 'gabriela.mendez@fundacite.gob.ve', 'CR0006', 'UNE0002'),
('EM0004', '15987654', 'Venezolano', 'Francisco Javier', 'Sánchez Colmenares', '1983-03-08', '0414-9876543', 'francisco.sanchez@fundacite.gob.ve', 'CR0005', 'UNE0002'),
('EM0005', '22345678', 'Venezolano', 'Adriana Carolina', 'Pérez Sequera', '1995-07-30', '0424-4567890', 'adriana.perez@fundacite.gob.ve', 'CR0002', 'UNE0002'),
('EM0006', '24111222', 'Venezolano', 'José Gregorio', 'Giménez Castillo', '1997-12-05', '0412-0001122', 'jose.gimenez@fundacite.gob.ve', 'CR0003', 'UNE0003');

-- ====================================================
-- INSERTS PARA LA TABLA: usuarios
-- ====================================================

-- INSERTAR EL USUARIO (Login: Super / Pass: SuperU2026)
-- El hash generado es para 'SuperU2026':
INSERT INTO usuarios (idUsuario, nombreUsuario, contrasena, idTipoUsuario, idEmpleado) 
VALUES ('Us0001', 'Super', '$2a$12$6Ok0TmT9TLQm9Mo3j/B83.8DN0sR0GZkC/w4rqiT7Ma9pS38HNL9u', 'Rol0001', 'EM0001');

INSERT INTO usuarios (idUsuario, nombreUsuario, contrasena, idTipoUsuario, idEmpleado ) VALUES 
('US0002', 'andres_admin', '$2a$12$2t0j7eN0VcbOlqYbYWNDw.etWlUJq5QHvsGOkdvmlEz2kES/88u8K', 'Rol0002', 'EM0002' ),
('US0003', 'gabriela_user', '$2a$12$y/odPJdXnMdTnYAJXyULwuX.d94kPS3rEVWapyCrfCato1nZDMw0K', 'Rol0003', 'EM0003' );