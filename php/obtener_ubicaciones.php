<?php
// Devuelve la última posición de cada ambulancia activa + su ruta reciente.
// Parámetros opcionales (GET):
//   activa_seg : se considera activa si envió un punto en los últimos N segundos (def. 120)
//   ruta_min   : minutos de recorrido a devolver (def. 30, máx. 240)
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

require_once 'conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$activaSeg = max(10, min(3600, (int)($_GET['activa_seg'] ?? 120)));
$rutaMin   = max(1, min(240, (int)($_GET['ruta_min'] ?? 30)));

try {
    // Última posición de cada ambulancia que envió algo recientemente
    $sql = "SELECT u.idambulancia, a.matricula, u.latitud, u.longitud, u.velocidad_ms,
                   TIMESTAMPDIFF(SECOND, u.fecha, NOW()) AS segundos
            FROM ubicacion u
            JOIN ambulancia a ON a.idambulancia = u.idambulancia
            WHERE u.idubicacion IN (
                SELECT MAX(idubicacion) FROM ubicacion
                WHERE fecha >= NOW() - INTERVAL ? SECOND
                GROUP BY idambulancia
            )";
    $stmt = $enlace->prepare($sql);
    $stmt->bind_param('i', $activaSeg);
    $stmt->execute();
    $res = $stmt->get_result();

    $ambulancias = [];
    $stmtRuta = $enlace->prepare(
        "SELECT latitud, longitud FROM ubicacion
         WHERE idambulancia = ? AND fecha >= NOW() - INTERVAL ? MINUTE
         ORDER BY idubicacion ASC LIMIT 1000"
    );

    while ($fila = $res->fetch_assoc()) {
        $id = (int)$fila['idambulancia'];
        $stmtRuta->bind_param('ii', $id, $rutaMin);
        $stmtRuta->execute();
        $rutaRes = $stmtRuta->get_result();
        $ruta = [];
        while ($p = $rutaRes->fetch_assoc()) {
            $ruta[] = [(float)$p['latitud'], (float)$p['longitud']];
        }

        $ambulancias[] = [
            'id'        => $id,
            'matricula' => $fila['matricula'],
            'lat'       => (float)$fila['latitud'],
            'lng'       => (float)$fila['longitud'],
            'velocidad' => $fila['velocidad_ms'] !== null ? (float)$fila['velocidad_ms'] : null,
            'segundos'  => (int)$fila['segundos'],
            'ruta'      => $ruta,
        ];
    }

    echo json_encode(['ok' => true, 'ambulancias' => $ambulancias]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al consultar ubicaciones', 'detalle' => $e->getMessage()]);
}
