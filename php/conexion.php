<?php
require_once 'config.php';

$enlace = new mysqli($server, $usuario, $pass, $bdatos);

if ($enlace->connect_error) {
    die("Error de conexión a la base de datos: " . $con->connect_error);
}

$enlace->set_charset("utf8mb4");
?>