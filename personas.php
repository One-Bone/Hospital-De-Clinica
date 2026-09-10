<?php
session_start();

// CONEXIÓN A BASE DE DATOS
$server = "localhost";
$usuario = "root";
$pass = "";
$bdatos = "hospital";
$enlace = mysqli_connect($server, $usuario, $pass, $bdatos);

if (!$enlace) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

// Verificar sesión activa
if (!isset($_SESSION['id'])) {
    header("Location: php/login.php");
    exit;
}

$error_msg = "";
$success_msg = "";

// -------------------------------------------------------------
// 1. ACCIÓN: CREAR PERSONA Y ASIGNAR ROL
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'crear') {
    $nombre           = trim($_POST['nombre']);
    $apellido         = trim($_POST['apellido']);
    $cedula_identidad = (int)$_POST['cedula_identidad'];
    $email            = trim($_POST['email']);
    $password_raw     = $_POST['pass'];
    $tipo_rol         = $_POST['tipo_rol']; // 'paciente' o 'funcionario'
    
    // Hash de contraseña (compatible con el esquema existente SHA-256)
    $pass_hashed = hash('sha256', $password_raw);

    // Insertar en la tabla usuario
    $sql_user = "INSERT INTO usuario (nombre, apellido, cedula_identidad, email, pass) VALUES (?, ?, ?, ?, ?)";
    $stmt_user = mysqli_prepare($enlace, $sql_user);
    mysqli_stmt_bind_param($stmt_user, "ssiss", $nombre, $apellido, $cedula_identidad, $email, $pass_hashed);
    
    if (mysqli_stmt_execute($stmt_user)) {
        $id_usuario_nuevo = mysqli_insert_id($enlace);

        // Según el rol, guardar en la tabla correspondiente
        if ($tipo_rol === 'paciente') {
            $tel_contacto = trim($_POST['tel_contacto']);
            $nro_hospital = trim($_POST['nro_hospital']);

            $sql_pac = "INSERT INTO paciente (id_usuario, tel_contacto, nro_hospital) VALUES (?, ?, ?)";
            $stmt_pac = mysqli_prepare($enlace, $sql_pac);
            mysqli_stmt_bind_param($stmt_pac, "iss", $id_usuario_nuevo, $tel_contacto, $nro_hospital);
            mysqli_stmt_execute($stmt_pac);

        } else if ($tipo_rol === 'funcionario') {
            $cargo    = trim($_POST['cargo']); // Médico, Chofer, Admin, Enfermero, etc.
            $legajo   = trim($_POST['legajo']);
            $contacto = trim($_POST['contacto_func']);

            $sql_func = "INSERT INTO funcionario (id_usuario, cargo, legajo, contacto) VALUES (?, ?, ?, ?)";
            $stmt_func = mysqli_prepare($enlace, $sql_func);
            mysqli_stmt_bind_param($stmt_func, "isss", $id_usuario_nuevo, $cargo, $legajo, $contacto);
            mysqli_stmt_execute($stmt_func);
        }

        header("Location: personas.php");
        exit;
    } else {
        $error_msg = "Error al registrar la persona: " . mysqli_error($enlace);
    }
}

