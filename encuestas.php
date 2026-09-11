<?php 
session_start(); 

// 1. CONEXIÓN A LA BASE DE DATOS
$server = "localhost";
$usuario = "root";
$pass = "";
$bdatos = "hospital";
$enlace = mysqli_connect($server, $usuario, $pass, $bdatos);

// Verificar sesión activa
if (!isset($_SESSION['id'])) {
    header("Location: php/login.php");
    exit;
}

// =============================================================
// A. CREAR NUEVA ENCUESTA (Guardar Links de Google Forms)
// =============================================================
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

// =============================================================
// B. ELIMINAR ENCUESTA
// =============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'eliminar') {
    $id_eliminar = (int)$_POST['delete_encuesta_id'];
    mysqli_query($enlace, "DELETE FROM encuesta WHERE idencuesta = $id_eliminar");
    header("Location: encuestas.php");
    exit;
}

// =============================================================
// C. LECTURA DE ENCUESTAS
// =============================================================
$consulta_encuestas = "SELECT * FROM encuesta ORDER BY fecha_creacion DESC";
$resultado_encuestas = mysqli_query($enlace, $consulta_encuestas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital de Clínica Montevideo - Encuestas</title>
    
    <link rel="stylesheet" href="css/style-a.css">
    <!-- El time() fuerza a que el navegador siempre cargue el CSS nuevo y no se buguee -->
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
                    <b><a href="perfil.html" class="user-name"><span><?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . ($_SESSION['apellido'] ?? '')); ?></span></a></b>
                <?php else: ?>
                    <a href="php/login.php"><span>Iniciar Sesion</span></a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Menu -->
        <div class="menu" id="menu-index">
            <a href="index.php">Inicio</a>
            <a href="documentos.php">Documentos</a>
            <a href="personas.php">Personas</a>
            <a href="encuestas.php">Encuestas</a>
            <a href="viajes.php">Viajes</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-body">
        <div class="main-content" id="main-content-historial">
            <h1 class="titulo-historial">Gestión de Encuestas</h1>

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
                                    <button class="btn-delete-encuesta" data-id="<?php echo $encuesta['idencuesta']; ?>" title="Eliminar encuesta">
                                        <i class="fa-solid fa-trash" style="color: #dc3545; font-size: 1.2rem;"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Botones de Google Forms -->
                            <div class="links-container">
                                <a href="<?php echo htmlspecialchars($encuesta['enlace_formulario']); ?>" target="_blank" class="link-btn btn-responder">
                                    <i class="fa-solid fa-pen-to-square"></i> Responder Formulario
                                </a>
                                <?php if (!empty($encuesta['enlace_respuestas'])): ?>
                                <a href="<?php echo htmlspecialchars($encuesta['enlace_respuestas']); ?>" target="_blank" class="link-btn btn-administrar">
                                    <i class="fa-solid fa-chart-pie"></i> Administrar Respuestas
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #777; font-size: 1.1rem; padding: 20px;">Aún no hay encuestas vinculadas. Haz clic en el botón flotante para agregar una.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Botón Flotante para Vincular Encuesta -->
    <button class="crg-btn" id="btnCargarEncuesta" title="Vincular nueva encuesta">
        <i class="fa-solid fa-plus" style="font-size: 1.8rem; color: white;"></i>
    </button>

    <!-- Modal: Vincular Encuesta -->
    <div class="modal-overlay" id="modalAltaEncuesta">
        <div class="modal-card">
            <h2 class="modal-title" style="margin-bottom: 1rem;">Vincular Encuesta</h2>
            
            <div style="text-align: center; margin-bottom: 20px; background: #f4f6f9; padding: 15px; border-radius: 8px;">
                <p style="font-size: 0.9rem; color: #555; margin-bottom: 10px;">Paso 1: Crea la encuesta en Google Forms y copia los links correspondientes.</p>
                <a href="https://docs.google.com/forms" target="_blank" style="display: inline-block; padding: 8px 20px; background-color: #673AB7; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">
                    <i class="fa-brands fa-google"></i> Abrir Google Forms
                </a>
            </div>

            <form action="encuestas.php" method="POST" class="form-carga" novalidate>
                <input type="hidden" name="action" value="crear">
                
                <label for="titulo">Título de la Encuesta:</label>
                <input type="text" id="titulo" name="titulo" class="input-carga" required placeholder="Ej: Satisfacción de pacientes">

                <label for="enlace_form">Link del Formulario (Para responder):</label>
                <input type="url" id="enlace_form" name="enlace_form" class="input-carga" required placeholder="https://docs.google.com/forms/d/e/.../viewform">

                <label for="enlace_resp">Link de Respuestas (Para administradores):</label>
                <input type="url" id="enlace_resp" name="enlace_resp" class="input-carga" required placeholder="https://docs.google.com/forms/d/.../edit#responses">

                <button type="submit" class="btn-submit-carga">Vincular Encuesta</button>
            </form>
        </div>
    </div>

    <!-- Modal: Confirmar Eliminación -->
    <div class="modal-overlay" id="deleteEncuestaModal">
        <div class="modal-card" style="height: auto; min-height: 200px; padding: 2.5rem;">
            <h2 class="modal-title" style="margin-bottom: 1rem; color: #dc3545;">Confirmar Eliminación</h2>
            <p style="text-align: center; margin-bottom: 2rem; color: #555;">¿Borrar esta encuesta? Los datos en Google Forms seguirán existiendo, solo se borrarán los accesos desde aquí.</p>
            
            <form action="encuestas.php" method="POST" style="display: flex; justify-content: space-around;">
                <input type="hidden" name="action" value="eliminar">
                <input type="hidden" name="delete_encuesta_id" id="delete_encuesta_id_input" value="">
                
                <button type="button" id="btnCancelDeleteEncuesta" style="padding: 10px 25px; border-radius: 8px; border: 1px solid #ccc; background: #fff; cursor: pointer; font-weight: bold;">Cancelar</button>
                <button type="submit" style="padding: 10px 25px; border-radius: 8px; border: none; background: #dc3545; color: white; font-weight: bold; cursor: pointer;">Sí, Eliminar</button>
            </form>
        </div>
    </div>

    <!-- Menú Lateral -->
    <div class="floating-menu-container">
        <div class="side-popup" id="sidePopup">
            <ul>
                <li class="MiniBtn"><a href="#">Editar Perfil</a></li>
                <li class="MiniBtn"><a href="#">Configuración</a></li>
                <li id="preferencesItem" class="MiniBtn"><a href="#">Preferencias</a></li>
                <li class="MiniBtn"><a href="php/logout.php">Cerrar sesión</a></li>
            </ul>
        </div>
        <button class="fab-btn" id="fabBtn">
            <i class="fa-solid fa-bars" id="fabIcon"></i>
        </button>
    </div>

    <script src="js/menu.js?v=<?php echo time(); ?>"></script>
    <script src="js/encuesta.js?v=<?php echo time(); ?>"></script>
</body>
</html>