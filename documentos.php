<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital de Clinica Montevideo - Documentación</title>
    
    <link rel="preload" href="css/style-a.css" as="style">
    <link rel="stylesheet" href="css/style-a.css" as="style">
    <link rel="preload" href="css/historial.css" as="style">
    <link rel="stylesheet" href="css/historial.css" as="style">

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
            <h1 class="titulo-historial">Documentación</h1>

            <!-- Historial Section -->
            <div class="Historial section">
                <div class="historial-card">
                    <div class="historial-card-header">
                        <div class="side-left-hs">
                            <h2 class="historial-title">Documento 1</h2>
                        </div>
                        <div class="side-right-hs">
                            <p class="historial-date">Fecha</p>
                        </div>
                    </div>
                    <p class="historial-description">Descripción del documento 1.</p>
                    <a href="#" class="qr-link">Ver Detalles</a>
                </div>

                <!-- Tarjetas -->
                <div class="historial-card">
                    <div class="historial-card-header">
                        <div class="side-left-hs">
                            <h2 class="historial-title">Documento 2</h2>
                        </div>
                        <div class="side-right-hs">
                            <p class="historial-date">Fecha</p>
                        </div>
                    </div>
                    <p class="historial-description">Descripción del documento 2.</p>
                    <a href="#" class="qr-link">Ver Detalles</a>
                </div>
            </div>
        </div>
    </main>


    <!-- NUEVO: Botón Crear Documentación (Bolita flotante más arriba) -->
    <button class="crg-btn" id="createBtn" 
        style="bottom: 90px; background-color: #007bff; border-radius: 50%; width: 50px; height: 50px; display: flex; justify-content: center; align-items: center; padding: 0;" 
        title="Crear Nueva Documentación">
        <i class="fa-solid fa-file-pen" style="font-size: 1.2rem; color: white; margin: 0;"></i> 
    </button>

    <!-- ORIGINAL: Botón de Cargar Archivo -->
    <button class="crg-btn" id="crgBtn" title="Cargar Archivo">
        <i class="cr-solid cr-plus" id="cr-plus"></i>
    </button>

    <div class="modal-overlay" id="createModal">
        <div class="modal-card">
            <h2 class="modal-title">Crear Documentación</h2>
            <form id="createForm" action="#" method="post">
                <label for="newDocName">Nombre del Documento:</label>
                <input type="text" id="newDocName" name="newDocName" required>

                <label for="newDocDate">Fecha:</label>
                <input type="date" id="newDocDate" name="newDocDate" required>

                <label for="newDocContent">Contenido / Texto:</label>
                <textarea id="newDocContent" name="newDocContent" rows="4" required style="width: 100%; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; padding: 8px; resize: vertical;"></textarea>

                <button type="submit">Crear y Guardar</button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="chargeModal">
        <div class="modal-card">
            <h2 class="modal-title">Cargar Documento</h2>
            <form id="chargeForm" action="#" method="post" enctype="multipart/form-data">
                <label for="studyName">Nombre del Documento:</label>
                <input type="text" id="studyName" name="studyName" required>

                <label for="studyDate">Fecha del Documento:</label>
                <input type="date" id="studyDate" name="studyDate" required>

                <label for="studyFile">Archivo del Documento:</label>
                <input type="file" id="studyFile" name="studyFile" accept=".pdf,.jpg,.png" required>

                <button type="submit">Cargar</button>
            </form>
        </div>
    </div>

    <!-- Seccion Mini Menu -->
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

    <!-- Preferences Modal -->
    <div class="modal-overlay" id="preferencesModal">
        <div class="modal-card">
            <!-- (Contenido de preferencias...) -->
            <h2 class="modal-title">Preferencias</h2>
            <div class="pref-row">
                <span class="pref-label">Modo Oscuro</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="darkModeToggle">
                    <span class="slider"></span>
                </label>
            </div>
            <!-- etc... -->
        </div>
    </div>
    
    <script src="js/menu.js"></script>
</body>
</html>