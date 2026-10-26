<?php
require_once 'conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Error: Método no permitido.";
    exit;
}

$campos = ['nombre', 'apellido', 'cedula', 'email', 'pass', 'conf-pass'];
foreach ($campos as $campo) {
    if (!isset($_POST[$campo]) || !is_string($_POST[$campo])) {
        http_response_code(400);
        echo "Error: Faltan completar campos obligatorios.";
        exit;
    }
}

$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$cedula = $_POST['cedula'];
$email = trim($_POST['email']);
$pass_ingresada = $_POST['pass'];
$confirmacion = $_POST['conf-pass'];

$nombre_valido = preg_match('/\A.{1,50}\z/u', $nombre) === 1;
$apellido_valido = preg_match('/\A.{1,50}\z/u', $apellido) === 1;
$email_valido = preg_match('/\A.{1,100}\z/u', $email) === 1 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
$password_valido = preg_match('/\A(?=.*[\d\W_]).{8,128}\z/u', $pass_ingresada) === 1;


if (
    !$nombre_valido || !$apellido_valido || !preg_match('/\A[0-9]{8}\z/', $cedula) || !$email_valido || !$password_valido || $pass_ingresada !== $confirmacion
) {
    http_response_code(400);
    echo "Error de validación: Asegúrate de que la cédula tenga exactamente 8 números y el correo sea válido.";
    exit;
}

$insertarDatos = "INSERT INTO usuario (nombre, apellido, cedula_identidad, email, pass)
                VALUES (?, ?, ?, ?, SHA2(?, 256))";
$stmt = mysqli_prepare($enlace, $insertarDatos);
mysqli_stmt_bind_param($stmt, "sssss", $nombre, $apellido, $cedula, $email, $pass_ingresada);

if (mysqli_stmt_execute($stmt)) {
    http_response_code(200);
    
    echo "Exito";
} else {
    http_response_code(500);

    if (mysqli_errno($enlace) == 1062) {
        echo "Error: Esta cédula ya se encuentra registrada en el sistema.";
    } else {
        echo "Error interno de la base de datos.";
    }
}

mysqli_stmt_close($stmt);
?>