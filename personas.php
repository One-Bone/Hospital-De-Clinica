<?php
session_start();

// BD connect
require_once 'php/conexion.php'; 


if (!$enlace) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

// Verify log session
if (!isset($_SESSION['id'])) {
    header("Location: php/login.php");
    exit;
}

$error_msg = "";
$error_translation_key = "";

// Create new person
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'crear') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $cedula_identidad = (int)$_POST['cedula_identidad'];
    $tipo_rol = $_POST['tipo_rol'];
    
    // Secure Validation
    $email = ($tipo_rol === 'funcionario' && !empty($_POST['email'])) ? trim($_POST['email']) : NULL;
    $pass_hashed = ($tipo_rol === 'funcionario' && !empty($_POST['pass'])) ? hash('sha256', $_POST['pass']) : NULL;

    $sql_user = "INSERT INTO usuario (nombre, apellido, cedula_identidad, email, pass) VALUES (?, ?, ?, ?, ?)";
    $stmt_user = mysqli_prepare($enlace, $sql_user);
    mysqli_stmt_bind_param($stmt_user, "ssiss", $nombre, $apellido, $cedula_identidad, $email, $pass_hashed);
    
    if (mysqli_stmt_execute($stmt_user)) {
        $id_usuario_nuevo = mysqli_insert_id($enlace);
        $insercion_exitosa = true;

        if ($tipo_rol === 'paciente') {
            $tel_contacto = isset($_POST['tel_contacto']) ? trim($_POST['tel_contacto']) : NULL;
            $nro_hospital = isset($_POST['nro_hospital']) ? trim($_POST['nro_hospital']) : NULL;

            $sql_pac = "INSERT INTO paciente (id_usuario, tel_contacto, nro_hospital) VALUES (?, ?, ?)";
            $stmt_pac = mysqli_prepare($enlace, $sql_pac);
            mysqli_stmt_bind_param($stmt_pac, "iss", $id_usuario_nuevo, $tel_contacto, $nro_hospital);
            
            if (!mysqli_stmt_execute($stmt_pac)) {
                $insercion_exitosa = false;
                $error_msg = "Error DB: No se pudo guardar el paciente.";
                $error_translation_key = "db_patient_error";
            }

        } else if ($tipo_rol === 'funcionario') {
            $cargo    = isset($_POST['cargo']) ? trim($_POST['cargo']) : NULL;
            $legajo   = isset($_POST['legajo']) ? trim($_POST['legajo']) : NULL;
            $contacto = isset($_POST['contacto_func']) ? trim($_POST['contacto_func']) : NULL;

            $sql_func = "INSERT INTO funcionario (id_usuario, cargo, legajo, contacto) VALUES (?, ?, ?, ?)";
            $stmt_func = mysqli_prepare($enlace, $sql_func);
            mysqli_stmt_bind_param($stmt_func, "isss", $id_usuario_nuevo, $cargo, $legajo, $contacto);
            
            if (!mysqli_stmt_execute($stmt_func)) {
                $insercion_exitosa = false;
                $error_msg = "Error DB: No se pudo guardar el funcionario.";
                $error_translation_key = "db_staff_error";
            }
        }

        if ($insercion_exitosa) {
            header("Location: personas.php");
            exit;
        } else {
            // Security Rollback 
            mysqli_query($enlace, "DELETE FROM usuario WHERE id_usuario = $id_usuario_nuevo");
        }
    } else {
        $error_msg = "Error al registrar: Es posible que la cédula ya exista.";
        $error_translation_key = "duplicate_create_error";
    }
}

// Delete Person
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'eliminar') {
    $id_eliminar = (int)$_POST['delete_persona_id'];

    // To not Broke FK
    // Erase from secondary, then from main table
    mysqli_query($enlace, "DELETE FROM paciente WHERE id_usuario = $id_eliminar");
    mysqli_query($enlace, "DELETE FROM funcionario WHERE id_usuario = $id_eliminar");
    mysqli_query($enlace, "DELETE FROM usuario WHERE id_usuario = $id_eliminar");

    header("Location: personas.php");
    exit;
}

