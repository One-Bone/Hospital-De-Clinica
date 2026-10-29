<?php
// Traslados: listar activos (GET), crear con ruta, eliminar o finalizar (POST JSON).
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

function responder(int $codigo, array $cuerpo): void
{
    http_response_code($codigo);
    echo json_encode($cuerpo);
    exit;
}

// Devuelve la coordenada validada o null
function coordenada($valor, float $max): ?float
{
    $n = filter_var($valor, FILTER_VALIDATE_FLOAT);
    return ($n === false || abs($n) > $max) ? null : $n;
}

try {
    // ---------- LISTAR ----------
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $res = $enlace->query(
            "SELECT t.idtraslado, t.idambulancia, a.matricula, t.punto_origen, t.destino,
                    t.origen_lat, t.origen_lng, t.destino_lat, t.destino_lng,
                    t.geometria, t.distancia_m, t.duracion_s,
                    t.tipo_traslado, t.conductor, t.paciente, t.insumos, t.nivel_necesidad
             FROM traslado t
             JOIN ambulancia a ON a.idambulancia = t.idambulancia
             WHERE t.estado = 'En curso' AND t.geometria IS NOT NULL
             ORDER BY t.hora_salida DESC"
        );

        $lista = [];
        while ($f = $res->fetch_assoc()) {
            $lista[] = [
                'idtraslado'      => (int)$f['idtraslado'],
                'idambulancia'    => (int)$f['idambulancia'],
                'matricula'       => $f['matricula'],
                'punto_origen'    => $f['punto_origen'],
                'destino'         => $f['destino'],
                'origen_lat'      => (float)$f['origen_lat'],
                'origen_lng'      => (float)$f['origen_lng'],
                'destino_lat'     => (float)$f['destino_lat'],
                'destino_lng'     => (float)$f['destino_lng'],
                'geometria'       => json_decode($f['geometria'], true) ?: [],
                'distancia_m'     => (float)$f['distancia_m'],
                'duracion_s'      => (int)$f['duracion_s'],
                'tipo_traslado'   => $f['tipo_traslado'],
                'conductor'       => $f['conductor'],
                'paciente'        => $f['paciente'],
                'insumos'         => $f['insumos'],
                'nivel_necesidad' => $f['nivel_necesidad'],
            ];
        }
        responder(200, ['ok' => true, 'traslados' => $lista]);
    }

    // ---------- ACCIONES ----------
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responder(405, ['ok' => false, 'error' => 'Método no permitido']);
    }

    $datos  = json_decode(file_get_contents('php://input'), true) ?? [];
    $accion = $datos['accion'] ?? 'crear';

    // Eliminar = borrado lógico (queda el historial)
    if ($accion === 'eliminar' || $accion === 'finalizar') {
        $id = filter_var($datos['idtraslado'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false) {
            responder(400, ['ok' => false, 'error' => 'Traslado inválido']);
        }
        $sql = $accion === 'eliminar'
            ? "UPDATE traslado SET estado = 'Eliminado' WHERE idtraslado = ?"
            : "UPDATE traslado SET estado = 'Finalizado', hora_llegada = NOW() WHERE idtraslado = ?";
        $stmt = $enlace->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        responder(200, ['ok' => true]);
    }

    if ($accion !== 'crear') {
        responder(400, ['ok' => false, 'error' => 'Acción no válida']);
    }

    // ---------- CREAR ----------
    $idAmbulancia = filter_var($datos['idambulancia'] ?? null, FILTER_VALIDATE_INT);
    $oLat = coordenada($datos['origen_lat'] ?? null, 90);
    $oLng = coordenada($datos['origen_lng'] ?? null, 180);
    $dLat = coordenada($datos['destino_lat'] ?? null, 90);
    $dLng = coordenada($datos['destino_lng'] ?? null, 180);
    $distancia = filter_var($datos['distancia_m'] ?? null, FILTER_VALIDATE_FLOAT);

    // Geoapify devuelve la duración con decimales (ej: 1322.7 s): la redondeamos a entero
    $duracion  = isset($datos['duracion_s']) && is_numeric($datos['duracion_s'])
        ? (int)round($datos['duracion_s'])
        : 0;

    $geometria = $datos['geometria'] ?? [];

    if (!$idAmbulancia || $oLat === null || $oLng === null || $dLat === null || $dLng === null
        || $distancia === false || $duracion <= 0
        || !is_array($geometria) || count($geometria) < 2 || count($geometria) > 5000) {
        responder(400, ['ok' => false, 'error' => 'Datos del traslado incompletos o inválidos']);
    }

    // Validar cada punto de la ruta
    $ruta = [];
    foreach ($geometria as $p) {
        $la = is_array($p) ? coordenada($p[0] ?? null, 90) : null;
        $lo = is_array($p) ? coordenada($p[1] ?? null, 180) : null;
        if ($la === null || $lo === null) {
            responder(400, ['ok' => false, 'error' => 'La ruta tiene puntos inválidos']);
        }
        $ruta[] = [$la, $lo];
    }

    $origenTexto  = mb_substr(trim($datos['origen_texto'] ?? ''), 0, 255);
    $destinoTexto = mb_substr(trim($datos['destino_texto'] ?? ''), 0, 255);
    $tipo      = mb_substr(trim($datos['tipo_traslado'] ?? ''), 0, 20);
    $conductor = mb_substr(trim($datos['conductor'] ?? ''), 0, 100);
    $paciente  = mb_substr(trim($datos['paciente'] ?? ''), 0, 100);
    $insumos   = mb_substr(trim($datos['insumos'] ?? ''), 0, 255);
    $nivel     = mb_substr(trim($datos['nivel_necesidad'] ?? ''), 0, 10);
    $horaSol   = preg_match('/^\d{2}:\d{2}$/', $datos['hora_solicitada'] ?? '') ? $datos['hora_solicitada'] : null;
    $geoJson   = json_encode($ruta);

    // Verificar que la ambulancia exista
    $chk = $enlace->prepare("SELECT 1 FROM ambulancia WHERE idambulancia = ?");
    $chk->bind_param('i', $idAmbulancia);
    $chk->execute();
    if ($chk->get_result()->num_rows === 0) {
        responder(400, ['ok' => false, 'error' => 'La ambulancia no existe']);
    }

    $stmt = $enlace->prepare(
        "INSERT INTO traslado
            (idambulancia, punto_origen, destino, estado, hora_salida, hora_estimada,
             tipo_traslado, conductor, paciente, insumos, nivel_necesidad, hora_solicitada,
             origen_lat, origen_lng, destino_lat, destino_lng, geometria, distancia_m, duracion_s)
         VALUES (?, ?, ?, 'En curso', NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND),
                 ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    // i s s i | s s s s s s | d d d d | s | d i
    $stmt->bind_param(
        'ississssssddddsdi',
        $idAmbulancia, $origenTexto, $destinoTexto, $duracion,
        $tipo, $conductor, $paciente, $insumos, $nivel, $horaSol,
        $oLat, $oLng, $dLat, $dLng,
        $geoJson, $distancia, $duracion
    );
    $stmt->execute();

    responder(201, ['ok' => true, 'id' => $enlace->insert_id]);

} catch (Throwable $e) {
    responder(500, ['ok' => false, 'error' => 'Error en traslados', 'detalle' => $e->getMessage()]);
}
