<?php
// conexion.php

// 1. carga del config
require_once 'config.php';

// 2. intento de conexion con la base de datos
$enlace = new mysqli($server, $usuario, $pass, $bdatos);

// 3. verificacion en caso de error 
if ($enlace->connect_error) {
    die("Error de conexión a la base de datos: " . $con->connect_error);
}

// 4. Establecemos UTF-8 para que no haya problemas con tildes y 'ñ'
$enlace->set_charset("utf8mb4");
?>