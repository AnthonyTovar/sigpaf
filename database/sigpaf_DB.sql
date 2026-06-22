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

CREATE TABLE nacionalidad(
    idNacionalidad VARCHAR(8) NOT NULL PRIMARY KEY,
    nomNacionalidad VARCHAR(20) NOT NULL
);

CREATE TABLE empleado(
    idEmpleado VARCHAR(8) NOT NULL PRIMARY KEY,
    cedulaEmpleado VARCHAR(9) NOT NULL UNIQUE, 
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
    capacidad INT(4) NOT NULL,
    idLugarActividad VARCHAR(8) NOT NULL,

    FOREIGN KEY (idLugarActividad) REFERENCES lugarActividad(idLugarActividad)
);

CREATE TABLE areaEspecifica(
    idAreaE VARCHAR(8) NOT NULL PRIMARY KEY,
    nomAreaE VARCHAR(50) NOT NULL UNIQUE
    );

CREATE TABLE vertice(
    idVertice VARCHAR(8) NOT NULL PRIMARY KEY,
    nombreVertice VARCHAR(150) NOT NULL UNIQUE,
    descVertice VARCHAR(250) NOT NULL,
    idAreaE VARCHAR(8) NOT NULL,

    FOREIGN KEY (idAreaE) REFERENCES areaEspecifica(idAreaE)
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
    nombreDocente VARCHAR(50) NOT NULL,
    apellidoDocente VARCHAR(50) NOT NULL,
    telfDocente VARCHAR(20) NULL
);

CREATE TABLE grandesTrasformaciones(
    idGranTransformacion VARCHAR(8) NOT NULL PRIMARY KEY,
    nomGranTransformacion VARCHAR(50) NOT NULL UNIQUE,
    descGranTrasnformacion VARCHAR(500) NULL
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
    idUnidadMedida VARCHAR(8) NOT NULL,    
    idLugarActividad VARCHAR(8) NOT NULL,
    idEspacioUtilizar VARCHAR(8) NOT NULL,
    idVertice VARCHAR(8) NOT NULL,
    idDocente VARCHAR(8) NOT NULL,
    idEmpleado VARCHAR(8) NOT NULL,
    idEstatus VARCHAR(8) NOT NULL,
    idHorario VARCHAR(8) NOT NULL,
    idGrupoEtnio VARCHAR(8) NOT NULL,
    idGranTransformacion VARCHAR(8) NOT NULL,

    FOREIGN KEY (idTipoActividad) REFERENCES tipoActividad(idTipoActividad),
    FOREIGN KEY (idUnidadMedida) REFERENCES unidadMedida(idUnidadMedida),
    FOREIGN KEY (idLugarActividad) REFERENCES lugarActividad(idLugarActividad),
    FOREIGN KEY (idEspacioUtilizar) REFERENCES espacioUtilizar(idEspacioUtilizar),
    FOREIGN KEY (idVertice) REFERENCES vertice(idVertice),
    FOREIGN KEY (idDocente) REFERENCES docente(idDocente),
    FOREIGN KEY (idEmpleado) REFERENCES empleado(idEmpleado),
    FOREIGN KEY (idEstatus) REFERENCES estatus(idEstatus),
    FOREIGN KEY (idHorario) REFERENCES horario(idHorario),
    FOREIGN KEY (idGrupoEtnio) REFERENCES grupoEtnio(idGrupoEtnio),
    FOREIGN KEY (idGranTransformacion) REFERENCES grandesTrasformaciones(idGranTransformacion)
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
    fechaNacBeneficiario DATE NOT NULL,
    sexoBeneficiario VARCHAR(4) NOT NULL,
    idNacionalidad VARCHAR(8) NOT NULL,

    FOREIGN KEY (idNacionalidad) REFERENCES nacionalidad(idNacionalidad)
);

CREATE TABLE SeguiActivBeneficiario(
    idSegActividadBenef VARCHAR(8) NOT NULL PRIMARY KEY,
    idSegActividad VARCHAR(8) NOT NULL,
    idBeneficiario VARCHAR(8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (idSegActividad) REFERENCES seguimientoActividad(idSegActividad),
    FOREIGN KEY (idBeneficiario) REFERENCES beneficiario(idBeneficiario)
);

-- 1. INSERTAR DATOS MAESTROS (Requeridos por la tabla Empleado)
INSERT INTO cargo (idCargo, nombreCargo, descripcionCargo) 
VALUES ('CR0001', 'Super Usuario', 'Acceso total al sistema SIGPAF');

INSERT INTO unidadEjecutora (idUnidadEjecutora, nomUnidadEjecutora, desUnidadEjecutora) 
VALUES ('UNE0001', 'SuperU', 'Super Usuario Con Todos los Permisos');

INSERT INTO nacionalidad (idNacionalidad, nomNacionalidad) 
VALUES ('NA0001', 'Venezolano');

-- 2. INSERTAR EL EMPLEADO (Asociado al Super Usuario)
INSERT INTO empleado (idEmpleado, cedulaEmpleado, nombres, apellidos, fechaNacimiento, telefonoEmpleado, correoEmpleado, idCargo, idUnidadEjecutora) 
VALUES ('EM0001', '00000000', 'Super', 'Usuario', '1990-01-01', '0000-0000000', 'super@usuario.com', 'CR0001', 'UNE0001');

-- 3. INSERTAR LOS ROLES (Definiendo la jerarquía de Fundacite)
INSERT INTO tipoUsuario (idTipoUsuario, rolUsuario) VALUES ('Rol0001', 'Super Usuario');
INSERT INTO tipoUsuario (idTipoUsuario, rolUsuario) VALUES ('Rol0002', 'Administrador');
INSERT INTO tipoUsuario (idTipoUsuario, rolUsuario) VALUES ('Rol0003', 'Usuario Comun');

-- 4. INSERTAR EL USUARIO (Login: Super / Pass: SuperU2026)
-- El hash generado para 'SuperU2026' es el siguiente:
INSERT INTO usuarios (idUsuario, nombreUsuario, contrasena, idTipoUsuario, idEmpleado) 
VALUES ('Us0001', 'Super', '$2a$12$6Ok0TmT9TLQm9Mo3j/B83.8DN0sR0GZkC/w4rqiT7Ma9pS38HNL9u', 'Rol0001', 'EM0001');