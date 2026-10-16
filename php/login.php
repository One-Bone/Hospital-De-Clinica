<?php

session_start();


require_once 'conexion.php';

$error_login = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['iniciar_sesion'])) {

    $id_ingresado = $_POST['id'] ?? null;
    $password_ingresada = $_POST['password'] ?? null;

    if (
        !is_string($id_ingresado)
        || !is_string($password_ingresada)
        || !preg_match('/\A[0-9]{8}\z/', $id_ingresado)
        || $password_ingresada === ''
        || mb_strlen($password_ingresada) > 512
    ) {
        $error_login = "Cédula o contraseña incorrecta.";
    } else {
        $consulta = "SELECT id_usuario, cedula_identidad, nombre, apellido
                     FROM usuario
                     WHERE cedula_identidad = ?
                     AND pass = SHA2(?, 256)";
        $stmt = mysqli_prepare($enlace, $consulta);
        mysqli_stmt_bind_param($stmt, "ss", $id_ingresado, $password_ingresada);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if ($usuario_logueado = mysqli_fetch_assoc($resultado)) {
            $_SESSION['id'] = $usuario_logueado['id_usuario'];
            $_SESSION['cedula'] = $usuario_logueado['cedula_identidad'];
            $_SESSION['nombre'] = $usuario_logueado['nombre'];
            $_SESSION['apellido'] = $usuario_logueado['apellido'];

            mysqli_stmt_close($stmt);
            header("Location: ../index.php");
            exit;
        }

        mysqli_stmt_close($stmt);
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
            <input type="text" id="id" name="id" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" autocomplete="username" required><br><br>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" maxlength="128" autocomplete="current-password" required><br><br>

            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Recuérdame</label><br><br>

            <input type="submit" name="iniciar_sesion" value="Iniciar sesión"><br><br>

            <label for="register">¿No tienes una cuenta?</label>
            <a href="../register.html">Regístrate aquí</a>
        </form>
    </div>
</body>
</html>