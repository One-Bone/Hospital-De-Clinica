-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-10-2026 a las 11:30:18
-- Versión del servidor: 10.11.16-MariaDB
-- Versión de PHP: 8.5.12

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

--
-- Volcado de datos para la tabla `ambulancia`
--

INSERT INTO `ambulancia` (`idambulancia`, `matricula`, `modelo`, `marca`) VALUES
(1, '11', '22', '33'),
(2, '11', '22', '33');

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
(1, 16, '12312312312', '123123123123'),
(2, 20, '099099099', '1'),
(5, 19, '', '');

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
  `punto_origen` varchar(255) DEFAULT NULL,
  `destino` varchar(255) DEFAULT NULL,
  `rol_acompanante` varchar(50) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `hora_salida` timestamp NOT NULL DEFAULT current_timestamp(),
  `hora_llegada` timestamp NULL DEFAULT NULL,
  `hora_estimada` timestamp NULL DEFAULT NULL,
  `tipo_traslado` varchar(20) DEFAULT NULL,
  `conductor` varchar(100) DEFAULT NULL,
  `paciente` varchar(100) DEFAULT NULL,
  `insumos` varchar(255) DEFAULT NULL,
  `nivel_necesidad` varchar(10) DEFAULT NULL,
  `hora_solicitada` time DEFAULT NULL,
  `origen_lat` decimal(10,7) DEFAULT NULL,
  `origen_lng` decimal(10,7) DEFAULT NULL,
  `destino_lat` decimal(10,7) DEFAULT NULL,
  `destino_lng` decimal(10,7) DEFAULT NULL,
  `geometria` longtext DEFAULT NULL,
  `distancia_m` float DEFAULT NULL,
  `duracion_s` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `traslado`
--

