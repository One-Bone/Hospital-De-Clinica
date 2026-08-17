<?php
 
$server = "localhost";
$usuario = "root";
$pass = "";
$bdatos = "hospital";
 
$enlace = mysqli_connect($server, $usuario, $pass, $bdatos);
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $id = $_POST['id'];
    $email = $_POST['email'];
    $pass_ingresada = $_POST['pass'];
 
    $insertarDatos = "INSERT INTO datosregister (nombre, apellido, id, email, pass) VALUES (?, ?, ?, ?, SHA2(?, 256))";
    $stmt = mysqli_prepare($enlace, $insertarDatos);
    mysqli_stmt_bind_param($stmt, "sssss", $nombre, $apellido, $id, $email, $pass_ingresada);
 
    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php");
        exit;
    } else {
        echo "Error al registrar: " . mysqli_error($enlace);
    }
}
?>
 