// -------------------------------------------------------------
// 2. ACCIÓN: EDITAR PERSONA
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar') {
    $id_usuario       = (int)$_POST['edit_id_usuario'];
    $nombre           = trim($_POST['edit_nombre']);
    $apellido         = trim($_POST['edit_apellido']);
    $cedula_identidad = (int)$_POST['edit_cedula_identidad'];
    $email            = trim($_POST['edit_email']);
    $tipo_rol         = $_POST['edit_tipo_rol'];

    // Actualizar tabla usuario
    if (!empty($_POST['edit_pass'])) {
        $pass_hashed = hash('sha256', $_POST['edit_pass']);
        $sql_up_user = "UPDATE usuario SET nombre = ?, apellido = ?, cedula_identidad = ?, email = ?, pass = ? WHERE id_usuario = ?";
        $stmt_up_user = mysqli_prepare($enlace, $sql_up_user);
        mysqli_stmt_bind_param($stmt_up_user, "ssissi", $nombre, $apellido, $cedula_identidad, $email, $pass_hashed, $id_usuario);
    } else {
        $sql_up_user = "UPDATE usuario SET nombre = ?, apellido = ?, cedula_identidad = ?, email = ? WHERE id_usuario = ?";
        $stmt_up_user = mysqli_prepare($enlace, $sql_up_user);
        mysqli_stmt_bind_param($stmt_up_user, "ssisi", $nombre, $apellido, $cedula_identidad, $email, $id_usuario);
    }
    mysqli_stmt_execute($stmt_up_user);

    // Limpiar roles anteriores para evitar inconsistencias
    mysqli_query($enlace, "DELETE FROM paciente WHERE id_usuario = $id_usuario");
    mysqli_query($enlace, "DELETE FROM funcionario WHERE id_usuario = $id_usuario");

    // Re-insertar rol actualizado
    if ($tipo_rol === 'paciente') {
        $tel_contacto = trim($_POST['edit_tel_contacto']);
        $nro_hospital = trim($_POST['edit_nro_hospital']);

        $sql_pac = "INSERT INTO paciente (id_usuario, tel_contacto, nro_hospital) VALUES (?, ?, ?)";
        $stmt_pac = mysqli_prepare($enlace, $sql_pac);
        mysqli_stmt_bind_param($stmt_pac, "iss", $id_usuario, $tel_contacto, $nro_hospital);
        mysqli_stmt_execute($stmt_pac);

    } else if ($tipo_rol === 'funcionario') {
        $cargo    = trim($_POST['edit_cargo']);
        $legajo   = trim($_POST['edit_legajo']);
        $contacto = trim($_POST['edit_contacto_func']);

        $sql_func = "INSERT INTO funcionario (id_usuario, cargo, legajo, contacto) VALUES (?, ?, ?, ?)";
        $stmt_func = mysqli_prepare($enlace, $sql_func);
        mysqli_stmt_bind_param($stmt_func, "isss", $id_usuario, $cargo, $legajo, $contacto);
        mysqli_stmt_execute($stmt_func);
    }

    header("Location: personas.php");
    exit;
}

// -------------------------------------------------------------
// 3. ACCIÓN: ELIMINAR PERSONA
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'eliminar') {
    $id_eliminar = (int)$_POST['delete_persona_id'];

    // Eliminar primero dependencias de rol por clave foránea
    mysqli_query($enlace, "DELETE FROM paciente WHERE id_usuario = $id_eliminar");
    mysqli_query($enlace, "DELETE FROM funcionario WHERE id_usuario = $id_eliminar");
    
    // Eliminar registro principal
    mysqli_query($enlace, "DELETE FROM usuario WHERE id_usuario = $id_eliminar");

    header("Location: personas.php");
    exit;
}

// -------------------------------------------------------------
// 4. LECTURA DE DATOS (JOIN DE USUARIO CON PACIENTE Y FUNCIONARIO)
// -------------------------------------------------------------
$consulta_personas = "
    SELECT 
        u.id_usuario, 
        u.nombre, 
        u.apellido, 
        u.cedula_identidad, 
        u.email,
        p.idpaciente, 
        p.tel_contacto, 
        p.nro_hospital,
        f.idfuncionario, 
        f.cargo, 
        f.legajo, 
        f.contacto AS contacto_func
    FROM usuario u
    LEFT JOIN paciente p ON u.id_usuario = p.id_usuario
    LEFT JOIN funcionario f ON u.id_usuario = f.id_usuario
    ORDER BY u.id_usuario DESC