INSERT INTO `traslado` (`idtraslado`, `idambulancia`, `punto_origen`, `destino`, `rol_acompanante`, `estado`, `hora_salida`, `hora_llegada`, `hora_estimada`, `tipo_traslado`, `conductor`, `paciente`, `insumos`, `nivel_necesidad`, `hora_solicitada`, `origen_lat`, `origen_lng`, `destino_lat`, `destino_lng`, `geometria`, `distancia_m`, `duracion_s`) VALUES
(1, 2, '-34.78291, -56.23009', '-34.89327, -56.17172', NULL, 'Eliminado', '2026-10-29 00:01:31', NULL, '2026-10-29 00:20:31', 'Paciente', 'JV JV', 'Lopez', 'espatula', 'Alta', '14:02:00', -34.7829061, -56.2300873, -34.8932672, -56.1717224, '[[-34.783423,-56.230472],[-34.783774,-56.229773],[-34.784086,-56.229127],[-34.78444,-56.228415],[-34.784771,-56.227749],[-34.784815,-56.227672],[-34.784935,-56.227437],[-34.784944,-56.22742],[-34.785283,-56.226756],[-34.785314,-56.226706],[-34.785354,-56.226672],[-34.785426,-56.226677],[-34.785505,-56.226711],[-34.785727,-56.226872],[-34.786213,-56.227227],[-34.786692,-56.227576],[-34.787678,-56.225537],[-34.788305,-56.224225],[-34.788367,-56.22413],[-34.788788,-56.224157],[-34.789534,-56.224205],[-34.790776,-56.224286],[-34.790845,-56.22429],[-34.791897,-56.224358],[-34.79243,-56.224378],[-34.793056,-56.224414],[-34.7935,-56.22443],[-34.793944,-56.224449],[-34.795777,-56.22453],[-34.796195,-56.22455],[-34.796517,-56.224596],[-34.796687,-56.22463],[-34.796792,-56.224645],[-34.796942,-56.224647],[-34.797001,-56.224647],[-34.797275,-56.224656],[-34.797306,-56.224657],[-34.79747,-56.224662],[-34.797769,-56.22464],[-34.797827,-56.224634],[-34.798036,-56.224603],[-34.798445,-56.224546],[-34.798476,-56.224541],[-34.798528,-56.224533],[-34.798861,-56.224481],[-34.799067,-56.224454],[-34.799341,-56.224414],[-34.799664,-56.224365],[-34.800026,-56.224308],[-34.800691,-56.224194],[-34.800868,-56.224155],[-34.801211,-56.224097],[-34.801549,-56.224043],[-34.802019,-56.223969],[-34.802166,-56.22394],[-34.802307,-56.223909],[-34.802391,-56.223881],[-34.8025,-56.223846],[-34.802673,-56.223782],[-34.802815,-56.223719],[-34.802925,-56.22367],[-34.80309,-56.223596],[-34.803174,-56.223544],[-34.803709,-56.22329],[-34.804027,-56.223143],[-34.804473,-56.222928],[-34.804565,-56.222882],[-34.804874,-56.222741],[-34.805027,-56.22266],[-34.805131,-56.22261],[-34.805698,-56.222329],[-34.805818,-56.222273],[-34.805891,-56.222237],[-34.806055,-56.222169],[-34.806133,-56.222136],[-34.806576,-56.221924],[-34.806741,-56.22185],[-34.806795,-56.221831],[-34.80685,-56.221813],[-34.807005,-56.221783],[-34.807175,-56.221754],[-34.807351,-56.221725],[-34.808329,-56.221545],[-34.808363,-56.221538],[-34.808406,-56.22153],[-34.808564,-56.221494],[-34.808584,-56.221491],[-34.809222,-56.221376],[-34.809891,-56.221249],[-34.810881,-56.221069],[-34.812091,-56.22085],[-34.812254,-56.220821],[-34.812536,-56.220791],[-34.813066,-56.220737],[-34.813813,-56.220671],[-34.814485,-56.220614],[-34.815115,-56.220562],[-34.815281,-56.220548],[-34.81573,-56.220509],[-34.816825,-56.220419],[-34.817439,-56.220362],[-34.818081,-56.22031],[-34.818848,-56.22025],[-34.819469,-56.220191],[-34.819612,-56.220178],[-34.819876,-56.220158],[-34.820639,-56.220101],[-34.821316,-56.22007],[-34.821658,-56.220054],[-34.822621,-56.220017],[-34.823171,-56.219989],[-34.823582,-56.219974],[-34.823699,-56.21997],[-34.823986,-56.219937],[-34.824466,-56.219882],[-34.824991,-56.219859],[-34.825519,-56.219833],[-34.825739,-56.219825],[-34.825901,-56.219821],[-34.825998,-56.219821],[-34.826149,-56.21983],[-34.826296,-56.219845],[-34.826553,-56.219877],[-34.826807,-56.219924],[-34.826962,-56.219962],[-34.827213,-56.220029],[-34.827381,-56.220067],[-34.827479,-56.220089],[-34.827556,-56.220107],[-34.827611,-56.220035],[-34.827666,-56.219951],[-34.827832,-56.219688],[-34.828445,-56.218649],[-34.82901,-56.217658],[-34.829399,-56.216992],[-34.829581,-56.216663],[-34.829717,-56.216435],[-34.830175,-56.215682],[-34.830509,-56.21513],[-34.83066,-56.214882],[-34.830765,-56.214685],[-34.831059,-56.214181],[-34.831464,-56.213491],[-34.832284,-56.212069],[-34.832855,-56.2111],[-34.833078,-56.210732],[-34.833164,-56.21059],[-34.833332,-56.210299],[-34.833569,-56.209893],[-34.833935,-56.209278],[-34.833998,-56.209168],[-34.834305,-56.208659],[-34.834416,-56.208472],[-34.834509,-56.208317],[-34.834606,-56.208154],[-34.83485,-56.207718],[-34.835569,-56.206507],[-34.835621,-56.20642],[-34.835899,-56.205957],[-34.837441,-56.203386],[-34.837639,-56.203055],[-34.837916,-56.202596],[-34.838501,-56.201591],[-34.838669,-56.201291],[-34.838752,-56.201145],[-34.838804,-56.201053],[-34.839321,-56.200156],[-34.839832,-56.1993],[-34.840802,-56.197661],[-34.840886,-56.197529],[-34.841053,-56.197247],[-34.841696,-56.196202],[-34.842247,-56.195294],[-34.842425,-56.194996],[-34.843043,-56.193957],[-34.843562,-56.193106],[-34.843667,-56.192933],[-34.844043,-56.192302],[-34.844161,-56.192129],[-34.844314,-56.191906],[-34.844387,-56.191814],[-34.844526,-56.191633],[-34.844806,-56.191324],[-34.845621,-56.190507],[-34.84594,-56.190187],[-34.846032,-56.190094],[-34.846845,-56.189294],[-34.84723,-56.188901],[-34.847514,-56.188613],[-34.847674,-56.188453],[-34.848324,-56.187777],[-34.848425,-56.187673],[-34.848503,-56.187585],[-34.849203,-56.186738],[-34.84961,-56.186237],[-34.850225,-56.185501],[-34.850428,-56.185227],[-34.850648,-56.184964],[-34.851001,-56.184538],[-34.851693,-56.183682],[-34.851743,-56.183618],[-34.852776,-56.182366],[-34.852861,-56.182262],[-34.852997,-56.182096],[-34.853279,-56.181751],[-34.853467,-56.181525],[-34.854416,-56.180327],[-34.855107,-56.179464],[-34.855178,-56.179376],[-34.855716,-56.178694],[-34.855876,-56.178494],[-34.85603,-56.178299],[-34.856788,-56.177356],[-34.856838,-56.177294],[-34.857516,-56.176458],[-34.857624,-56.17632],[-34.857802,-56.176087],[-34.858156,-56.175616],[-34.858844,-56.174712],[-34.859503,-56.173847],[-34.859579,-56.173748],[-34.860271,-56.172841],[-34.861041,-56.171828],[-34.861196,-56.171624],[-34.861348,-56.171424],[-34.86168,-56.170988],[-34.861894,-56.170713],[-34.862386,-56.17006],[-34.862399,-56.170043],[-34.862503,-56.169907],[-34.862687,-56.169679],[-34.862721,-56.169641],[-34.862773,-56.169591],[-34.862811,-56.169566],[-34.862876,-56.169556],[-34.862944,-56.169592],[-34.863014,-56.169617],[-34.863087,-56.169628],[-34.863161,-56.169627],[-34.863233,-56.169614],[-34.863303,-56.169587],[-34.86337,-56.169549],[-34.863431,-56.169499],[-34.863486,-56.16944],[-34.863551,-56.169337],[-34.863598,-56.169221],[-34.863618,-56.169138],[-34.863628,-56.169054],[-34.863629,-56.168968],[-34.86362,-56.168882],[-34.863601,-56.1688],[-34.863574,-56.168721],[-34.863537,-56.168647],[-34.863566,-56.168549],[-34.863609,-56.168476],[-34.863642,-56.168433],[-34.863803,-56.168225],[-34.864089,-56.167855],[-34.864312,-56.167565],[-34.865066,-56.166589],[-34.865321,-56.166248],[-34.86556,-56.165943],[-34.865706,-56.165743],[-34.866069,-56.165277],[-34.866322,-56.164947],[-34.867025,-56.164016],[-34.867686,-56.163163],[-34.867892,-56.162881],[-34.868589,-56.164769],[-34.868725,-56.16514],[-34.868789,-56.16531],[-34.869053,-56.166034],[-34.869117,-56.16621],[-34.869299,-56.166709],[-34.86932,-56.166763],[-34.869384,-56.166933],[-34.86943,-56.167054],[-34.869303,-56.167368],[-34.869205,-56.167589],[-34.869138,-56.167745],[-34.869125,-56.167793],[-34.869116,-56.167859],[-34.869126,-56.16796],[-34.869136,-56.168024],[-34.869163,-56.168129],[-34.869176,-56.168163],[-34.869198,-56.168204],[-34.869236,-56.16826],[-34.869449,-56.168224],[-34.869586,-56.168185],[-34.869636,-56.168158],[-34.869677,-56.168131],[-34.869713,-56.168102],[-34.869744,-56.168072],[-34.869863,-56.167942],[-34.869911,-56.167853],[-34.869944,-56.167802],[-34.869973,-56.167773],[-34.870009,-56.167745],[-34.870062,-56.167716],[-34.870113,-56.167697],[-34.870175,-56.167686],[-34.870299,-56.167676],[-34.870828,-56.167622],[-34.871756,-56.167521],[-34.872968,-56.167399],[-34.873894,-56.16729],[-34.874866,-56.167189],[-34.875806,-56.167089],[-34.876682,-56.166996],[-34.877327,-56.166924],[-34.878179,-56.166834],[-34.879553,-56.166684],[-34.881735,-56.166452],[-34.883434,-56.166278],[-34.884164,-56.166196],[-34.88431,-56.166181],[-34.884704,-56.166152],[-34.884904,-56.166126],[-34.885096,-56.166102],[-34.885374,-56.166072],[-34.886074,-56.165997],[-34.88696,-56.165907],[-34.887005,-56.165903],[-34.887932,-56.165802],[-34.888012,-56.165793],[-34.888746,-56.165713],[-34.888828,-56.165704],[-34.889235,-56.165664],[-34.88972,-56.165606],[-34.889957,-56.16558],[-34.890622,-56.165509],[-34.891715,-56.165396],[-34.892666,-56.165296],[-34.892788,-56.165285],[-34.892797,-56.165393],[-34.892828,-56.165762],[-34.892882,-56.166433],[-34.892971,-56.167496],[-34.892989,-56.167709],[-34.89308,-56.168936],[-34.893183,-56.170223],[-34.893274,-56.17156],[-34.893287,-56.17172]]', 16100, 1140),
(2, 1, 'Avenida Daniel Fernandez Crespo 2061, 11800 Montevideo, Uruguay', '-34.89823, -56.16250', NULL, 'Eliminado', '2026-10-29 00:45:15', NULL, '2026-10-29 00:50:15', 'Paciente', 'JV JV', 'Lopez', 'espatula', 'Alta', '14:02:00', -34.8952383, -56.1828804, -34.8982302, -56.1624956, '[[-34.895206,-56.182848],[-34.895009,-56.182454],[-34.894553,-56.181551],[-34.894442,-56.18132],[-34.894399,-56.18123],[-34.895064,-56.180676],[-34.89507,-56.180671],[-34.895123,-56.180668],[-34.895203,-56.180664],[-34.895299,-56.180658],[-34.895979,-56.179741],[-34.896072,-56.179608],[-34.896592,-56.178885],[-34.897019,-56.178291],[-34.897144,-56.178122],[-34.897848,-56.177203],[-34.89844,-56.176433],[-34.898599,-56.17623],[-34.899579,-56.174947],[-34.900036,-56.174311],[-34.900068,-56.174258],[-34.900095,-56.174229],[-34.90012,-56.174211],[-34.900925,-56.173807],[-34.901011,-56.173793],[-34.901046,-56.17379],[-34.90108,-56.173786],[-34.901057,-56.173725],[-34.901001,-56.173575],[-34.900672,-56.172691],[-34.899984,-56.170888],[-34.899555,-56.169804],[-34.899141,-56.168747],[-34.898664,-56.167509],[-34.898454,-56.166964],[-34.898812,-56.166925],[-34.899065,-56.166894],[-34.89898,-56.165895],[-34.898969,-56.165767],[-34.898895,-56.164724],[-34.898888,-56.164615],[-34.898878,-56.164415],[-34.898885,-56.164263],[-34.898888,-56.164225],[-34.898887,-56.164187],[-34.898867,-56.163773],[-34.898851,-56.163708],[-34.898806,-56.163634],[-34.898795,-56.163509],[-34.898787,-56.163395],[-34.898797,-56.163326],[-34.898839,-56.163235],[-34.898875,-56.163195],[-34.898932,-56.163164],[-34.89905,-56.163142],[-34.899114,-56.163115],[-34.899131,-56.163058],[-34.899136,-56.162967],[-34.899111,-56.162622],[-34.89909,-56.162392],[-34.899083,-56.162297],[-34.899058,-56.162237],[-34.899007,-56.16219],[-34.898942,-56.162174],[-34.898679,-56.162206],[-34.898623,-56.162219],[-34.898414,-56.162293],[-34.89828,-56.162325],[-34.898216,-56.162333]]', 2508, 300),
(3, 2, 'Arturo Lezama 2145, 11800 Montevideo, Uruguay', 'Manuel Alonso 1647, 11600 Montevideo, Uruguay', NULL, 'Eliminado', '2026-10-29 00:53:40', NULL, '2026-10-29 01:01:32', 'Paciente', 'JV JV', 'EEQUIELZINI BAILARINI?', 'SKIBIDI TOILET', 'Alta', '14:11:00', -34.8905215, -56.1907768, -34.8977726, -56.1481190, '[[-34.890518,-56.19074],[-34.890274,-56.190771],[-34.889356,-56.190862],[-34.889269,-56.189731],[-34.88919,-56.188621],[-34.889164,-56.188215],[-34.889142,-56.188091],[-34.889501,-56.187779],[-34.889519,-56.187763],[-34.889593,-56.187698],[-34.889677,-56.187692],[-34.889747,-56.187696],[-34.889814,-56.187698],[-34.88988,-56.187705],[-34.890082,-56.187782],[-34.890115,-56.187778],[-34.890207,-56.187765],[-34.890427,-56.187872],[-34.890485,-56.187863],[-34.890548,-56.187893],[-34.890885,-56.188054],[-34.891276,-56.188249],[-34.891622,-56.188417],[-34.891665,-56.188433],[-34.891715,-56.188442],[-34.891759,-56.188438],[-34.891828,-56.188418],[-34.891889,-56.188381],[-34.891907,-56.188368],[-34.89195,-56.18831],[-34.892101,-56.188146],[-34.89218,-56.188074],[-34.892204,-56.188],[-34.892289,-56.187745],[-34.892314,-56.187669],[-34.892356,-56.187541],[-34.892419,-56.18736],[-34.892537,-56.187032],[-34.892575,-56.186974],[-34.892635,-56.186818],[-34.89267,-56.186582],[-34.892671,-56.186518],[-34.892671,-56.186499],[-34.892688,-56.185936],[-34.892732,-56.185909],[-34.893414,-56.185484],[-34.893587,-56.185382],[-34.894482,-56.184801],[-34.895059,-56.184455],[-34.895221,-56.184353],[-34.895975,-56.183907],[-34.896703,-56.183479],[-34.897197,-56.18318],[-34.897695,-56.18289],[-34.898409,-56.182453],[-34.898051,-56.181514],[-34.897923,-56.181171],[-34.897591,-56.180319],[-34.897518,-56.180148],[-34.897348,-56.179743],[-34.897254,-56.179557],[-34.897116,-56.179299],[-34.897002,-56.179129],[-34.896806,-56.178893],[-34.896748,-56.178826],[-34.896622,-56.178684],[-34.896446,-56.17846],[-34.896334,-56.178298],[-34.896161,-56.178048],[-34.896095,-56.177951],[-34.895977,-56.177763],[-34.896266,-56.177313],[-34.896313,-56.177236],[-34.896587,-56.176778],[-34.896517,-56.175932],[-34.896441,-56.174841],[-34.896338,-56.173612],[-34.896264,-56.172728],[-34.896184,-56.171627],[-34.896154,-56.171242],[-34.896046,-56.169885],[-34.895954,-56.16859],[-34.895871,-56.167477],[-34.895879,-56.16734],[-34.895899,-56.167235],[-34.895869,-56.167096],[-34.895534,-56.16615],[-34.895483,-56.166007],[-34.895345,-56.165612],[-34.895232,-56.165305],[-34.895161,-56.165134],[-34.895125,-56.165045],[-34.895027,-56.164854],[-34.894981,-56.164758],[-34.894892,-56.16458],[-34.894568,-56.16392],[-34.89447,-56.163712],[-34.894369,-56.163485],[-34.894217,-56.16309],[-34.894053,-56.162665],[-34.894012,-56.162548],[-34.893759,-56.161943],[-34.893638,-56.16167],[-34.893506,-56.16141],[-34.893379,-56.161173],[-34.89297,-56.160394],[-34.892276,-56.159118],[-34.892244,-56.159047],[-34.892216,-56.158971],[-34.892203,-56.158912],[-34.892177,-56.158812],[-34.892139,-56.158628],[-34.892125,-56.158562],[-34.892106,-56.158465],[-34.892003,-56.158023],[-34.891954,-56.157826],[-34.891939,-56.15764],[-34.891752,-56.156864],[-34.891473,-56.155681],[-34.891435,-56.155522],[-34.891533,-56.155439],[-34.891577,-56.15539],[-34.892054,-56.154463],[-34.892179,-56.154221],[-34.892394,-56.153796],[-34.893091,-56.15241],[-34.893578,-56.15144],[-34.893637,-56.151338],[-34.893696,-56.151279],[-34.893735,-56.15125],[-34.893798,-56.151217],[-34.893886,-56.151154],[-34.89391,-56.151118],[-34.893932,-56.151033],[-34.893932,-56.151013],[-34.893932,-56.150982],[-34.893932,-56.150939],[-34.893923,-56.15089],[-34.893907,-56.150816],[-34.893898,-56.150764],[-34.893897,-56.150716],[-34.893905,-56.15067],[-34.893914,-56.150651],[-34.893943,-56.150582],[-34.893966,-56.150538],[-34.894015,-56.150452],[-34.894052,-56.150379],[-34.894108,-56.150272],[-34.894245,-56.15],[-34.894308,-56.149865],[-34.894326,-56.149829],[-34.894399,-56.149687],[-34.894451,-56.149603],[-34.894506,-56.149522],[-34.894792,-56.149125],[-34.894857,-56.149115],[-34.894908,-56.149111],[-34.895021,-56.149107],[-34.895099,-56.149103],[-34.895991,-56.149056],[-34.896167,-56.149046],[-34.896262,-56.149005],[-34.89669,-56.148745],[-34.897617,-56.148181],[-34.897669,-56.148165],[-34.897732,-56.148145],[-34.897773,-56.14812]]', 5426, 472),
(4, 1, 'José Llupes 5411, 11900 Montevideo, Uruguay', 'Benito Álvarez 829, 12900 Montevideo, Uruguay', NULL, 'Eliminado', '2026-10-29 00:55:30', NULL, '2026-10-29 01:01:57', 'Paciente', 'JV JV', 'EEQUIELZINI BAILARINI?', 'SKIBIDI TOILET', 'Media', NULL, -34.8431414, -56.2432194, -34.8414508, -56.2107754, '[[-34.843007,-56.243107],[-34.843146,-56.242861],[-34.843083,-56.242802],[-34.842476,-56.242271],[-34.842169,-56.242798],[-34.84168,-56.24238],[-34.841462,-56.242194],[-34.841168,-56.241916],[-34.840914,-56.241631],[-34.839789,-56.24036],[-34.839594,-56.240182],[-34.838189,-56.239061],[-34.838089,-56.238981],[-34.837563,-56.238478],[-34.836865,-56.237809],[-34.836737,-56.237681],[-34.835911,-56.236896],[-34.835586,-56.236602],[-34.835325,-56.236372],[-34.835108,-56.236177],[-34.834968,-56.236059],[-34.834253,-56.235601],[-34.833913,-56.235418],[-34.83378,-56.235328],[-34.83369,-56.235263],[-34.833603,-56.235181],[-34.833503,-56.235074],[-34.833379,-56.234938],[-34.833193,-56.234742],[-34.833156,-56.234706],[-34.833094,-56.234656],[-34.833016,-56.234577],[-34.831615,-56.233309],[-34.831796,-56.233006],[-34.83183,-56.232947],[-34.832075,-56.232539],[-34.832038,-56.232509],[-34.831875,-56.232364],[-34.83168,-56.23219],[-34.831499,-56.232032],[-34.831367,-56.231913],[-34.830992,-56.231583],[-34.830803,-56.231419],[-34.830441,-56.231084],[-34.830255,-56.230927],[-34.829963,-56.230667],[-34.829849,-56.230557],[-34.82973,-56.230443],[-34.829481,-56.230223],[-34.829654,-56.229933],[-34.830079,-56.22922],[-34.83038,-56.228713],[-34.830497,-56.228524],[-34.8308,-56.22801],[-34.831013,-56.227651],[-34.831147,-56.227424],[-34.831503,-56.22682],[-34.831618,-56.226625],[-34.831904,-56.226138],[-34.831921,-56.226108],[-34.832502,-56.225138],[-34.832684,-56.224832],[-34.832741,-56.224738],[-34.832864,-56.224529],[-34.834466,-56.221802],[-34.834554,-56.221644],[-34.834602,-56.22156],[-34.834649,-56.221475],[-34.835109,-56.220669],[-34.83558,-56.219847],[-34.835853,-56.219443],[-34.836072,-56.219086],[-34.836532,-56.218322],[-34.836983,-56.217568],[-34.837236,-56.217155],[-34.837451,-56.216772],[-34.837677,-56.216383],[-34.837884,-56.215991],[-34.838066,-56.215623],[-34.838089,-56.21558],[-34.838151,-56.215466],[-34.838307,-56.215207],[-34.838599,-56.214723],[-34.839077,-56.213896],[-34.839455,-56.213251],[-34.840018,-56.212284],[-34.84052,-56.211428],[-34.840711,-56.211101],[-34.841148,-56.21038],[-34.841471,-56.210675],[-34.841499,-56.210702]]', 4368, 387);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ubicacion`
--

CREATE TABLE `ubicacion` (
  `idubicacion` int(11) NOT NULL,
  `idambulancia` int(6) NOT NULL,
  `idtraslado` int(10) DEFAULT NULL,
  `latitud` decimal(10,7) NOT NULL,
  `longitud` decimal(10,7) NOT NULL,
  `precision_m` float DEFAULT NULL,
  `velocidad_ms` float DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ubicacion`
