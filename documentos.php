<?php
session_start();
require_once 'php/conexion.php';

// Verificamos sesión
if (!isset($_SESSION['id'])) {
    header("Location: php/login.php");
    exit;
}

// Subida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['studyFile'])) {
    $titulo = $_POST['studyName'];
    $descripcion = $_POST['studyDesc'];
    $directorio_destino = 'uploads/';
    
    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0777, true);
    }

    $nombre_archivo = basename($_FILES["studyFile"]["name"]);
    $nombre_archivo_unico = time() . '_' . $nombre_archivo; 
    $ruta_final_archivo = $directorio_destino . $nombre_archivo_unico;

    if (move_uploaded_file($_FILES["studyFile"]["tmp_name"], $ruta_final_archivo)) {
        
        $insertar = "INSERT INTO documentacion (titulo, descripcion, archivo) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($enlace, $insertar);
        mysqli_stmt_bind_param($stmt, "sss", $titulo, $descripcion, $ruta_final_archivo);
        mysqli_stmt_execute($stmt);
        
        $id_doc_nuevo = mysqli_insert_id($enlace);

        $host = ($_SERVER['HTTP_HOST'] === 'localhost') ? gethostbyname(gethostname()) : $_SERVER['HTTP_HOST'];
        $protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        
        $directorio_base = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
        $url_descarga = sprintf("%s://%s%s/%s", $protocolo, $host, $directorio_base, $ruta_final_archivo);
        
        $api_url = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=" . urlencode($url_descarga);
        $qr_image_data = @file_get_contents($api_url);
        
        if ($qr_image_data !== false) {
            $nombre_qr = "qr_" . time() . "_doc" . $id_doc_nuevo . ".png";
            $ruta_qr = $directorio_destino . $nombre_qr;

            file_put_contents($ruta_qr, $qr_image_data);
            
            $insert_qr = "INSERT INTO qr (iddocumento, codigo_enlace) VALUES (?, ?)";
            $stmt_qr = mysqli_prepare($enlace, $insert_qr);
            mysqli_stmt_bind_param($stmt_qr, "is", $id_doc_nuevo, $ruta_qr);
            mysqli_stmt_execute($stmt_qr);
        }

        header("Location: documentos.php");
        exit;
    } else {
        $error_upload = true;
    }
}

// LECTURA DE ARCHIVOS
$consulta_docs = "SELECT d.*, q.codigo_enlace 
                FROM documentacion d 
                LEFT JOIN qr q ON d.iddocumento = q.iddocumento 
                ORDER BY d.fecha_carga DESC";
$resultado_docs = mysqli_query($enlace, $consulta_docs);

// ELIMINACIÓN DE ARCHIVOS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_doc_id'])) {
    $id_eliminar = (int)$_POST['delete_doc_id'];
    
    $consulta_archivo = "SELECT archivo FROM documentacion WHERE iddocumento = $id_eliminar";
    $resultado_archivo = mysqli_query($enlace, $consulta_archivo);
    if ($fila = mysqli_fetch_assoc($resultado_archivo)) {
        if (file_exists($fila['archivo'])) unlink($fila['archivo']); 
    }

    $consulta_qr = "SELECT codigo_enlace FROM qr WHERE iddocumento = $id_eliminar";
    $resultado_qr = mysqli_query($enlace, $consulta_qr);
    if ($fila_qr = mysqli_fetch_assoc($resultado_qr)) {
        if (file_exists($fila_qr['codigo_enlace'])) unlink($fila_qr['codigo_enlace']);
    }
    
    mysqli_query($enlace, "DELETE FROM qr WHERE iddocumento = $id_eliminar");
    mysqli_query($enlace, "DELETE FROM documentacion WHERE iddocumento = $id_eliminar");
    
    header("Location: documentos.php");
    exit;
}
?>
<!DOCTYPE html>

