<?php
// Alta y baja de ambulancias (POST JSON). Solo para usuarios logueados.
//   crear:    { accion: "crear", matricula, marca, modelo }
//   eliminar: { accion: "eliminar", idambulancia }
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

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
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function responder(int $codigo, array $cuerpo): void
{
    http_response_code($codigo);
    echo json_encode($cuerpo);
    exit;
}

$datos  = json_decode(file_get_contents('php://input'), true) ?? [];
$accion = $datos['accion'] ?? '';

try {
    // ---------- CREAR ----------
    if ($accion === 'crear') {
        $matricula = mb_substr(mb_strtoupper(trim($datos['matricula'] ?? '')), 0, 50);
        $marca     = mb_substr(trim($datos['marca'] ?? ''), 0, 50);
        $modelo    = mb_substr(trim($datos['modelo'] ?? ''), 0, 50);

        if ($matricula === '') {
            responder(400, ['ok' => false, 'error' => 'Falta la matrícula']);
        }

        $chk = $enlace->prepare("SELECT 1 FROM ambulancia WHERE matricula = ?");
        $chk->bind_param('s', $matricula);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            responder(409, ['ok' => false, 'error' => 'Ya existe una ambulancia con esa matrícula']);
        }

        $stmt = $enlace->prepare("INSERT INTO ambulancia (matricula, marca, modelo) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $matricula, $marca, $modelo);
        $stmt->execute();

        responder(201, ['ok' => true, 'id' => $enlace->insert_id]);
    }

    // ---------- ELIMINAR ----------
    if ($accion === 'eliminar') {
        $id = filter_var($datos['idambulancia'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id <= 0) {
            responder(400, ['ok' => false, 'error' => 'Ambulancia inválida']);
        }

        $chk = $enlace->prepare("SELECT 1 FROM ambulancia WHERE idambulancia = ?");
        $chk->bind_param('i', $id);
        $chk->execute();
        if ($chk->get_result()->num_rows === 0) {
            responder(404, ['ok' => false, 'error' => 'La ambulancia no existe']);
        }

        // La tabla traslado protege la ambulancia: si tiene traslados, no se borra
        $tr = $enlace->prepare("SELECT COUNT(*) AS n FROM traslado WHERE idambulancia = ?");
        $tr->bind_param('i', $id);
        $tr->execute();
        $n = (int)($tr->get_result()->fetch_assoc()['n'] ?? 0);
        if ($n > 0) {
            responder(409, ['ok' => false, 'error' => "No se puede eliminar: tiene $n traslado(s) registrado(s)"]);
        }

        // Borra la ambulancia y su historial de ubicaciones juntos
        $enlace->begin_transaction();
        try {
            $d1 = $enlace->prepare("DELETE FROM ubicacion WHERE idambulancia = ?");
            $d1->bind_param('i', $id);
            $d1->execute();

            $d2 = $enlace->prepare("DELETE FROM ambulancia WHERE idambulancia = ?");
            $d2->bind_param('i', $id);
            $d2->execute();

            $enlace->commit();
        } catch (Throwable $e) {
            $enlace->rollback();
            throw $e;
        }

        responder(200, ['ok' => true]);
    }

    responder(400, ['ok' => false, 'error' => 'Acción no válida']);

} catch (Throwable $e) {
    responder(500, ['ok' => false, 'error' => 'Error al guardar la ambulancia', 'detalle' => $e->getMessage()]);
}
