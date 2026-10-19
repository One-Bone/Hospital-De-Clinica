<?php
// Recibe un punto GPS del conductor y lo guarda en la tabla `ubicacion`.
session_start();
header('Content-Type: application/json; charset=utf-8');

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

    $stmt = $enlace->prepare(
        "INSERT INTO ubicacion (idambulancia, idtraslado, latitud, longitud, precision_m, velocidad_ms)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('iidddd', $idambulancia, $idtraslado, $lat, $lng, $precision, $velocidad);
    $stmt->execute();
    echo json_encode(['ok' => true, 'id' => $enlace->insert_id]);
} catch (Throwable $e) {
    http_response_code(500);
    // 'detalle' ayuda a depurar; podés borrarlo cuando todo ande.
    echo json_encode(['ok' => false, 'error' => 'No se pudo guardar el punto', 'detalle' => $e->getMessage()]);
}
