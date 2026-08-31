<?php

$server = "localhost";
$usuario = "root";
$pass = "";
$bdatos = "hospital";

$enlace = mysqli_connect($server, $usuario, $pass, $bdatos);

if (isset($_POST['registrarse'])) {

    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $id = $_POST['id'];
    $email = $_POST['email'];
    $contrasena = $_POST['pass'];
    $confirmar = $_POST['conf_pass'];

    if ($contrasena != $confirmar) {
        header("Location: register.html?error=1");
        exit;
    }

    $insertarDatos = "INSERT INTO datosregister (nombre, apellido, id, email, pass)
                    VALUES ('$nombre', '$apellido', '$id', '$email', SHA2('$contrasena', 256))";

    $ejecutarInsertar = mysqli_query($enlace, $insertarDatos);

    if ($ejecutarInsertar) {
        header("Location: index.php");
        exit;
    } else {
        header("Location: register.html?error=2");
        exit;
    }
}
?>