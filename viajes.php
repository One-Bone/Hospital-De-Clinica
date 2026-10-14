<?php
session_start();

require_once 'php/config.php';

if(!isset($_SESSION['id'])){
    header("Location: php/login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="es" data-i18n-file="json/viajes.json">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="page_title">Hospital de Clinica Montevideo - Viajes</title>
    <link rel="preload" href="css/style-a.css" as="style">
    <link rel="stylesheet" href="css/style-a.css" as="style">
    <link rel="preload" href="css/viajes.css" as="style">
    <link rel="stylesheet" href="css/viajes.css" as="style">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
</head>
<body>
    <!-- Header -->
    <header class="header" id="header-index">
    <!-- Menu -->
    <div class="menu" id="menu-index">
        <?php if (isset($_SESSION['id'])): ?>
        <a href="index.php" data-i18n="home">Inicio</a>
        <a href="documentos.php" data-i18n="documents">Documentos</a>
        <a href="personas.php" data-i18n="people">Personas</a>
        <a href="encuestas.php" data-i18n="surveys">Encuestas</a>
        <a href="viajes.php" data-i18n="trips">Viajes</a>
        <?php else: ?>
        <a href="index.php" data-i18n="home">Inicio</a>
        <?php endif; ?>
    </div>
    </header>

    <!-- Map Content -->
    <main class="main-viajes">
        <div id="map"></div>
    </main>

    <!-- Zoom in/out btn -->
    <div class="zoom-controls">
        <button class="zoom-btn" id="zoomInBtn">+</button>
        <button class="zoom-btn" id="zoomOutBtn">-</button>
    </div>

    <!-- Botón simulación ambulancia -->
    <div class="map-ambulance">
        <button class="ambulance-map" id="ambulanceMap" title="Simular movimiento de ambulancia" data-i18n-title="simulate_ambulance">
            <i class="fa-solid fa-truck-medical"></i>
        </button>
    </div>

    <!-- Button Ambulance -->
    <div class="floating-ambulance">
        <button class="ambulance-btn" id="ambulanceBtn">
            <i class="fa-solid fa-ambulance"></i>
        </button>
        <span data-i18n="request_transport">Solicitar traslado</span>
    </div>

    <!-- Ambulance Modal -->
    <div class="modal-overlay" id="trasladoModal">
            <div class="modal-card modal-traslado">
                <h2 class="modal-title-traslado" data-i18n="request_transport_title">Solicitar Traslado</h2>
                
                <form id="formSolicitarTraslado" class="traslado-form-scroll">
                    <div class="form-row">
                        <label class="form-label" data-i18n="transport_type">Tipo de traslado</label>
                        <select class="form-input custom-select" id="tipoTraslado">
                            <option value="Paciente" data-i18n="patient">Paciente</option>
                            <option value="Insumos" data-i18n="supplies">Insumos</option>
                            <option value="Ambos" data-i18n="both">Ambos</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <label class="form-label" data-i18n="departure">Salida</label>
                        <input type="text" class="form-input" id="direccionSalida" placeholder="Ingrese Dirección" data-i18n-placeholder="enter_address" required>
                    </div>
                
                    <div class="form-row">
                        <label class="form-label" data-i18n="arrival">Llegada</label>
                        <input type="text" class="form-input" id="direccionLlegada" placeholder="Ingrese Dirección" data-i18n-placeholder="enter_address" required>
                    </div>
                
                    <div class="form-row">
                        <label class="form-label" data-i18n="arrival_time">Hora de llegada</label>
                        <input type="time" class="form-input btn-blue-input" id="horaLlegada">
                    </div>
                
                    <div class="form-row">
                        <label class="form-label" data-i18n="urgency">Nivel de necesidad</label>
                        <select class="form-input custom-select select-red" id="nivelNecesidad">
                            <option value="Alta" data-i18n="high">Alta</option>
                            <option value="Media" data-i18n="medium">Media</option>
                            <option value="Baja" data-i18n="low">Baja</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <label class="form-label" data-i18n="supplies">Insumos</label>
                        <input type="text" class="form-input" id="listaInsumos" placeholder="Lista de Insumos" data-i18n-placeholder="supplies_list">
                    </div>
                
                    <div class="form-row">
                        <label class="form-label" data-i18n="for">Para</label>
                        <input type="text" class="form-input" id="asignacionPara" value="Ambulancia más Cercana" data-i18n-value="nearest_ambulance">
                    </div>
                
                    <div class="form-row">
                        <label class="form-label" data-i18n="patient">Paciente</label>
                        <input type="text" class="form-input" id="datosPaciente" placeholder="Registrar más tarde" data-i18n-placeholder="register_later">
                    </div>
                
                    <div class="form-row">
                        <label class="form-label" data-i18n="driver">Conductor</label>
                        <input type="text" class="form-input" id="datosConductor" value="Automático" data-i18n-value="automatic">
                    </div>
                
                    <div class="form-submit-container">
                        <button type="submit" class="btn-submit-traslado" data-i18n="send">Enviar</button>
                    </div>
                </form>
            </div>
    </div>
    <!-- Seccion Mini Menu -->
    <!-- Button FLoat -->
    <div class="floating-menu-container">

        <!-- Popup Menu -->
        <div class="side-popup" id="sidePopup">
                <ul>
                    <li class="MiniBtn"><a href="#" data-i18n="edit_profile">Editar Perfil</a></li>
                    <li class="MiniBtn"><a href="#configuracion.html" data-i18n="settings">Configuracion</a></li>
                    <li id="preferencesItem" class="MiniBtn"><a href="#" id="btnOpenPreferences" data-i18n="preferences">Preferencias</a></li>
                    <li class="MiniBtn"><a href="php/logout.php" data-i18n="sign_out">Cerrar sesion</a></li>
                </ul>
            <?php else: ?>
                <ul>
                    <li class="MiniBtn"><a href="php/login.php" data-i18n="sign_in">Iniciar Sesion</a></li>
                    <li class="MiniBtn"><a href="register.html" data-i18n="register">Registrarse</a></li>
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
            <h2 class="modal-title" data-i18n="preferences">Preferencias</h2>

            <!-- Dark Switch -->
            <div class="pref-row">
                <span class="pref-label" data-i18n="dark_mode">Modo Oscuro</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="darkModeToggle">
                    <span class="slider"></span>
                </label>
            </div>

            <!-- Lenguaje Dropdown -->
            <div class="pref-row">
                <span class="pref-label" data-i18n="language">Idioma</span>
                <div class="custom-dropdown" id="dropdownLanguage">
                    <button class="dropdown-btn" id="dropdownBtn">Español</button>
                    <div class="dropdown-content">
                        <div class="dropdown-item active" data-lang="es" data-i18n="language_es">Español</div>
                        <div class="dropdown-item" data-lang="en" data-i18n="language_en">English</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
    <script src="js/menu.js?v=<?php echo time(); ?>"></script>
    <script src="js/idioma.js?v=<?php echo time(); ?>"></script>
    <script src="js/viajes.js?v=<?php echo time(); ?>"></script>
</body>
</html>