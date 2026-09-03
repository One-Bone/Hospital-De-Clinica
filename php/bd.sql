
-- Tabla usuario
CREATE TABLE usuario (
    id_usuario INT PRIMARY KEY,
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    documento VARCHAR(50),
    email VARCHAR(50),
    contrasena VARCHAR(255)
);

CREATE TABLE funcionario (
    id_funcionario INT PRIMARY KEY,
    id_usuario INT,
    cargo VARCHAR(50),
    legajo VARCHAR(50),
    contacto VARCHAR(50),
    CONSTRAINT fk_funcionario_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

-- Tabla ambulancia
CREATE TABLE ambulancia (
    id_ambulancia INT(6) PRIMARY KEY,
    matricula VARCHAR(50),
    modelo VARCHAR(50),
    marca VARCHAR(50)
);

-- Tabla paciente
CREATE TABLE paciente (
    id_paciente INT(6) PRIMARY KEY,
    id_usuario INT(6),
    tel_contacto VARCHAR(50),
    nro_hospital VARCHAR(50),
    CONSTRAINT fk_paciente_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

-- Tabla documentacion
CREATE TABLE documentacion (
    id_documento INT(6) PRIMARY KEY,
    categoria VARCHAR(50),
    titulo VARCHAR(50),
    nombre_documento VARCHAR(50),
    fecha_carga TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla QR
CREATE TABLE qr (
    id_qr INT(6) PRIMARY KEY,
    id_documento INT(6),
    codigo_enlace VARCHAR(50),
    fecha_gen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_qr_documentacion
        FOREIGN KEY (id_documento) REFERENCES documentacion(id_documento)
);

-- Tabla traslado
CREATE TABLE traslado (
    id_traslado INT(10) PRIMARY KEY,
    id_ambulancia INT(6),
    punto_origen VARCHAR(50),
    destino VARCHAR(50),
    rol_acompanante VARCHAR(50),
    estado VARCHAR(50),
    hora_salida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hora_llegada TIMESTAMP NULL,
    hora_estimada TIMESTAMP NULL,
    CONSTRAINT fk_traslado_ambulancia
        FOREIGN KEY (id_ambulancia) REFERENCES ambulancia(id_ambulancia)
);

-- Tabla encuesta
CREATE TABLE encuesta (
    id_encuesta INT(6) PRIMARY KEY,
    id_qr INT(6),
    titulo_encuesta VARCHAR(50),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_encuesta_qr
        FOREIGN KEY (id_qr) REFERENCES qr(id_qr)
);

-- Tabla resultado
CREATE TABLE resultado (
    id_respuesta INT(6) PRIMARY KEY,
    id_encuesta INT(6),
    resultado VARCHAR(50),
    fecha_respuesta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_resultado_encuesta
        FOREIGN KEY (id_encuesta) REFERENCES encuesta(id_encuesta)
);

-- Tabla clase
CREATE TABLE clase (
    id_clase INT(6) PRIMARY KEY,
    descripcion_clase VARCHAR(50)
);

-- Tabla elemento
CREATE TABLE elemento (
    id_elemento INT(6) PRIMARY KEY,
    id_traslado INT(10),
    id_clase INT(6),
    tipo_elemento VARCHAR(50),
    nombre_elemento VARCHAR(50),
    CONSTRAINT fk_elemento_traslado
        FOREIGN KEY (id_traslado) REFERENCES traslado(id_traslado),
    CONSTRAINT fk_elemento_clase
        FOREIGN KEY (id_clase) REFERENCES clase(id_clase)
);

INSERT INTO usuario
(id_usuario, nombre, apellido, documento, email, contrasena)
VALUES
(1, 'Juansin', 'Benitez', '57570155', 'juansin@gmail.com', SHA2('lajsd52', 256)),
(2, 'Ezequielini', 'Asasini', '12374194', 'ezekielini@gmail.com', SHA2('aguantemandy123', 256)),
(3, 'Patronsini', 'Asasini', '50123501', 'ptrnasasini@hotmail.com', SHA2('idontwanttosettheworldonfire', 256)),
(4, 'Jonatansini', 'Vasquezini', '12398418', 'vazquezini@gmail.com', SHA2('brawlstar143', 256)),
(5, 'Tung Tung', 'Sahur', '67676767', 'triplet@gmail.com', SHA2('tuntuntunsahur12367', 256));

SELECT *
FROM usuario;

INSERT INTO funcionario
(id_funcionario, id_usuario, cargo, legajo, contacto)
VALUES
(100, 1, 'Chofer', 'L001', '099123456');

SELECT 
    funcionario.id_funcionario,
    usuario.id_usuario,
    usuario.nombre,
    usuario.apellido,
    funcionario.cargo,
    funcionario.legajo
FROM funcionario
INNER JOIN usuario
ON funcionario.id_usuario = usuario.id_usuario;