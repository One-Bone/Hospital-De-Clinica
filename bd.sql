
-- Tabla usuario
CREATE TABLE usuario (
    id INT PRIMARY KEY,
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    documento VARCHAR(50),
    email VARCHAR(50),
    contrasena VARCHAR(255)
);

CREATE TABLE funcionario (
    idfuncionario INT PRIMARY KEY,
    id INT,
    cargo VARCHAR(50),
    legajo VARCHAR(50),
    contacto VARCHAR(50),
    CONSTRAINT fk_funcionario_usuario
        FOREIGN KEY (id) REFERENCES usuario(id)
);

-- Tabla ambulancia
CREATE TABLE ambulancia (
    idambulancia INT(6) PRIMARY KEY,
    matricula VARCHAR(50),
    modelo VARCHAR(50),
    marca VARCHAR(50)
);

-- Tabla paciente
CREATE TABLE paciente (
    idpaciente INT(6) PRIMARY KEY,
    id INT(6),
    tel_contacto VARCHAR(50),
    nro_hospital VARCHAR(50),
    CONSTRAINT fk_paciente_usuario
        FOREIGN KEY (id) REFERENCES usuario(id)
);

-- Tabla documentacion
CREATE TABLE documentacion (
    iddocumento INT(6) PRIMARY KEY,
    categoria VARCHAR(50),
    titulo VARCHAR(50),
    nombre_documento VARCHAR(50),
    fecha_carga TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla QR
CREATE TABLE qr (
    idqr INT(6) PRIMARY KEY,
    iddocumento INT(6),
    codigo_enlace VARCHAR(50),
    fecha_gen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_qr_documentacion
        FOREIGN KEY (iddocumento) REFERENCES documentacion(iddocumento)
);

-- Tabla traslado
CREATE TABLE traslado (
    idtraslado INT(10) PRIMARY KEY,
    idambulancia INT(6),
    punto_origen VARCHAR(50),
    destino VARCHAR(50),
    rol_acompanante VARCHAR(50),
    estado VARCHAR(50),
    hora_salida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hora_llegada TIMESTAMP NULL,
    hora_estimada TIMESTAMP NULL,
    CONSTRAINT fk_traslado_ambulancia
        FOREIGN KEY (idambulancia) REFERENCES ambulancia(idambulancia)
);

-- Tabla encuesta
CREATE TABLE encuesta (
    idencuesta INT(6) PRIMARY KEY,
    idqr INT(6),
    titulo_encuesta VARCHAR(50),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_encuesta_qr
        FOREIGN KEY (idqr) REFERENCES qr(idqr)
);

-- Tabla resultado
CREATE TABLE resultado (
    idrespuesta INT(6) PRIMARY KEY,
    idencuesta INT(6),
    resultado VARCHAR(50),
    fecha_respuesta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_resultado_encuesta
        FOREIGN KEY (idencuesta) REFERENCES encuesta(idencuesta)
);

-- Tabla clase
CREATE TABLE clase (
    idclase INT(6) PRIMARY KEY,
    descripcion_clase VARCHAR(50)
);

-- Tabla elemento
CREATE TABLE elemento (
    idelemento INT(6) PRIMARY KEY,
    idtraslado INT(10),
    idclase INT(6),
    tipo_elemento VARCHAR(50),
    nombre_elemento VARCHAR(50),
    CONSTRAINT fk_elemento_traslado
        FOREIGN KEY (idtraslado) REFERENCES traslado(idtraslado),
    CONSTRAINT fk_elemento_clase
        FOREIGN KEY (idclase) REFERENCES clase(idclase)
);

INSERT INTO usuario
(id, nombre, apellido, documento, email, contrasena)
VALUES
(1, 'Juansin', 'Benitez', '57570155', 'juansin@gmail.com', SHA2('lajsd52', 256)),
(2, 'Ezequielini', 'Asasini', '12374194', 'ezekielini@gmail.com', SHA2('aguantemandy123', 256)),
(3, 'Patronsini', 'Asasini', '50123501', 'ptrnasasini@hotmail.com', SHA2('idontwanttosettheworldonfire', 256)),
(4, 'Jonatansini', 'Vasquezini', '12398418', 'vazquezini@gmail.com', SHA2('brawlstar143', 256)),
(5, 'Tung Tung', 'Sahur', '67676767', 'triplet@gmail.com', SHA2('tuntuntunsahur12367', 256)),

SELECT *
FROM usuario;

INSERT INTO funcionario
(idfuncionario, id, cargo, legajo, contacto)
VALUES
(100, 1, 'Chofer', 'L001', '099123456');

SELECT 
    funcionario.idfuncionario,
    usuario.id,
    usuario.nombre,
    usuario.apellido,
    funcionario.cargo,
    funcionario.legajo
FROM funcionario
INNER JOIN usuario
ON funcionario.id = usuario.id;