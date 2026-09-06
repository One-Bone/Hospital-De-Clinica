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
 
        // Guardamos los datos del usuario en la sesion,
        // para poder usarlos en las demas paginas del sitio.
        $_SESSION['id'] = $usuario_logueado['id_usuario'];
        $_SESSION['cedula'] = $usuario_logueado['cedula_identidad'];
        $_SESSION['nombre'] = $usuario_logueado['nombre'];
 
        header("Location: ../index.html");
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
 
        <form id="loginForm" method="POST" action="index.php">
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