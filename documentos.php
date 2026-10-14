<?php
session_start();

// Load the shared page translations in English.
$translationsJson = file_get_contents(__DIR__ . '/json/documentos.json');
if ($translationsJson === false) {
    throw new RuntimeException('Unable to read json/documentos.json.');
}

$translationsByLanguage = json_decode($translationsJson, true, 512, JSON_THROW_ON_ERROR);
if (!isset($translationsByLanguage['en']) || !is_array($translationsByLanguage['en'])) {
    throw new RuntimeException('English translations are missing from json/documentos.json.');
}
$translations = $translationsByLanguage['en'];

// BD CONNECT
require_once 'php/conexion.php';



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
        $error_msg = $translations['upload_error'];
    }
}

// Reading
// All files from DB order by timestamp
$consulta_docs = "SELECT * FROM documentacion ORDER BY fecha_carga DESC";
$resultado_docs = mysqli_query($enlace, $consulta_docs);

// Deleting doc
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_doc_id'])) {
    $id_eliminar = (int)$_POST['delete_doc_id'];
    
    // Searching file path in BD
    $consulta_archivo = "SELECT archivo FROM documentacion WHERE iddocumento = $id_eliminar";
    $resultado_archivo = mysqli_query($enlace, $consulta_archivo);
    
    if ($fila = mysqli_fetch_assoc($resultado_archivo)) {
        $ruta_archivo = $fila['archivo'];
        // If file exist, delete
        if (file_exists($ruta_archivo)) {
            unlink($ruta_archivo); 
        }
    }
    
    // Delete from DB
    $delete_sql = "DELETE FROM documentacion WHERE iddocumento = $id_eliminar";
    mysqli_query($enlace, $delete_sql);
    
    // Reload page
    header("Location: documentos.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($translations['page_title'], ENT_QUOTES, 'UTF-8'); ?></title>
    
    <link rel="preload" href="css/style-a.css" as="style">
    <link rel="preload" href="css/doc.css?v=<?php echo time(); ?>" as="style">
    
    <link rel="stylesheet" href="css/style-a.css">
    <link rel="stylesheet" href="css/doc.css?v=<?php echo time(); ?>">

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
                    <b><a class="user-name"><span><?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . ($_SESSION['apellido'] ?? '')); ?></span></a></b>
                <?php else: ?>
                    <a href="php/login.php"><span><?php echo htmlspecialchars($translations['sign_in'], ENT_QUOTES, 'UTF-8'); ?></span></a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Menu -->
        <div class="menu" id="menu-index">
            <a href="index.php"><?php echo htmlspecialchars($translations['home'], ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="documentos.php"><?php echo htmlspecialchars($translations['documents'], ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="personas.php"><?php echo htmlspecialchars($translations['people'], ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="encuestas.php"><?php echo htmlspecialchars($translations['surveys'], ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="viajes.php"><?php echo htmlspecialchars($translations['trips'], ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-body">
        <div class="main-content" id="main-content-historial">
            <h1 class="titulo-historial"><?php echo htmlspecialchars($translations['section_title'], ENT_QUOTES, 'UTF-8'); ?></h1>

            <?php if (isset($error_msg)): ?>
                <p style="color: red; font-weight: bold; text-align: center;"><?php echo htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8'); ?></p>
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
                                <div class="side-right-hs" style="display: flex; align-items: center; gap: 15px;">
                                    <!-- Format Date and Hour -->
                                    <p class="historial-date"><?php echo date("d/m/Y H:i", strtotime($doc['fecha_carga'])); ?></p>
                                    
                                    <!-- Botón de Basurero. Usamos data-id para guardar el ID oculto -->
                                    <button class="btn-delete-doc" data-id="<?php echo $doc['iddocumento']; ?>" title="<?php echo htmlspecialchars($translations['delete_document'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <i class="fa-solid fa-trash" style="color: #dc3545; font-size: 1.2rem;"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="historial-description" style="margin-bottom: 15px; color: #555;">
                                <?php echo htmlspecialchars($doc['descripcion']); ?>
                            </p>
                            <!-- See File -->
                            <a href="<?php echo htmlspecialchars($doc['archivo']); ?>" target="_blank" class="qr-link">
                                <?php echo htmlspecialchars($translations['view_file'], ENT_QUOTES, 'UTF-8'); ?> <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.8rem; margin-left: 5px;"></i>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #777;"><?php echo htmlspecialchars($translations['empty_documents'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </main>

<!-- Load file button-->
    <button class="crg-btn" id="crgBtn" title="<?php echo htmlspecialchars($translations['upload_file'], ENT_QUOTES, 'UTF-8'); ?>">
        <i class="fa-solid fa-plus" style="font-size: 1.8rem; color: white;"></i>
    </button>

    <!-- Upload file modal -->
    <div class="modal-overlay" id="chargeModal">
        <div class="modal-card">
            <h2 class="modal-title"><?php echo htmlspecialchars($translations['upload_document'], ENT_QUOTES, 'UTF-8'); ?></h2>
            
            <!-- multi-file upload -->
            <form id="chargeForm" action="documentos.php" method="POST" enctype="multipart/form-data" class="form-carga">
                
                <label for="studyName"><?php echo htmlspecialchars($translations['document_title'], ENT_QUOTES, 'UTF-8'); ?></label>
                <input type="text" id="studyName" name="studyName" required class="input-carga">

                <label for="studyDesc"><?php echo htmlspecialchars($translations['description_label'], ENT_QUOTES, 'UTF-8'); ?></label>
                <textarea id="studyDesc" name="studyDesc" rows="3" required class="input-carga"></textarea>

                <label for="studyFile"><?php echo htmlspecialchars($translations['file_formats'], ENT_QUOTES, 'UTF-8'); ?></label>
                <input type="file" id="studyFile" name="studyFile" accept=".pdf,.jpg,.png,.jpeg" required class="input-carga" style="border: none; padding-left: 0;">

                <button type="submit" class="btn-submit-carga">
                    <?php echo htmlspecialchars($translations['upload_submit'], ENT_QUOTES, 'UTF-8'); ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Side menu popup -->
    <div class="floating-menu-container">
        <div class="side-popup" id="sidePopup">
            <ul>
                <li class="MiniBtn"><a href="#"><?php echo htmlspecialchars($translations['edit_profile'], ENT_QUOTES, 'UTF-8'); ?></a></li>
                <li class="MiniBtn"><a href="#"><?php echo htmlspecialchars($translations['settings'], ENT_QUOTES, 'UTF-8'); ?></a></li>
                <li id="preferencesItem" class="MiniBtn"><a href="#" id="btnOpenPreferences"><?php echo htmlspecialchars($translations['preferences'], ENT_QUOTES, 'UTF-8'); ?></a></li>
                <li class="MiniBtn"><a href="php/logout.php"><?php echo htmlspecialchars($translations['sign_out'], ENT_QUOTES, 'UTF-8'); ?></a></li>
            </ul>
        </div>
        <!-- Floating Action Button -->
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

    <script src="js/menu.js?v=<?php echo time(); ?>"></script>
    <script src="js/doc.js?v=<?php echo time(); ?>"></script>

    <!-- Modal Confirmar Eliminación -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-card" style="height: auto; min-height: 200px; padding: 2.5rem;">
            <h2 class="modal-title" style="margin-bottom: 1rem; color: #dc3545;"><?php echo htmlspecialchars($translations['confirm_deletion'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <p style="text-align: center; margin-bottom: 2rem; color: #555;"><?php echo htmlspecialchars($translations['confirm_deletion_message'], ENT_QUOTES, 'UTF-8'); ?></p>
            
            <form id="deleteForm" action="documentos.php" method="POST" style="display: flex; justify-content: space-around;">
                <!-- Este input oculto guarda el ID del documento que vamos a borrar -->
                <input type="hidden" name="delete_doc_id" id="delete_doc_id_input" value="">
                
                <button type="button" id="btnCancelDelete" style="padding: 10px 25px; border-radius: 8px; border: 1px solid #ccc; background: #fff; cursor: pointer; font-weight: bold;"><?php echo htmlspecialchars($translations['cancel'], ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="submit" style="padding: 10px 25px; border-radius: 8px; border: none; background: #dc3545; color: white; font-weight: bold; cursor: pointer;"><?php echo htmlspecialchars($translations['confirm_delete'], ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
        </div>
    </div>
</body>
</html>