";
$resultado_personas = mysqli_query($enlace, $consulta_personas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital de Clínica Montevideo - Gestión de Personas</title>
    
    <link rel="preload" href="css/style-a.css" as="style">
    <link rel="preload" href="css/persona.css?v=<?php echo time(); ?>" as="style">
    
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
                    <b><a href="perfil.html" class="user-name"><span><?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . ($_SESSION['apellido'] ?? '')); ?></span></a></b>
                <?php else: ?>
                    <a href="php/login.php"><span>Iniciar Sesion</span></a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Menu principal -->
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
        <div class="main-content" id="main-content-personas">
            <h1 class="titulo-personas">Gestión de Personas y Roles</h1>

            <?php if (!empty($error_msg)): ?>
                <p style="color: red; font-weight: bold; text-align: center; margin-bottom: 1rem;"><?php echo $error_msg; ?></p>
            <?php endif; ?>

            <!-- Listado de Personas -->
            <div class="personas-section">
                <?php if (mysqli_num_rows($resultado_personas) > 0): ?>
                    <?php while ($persona = mysqli_fetch_assoc($resultado_personas)): ?>
                        <?php 
                            // Determinar rol principal y etiqueta
                            $rol_badge = "Sin Rol";
                            $badge_class = "badge-neutral";
                            $detalle_rol = "";

                            if (!empty($persona['idpaciente'])) {
                                $rol_badge = "Paciente";
                                $badge_class = "badge-paciente";
                                $detalle_rol = "Nº Hospital: " . htmlspecialchars($persona['nro_hospital']) . " | Tel: " . htmlspecialchars($persona['tel_contacto']);
                            } else if (!empty($persona['idfuncionario'])) {
                                $cargo = !empty($persona['cargo']) ? $persona['cargo'] : 'Funcionario';
                                $rol_badge = htmlspecialchars($cargo);
                                $badge_class = "badge-funcionario";
                                $detalle_rol = "Legajo: " . htmlspecialchars($persona['legajo']) . " | Contacto: " . htmlspecialchars($persona['contacto_func']);
                            }
                        ?>
                        <div class="persona-card">
                            <div class="persona-card-header">
                                <div class="side-left-persona">
                                    <h2 class="persona-title">
                                        <i class="fa-solid fa-user-gear" style="margin-right: 8px; color: var(--secondary-color);"></i>
                                        <?php echo htmlspecialchars($persona['nombre'] . ' ' . $persona['apellido']); ?>
                                    </h2>
                                    <span class="persona-badge <?php echo $badge_class; ?>">
                                        <?php echo $rol_badge; ?>
                                    </span>
                                </div>
                                <div class="side-right-persona">
                                    <!-- Botón Editar -->
                                    <button class="btn-action btn-edit-persona" 
                                            data-id="<?php echo $persona['id_usuario']; ?>"
                                            data-nombre="<?php echo htmlspecialchars($persona['nombre']); ?>"
                                            data-apellido="<?php echo htmlspecialchars($persona['apellido']); ?>"
                                            data-cedula="<?php echo htmlspecialchars($persona['cedula_identidad']); ?>"
                                            data-email="<?php echo htmlspecialchars($persona['email']); ?>"
                                            data-rol="<?php echo !empty($persona['idpaciente']) ? 'paciente' : (!empty($persona['idfuncionario']) ? 'funcionario' : 'ninguno'); ?>"
                                            data-tel="<?php echo htmlspecialchars($persona['tel_contacto'] ?? ''); ?>"
                                            data-nrohospital="<?php echo htmlspecialchars($persona['nro_hospital'] ?? ''); ?>"
                                            data-cargo="<?php echo htmlspecialchars($persona['cargo'] ?? ''); ?>"
                                            data-legajo="<?php echo htmlspecialchars($persona['legajo'] ?? ''); ?>"
                                            data-contactofunc="<?php echo htmlspecialchars($persona['contacto_func'] ?? ''); ?>"
                                            title="Editar Persona">
                                        <i class="fa-solid fa-pen-to-square" style="color: #3a4dbf; font-size: 1.2rem;"></i>
                                    </button>

                                    <!-- Botón Eliminar -->
                                    <button class="btn-action btn-delete-persona" data-id="<?php echo $persona['id_usuario']; ?>" title="Eliminar Persona">
                                        <i class="fa-solid fa-trash" style="color: #dc3545; font-size: 1.2rem;"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="persona-info-body">
                                <p><strong>CI:</strong> <?php echo htmlspecialchars($persona['cedula_identidad']); ?> | <strong>Email:</strong> <?php echo htmlspecialchars($persona['email']); ?></p>
                                <?php if (!empty($detalle_rol)): ?>
                                    <p class="persona-detalle-rol"><?php echo $detalle_rol; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #777;">No hay personas o usuarios registrados en el sistema.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Botón flotante para registrar nueva persona -->
    <button class="crg-btn-persona" id="btnCargarPersona" title="Registrar Persona">
        <i class="fa-solid fa-user-plus" style="font-size: 1.5rem; color: white;"></i>
    </button>

    <!-- Modal: Registrar Persona -->
    <div class="modal-overlay" id="modalAltaPersona">
        <div class="modal-card modal-persona-card">
            <h2 class="modal-title">Registrar Persona</h2>
            
            <form id="formAltaPersona" action="personas.php" method="POST" class="form-persona">
                <input type="hidden" name="action" value="crear">

                <div class="form-row-double">
                    <div>
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" required class="input-persona">
                    </div>
                    <div>
                        <label for="apellido">Apellido:</label>
                        <input type="text" id="apellido" name="apellido" required class="input-persona">
                    </div>
                </div>

                <div class="form-row-double">
                    <div>
                        <label for="cedula_identidad">Cédula de Identidad:</label>
                        <input type="number" id="cedula_identidad" name="cedula_identidad" required class="input-persona">
                    </div>
                    <div>
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" id="email" name="email" required class="input-persona">
                    </div>
                </div>

                <label for="pass">Contraseña Inicial:</label>
                <input type="password" id="pass" name="pass" required class="input-persona">

                <label for="tipo_rol">Rol / Tipo de Persona:</label>
                <select id="tipo_rol" name="tipo_rol" required class="input-persona select-persona">
                    <option value="" disabled selected>-- Seleccione un Rol --</option>
                    <option value="paciente">Paciente</option>
                    <option value="funcionario">Funcionario (Médico, Chofer, Admin, etc.)</option>
                </select>

                <!-- Campos dinámicos Paciente -->
                <div id="campos_paciente" class="campos-rol-dinamicos" style="display: none;">
                    <div class="form-row-double">
                        <div>
                            <label for="tel_contacto">Teléfono de Contacto:</label>
                            <input type="text" id="tel_contacto" name="tel_contacto" class="input-persona">
                        </div>
                        <div>
                            <label for="nro_hospital">Nº de Hospital / Registro:</label>
                            <input type="text" id="nro_hospital" name="nro_hospital" class="input-persona">
                        </div>
                    </div>
                </div>

                <!-- Campos dinámicos Funcionario -->
                <div id="campos_funcionario" class="campos-rol-dinamicos" style="display: none;">
                    <label for="cargo">Cargo / Función:</label>
                    <select id="cargo" name="cargo" class="input-persona select-persona">
                        <option value="Medico">Médico</option>
                        <option value="Chofer">Chofer / Conductor</option>
                        <option value="Enfermero">Enfermero/a</option>
                        <option value="Administrativo">Administrativo</option>
                        <option value="Admin">Administrador General</option>
                    </select>

                    <div class="form-row-double">
                        <div>
                            <label for="legajo">Número de Legajo:</label>
                            <input type="text" id="legajo" name="legajo" class="input-persona">
                        </div>
                        <div>
                            <label for="contacto_func">Contacto Laboral:</label>
                            <input type="text" id="contacto_func" name="contacto_func" class="input-persona">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit-persona">
                    Guardar Persona
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Editar Persona -->
    <div class="modal-overlay" id="modalEditarPersona">
        <div class="modal-card modal-persona-card">
            <h2 class="modal-title">Editar Persona</h2>
            
            <form id="formEditarPersona" action="personas.php" method="POST" class="form-persona">
                <input type="hidden" name="action" value="editar">
                <input type="hidden" name="edit_id_usuario" id="edit_id_usuario" value="">

                <div class="form-row-double">
                    <div>
                        <label for="edit_nombre">Nombre:</label>
                        <input type="text" id="edit_nombre" name="edit_nombre" required class="input-persona">
                    </div>
                    <div>
                        <label for="edit_apellido">Apellido:</label>
                        <input type="text" id="edit_apellido" name="edit_apellido" required class="input-persona">
                    </div>
                </div>

                <div class="form-row-double">
                    <div>
                        <label for="edit_cedula_identidad">Cédula de Identidad:</label>
                        <input type="number" id="edit_cedula_identidad" name="edit_cedula_identidad" required class="input-persona">
                    </div>
                    <div>
                        <label for="edit_email">Correo Electrónico:</label>
                        <input type="email" id="edit_email" name="edit_email" required class="input-persona">
                    </div>
                </div>

                <label for="edit_pass">Nueva Contraseña (dejar en blanco para conservar actual):</label>
                <input type="password" id="edit_pass" name="edit_pass" class="input-persona">

                <label for="edit_tipo_rol">Rol / Tipo de Persona:</label>
                <select id="edit_tipo_rol" name="edit_tipo_rol" required class="input-persona select-persona">
                    <option value="paciente">Paciente</option>
                    <option value="funcionario">Funcionario (Médico, Chofer, Admin, etc.)</option>
                </select>

                <!-- Campos dinámicos Paciente Editar -->
                <div id="edit_campos_paciente" class="campos-rol-dinamicos" style="display: none;">
                    <div class="form-row-double">
                        <div>
                            <label for="edit_tel_contacto">Teléfono de Contacto:</label>
                            <input type="text" id="edit_tel_contacto" name="edit_tel_contacto" class="input-persona">
                        </div>
                        <div>
                            <label for="edit_nro_hospital">Nº de Hospital:</label>
                            <input type="text" id="edit_nro_hospital" name="edit_nro_hospital" class="input-persona">
                        </div>
                    </div>
                </div>

                <!-- Campos dinámicos Funcionario Editar -->
                <div id="edit_campos_funcionario" class="campos-rol-dinamicos" style="display: none;">
                    <label for="edit_cargo">Cargo / Función:</label>
                    <select id="edit_cargo" name="edit_cargo" class="input-persona select-persona">
                        <option value="Medico">Médico</option>
                        <option value="Chofer">Chofer / Conductor</option>
                        <option value="Enfermero">Enfermero/a</option>
                        <option value="Administrativo">Administrativo</option>
                        <option value="Admin">Administrador General</option>
                    </select>

                    <div class="form-row-double">
                        <div>
                            <label for="edit_legajo">Número de Legajo:</label>
                            <input type="text" id="edit_legajo" name="edit_legajo" class="input-persona">
                        </div>
                        <div>
                            <label for="edit_contacto_func">Contacto Laboral:</label>
                            <input type="text" id="edit_contacto_func" name="edit_contacto_func" class="input-persona">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit-persona">
                    Actualizar Datos
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Confirmar Eliminación -->
    <div class="modal-overlay" id="deletePersonaModal">
        <div class="modal-card" style="height: auto; min-height: 200px; padding: 2.5rem;">
            <h2 class="modal-title" style="margin-bottom: 1rem; color: #dc3545;">Confirmar Eliminación</h2>
            <p style="text-align: center; margin-bottom: 2rem; color: #555;">¿Estás seguro de que deseas eliminar esta persona? Se borrarán sus datos de usuario y rol de la base de datos.</p>
            
            <form id="deletePersonaForm" action="personas.php" method="POST" style="display: flex; justify-content: space-around;">
                <input type="hidden" name="action" value="eliminar">
                <input type="hidden" name="delete_persona_id" id="delete_persona_id_input" value="">
                
                <button type="button" id="btnCancelDeletePersona" style="padding: 10px 25px; border-radius: 8px; border: 1px solid #ccc; background: #fff; cursor: pointer; font-weight: bold;">Cancelar</button>
                <button type="submit" style="padding: 10px 25px; border-radius: 8px; border: none; background: #dc3545; color: white; font-weight: bold; cursor: pointer;">Sí, Eliminar</button>
            </form>
        </div>
    </div>

    <!-- Menú lateral inferior -->
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
    <script src="js/persona.js?v=<?php echo time(); ?>"></script>
</body>
</html>