<html lang="es" data-i18n-file="json/documentos.json">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="page_title">Hospital de Clinica Montevideo - Documentación</title>
    
    <link rel="preload" href="css/style-a.css" as="style">
    <link rel="preload" href="css/doc.css?v=<?php echo time(); ?>" as="style">
    
    <link rel="stylesheet" href="css/style-a.css">
    <link rel="stylesheet" href="css/doc.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
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
                    <a href="php/login.php"><span data-i18n="sign_in">Iniciar sesión</span></a>
                <?php endif; ?>
            </div>
        </div>

        <div class="menu" id="menu-index">
            <a href="index.php" data-i18n="home">Inicio</a>
            <a href="documentos.php" data-i18n="documents">Documentos</a>
            <a href="personas.php" data-i18n="people">Personas</a>
            <a href="encuestas.php" data-i18n="surveys">Encuestas</a>
            <a href="viajes.php" data-i18n="trips">Viajes</a>
        </div>
    </header>

    <main class="main-body">
        <div class="main-content" id="main-content-historial">
            <h1 class="titulo-historial" data-i18n="section_title">Documentación</h1>

            <?php if (isset($error_upload)): ?>
                <p style="color: red; font-weight: bold; text-align: center;" data-i18n="upload_error">Error al subir.</p>
            <?php endif; ?>

            <div class="Historial section">
                <?php if (mysqli_num_rows($resultado_docs) > 0): ?>
                    <?php while ($doc = mysqli_fetch_assoc($resultado_docs)): ?>
                        <div class="historial-card">
                            <div class="historial-card-header">
                                <div class="side-left-hs">
                                    <h2 class="historial-title"><?php echo htmlspecialchars($doc['titulo']); ?></h2>
                                </div>
                                
                                <div class="side-right-hs" style="display: flex; align-items: center; gap: 15px;">
                                    <p class="historial-date"><?php echo date("d/m/Y H:i", strtotime($doc['fecha_carga'])); ?></p>
                                    
                                    <button class="btn-delete-doc" data-id="<?php echo $doc['iddocumento']; ?>" data-i18n-title="delete_document" title="Eliminar documento">
                                        <i class="fa-solid fa-trash" style="color: #dc3545; font-size: 1.2rem;"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="historial-description" style="margin-bottom: 15px; color: #555;">
                                <?php echo htmlspecialchars($doc['descripcion']); ?>
                            </p>
                                                
                            <div style="display: flex; justify-content: space-between; align-items: center;">

                                <a href="<?php echo htmlspecialchars($doc['archivo']); ?>" target="_blank" class="qr-link" style="text-decoration:none; padding:8px 15px; background-color:var(--secondary-color); color:white; border-radius:6px; font-weight:bold;">
                                    <span data-i18n="view_file">Ver archivo</span> <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.8rem; margin-left: 5px;"></i>
                                </a>

                                <?php if (!empty($doc['codigo_enlace'])): ?>
                                <a href="imprimir_qr.php?qr=<?php echo urlencode($doc['codigo_enlace']); ?>&titulo=<?php echo urlencode($doc['titulo']); ?>" target="_blank" style="color: var(--primary-color); font-size: 2rem; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'" title="Abrir e Imprimir QR">
                                    <i class="fa-solid fa-qrcode"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                            
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #777;" data-i18n="empty_documents">No hay documentos cargados en el sistema.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <button class="crg-btn" id="crgBtn" data-i18n-title="upload_file" title="Cargar archivo">
        <i class="fa-solid fa-plus" style="font-size: 1.8rem; color: white;"></i>
    </button>

    <div class="modal-overlay" id="chargeModal">
        <div class="modal-card">
            <h2 class="modal-title" data-i18n="upload_document">Cargar documento</h2>
            
            <form id="chargeForm" action="documentos.php" method="POST" enctype="multipart/form-data" class="form-carga">
                <label for="studyName" data-i18n="document_title">Título del documento:</label>
                <input type="text" id="studyName" name="studyName" required class="input-carga">

                <label for="studyDesc" data-i18n="description_label">Descripción:</label>
                <textarea id="studyDesc" name="studyDesc" rows="3" required class="input-carga"></textarea>

                <label for="studyFile" data-i18n="file_formats">Archivo (PDF, JPG, PNG):</label>
                <input type="file" id="studyFile" name="studyFile" accept=".pdf,.jpg,.png,.jpeg" required class="input-carga" style="border: none; padding-left: 0;">

                <button type="submit" class="btn-submit-carga" data-i18n="upload_submit">Subir y guardar</button>
            </form>
        </div>
    </div>

    <!-- Menú lateral -->
    <div class="floating-menu-container">
        <div class="side-popup" id="sidePopup">
            <ul>
                
                
                <li id="preferencesItem" class="MiniBtn"><a href="#" id="btnOpenPreferences" data-i18n="preferences">Preferencias</a></li>
                <li class="MiniBtn"><a href="php/logout.php" data-i18n="sign_out">Cerrar sesión</a></li>
            </ul>
        </div>
        <button class="fab-btn" id="fabBtn">
            <i class="fa-solid fa-bars" id="fabIcon"></i>
        </button>
    </div>

    <!-- Modal de Preferencias -->
    <div class="modal-overlay" id="preferencesModal">
        <div class="modal-card">
            <h2 class="modal-title" data-i18n="preferences">Preferencias</h2>

            <div class="pref-row">
                <span class="pref-label" data-i18n="dark_mode">Modo Oscuro</span>
                <label class="toggle-switch">
                    <input type="checkbox" id="darkModeToggle">
                    <span class="slider"></span>
                </label>
            </div>

            <div class="pref-row">
                <span class="pref-label" data-i18n="language">Idioma</span>
                <div class="custom-dropdown" id="dropdownLanguage">
                    <button class="dropdown-btn" id="dropdownBtn">Español</button>
                    <div class="dropdown-content">
                        <div class="dropdown-item active" data-lang="es">Español</div>
                        <div class="dropdown-item" data-lang="en">English</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="deleteModal">
        <div class="modal-card" style="height: auto; min-height: 200px; padding: 2.5rem;">
            <h2 class="modal-title" style="margin-bottom: 1rem; color: #dc3545;" data-i18n="confirm_deletion">Confirmar eliminación</h2>
            <p style="text-align: center; margin-bottom: 2rem; color: #555;" data-i18n="confirm_deletion_message">¿Estás seguro de que deseas eliminar este documento? Se borrará permanentemente de la base de datos y del servidor.</p>
            
            <form id="deleteForm" action="documentos.php" method="POST" style="display: flex; justify-content: space-around;">
                <input type="hidden" name="delete_doc_id" id="delete_doc_id_input" value="">
                
                <button type="button" id="btnCancelDelete" style="padding: 10px 25px; border-radius: 8px; border: 1px solid #ccc; background: #fff; cursor: pointer; font-weight: bold;" data-i18n="cancel">Cancelar</button>
                <button type="submit" style="padding: 10px 25px; border-radius: 8px; border: none; background: #dc3545; color: white; font-weight: bold; cursor: pointer;" data-i18n="confirm_delete">Sí, eliminar</button>
            </form>
        </div>
    </div>

    <script src="js/menu.js?v=<?php echo time(); ?>"></script>
    <script src="js/doc.js?v=<?php echo time(); ?>"></script>
    <script src="js/idioma.js?v=<?php echo time(); ?>"></script>
</body>
</html>