--

INSERT INTO `ubicacion` (`idubicacion`, `idambulancia`, `idtraslado`, `latitud`, `longitud`, `precision_m`, `velocidad_ms`, `fecha`) VALUES
(1, 1, NULL, -34.8800000, -56.1500000, 50000, NULL, '2026-10-28 22:56:24'),
(2, 1, NULL, -34.8800000, -56.1500000, 50000, NULL, '2026-10-28 22:56:27'),
(3, 1, NULL, -34.8800000, -56.1500000, 50000, NULL, '2026-10-28 22:57:11'),
(4, 1, NULL, -34.8800000, -56.1500000, 50000, NULL, '2026-10-28 22:57:12'),
(5, 1, NULL, -34.8800000, -56.1500000, 50000, NULL, '2026-10-28 23:02:21'),
(6, 1, NULL, -34.8800000, -56.1500000, 50000, NULL, '2026-10-28 23:02:23'),
(7, 1, NULL, -32.3359320, -58.0900310, 500, NULL, '2026-10-28 23:08:24'),
(8, 1, NULL, -34.8800000, -56.1500000, 50000, NULL, '2026-10-28 23:22:32'),
(9, 1, NULL, -32.3359320, -58.0900310, 381, NULL, '2026-10-28 23:26:04'),
(10, 1, NULL, -32.3362616, -58.0899968, 200, NULL, '2026-10-28 23:27:17'),
(15, 1, NULL, -32.3359320, -58.0900310, 500, NULL, '2026-10-29 00:39:50'),
(16, 1, NULL, -32.3359320, -58.0900310, 500, NULL, '2026-10-29 00:40:45'),
(17, 1, NULL, -32.3357738, -58.0902320, 292, NULL, '2026-10-29 00:41:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `cedula_identidad` varchar(8) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `pass` varchar(255) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`nombre`, `apellido`, `cedula_identidad`, `email`, `pass`, `id_usuario`) VALUES
('Ezequiel', 'Vazquez', '52817436', 'porqueelpansabemejorquelallave@gmail.com', '9133516e8d7fad9bd014903e0d1acc47d8ba73b83a261c44134562a16c314017', 1),
('Amon', 'Gus', '39264175', NULL, NULL, 16),
('Ana', 'Sosa', '45556768', 'anasosa@gmail.com', '85467a30314f402f466c3d001344b17cb867cf873b685e8e54ae75bf9fb4faa9', 17),
('CANDELA', 'Echeveste Coria', '58090453', 'jsjsjsjjsjsjsjsjs@gmail.com', '1298b56cba5537667c592577e89fc42d33f9c4342084f93d2f6a47125748ad58', 18),
('Hola', 'Chau', '67428519', NULL, '9133516e8d7fad9bd014903e0d1acc47d8ba73b83a261c44134562a16c314017', 19),
('Juan', 'B', '57570155', NULL, NULL, 20),
('JV', 'JV', '11234567', '111@gmail.com', 'ef797c8118f02dfb649607dd5d3f8c7623048c9c063d532cc95c5ed7a898a64f', 21);

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
-- Indices de la tabla `ubicacion`
--
ALTER TABLE `ubicacion`
  ADD PRIMARY KEY (`idubicacion`),
  ADD KEY `idx_ambulancia_fecha` (`idambulancia`,`fecha`);

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
  MODIFY `idambulancia` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `clase`
--
ALTER TABLE `clase`
  MODIFY `idclase` int(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentacion`
--
ALTER TABLE `documentacion`
  MODIFY `iddocumento` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `idfuncionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `paciente`
--
ALTER TABLE `paciente`
  MODIFY `idpaciente` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `idtraslado` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ubicacion`
--
ALTER TABLE `ubicacion`
  MODIFY `idubicacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
