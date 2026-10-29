<?php
// Recibe un punto GPS del conductor y lo guarda en la tabla `ubicacion`.
// Filtros:
//  1) Descarta puntos con error de GPS muy grande.
//  2) Descarta saltos imposibles (un GPS que de golpe ubica a la ambulancia a kilómetros).
//  3) Descarta puntos casi repetidos (ambulancia quieta), pero guarda igual uno cada INTERVALO_MINIMO_S.
session_start();
header('Content-Type: application/json; charset=utf-8');

const PRECISION_MAXIMA_M = 500;   // error de GPS máximo aceptado (metros)
const DISTANCIA_MINIMA_M = 15;    // movimiento mínimo para guardar un punto (metros)
const INTERVALO_MINIMO_S = 30;    // si no se movió, igual guarda cada N segundos
const VELOCIDAD_MAXIMA_MS = 42;   // ~150 km/h: más rápido que eso es un error del GPS
const MARGEN_SALTO_M = 60;        // tolerancia extra para el salto (metros)
const VENTANA_REFERENCIA_S = 120; // después de este tiempo, el último punto ya no se usa como referencia

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

require_once 'conexion.php';

// En PHP 8.0 mysqli no lanza excepciones por defecto; esto lo activa.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Distancia en metros entre dos coordenadas (fórmula de Haversine)
function distanciaM(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $R = 6371000;
    $rad = fn($x) => $x * M_PI / 180;
    $dLat = $rad($lat2 - $lat1);
    $dLon = $rad($lon2 - $lon1);
    $a = sin($dLat / 2) ** 2 + cos($rad($lat1)) * cos($rad($lat2)) * sin($dLon / 2) ** 2;
    return 2 * $R * asin(sqrt($a));
}

$datos = json_decode(file_get_contents('php://input'), true);

$idambulancia = filter_var($datos['idambulancia'] ?? null, FILTER_VALIDATE_INT);
$lat = filter_var($datos['lat'] ?? null, FILTER_VALIDATE_FLOAT);
$lng = filter_var($datos['lng'] ?? null, FILTER_VALIDATE_FLOAT);
$precision = isset($datos['precision']) ? filter_var($datos['precision'], FILTER_VALIDATE_FLOAT) : null;
$velocidad = isset($datos['velocidad']) ? filter_var($datos['velocidad'], FILTER_VALIDATE_FLOAT) : null;
$idtraslado = isset($datos['idtraslado']) ? filter_var($datos['idtraslado'], FILTER_VALIDATE_INT) : null;

// Validaciones básicas (rangos reales de coordenadas)
if ($idambulancia === false || $idambulancia === null ||
    $lat === false || $lat === null || $lat < -90 || $lat > 90 ||
    $lng === false || $lng === null || $lng < -180 || $lng > 180) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
    exit;
}
if ($precision === false) $precision = null;
if ($velocidad === false) $velocidad = null;
if ($idtraslado === false || $idtraslado === 0) $idtraslado = null;

try {
    // Sin clave foránea, verificamos a mano que la ambulancia exista
    $chk = $enlace->prepare("SELECT 1 FROM ambulancia WHERE idambulancia = ?");
    $chk->bind_param('i', $idambulancia);
    $chk->execute();
    if ($chk->get_result()->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'La ambulancia no existe']);
        exit;
    }
    $chk->close();

    // 1) Descartar puntos con mala precisión
    if ($precision !== null && $precision > PRECISION_MAXIMA_M) {
        echo json_encode(['ok' => true, 'guardado' => false, 'motivo' => 'precision_baja']);
        exit;
    }

    // Último punto guardado de esta ambulancia
    $ult = $enlace->prepare(
        "SELECT latitud, longitud, TIMESTAMPDIFF(SECOND, fecha, NOW()) AS seg
         FROM ubicacion WHERE idambulancia = ? ORDER BY idubicacion DESC LIMIT 1"
    );
    $ult->bind_param('i', $idambulancia);
    $ult->execute();
    $prev = $ult->get_result()->fetch_assoc();
    $ult->close();

    if ($prev) {
        $seg = (int)$prev['seg'];
        $movido = distanciaM((float)$prev['latitud'], (float)$prev['longitud'], $lat, $lng);

        // 2) Salto imposible: el punto está demasiado lejos para lo que pudo recorrer en ese tiempo.
        //    Solo se controla si el último punto es reciente; si hace mucho, se toma como nueva referencia.
        if ($seg <= VENTANA_REFERENCIA_S && $movido > MARGEN_SALTO_M + VELOCIDAD_MAXIMA_MS * max(1, $seg)) {
            echo json_encode(['ok' => true, 'guardado' => false, 'motivo' => 'salto_gps']);
            exit;
        }

        // 3) Ambulancia quieta: no guardar puntos repetidos, salvo uno cada INTERVALO_MINIMO_S
        if ($movido < DISTANCIA_MINIMA_M && $seg < INTERVALO_MINIMO_S) {
            echo json_encode(['ok' => true, 'guardado' => false, 'motivo' => 'sin_movimiento']);
            exit;
        }
    }

    $stmt = $enlace->prepare(
        "INSERT INTO ubicacion (idambulancia, idtraslado, latitud, longitud, precision_m, velocidad_ms)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('iidddd', $idambulancia, $idtraslado, $lat, $lng, $precision, $velocidad);
    $stmt->execute();
    echo json_encode(['ok' => true, 'guardado' => true, 'id' => $enlace->insert_id]);
} catch (Throwable $e) {
    http_response_code(500);
    // 'detalle' ayuda a depurar; podés borrarlo cuando todo ande.
    echo json_encode(['ok' => false, 'error' => 'No se pudo guardar el punto', 'detalle' => $e->getMessage()]);
}
