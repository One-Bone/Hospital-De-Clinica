-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-09-2026 a las 13:34:49
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `hospital`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ambulancia`
--

CREATE TABLE `ambulancia` (
  `idambulancia` int(6) NOT NULL,
  `matricula` varchar(50) DEFAULT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `marca` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clase`
--

CREATE TABLE `clase` (
  `idclase` int(6) NOT NULL,
  `descripcion_clase` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentacion`
--

CREATE TABLE `documentacion` (
  `iddocumento` int(6) NOT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `titulo` varchar(50) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `nombre_documento` varchar(50) DEFAULT NULL,
  `fecha_carga` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `documentacion`
--

INSERT INTO `documentacion` (`iddocumento`, `categoria`, `titulo`, `descripcion`, `archivo`, `nombre_documento`, `fecha_carga`) VALUES
(7, NULL, 'Syntax', 'oHHE YEAHAH', 'uploads/1789044716_Screenshot 2026-09-09 at 12-33-15 Hospital de Clinica Montevideo - Documentación.png', NULL, '2026-09-10 12:51:56'),
(9, NULL, 'AS', 'dasd', 'uploads/1789137186_Syntax.png', NULL, '2026-09-11 14:33:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `elemento`
--

CREATE TABLE `elemento` (
  `idelemento` int(6) NOT NULL,
  `idtraslado` int(10) DEFAULT NULL,
  `idclase` int(6) DEFAULT NULL,
  `tipo_elemento` varchar(50) DEFAULT NULL,
  `nombre_elemento` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuesta`
--

CREATE TABLE `encuesta` (
  `idencuesta` int(6) NOT NULL,
  `idqr` int(6) DEFAULT NULL,
  `titulo_encuesta` varchar(50) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `enlace_formulario` varchar(255) NOT NULL,
  `enlace_respuestas` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `encuesta`
--

INSERT INTO `encuesta` (`idencuesta`, `idqr`, `titulo_encuesta`, `fecha_creacion`, `enlace_formulario`, `enlace_respuestas`) VALUES
(1, NULL, 'Hola?', '2026-09-11 14:55:45', 'https://docs.google.com/forms/d/e/1FAIpQLSeyhuw8lKLN4RM7yfgspwb-H1ROZkMm7yfhrHK2haAx--uPxA/viewform?usp=publish-editor', 'https://docs.google.com/forms/d/123J_sP6HtYlQ5aKH7AojkmpCYvMmkLsrl2qTU3mA68s/edit');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `funcionario`
--

CREATE TABLE `funcionario` (
  `idfuncionario` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `cargo` varchar(50) DEFAULT NULL,
  `legajo` varchar(50) DEFAULT NULL,
  `contacto` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paciente`
--

CREATE TABLE `paciente` (
  `idpaciente` int(6) NOT NULL,
  `id_usuario` int(6) DEFAULT NULL,
  `tel_contacto` varchar(50) DEFAULT NULL,
  `nro_hospital` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `paciente`
--

INSERT INTO `paciente` (`idpaciente`, `id_usuario`, `tel_contacto`, `nro_hospital`) VALUES
(1, 16, '12312312312', '123123123123');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `qr`
--

CREATE TABLE `qr` (
  `idqr` int(6) NOT NULL,
  `iddocumento` int(6) DEFAULT NULL,
  `codigo_enlace` varchar(50) DEFAULT NULL,
  `fecha_gen` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resultado`
--

CREATE TABLE `resultado` (
  `idrespuesta` int(6) NOT NULL,
  `idencuesta` int(6) DEFAULT NULL,
  `resultado` varchar(50) DEFAULT NULL,
  `fecha_respuesta` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `traslado`
--

CREATE TABLE `traslado` (
  `idtraslado` int(10) NOT NULL,
  `idambulancia` int(6) DEFAULT NULL,
  `punto_origen` varchar(50) DEFAULT NULL,
  `destino` varchar(50) DEFAULT NULL,
  `rol_acompanante` varchar(50) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `hora_salida` timestamp NOT NULL DEFAULT current_timestamp(),
  `hora_llegada` timestamp NULL DEFAULT NULL,
  `hora_estimada` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `cedula_identidad` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `pass` varchar(255) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`nombre`, `apellido`, `cedula_identidad`, `email`, `pass`, `id_usuario`) VALUES
('Ezequiel', 'Vazquez', 566373773, 'porqueelpansabemejorquelallave@gmail.com', '9133516e8d7fad9bd014903e0d1acc47d8ba73b83a261c44134562a16c314017', 1),
('Amon', 'Gus', 2147483647, NULL, NULL, 16),
('Ana', 'Sosa', 45556768, 'anasosa@gmail.com', '85467a30314f402f466c3d001344b17cb867cf873b685e8e54ae75bf9fb4faa9', 17);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ambulancia`
--
ALTER TABLE `ambulancia`
  ADD PRIMARY KEY (`idambulancia`);

--
-- Indices de la tabla `clase`
--
ALTER TABLE `clase`
  ADD PRIMARY KEY (`idclase`);

--
-- Indices de la tabla `documentacion`
--
ALTER TABLE `documentacion`
  ADD PRIMARY KEY (`iddocumento`);

--
-- Indices de la tabla `elemento`
--
ALTER TABLE `elemento`
  ADD PRIMARY KEY (`idelemento`),
  ADD KEY `fk_elemento_traslado` (`idtraslado`),
  ADD KEY `fk_elemento_clase` (`idclase`);

--
-- Indices de la tabla `encuesta`
--
ALTER TABLE `encuesta`
  ADD PRIMARY KEY (`idencuesta`),
  ADD KEY `fk_encuesta_qr` (`idqr`);

--
-- Indices de la tabla `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`idfuncionario`),
  ADD KEY `fk_funcionario_usuario` (`id_usuario`);

--
-- Indices de la tabla `paciente`
--
ALTER TABLE `paciente`
  ADD PRIMARY KEY (`idpaciente`),
  ADD KEY `fk_paciente_usuario` (`id_usuario`);

--
-- Indices de la tabla `qr`
--
ALTER TABLE `qr`
  ADD PRIMARY KEY (`idqr`),
  ADD KEY `fk_qr_documentacion` (`iddocumento`);

--
-- Indices de la tabla `resultado`
--
ALTER TABLE `resultado`
  ADD PRIMARY KEY (`idrespuesta`),
  ADD KEY `fk_resultado_encuesta` (`idencuesta`);

--
-- Indices de la tabla `traslado`
--
ALTER TABLE `traslado`
  ADD PRIMARY KEY (`idtraslado`),
  ADD KEY `fk_traslado_ambulancia` (`idambulancia`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `cedula` (`cedula_identidad`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ambulancia`
--
ALTER TABLE `ambulancia`
  MODIFY `idambulancia` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clase`
--
ALTER TABLE `clase`
  MODIFY `idclase` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentacion`
--
ALTER TABLE `documentacion`
  MODIFY `iddocumento` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `elemento`
--
ALTER TABLE `elemento`
  MODIFY `idelemento` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `encuesta`
--
ALTER TABLE `encuesta`
  MODIFY `idencuesta` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `idfuncionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `paciente`
--
ALTER TABLE `paciente`
  MODIFY `idpaciente` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `qr`
--
ALTER TABLE `qr`
  MODIFY `idqr` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `resultado`
--
ALTER TABLE `resultado`
  MODIFY `idrespuesta` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `traslado`
--
ALTER TABLE `traslado`
  MODIFY `idtraslado` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `elemento`
--
ALTER TABLE `elemento`
  ADD CONSTRAINT `fk_elemento_clase` FOREIGN KEY (`idclase`) REFERENCES `clase` (`idclase`),
  ADD CONSTRAINT `fk_elemento_traslado` FOREIGN KEY (`idtraslado`) REFERENCES `traslado` (`idtraslado`);

--
-- Filtros para la tabla `encuesta`
--
ALTER TABLE `encuesta`
  ADD CONSTRAINT `fk_encuesta_qr` FOREIGN KEY (`idqr`) REFERENCES `qr` (`idqr`);

--
-- Filtros para la tabla `funcionario`
--
ALTER TABLE `funcionario`
  ADD CONSTRAINT `fk_funcionario_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `paciente`
--
ALTER TABLE `paciente`
  ADD CONSTRAINT `fk_paciente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `qr`
--
ALTER TABLE `qr`
  ADD CONSTRAINT `fk_qr_documentacion` FOREIGN KEY (`iddocumento`) REFERENCES `documentacion` (`iddocumento`);

--
-- Filtros para la tabla `resultado`
--
ALTER TABLE `resultado`
  ADD CONSTRAINT `fk_resultado_encuesta` FOREIGN KEY (`idencuesta`) REFERENCES `encuesta` (`idencuesta`);

--
-- Filtros para la tabla `traslado`
--
ALTER TABLE `traslado`
  ADD CONSTRAINT `fk_traslado_ambulancia` FOREIGN KEY (`idambulancia`) REFERENCES `ambulancia` (`idambulancia`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
