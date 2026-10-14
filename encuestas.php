<?php 
session_start(); 

require_once 'php/conexion.php';


if (!isset($_SESSION['id'])) {
    header("Location: php/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'crear') {
    $titulo = trim($_POST['titulo']);
    $enlace_form = trim($_POST['enlace_form']);
    $enlace_resp = trim($_POST['enlace_resp']);
    
    $sql_insert = "INSERT INTO encuesta (titulo_encuesta, enlace_formulario, enlace_respuestas) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($enlace, $sql_insert);
    mysqli_stmt_bind_param($stmt, "sss", $titulo, $enlace_form, $enlace_resp);
    
    mysqli_stmt_execute($stmt);
    header("Location: encuestas.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'eliminar') {
    $id_eliminar = (int)$_POST['delete_encuesta_id'];
    mysqli_query($enlace, "DELETE FROM encuesta WHERE idencuesta = $id_eliminar");
    header("Location: encuestas.php");
    exit;
}

$consulta_encuestas = "SELECT * FROM encuesta ORDER BY fecha_creacion DESC";
$resultado_encuestas = mysqli_query($enlace, $consulta_encuestas);
?>
<!DOCTYPE html>
<html lang="es" data-i18n-file="json/encuestas.json">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="page_title">Hospital de Clínica Montevideo - Encuestas</title>
    
    <link rel="stylesheet" href="css/style-a.css">
    <link rel="stylesheet" href="css/enc.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header" id="header-index">
        <div class="header-top">
            <div class="logo" id="logo-index">
                Hospital de Clinica <br> Montevideo
            </div>
            <div class="user-profile">
                <?php if (isset($_SESSION['id'])): ?>
                    <i class="fa-solid fa-circle-user avatar"></i>
                    <b><a class="user-name"><span><?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . ($_SESSION['apellido'] ?? '')); ?></span></a></b>
                <?php else: ?>
                    <a href="php/login.php"><span data-i18n="sign_in">Iniciar Sesion</span></a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Menu -->
        <div class="menu" id="menu-index">
            <a href="index.php" data-i18n="home">Inicio</a>
            <a href="documentos.php" data-i18n="documents">Documentos</a>
            <a href="personas.php" data-i18n="people">Personas</a>
            <a href="encuestas.php" data-i18n="surveys">Encuestas</a>
            <a href="viajes.php" data-i18n="trips">Viajes</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-body">
        <div class="main-content" id="main-content-historial">
            <h1 class="titulo-historial" data-i18n="section_title">Gestión de Encuestas</h1>

            <div class="Historial section">
                <?php if (mysqli_num_rows($resultado_encuestas) > 0): ?>
                    <?php while ($encuesta = mysqli_fetch_assoc($resultado_encuestas)): ?>
                        <div class="historial-card">
                            <div class="historial-card-header">
                                <div class="side-left-hs">
                                    <h2 class="historial-title">
                                        <i class="fa-solid fa-clipboard-list" style="color: var(--secondary-color); margin-right: 8px;"></i>
                                        <?php echo htmlspecialchars($encuesta['titulo_encuesta']); ?>
                                    </h2>
                                </div>
                                <div class="side-right-hs" style="display: flex; align-items: center; gap: 15px;">
                                    <p class="historial-date"><?php echo date("d/m/Y H:i", strtotime($encuesta['fecha_creacion'])); ?></p>
                                    
                                    <!-- Botón Eliminar -->
                                    <button class="btn-delete-encuesta" data-id="<?php echo $encuesta['idencuesta']; ?>" data-i18n-title="delete_survey" title="Eliminar encuesta">
                                        <i class="fa-solid fa-trash" style="color: #dc3545; font-size: 1.2rem;"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Botones de Google Forms -->
                            <div class="links-container">
                                <a href="<?php echo htmlspecialchars($encuesta['enlace_formulario']); ?>" target="_blank" class="link-btn btn-responder">
                                    <i class="fa-solid fa-pen-to-square"></i> <span data-i18n="answer_form">Responder Formulario</span>
                                </a>
                                <?php if (!empty($encuesta['enlace_respuestas'])): ?>
                                <a href="<?php echo htmlspecialchars($encuesta['enlace_respuestas']); ?>" target="_blank" class="link-btn btn-administrar">
                                    <i class="fa-solid fa-chart-pie"></i> <span data-i18n="manage_responses">Administrar Respuestas</span>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #777; font-size: 1.1rem; padding: 20px;" data-i18n="empty_surveys">Aún no hay encuestas vinculadas. Haz clic en el botón flotante para agregar una.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Botón Flotante para Vincular Encuesta -->
    <button class="crg-btn" id="btnCargarEncuesta" data-i18n-title="link_survey" title="Vincular nueva encuesta">
        <i class="fa-solid fa-plus" style="font-size: 1.8rem; color: white;"></i>
    </button>

    <!-- Modal: Vincular Encuesta -->
    <div class="modal-overlay" id="modalAltaEncuesta">
        <div class="modal-card">
            <h2 class="modal-title" style="margin-bottom: 1rem;" data-i18n="link_survey_title">Vincular Encuesta</h2>
            
            <div style="text-align: center; margin-bottom: 20px; background: #f4f6f9; padding: 15px; border-radius: 8px;">
                <p style="font-size: 0.9rem; color: #555; margin-bottom: 10px;" data-i18n="step_one">Paso 1: Crea la encuesta en Google Forms y copia los links correspondientes.</p>
                <a href="https://docs.google.com/forms" target="_blank" style="display: inline-block; padding: 8px 20px; background-color: #673AB7; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">
                    <i class="fa-brands fa-google"></i> <span data-i18n="open_google_forms">Abrir Google Forms</span>
                </a>
            </div>

            <form action="encuestas.php" method="POST" class="form-carga" novalidate>
                <input type="hidden" name="action" value="crear">
                
                <label for="titulo" data-i18n="survey_title">Título de la Encuesta:</label>
                <input type="text" id="titulo" name="titulo" class="input-carga" required placeholder="Ej: Satisfacción de pacientes" data-i18n-placeholder="survey_title_placeholder">

                <label for="enlace_form" data-i18n="form_link">Link del Formulario (Para responder):</label>
                <input type="url" id="enlace_form" name="enlace_form" class="input-carga" required placeholder="https://docs.google.com/forms/d/e/.../viewform">

                <label for="enlace_resp" data-i18n="responses_link">Link de Respuestas (Para administradores):</label>
                <input type="url" id="enlace_resp" name="enlace_resp" class="input-carga" required placeholder="https://docs.google.com/forms/d/.../edit#responses">

                <button type="submit" class="btn-submit-carga" data-i18n="link_survey_submit">Vincular Encuesta</button>
            </form>
        </div>
    </div>

    <!-- Modal: Confirmar Eliminación -->
    <div class="modal-overlay" id="deleteEncuestaModal">
        <div class="modal-card" style="height: auto; min-height: 200px; padding: 2.5rem;">
            <h2 class="modal-title" style="margin-bottom: 1rem; color: #dc3545;" data-i18n="confirm_deletion">Confirmar Eliminación</h2>
            <p style="text-align: center; margin-bottom: 2rem; color: #555;" data-i18n="confirm_delete_message">¿Borrar esta encuesta? Los datos en Google Forms seguirán existiendo, solo se borrarán los accesos desde aquí.</p>
            
            <form action="encuestas.php" method="POST" style="display: flex; justify-content: space-around;">
                <input type="hidden" name="action" value="eliminar">
                <input type="hidden" name="delete_encuesta_id" id="delete_encuesta_id_input" value="">
                
                <button type="button" id="btnCancelDeleteEncuesta" style="padding: 10px 25px; border-radius: 8px; border: 1px solid #ccc; background: #fff; cursor: pointer; font-weight: bold;" data-i18n="cancel">Cancelar</button>
                <button type="submit" style="padding: 10px 25px; border-radius: 8px; border: none; background: #dc3545; color: white; font-weight: bold; cursor: pointer;" data-i18n="confirm_delete">Sí, Eliminar</button>
            </form>
        </div>
    </div>

    <!-- Menú Lateral -->
    <div class="floating-menu-container">
        <div class="side-popup" id="sidePopup">
            <ul>
                <li class="MiniBtn"><a href="#" data-i18n="edit_profile">Editar Perfil</a></li>
                <li class="MiniBtn"><a href="#" data-i18n="settings">Configuración</a></li>
                <li id="preferencesItem" class="MiniBtn"><a href="#" id="btnOpenPreferences" data-i18n="preferences">Preferencias</a></li>
                <li class="MiniBtn"><a href="php/logout.php" data-i18n="sign_out">Cerrar sesión</a></li>
            </ul>
        </div>
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

    <script src="js/menu.js?v=<?php echo time(); ?>"></script>
    <script src="js/idioma.js?v=<?php echo time(); ?>"></script>
    <script src="js/encuesta.js?v=<?php echo time(); ?>"></script>
</body>
</html>