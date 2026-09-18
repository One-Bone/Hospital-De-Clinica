<?php

require_once 'php/conexion.php'; // Nota: Si este archivo está dentro de una carpeta (ej. php/ o views/), usa: require_once '../conexion.php';

$error_login = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $cedula = $_POST['cedula'];
    $email = $_POST['email'];
    $pass_ingresada = $_POST['pass'];

    #Consulta preparada con SHA2 para la contrasñea
    $insertarDatos = "INSERT INTO usuario (nombre, apellido, cedula_identidad, email, pass) VALUES (?, ?, ?, ?, SHA2(?, 256))";
    $stmt = mysqli_prepare($enlace, $insertarDatos);
    mysqli_stmt_bind_param($stmt, "sssss", $nombre, $apellido, $cedula, $email, $pass_ingresada);

    if (mysqli_stmt_execute($stmt)) {
        http_response_code(200);
        echo "Exito";
        exit;
    } else {
        http_response_code(500);
        echo "Error al registrar: " . mysqli_error($enlace);
        exit;
    }
}
?>
