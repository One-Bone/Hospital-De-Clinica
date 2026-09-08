<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital de Clinica Montevideo</title>
    <link rel="preload" href="css/style-a.css" as="style">
    <link rel="stylesheet" href="css/style-a.css" as="style">
    <link rel="preload" href="css/historial.css" as="style">
    <link rel="stylesheet" href="css/historial.css" as="style">
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
        <?php if (isset($_SESSION['id'])): ?>
        <a href="index.php">Inicio</a>
        <a href="documentos.php">Documentos</a>
        <a href="encuestas.php">Encuestas</a>
        <a href="viajes.php">Viajes</a>
        <?php else: ?>
        <a href="index.php">Inicio</a>
        <?php endif; ?>
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
                            <h2 class="historial-title">Estudio 1</h2>
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

                <div class="historial-card">
                    <div class="historial-card-header">
                        <div class="side-left-hs">
                            <h2 class="historial-title">Estudio 3</h2>
                        </div>
                        <div class="side-right-hs">
                            <p class="historial-date">Fecha</p>
                        </div>
                    </div>
                    <p class="historial-description">Descripción del estudio 3  .</p>
                    <a href="#" class="qr-link">Ver Detalles</a>
                </div>

                <div class="historial-card">
                    <div class="historial-card-header">
                        <div class="side-left-hs">
                            <h2 class="historial-title">Estudio 4</h2>
                        </div>
                        <div class="side-right-hs">
                            <p class="historial-date">Fecha</p>
                        </div>
                    </div>
                    <p class="historial-description">Descripción del estudio 4.</p>
                    <a href="#" class="qr-link">Ver Detalles</a>
                </div>

                <div class="historial-card">
                    <div class="historial-card-header">
                        <div class="side-left-hs">
                            <h2 class="historial-title">Estudio 5</h2>
                        </div>
                        <div class="side-right-hs">
                            <p class="historial-date">Fecha</p>
                        </div>
                    </div>
                    <p class="historial-description">Descripción del estudio 5.</p>
                    <a href="#" class="qr-link">Ver Detalles</a>
                </div>

            </div>
        </div>
    </main>

    <!-- Button Charge -->
    <button class="crg-btn" id="crgBtn">
        <i class="cr-solid cr-plus" id="cr-plus"></i>
    </button>

    <!-- Charge Modal -->
    <div class="modal-overlay" id="chargeModal">
        <div class="modal-card">
            <h2 class="modal-title">Cargar Estudio</h2>
            <form id="chargeForm" action="#" method="post" enctype="multipart/form-data">
                <label for="studyName">Nombre del Estudio:</label>
                <input type="text" id="studyName" name="studyName" required>

                <label for="studyDate">Fecha del Estudio:</label>
                <input type="date" id="studyDate" name="studyDate" required>

                <label for="studyFile">Archivo del Estudio:</label>
                <input type="file" id="studyFile" name="studyFile" accept=".pdf,.jpg,.png" required>

                <button type="submit">Cargar</button>
            </form>
        </div>

    <!-- Seccion Mini Menu -->
    <!-- Button FLoat -->
    <div class="floating-menu-container">

        <!-- Popup Menu -->
        <div class="side-popup" id="sidePopup">
            <?php if (isset($_SESSION['id'])): ?>
                <ul>
                    <li class="MiniBtn"><a href="#">Editar Perfil</a></li>
                    <li class="MiniBtn"><a href="#configuracion.html">Configuracion</a></li>
                    <li id="preferencesItem" class="MiniBtn"><a href="#" id="btnOpenPreferences">Preferencias</a></li>
                    <li class="MiniBtn"><a href="php/logout.php">Cerrar sesion</a></li>
                </ul>
            <?php else: ?>
                <ul>
                    <li class="MiniBtn"><a href="php/login.php">Iniciar Sesion</a></li>
                    <li class="MiniBtn"><a href="register.html">Registrarse</a></li>
                </ul>
            <?php endif; ?>
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
