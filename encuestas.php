<?php 
session_start(); 

// BD connect
$server = "localhost";
$usuario = "root";
$pass = "";
$bdatos = "hospital";
$enlace = mysqli_connect($server, $usuario, $pass, $bdatos);

// Verify log session
if (!isset($_SESSION['id'])) {
    header("Location: php/login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital de Clinica Montevideo</title>
    <link rel="preload" href="css/style-a.css" as="style">
    <link rel="stylesheet" href="css/style-a.css?v=<?php echo time(); ?>" as="style">
    
    <link rel="preload" href="css/doc-enc.css" as="style">
    <link rel="stylesheet" href="css/doc-enc.css?v=<?php echo time(); ?>" as="style">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header" id="header-index">
    <div class="header-top">
        <div class="logo" id="logo-index">
        Hospital de Clinica <br> Montevideo
        </div>
        <!-- <div class="buscador" id="buscador-index"> -->
            <!-- <input type="text" placeholder="Buscar..."> -->
        <!-- </div> -->
        <div class="user-profile">
            <?php if (isset($_SESSION['id'])): ?>
                <i class="fa-solid fa-circle-user avatar"></i>
                <b><a href="perfil.html" class="user-name"><span><?php echo $_SESSION['nombre'] . ' ' . $_SESSION['apellido']; ?></span></a></b>
            <?php else: ?>
                <a href="php/login.php"><span>Iniciar Sesion</span></a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Menu -->
    <div class="menu" id="menu-index">
        <a href="index.php">Inicio</a>
        <a href="documentos.php">Documentos</a>
        <a href="encuestas.php">Encuestas</a>
        <a href="viajes.php">Viajes</a>
    </div>
    </header>

    <!-- Main Content -->
    <main class="main-body">
        <div class="main-content" id="main-content-historial">
            <h1 class="titulo-historial">Historial de Estudios</h1>

            <!-- Historial Section -->
            <div class="Historial section">

                <div class="historial-card">
                    <div class="historial-card-header">
                        <div class="side-left-hs">
                            <h2 class="historial-title">Encuesta</h2>
                        </div>
                        <div class="side-right-hs">
                            <p class="historial-date">Fecha</p>
                        </div>
                    </div>
                    <p class="historial-description">Descripción del estudio 1.</p>
                    <a href="#" class="qr-link">Ver Detalles</a>
                </div>
                
                <div class="historial-card">
                    <div class="historial-card-header">
                        <div class="side-left-hs">
                            <h2 class="historial-title">Estudio 2</h2>
                        </div>
                        <div class="side-right-hs">
                            <p class="historial-date">Fecha</p>
                        </div>
                    </div>
                    <p class="historial-description">Descripción del estudio 2.</p>
                    <a href="#" class="qr-link">Ver Detalles</a>
                </div>

            </div>
        </div>
    </main>

    <!-- Seccion Mini Menu -->
    <!-- Button FLoat -->
    <div class="floating-menu-container">

        <!-- Popup Menu -->
        <div class="side-popup" id="sidePopup">
                <ul>
                    <li class="MiniBtn"><a href="#">Editar Perfil</a></li>
                    <li class="MiniBtn"><a href="#configuracion.html">Configuracion</a></li>
                    <li id="preferencesItem" class="MiniBtn"><a href="#" id="btnOpenPreferences">Preferencias</a></li>
                    <li class="MiniBtn"><a href="php/logout.php">Cerrar sesion</a></li>
                </ul>
        </div>

        <!-- Button Toggle -->
        <button class="fab-btn" id="fabBtn">
            <i class="fa-solid fa-bars" id="fabIcon"></i>
        </button>
    </div>

    <!-- Preferences -->
    <div class="modal-overlay" id="preferencesModal">
        <div class="modal-card">
            <h2 class="modal-title">Preferencias</h2>

            <!-- Dark Switch -->
            <div class="pref-row">
                <span class="pref-label">Modo Oscuro</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="darkModeToggle">
                    <span class="slider"></span>
                </label>
            </div>

            <!-- Lenguaje Dropdown -->
            <div class="pref-row">
                <span class="pref-label">Idioma</span>
                <div class="custom-dropdown" id="dropdownLanguage">
                    <button class="dropdown-btn" id="dropdownBtn">Español</button>
                    <div class="dropdown-content">
                        <div class="dropdown-item active" data-lang="Español">Español</div>
                        <div class="dropdown-item" data-lang="Ingles">Ingles</div>
                        <div class="dropdown-item" data-lang="Frances">Frances</div>
                        <div class="dropdown-item" data-lang="Portugues">Portugues</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/menu.js"></script>
</body>
</html>
