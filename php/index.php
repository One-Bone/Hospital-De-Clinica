<?php
 
$server = "localhost";
$usuario = "root";
$pass = "";
$bdatos = "hospital";
 
$enlace = mysqli_connect($server, $usuario, $pass, $bdatos);
 
 
// Este bloque solo corre si el formulario fue enviado (metodo POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['iniciar_sesion'])) {
 
    $id_ingresado = $_POST['id'];
    $password_ingresada = $_POST['password'];
 
    if (mysqli_num_rows($resultado) === 1) {
        // Login correcto
        $usuario_logueado = mysqli_fetch_assoc($resultado);
        echo "Bienvenido, " . htmlspecialchars($usuario_logueado['nombre']);
                header("Location: index.html");

    } else {
        $error_login = "Cedula o contraseña incorrecta.";
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
 
        <form id="loginForm" method="POST" action="../index.html">
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