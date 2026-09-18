<?php
// conexion.php

// 1. carga del config
require_once 'config.php';

// 2. intento de conexion con la base de datos
$con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 3. verificacion en caso de error 
if ($con->connect_error) {
    die("Error de conexión a la base de datos: " . $con->connect_error);
}

// 4. Establecemos UTF-8 para que no haya problemas con tildes y 'ñ'
$con->set_charset("utf8mb4");
?>