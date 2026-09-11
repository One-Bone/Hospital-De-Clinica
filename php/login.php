<?php

session_start();

$server = "localhost";
$usuario = "root";
$pass = "";
$bdatos = "hospital";

$enlace = mysqli_connect($server, $usuario, $pass, $bdatos);

$error_login = "";

if (isset($_POST['iniciar_sesion'])) {

    $id_ingresado = $_POST['id'];
    $password_ingresada = $_POST['password'];

    $consulta = "SELECT * FROM usuario
                WHERE cedula_identidad = '$id_ingresado'
                AND pass = SHA2('$password_ingresada', 256)";

    $resultado = mysqli_query($enlace, $consulta);

    if (mysqli_num_rows($resultado) === 1) {
        $usuario_logueado = mysqli_fetch_assoc($resultado);

        #Guardar datos de sesion para que puedan ser usados en el resto de la pagina
        $_SESSION['id'] = $usuario_logueado['id_usuario'];
        $_SESSION['cedula'] = $usuario_logueado['cedula_identidad'];
        $_SESSION['nombre'] = $usuario_logueado['nombre'];
        $_SESSION['apellido'] = $usuario_logueado['apellido'];

        header("Location: ../index.php");
        exit;
    } else {
        $error_login = "Cédula o contraseña incorrecta.";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hospital de Clínica Montevideo</title>
    <link rel="stylesheet" href="../css/register-login.css">
</head>
<body>
    <div class="Login">
        <h1>Inicio de sesión</h1>

        <?php if ($error_login): ?>
            <p style="color:red;"><?= htmlspecialchars($error_login) ?></p>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="">
            <label for="id">Número de cédula:</label>
            <input type="text" id="id" name="id" required><br><br>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required><br><br>

            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Recuérdame</label><br><br>

            <input type="submit" name="iniciar_sesion" value="Iniciar sesión"><br><br>

            <label for="register">¿No tienes una cuenta?</label>
            <a href="../register.html">Regístrate aquí</a>
        </form>
    </div>
</body>
</html>