// Modify Person
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar') {
    $id_modificar = (int)$_POST['edit_persona_id'];
    $nombre = trim($_POST['edit_nombre']);
    $apellido = trim($_POST['edit_apellido']);
    $cedula_identidad = (int)$_POST['edit_cedula_identidad'];
    $tipo_rol = $_POST['edit_tipo_rol'];

    $email = ($tipo_rol === 'funcionario' && !empty($_POST['edit_email'])) ? trim($_POST['edit_email']) : NULL;
    
    if (empty($_POST['edit_pass'])) {
        $sql_user = "UPDATE usuario SET nombre = ?, apellido = ?, cedula_identidad = ?, email = ? WHERE id_usuario = ?";
        $stmt_user = mysqli_prepare($enlace, $sql_user);
        mysqli_stmt_bind_param($stmt_user, "ssisi", $nombre, $apellido, $cedula_identidad, $email, $id_modificar);
    } else {
        $pass_hashed = hash('sha256', $_POST['edit_pass']);
        $sql_user = "UPDATE usuario SET nombre = ?, apellido = ?, cedula_identidad = ?, email = ?, pass = ? WHERE id_usuario = ?";
        $stmt_user = mysqli_prepare($enlace, $sql_user);
        mysqli_stmt_bind_param($stmt_user, "ssissi", $nombre, $apellido, $cedula_identidad, $email, $pass_hashed, $id_modificar);
    }

    if (mysqli_stmt_execute($stmt_user)){
        mysqli_query($enlace, "DELETE FROM paciente WHERE id_usuario = $id_modificar");
        mysqli_query($enlace, "DELETE FROM funcionario WHERE id_usuario = $id_modificar");

        if ($tipo_rol === 'paciente') {
            $tel_contacto = isset($_POST['edit_tel_contacto']) ? trim($_POST['edit_tel_contacto']) : NULL;
            $nro_hospital = isset($_POST['edit_nro_hospital']) ? trim($_POST['edit_nro_hospital']) : NULL;

            $sql_pac = "INSERT INTO paciente (id_usuario, tel_contacto, nro_hospital) VALUES (?, ?, ?)";
            $stmt_pac = mysqli_prepare($enlace, $sql_pac);
            mysqli_stmt_bind_param($stmt_pac, "iss", $id_modificar, $tel_contacto, $nro_hospital);
            mysqli_stmt_execute($stmt_pac);

        }else if($tipo_rol === 'funcionario'){
            $cargo    = isset($_POST['edit_cargo']) ? trim($_POST['edit_cargo']) : NULL;
            $legajo   = isset($_POST['edit_legajo']) ? trim($_POST['edit_legajo']) : NULL;
            $contacto = isset($_POST['edit_contacto_func']) ? trim($_POST['edit_contacto_func']) : NULL;

            $sql_func = "INSERT INTO funcionario (id_usuario, cargo, legajo, contacto) VALUES (?, ?, ?, ?)";
            $stmt_func = mysqli_prepare($enlace, $sql_func);
            mysqli_stmt_bind_param($stmt_func, "isss", $id_modificar, $cargo, $legajo, $contacto);
            mysqli_stmt_execute($stmt_func);

        }else{
            $error_msg = "Error al modificar: Rol no válido.";
            $error_translation_key = "invalid_role_error";
        }
    }else{
        $error_msg = "Error al modificar: Es posible que la cédula ya exista.";
        $error_translation_key = "duplicate_edit_error";
    }
}

// Searching method
// Capture user input
$search = isset($_GET['search']) ? mysqli_real_escape_string($enlace, trim($_GET['search'])) : '';
$filtro_rol = isset($_GET['rol']) ? $_GET['rol'] : 'todos';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'recientes';

// Cross data consultation
$consulta_personas = "
    SELECT 
        u.id_usuario, u.nombre, u.apellido, u.cedula_identidad, u.email,
        p.idpaciente, p.tel_contacto, p.nro_hospital,
        f.idfuncionario, f.cargo, f.legajo, f.contacto AS contacto_func
    FROM usuario u
    LEFT JOIN paciente p ON u.id_usuario = p.id_usuario
    LEFT JOIN funcionario f ON u.id_usuario = f.id_usuario
    WHERE 1=1
";

// Apply Search filter
if (!empty($search)) {
    $consulta_personas .= " AND (u.nombre LIKE '%$search%' OR u.apellido LIKE '%$search%' OR u.cedula_identidad LIKE '%$search%')";
}

// Apply role filter
if ($filtro_rol === 'pacientes') {
    $consulta_personas .= " AND p.idpaciente IS NOT NULL";
} elseif ($filtro_rol === 'funcionarios') {
    $consulta_personas .= " AND f.idfuncionario IS NOT NULL";
}

