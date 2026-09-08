<?php
session_start();

// BD CONNECT
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

// BD FILE UPLOAD
// Verify form send
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['studyFile'])) {
    
    $titulo = $_POST['studyName'];
    $descripcion = $_POST['studyDesc'];
    
    // Folder uploaded files
    $directorio_destino = 'uploads/';
    
    // Folder !exist, create with 0777 (read/write/execute for all users)
    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0777, true);
    }

    // Get file name
    $nombre_archivo = basename($_FILES["studyFile"]["name"]);
    
    // Avoid overwritting files w same name
    // Time stamp in file name start to avoid
    $nombre_archivo_unico = time() . '_' . $nombre_archivo; 
    $ruta_final_archivo = $directorio_destino . $nombre_archivo_unico;

    // move_uploaded_file moves from temp folder to end folder
    if (move_uploaded_file($_FILES["studyFile"]["tmp_name"], $ruta_final_archivo)) {
        
        // if exit when move, insert into DB
        $insertar = "INSERT INTO documentacion (titulo, descripcion, archivo) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($enlace, $insertar);
        mysqli_stmt_bind_param($stmt, "sss", $titulo, $descripcion, $ruta_final_archivo);
        mysqli_stmt_execute($stmt);
        header("Location: documentos.php");
        exit;
    } else {
        $error_msg = "Error: No se pudo mover el archivo al directorio de destino.";
    }
}

// Reading
// All files from DB order by timestamp
$consulta_docs = "SELECT * FROM documentacion ORDER BY fecha_carga DESC";
$resultado_docs = mysqli_query($enlace, $consulta_docs);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital de Clinica Montevideo - Documentación</title>
    <link rel="stylesheet" href="css/style-a.css">
    <link rel="stylesheet" href="css/doc-enc.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header" id="header-index">
        <div class="header-top">
            <div class="logo">
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
            <a href="encuestas.php">Encuestas</a>
            <a href="viajes.php">Viajes</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-body">
        <div class="main-content" id="main-content-historial">
            <h1 class="titulo-historial">Documentación</h1>

            <?php if (isset($error_msg)): ?>
                <p style="color: red; font-weight: bold; text-align: center;"><?php echo $error_msg; ?></p>
            <?php endif; ?>

            <!-- Php loop to renderize from BD -->
            <div class="Historial section">
                <?php if (mysqli_num_rows($resultado_docs) > 0): ?>
                    <?php while ($doc = mysqli_fetch_assoc($resultado_docs)): ?>
                        <div class="historial-card">
                            <div class="historial-card-header">
                                <div class="side-left-hs">
                                    <h2 class="historial-title"><?php echo htmlspecialchars($doc['titulo']); ?></h2>
                                </div>
                                <div class="side-right-hs">
                                    <!-- Format Date and Hour -->
                                    <p class="historial-date"><?php echo date("d/m/Y H:i", strtotime($doc['fecha_carga'])); ?></p>
                                </div>
                            </div>
                            <p class="historial-description" style="margin-bottom: 15px; color: #555;">
                                <?php echo htmlspecialchars($doc['descripcion']); ?>
                            </p>
                            <!-- See File -->
                            <a href="<?php echo htmlspecialchars($doc['archivo']); ?>" target="_blank" class="qr-link">
                                Ver Archivo <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.8rem; margin-left: 5px;"></i>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #777;">No hay documentos cargados en el sistema.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Load file button -->
    <button class="crg-btn" id="crgBtn" title="Cargar Archivo">
        <i class="fa-solid fa-plus" style="font-size: 1.8rem; color: white;"></i>
    </button>

    <!-- Upload file modal -->
    <div class="modal-overlay" id="chargeModal">
        <div class="modal-card">
            <h2 class="modal-title">Cargar Documento</h2>
            
            <!-- multi-file upload -->
            <form id="chargeForm" action="documentos.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column;">
                
                <label for="studyName">Título del Documento:</label>
                <input type="text" id="studyName" name="studyName" required style="margin-bottom: 15px; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">

                <label for="studyDesc">Descripción:</label>
                <textarea id="studyDesc" name="studyDesc" rows="3" required style="margin-bottom: 15px; padding: 8px; border-radius: 4px; border: 1px solid #ccc; resize: vertical;"></textarea>

                <label for="studyFile">Archivo (PDF, JPG, PNG):</label>
                <input type="file" id="studyFile" name="studyFile" accept=".pdf,.jpg,.png,.jpeg" required style="margin-bottom: 25px;">

                <button type="submit" style="padding: 12px; background-color: var(--secondary-color); color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                    Subir y Guardar
                </button>
            </form>
        </div>
    </div>

    <!-- Side menu popup -->
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

    <script src="js/menu.js"></script>
    
    <!-- Script open file modal -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const crgBtn = document.getElementById('crgBtn');
            const chargeModal = document.getElementById('chargeModal');

            // Open
            crgBtn.addEventListener('click', () => {
                chargeModal.classList.add('active');
            });

            // Close click outside
            chargeModal.addEventListener('click', (e) => {
                if (e.target === chargeModal) {
                    chargeModal.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>