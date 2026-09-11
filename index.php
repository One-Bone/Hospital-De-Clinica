<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital de Clinica Montevideo</title>
    <link rel="preload" href="css/style-a.css" as="style">
    <link rel="stylesheet" href="css/style-a.css?v=<?php echo time(); ?>" as="style">
    
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
            <!-- Session -->
            <?php if (isset($_SESSION['id'])): ?>
                <i class="fa-solid fa-circle-user avatar"></i>
                <b><a class="user-name"><span><?php echo $_SESSION['nombre'] . ' ' . $_SESSION['apellido']; ?></span></a></b>
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
        <a href="personas.php">Personas</a>
        <a href="encuestas.php">Encuestas</a>
        <a href="viajes.php">Viajes</a>
        <?php else: ?>
        <a href="index.php">Inicio</a>
        <?php endif; ?>
    </div>
    </header>

    <!-- Main Content -->
    <main class="main-body">
        <div class="main-content" id="main-content-index">
            <?php if (isset($_SESSION['id'])): ?>
                <section class="services-section">
                    <div class="services-grid">

                        <div class="service-item">
                            <div onclick="window.location.href='documentos.php'" class="service-card">
                                <i class="fa-solid fa-file-alt"></i>
                            </div>
                            <b>Documentos</b>
                        </div>

                        <div class="service-item">
                            <div onclick="window.location.href='personas.php'" class="service-card">
                                <i class="fa-solid fa-user-friends"></i>
                            </div>
                            <b>Personas</b>
                        </div>

                        <div class="service-item">
                            <div onclick="window.location.href='encuestas.php'" class="service-card">
                                <i class="fa-solid fa-chart-bar"></i>
                            </div>
                            <b>Encuestas</b>
                        </div>

                        <div class="service-item">
                            <div onclick="window.location.href='viajes.php'" class="service-card">
                                <i class="fa-solid fa-ambulance"></i>
                            </div>
                            <b>Viajes</b>
                        </div>
                    </div>
                </section>
            <?php else: ?>
                <section class="login-prompt">
                    <p>Inicia sesión para acceder a los servicios del Hospital de Clínica Montevideo.</p>
                    <a href="php/login.php" class="login-button">Iniciar Sesión</a>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <!-- Seccion Mini Menu -->
    <!-- Button FLoat -->
    <div class="floating-menu-container">

        <!-- Popup Menu -->
        <div class="side-popup" id="sidePopup">

            <!-- Session -->
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