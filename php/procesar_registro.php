<?php
 
$server = "localhost";
$usuario = "root";
$pass = "";
$bdatos = "hospital";
 
$enlace = mysqli_connect($server, $usuario, $pass, $bdatos);
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $cedula = $_POST['cedula'];
    $email = $_POST['email'];
    $pass_ingresada = $_POST['pass'];
 
    // Consulta preparada: especificamos las columnas en el mismo orden que los valores,
    // y ciframos la contraseña con SHA2 antes de guardarla.
    $insertarDatos = "INSERT INTO usuario (nombre, apellido, cedula_identidad, email, pass) VALUES (?, ?, ?, ?, SHA2(?, 256))";
    $stmt = mysqli_prepare($enlace, $insertarDatos);
    mysqli_stmt_bind_param($stmt, "sssss", $nombre, $apellido, $cedula, $email, $pass_ingresada);
 
    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../php/login.php");
        exit;
    } else {
        echo "Error al registrar: " . mysqli_error($enlace);
    }
}
?>
 