// Apply order filter
if ($orden === 'az') {
    $consulta_personas .= " ORDER BY u.nombre ASC";
} elseif ($orden === 'za') {
    $consulta_personas .= " ORDER BY u.nombre DESC";
} else {
    $consulta_personas .= " ORDER BY u.id_usuario DESC"; // Por defecto: más recientes
}

// Execute dinamic consultation
$resultado_personas = mysqli_query($enlace, $consulta_personas);
?>
<!DOCTYPE html>
<html lang="es" data-i18n-file="json/personas.json">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="page_title">Hospital de Clínica Montevideo - Gestión de Personas</title>
    
    <link rel="stylesheet" href="css/style-a.css">
    <link rel="stylesheet" href="css/persona.css?v=<?php echo time(); ?>">
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
                    <a href="php/login.php"><span data-i18n="sign_in">Iniciar Sesion</span></a>
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
        <div class="main-content" id="main-content-personas">
            <h1 class="titulo-personas" data-i18n="directory">Directorio de Personas</h1>

            <?php if (!empty($error_msg)): ?>
                <p style="color: red; font-weight: bold; text-align: center; margin-bottom: 1rem;" data-i18n="<?php echo htmlspecialchars($error_translation_key, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <!-- Control Bar-->
            <div class="control-bar">
                <form method="GET" action="personas.php" class="form-controls">
                    
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" placeholder="Buscar CI, Nombre..." data-i18n-placeholder="search_placeholder" value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="select-box">
                        <label data-i18n="role">Rol:</label>
                        <select name="rol" onchange="this.form.submit()">
                            <option value="todos" <?php if($filtro_rol=='todos') echo 'selected'; ?> data-i18n="all">Todos</option>
                            <option value="pacientes" <?php if($filtro_rol=='pacientes') echo 'selected'; ?> data-i18n="patients">Pacientes</option>
                            <option value="funcionarios" <?php if($filtro_rol=='funcionarios') echo 'selected'; ?> data-i18n="staff">Funcionarios</option>
                        </select>
                    </div>

                    <div class="select-box">
                        <label data-i18n="sort">Ordenar:</label>
                        <select name="orden" onchange="this.form.submit()">
                            <option value="recientes" <?php if($orden=='recientes') echo 'selected'; ?> data-i18n="most_recent">Más Recientes</option>
                            <option value="az" <?php if($orden=='az') echo 'selected'; ?> data-i18n="name_ascending">Nombre (A-Z)</option>
                            <option value="za" <?php if($orden=='za') echo 'selected'; ?> data-i18n="name_descending">Nombre (Z-A)</option>
                        </select>
                    </div>

                </form>
            </div>

            <!-- People List -->
            <div class="personas-section">
                <?php if (mysqli_num_rows($resultado_personas) > 0): ?>
                    <?php while ($persona = mysqli_fetch_assoc($resultado_personas)): ?>
                        <?php 
                            // Dynamic people render
                            if (!empty($persona['idpaciente'])) {
                                $rol_badge = '<span data-i18n="patient">Paciente</span>';
                                $badge_class = "badge-paciente";
                                $detalle_rol = '<span data-i18n="medical_record">Nº Historial:</span> ' . htmlspecialchars($persona['nro_hospital']) . ' | <span data-i18n="phone_short">Tel:</span> ' . htmlspecialchars($persona['tel_contacto']);
                            } else {
                                $cargo = $persona['cargo'] ?? 'Funcionario';
                                $cargo_translations = [
                                    'Medico' => 'doctor',
                                    'Chofer' => 'driver',
                                    'Enfermero' => 'nurse',
                                    'Admin' => 'administrator',
                                    'Funcionario' => 'staff_member',
                                ];
                                $cargo_key = $cargo_translations[$cargo] ?? null;
                                $rol_badge = $cargo_key
                                    ? '<span data-i18n="' . $cargo_key . '">' . htmlspecialchars($cargo, ENT_QUOTES, 'UTF-8') . '</span>'
                                    : htmlspecialchars($cargo, ENT_QUOTES, 'UTF-8');
                                $badge_class = "badge-funcionario";
                                $detalle_rol = '<span data-i18n="employee_number">Legajo:</span> ' . htmlspecialchars($persona['legajo']) . ' | <span data-i18n="contact">Contacto:</span> ' . htmlspecialchars($persona['contacto_func']);
                            }
                        ?>
                        <div class="persona-card">
                            <div class="persona-card-header">
                                <div class="side-left-persona">
                                    <h2 class="persona-title">
                                        <i class="fa-solid fa-user" style="margin-right: 8px; color: var(--secondary-color);"></i>
                                        <?php echo htmlspecialchars($persona['nombre'] . ' ' . $persona['apellido']); ?>
                                    </h2>
                                    <span class="persona-badge <?php echo $badge_class; ?>">
                                        <?php echo $rol_badge; ?>
                                    </span>
                                </div>
                                <div class="side-right-persona">
                                    <button class="btn-action btn-edit-persona" 
                                        data-id="<?php echo $persona['id_usuario']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($persona['nombre']); ?>" 
                                        data-apellido="<?php echo htmlspecialchars($persona['apellido']); ?>"
                                        data-cedula="<?php echo htmlspecialchars($persona['cedula_identidad']); ?>"
                                        data-email="<?php echo htmlspecialchars($persona['email'] ?? ''); ?>"
                                        data-rol="<?php echo !empty($persona['idpaciente']) ? 'paciente' : 'funcionario'; ?>"
                                        data-tel="<?php echo htmlspecialchars($persona['tel_contacto'] ?? ''); ?>"
                                        data-nrohospital="<?php echo htmlspecialchars($persona['nro_hospital'] ?? ''); ?>"
                                        data-cargo="<?php echo htmlspecialchars($persona['cargo'] ?? ''); ?>"
                                        data-legajo="<?php echo htmlspecialchars($persona['legajo'] ?? ''); ?>"
                                        data-contactofunc="<?php echo htmlspecialchars($persona['contacto_func'] ?? ''); ?>"
                                    data-i18n-title="edit_person" title="Editar Persona">
                                    <i class="fa-solid fa-edit" style="color: #007bff; font-size: 1.2rem;"></i>
                                    </button>
                                    <button class="btn-action btn-delete-persona" 
                                        data-id="<?php echo $persona['id_usuario']; ?>" data-i18n-title="delete_person" title="Eliminar Persona">
                                        <i class="fa-solid fa-trash" style="color: #dc3545; font-size: 1.2rem;"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="persona-info-body">
                                <p><strong data-i18n="id_short">CI:</strong> <?php echo htmlspecialchars($persona['cedula_identidad']); ?> 
                                <?php if($persona['email']) echo '| <strong data-i18n="email">Email:</strong> ' . htmlspecialchars($persona['email']); ?></p>
                                <p class="persona-detalle-rol"><?php echo $detalle_rol; ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #777;" data-i18n="no_people">No se encontraron personas con esos filtros.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Register person btn -->
    <button class="crg-btn-persona" id="btnCargarPersona" data-i18n-title="register_person" title="Registrar Persona">
        <i class="fa-solid fa-user-plus" style="font-size: 1.5rem; color: white;"></i>
    </button>

    <!-- Edit person modal -->
    <div class="modal-overlay" id="editPersonaModal">
        <div class="modal-card modal-persona-card">
            <h2 class="modal-title" data-i18n="edit_person_title">Editar Persona</h2>
            
            <form id="formEditarPersona" action="personas.php" method="POST" class="form-persona" novalidate>
                <input type="hidden" id="edit_persona_id" name="edit_persona_id" value="">
                <input type="hidden" name="action" value="editar">

                <div class="form-row-double">
                    <div>
                        <label for="nombre" data-i18n="name">Nombre:</label>
                        <input type="text" id="edit_nombre" name="edit_nombre" required class="input-persona">
                    </div>
                    <div>
                        <label for="apellido" data-i18n="surname">Apellido:</label>
                        <input type="text" id="edit_apellido" name="edit_apellido" required class="input-persona">
                    </div>
                </div>

                <div class="form-row-double">
                    <div>
                        <label for="cedula_identidad" data-i18n="identity_number">Cédula de Identidad:</label>
                        <input type="number" id="edit_cedula_identidad" name="edit_cedula_identidad" required class="input-persona">
                    </div>
                    <div>
                        <label for="edit_tipo_rol" data-i18n="person_type">Es un:</label>
                        <select id="edit_tipo_rol" name="edit_tipo_rol" required class="input-persona select-persona">
                            <option value="" disabled selected data-i18n="select_option">Seleccione...</option>
                            <option value="paciente" data-i18n="patient">Paciente</option>
                            <option value="funcionario" data-i18n="hospital_staff">Funcionario del Hospital</option>
                        </select>
                    </div>
                </div>

                <!-- Dynamic patient fields -->
                <div id="edit_campos_paciente" class="campos-rol-dinamicos" style="display: none;">
                    <div class="form-row-double">
                        <div>
                            <label for="tel_contacto" data-i18n="phone_contact">Teléfono de Contacto:</label>
                            <input type="text" id="edit_tel_contacto" name="edit_tel_contacto" class="input-persona">
                        </div>
                        <div>
                            <label for="nro_hospital" data-i18n="hospital_number">Nº de Hospital / Registro:</label>
                            <input type="text" id="edit_nro_hospital" name="edit_nro_hospital" class="input-persona">
                        </div>
                    </div>
                </div>

                <!-- Dynamic employee fields -->
                <div id="edit_campos_funcionario" class="campos-rol-dinamicos" style="display: none;">
                    <p style="color:#d9534f; font-size:0.85rem; margin-bottom:10px;" data-i18n="staff_access_note">* Los funcionarios requieren acceso web, asigne correo y clave.</p>
                    <div class="form-row-double">
                        <div>
                            <label for="email" data-i18n="email">Correo Electrónico:</label>
                            <input type="email" id="edit_email" name="edit_email" class="input-persona">
                        </div>
                        <div>
                            <label for="pass" data-i18n="password_no_change">Contraseña (Vacio para no cambiar):</label>
                            <input type="password" id="edit_pass" name="edit_pass" class="input-persona">
                        </div>
                    </div>
                    <label for="cargo" data-i18n="job_title">Cargo / Función:</label>
                    <select id="edit_cargo" name="edit_cargo" class="input-persona select-persona">
                        <option value="Medico" data-i18n="doctor">Médico</option>
                        <option value="Chofer" data-i18n="driver">Chofer / Conductor</option>
                        <option value="Enfermero" data-i18n="nurse">Enfermero/a</option>
                        <option value="Admin" data-i18n="administrator">Administrador</option>
                    </select>
                    <div class="form-row-double">
                        <div>
                            <label for="legajo" data-i18n="employee_number_label">Número de Legajo:</label>
                            <input type="text" id="edit_legajo" name="edit_legajo" class="input-persona">
                        </div>
                        <div>
                            <label for="contacto_func" data-i18n="work_contact">Contacto Laboral:</label>
                            <input type="text" id="edit_contacto_func" name="edit_contacto_func" class="input-persona">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit-persona" data-i18n="save_person">Guardar Persona</button>
            </form>
        </div>
    </div>

    <!-- Register new -->
    <div class="modal-overlay" id="modalAltaPersona">
        <div class="modal-card modal-persona-card">
            <h2 class="modal-title" data-i18n="register_new_person">Registrar Nueva Persona</h2>
            
            <form id="formAltaPersona" action="personas.php" method="POST" class="form-persona" novalida>
                <input type="hidden" name="action" value="crear">

                <div class="form-row-double">
                    <div>
                        <label for="nombre" data-i18n="name">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" required class="input-persona">
                    </div>
                    <div>
                        <label for="apellido" data-i18n="surname">Apellido:</label>
                        <input type="text" id="apellido" name="apellido" required class="input-persona">
                    </div>
                </div>

                <div class="form-row-double">
                    <div>
                        <label for="cedula_identidad" data-i18n="identity_number">Cédula de Identidad:</label>
                        <input type="number" id="cedula_identidad" name="cedula_identidad" required class="input-persona">
                    </div>
                    <div>
                        <label for="tipo_rol" data-i18n="person_type">Es un:</label>
                        <select id="tipo_rol" name="tipo_rol" required class="input-persona select-persona">
                            <option value="" disabled selected data-i18n="select_option">Seleccione...</option>
                            <option value="paciente" data-i18n="patient">Paciente</option>
                            <option value="funcionario" data-i18n="hospital_staff">Funcionario del Hospital</option>
                        </select>
                    </div>
                </div>

                <!-- Dynamic patient fields -->
                <div id="campos_paciente" class="campos-rol-dinamicos" style="display: none;">
                    <div class="form-row-double">
                        <div>
                            <label for="tel_contacto" data-i18n="phone_contact">Teléfono de Contacto:</label>
                            <input type="text" id="tel_contacto" name="tel_contacto" class="input-persona">
                        </div>
                        <div>
                            <label for="nro_hospital" data-i18n="hospital_number">Nº de Hospital / Registro:</label>
                            <input type="text" id="nro_hospital" name="nro_hospital" class="input-persona">
                        </div>
                    </div>
                </div>

                <!-- Dynamic employee fields -->
                <div id="campos_funcionario" class="campos-rol-dinamicos" style="display: none;">
                    <p style="color:#d9534f; font-size:0.85rem; margin-bottom:10px;" data-i18n="staff_access_note">* Los funcionarios requieren acceso web, asigne correo y clave.</p>
                    <div class="form-row-double">
                        <div>
                            <label for="email" data-i18n="email">Correo Electrónico:</label>
                            <input type="email" id="email" name="email" class="input-persona">
                        </div>
                        <div>
                            <label for="pass" data-i18n="password">Contraseña:</label>
                            <input type="password" id="pass" name="pass" class="input-persona">
                        </div>
                    </div>
                    <label for="cargo" data-i18n="job_title">Cargo / Función:</label>
                    <select id="cargo" name="cargo" class="input-persona select-persona">
                        <option value="Medico" data-i18n="doctor">Médico</option>
                        <option value="Chofer" data-i18n="driver">Chofer / Conductor</option>
                        <option value="Enfermero" data-i18n="nurse">Enfermero/a</option>
                        <option value="Admin" data-i18n="administrator">Administrador</option>
                    </select>
                    <div class="form-row-double">
                        <div>
                            <label for="legajo" data-i18n="employee_number_label">Número de Legajo:</label>
                            <input type="text" id="legajo" name="legajo" class="input-persona">
                        </div>
                        <div>
                            <label for="contacto_func" data-i18n="work_contact">Contacto Laboral:</label>
                            <input type="text" id="contacto_func" name="contacto_func" class="input-persona">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit-persona" data-i18n="save_person">Guardar Persona</button>
            </form>
        </div>
    </div>

    <!-- Delete Person Modal -->
    <div class="modal-overlay" id="deletePersonaModal">
        <div class="modal-card" style="height: auto; min-height: 200px; padding: 2.5rem;">
            <h2 class="modal-title" style="margin-bottom: 1rem; color: #dc3545;" data-i18n="confirm_deletion">Confirmar Eliminación</h2>
            <p style="text-align: center; margin-bottom: 2rem; color: #555;" data-i18n="delete_person_message">¿Estás seguro de que deseas eliminar esta persona? Esta acción no se puede deshacer.</p>
            <form id="deletePersonaForm" action="personas.php" method="POST" style="display: flex; justify-content: space-around;">
                <input type="hidden" name="action" value="eliminar">
                <input type="hidden" name="delete_persona_id" id="delete_persona_id_input" value="">
                
                <button type="button" id="btnCancelDeletePersona" style="padding: 10px 25px; border-radius: 8px; border: 1px solid #ccc; background: #fff; cursor: pointer; font-weight: bold;" data-i18n="cancel">Cancelar</button>
                <button type="submit" style="padding: 10px 25px; border-radius: 8px; border: none; background: #dc3545; color: white; font-weight: bold; cursor: pointer;" data-i18n="confirm_delete">Sí, Eliminar</button>
            </form>
        </div>
    </div>

    <!-- Side menu popup -->
    <div class="floating-menu-container">
        <div class="side-popup" id="sidePopup">
            <ul>
                <li class="MiniBtn"><a href="#" data-i18n="edit_profile">Editar Perfil</a></li>
                <li class="MiniBtn"><a href="#" data-i18n="settings">Configuración</a></li>
                <li id="preferencesItem" class="MiniBtn"><a href="#" id="btnOpenPreferences" data-i18n="preferences">Preferencias</a></li>
                <li class="MiniBtn"><a href="php/logout.php" data-i18n="sign_out">Cerrar sesión</a></li>
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

    <script src="js/persona.js?v=<?php echo time(); ?>"></script>
    <script src="js/menu.js?v=<?php echo time(); ?>"></script>
    <script src="js/idioma.js?v=<?php echo time(); ?>"></script>
</body>
</html>