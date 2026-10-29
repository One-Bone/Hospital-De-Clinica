<?php
session_start();
require_once 'php/conexion.php';

if (!isset($_SESSION['id'])) {
    header("Location: php/login.php");
    exit;
}

$ambulancias = $enlace->query("SELECT idambulancia, matricula, modelo, marca FROM ambulancia ORDER BY matricula");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modo conductor - Hospital de Clínicas</title>
    <style>
        :root{
            --primary-color:#2a2a2b;
            --secondary-color:#3a4dbf;
            --background-color:#eafffe;
            --main-color:#ffffff;
            --ok:#1f9d55;
            --error:#d63030;
            --grey-color:#747272;
        }
        *{margin:0;padding:0;box-sizing:border-box}
        [hidden]{display:none !important}
        body{
            font-family:Arial,sans-serif;
            background:var(--background-color);
            color:var(--primary-color);
            min-height:100vh;
            min-height:100dvh;
            display:flex;
            flex-direction:column;
        }
        header{
            background:var(--primary-color);
            color:var(--main-color);
            padding:1rem 1.25rem;
            font-weight:bold;
        }
        main{
            flex:1;
            width:100%;
            max-width:480px;
            margin:0 auto;
            padding:1.5rem 1.25rem 2rem;
            display:flex;
            flex-direction:column;
            gap:1.5rem;
        }
        label{display:block;font-weight:bold;margin-bottom:.4rem}
        select{
            width:100%;
            height:52px;
            border-radius:14px;
            border:2px solid var(--primary-color);
            background:var(--main-color);
            font-size:1.05rem;
            padding:0 1rem;
        }
        select:disabled{opacity:.6}
        .estado{
            background:var(--main-color);
            border-radius:18px;
            padding:1.25rem;
            box-shadow:0 4px 14px rgba(0,0,0,.1);
            display:flex;
            flex-direction:column;
            gap:.5rem;
        }
        .estado-titulo{
            display:flex;
            align-items:center;
            gap:.6rem;
            font-size:1.25rem;
            font-weight:bold;
        }
        .punto{
            width:14px;height:14px;border-radius:50%;
            background:var(--grey-color);
            flex-shrink:0;
        }
        .punto.activo{background:var(--ok);animation:pulso 1.4s ease-out infinite}
        .punto.error{background:var(--error)}
        @keyframes pulso{
            0%{box-shadow:0 0 0 0 rgba(31,157,85,.55)}
            100%{box-shadow:0 0 0 14px rgba(31,157,85,0)}
        }
        @media (prefers-reduced-motion:reduce){.punto.activo{animation:none}}
        .detalle{font-size:.95rem;color:var(--grey-color);line-height:1.5}
        .mensaje-error{color:var(--error);font-weight:bold;font-size:.95rem}
        #btnToggle{
            width:100%;
            height:72px;
            border:none;
            border-radius:20px;
            background:var(--secondary-color);
            color:var(--main-color);
            font-size:1.3rem;
            font-weight:bold;
            cursor:pointer;
        }
        #btnToggle:active{background:#303061}
        #btnToggle.detener{background:var(--error)}
        #btnToggle:focus-visible,select:focus-visible{outline:3px solid var(--primary-color);outline-offset:3px}
        .aviso{font-size:.85rem;color:var(--grey-color);line-height:1.5}
        .volver{color:var(--secondary-color);font-weight:bold;text-decoration:none;text-align:center}

        /* Gestión de ambulancias */
        .gestion{
            background:var(--main-color);
            border-radius:18px;
            padding:1rem 1.25rem;
            box-shadow:0 4px 14px rgba(0,0,0,.1);
            display:flex;
            flex-direction:column;
            gap:.75rem;
        }
        .gestion-titulo{font-weight:bold;font-size:1rem}
        .acciones-amb{display:flex;gap:.5rem;flex-wrap:wrap}
        .btn-chico{
            flex:1;min-width:140px;height:44px;border:none;border-radius:12px;
            background:var(--secondary-color);color:var(--main-color);
            font-weight:bold;font-size:.95rem;cursor:pointer;
        }
        .btn-chico:active{background:#303061}
        .btn-chico.peligro{background:var(--error)}
        .btn-chico.peligro:active{background:#a82424}
        .btn-chico.gris{background:var(--grey-color)}
        .btn-chico:disabled{opacity:.5;cursor:default}
        .form-amb{display:flex;flex-direction:column;gap:.5rem}
        .form-amb input{
            height:44px;border-radius:12px;border:2px solid var(--primary-color);
            padding:0 .8rem;font-size:1rem;background:var(--main-color);
        }
        .form-amb .acciones-amb{margin-top:.25rem}
        .mensaje-gestion{font-size:.9rem;color:var(--grey-color);min-height:1.2em}
    </style>
</head>
<body>
    <header>Modo conductor</header>

    <main>
        <div>
            <label for="selAmbulancia">Vehículo</label>
            <select id="selAmbulancia">
                <?php if ($ambulancias && $ambulancias->num_rows > 0): ?>
                    <?php while ($a = $ambulancias->fetch_assoc()): ?>
                        <option value="<?php echo (int)$a['idambulancia']; ?>">
                            <?php echo htmlspecialchars($a['matricula'] . ' - ' . $a['marca'] . ' ' . $a['modelo']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="">No hay ambulancias registradas</option>
                <?php endif; ?>
            </select>
        </div>

        <section class="estado" aria-live="polite">
            <div class="estado-titulo">
                <span class="punto" id="punto"></span>
                <span id="estadoTexto">Seguimiento detenido</span>
            </div>
            <p class="detalle" id="detalle">Elegí tu vehículo y tocá “Iniciar seguimiento”.</p>
            <p class="mensaje-error" id="error" role="alert"></p>
        </section>

        <button id="btnToggle" type="button">Iniciar seguimiento</button>

        <section class="gestion">
            <span class="gestion-titulo">Gestionar ambulancias</span>

            <div class="acciones-amb">
                <button type="button" id="btnNueva" class="btn-chico">+ Nueva ambulancia</button>
                <button type="button" id="btnEliminar" class="btn-chico peligro">Eliminar seleccionada</button>
            </div>

            <form id="formNueva" class="form-amb" hidden>
                <label for="nMatricula">Matrícula</label>
                <input id="nMatricula" maxlength="50" required placeholder="Ej: 11 - 33 22">

                <label for="nMarca">Marca</label>
                <input id="nMarca" maxlength="50" placeholder="Ej: Mercedes">

                <label for="nModelo">Modelo</label>
                <input id="nModelo" maxlength="50" placeholder="Ej: Sprinter">

                <div class="acciones-amb">
                    <button type="submit" class="btn-chico">Guardar ambulancia</button>
                    <button type="button" id="btnCancelarNueva" class="btn-chico gris">Cancelar</button>
                </div>
            </form>

            <p class="mensaje-gestion" id="mensajeGestion" aria-live="polite"></p>
        </section>

        <p class="aviso">
            Mantené esta pantalla abierta y encendida mientras conducís. Si cambiás de app o se
            bloquea el teléfono, el navegador puede dejar de enviar la ubicación.
        </p>

        <a class="volver" href="viajes.php">Ver mapa de viajes</a>
    </main>

    <script>
    (function () {
        const INTERVALO_MS = 5000; // un punto cada 5 segundos
        const btn = document.getElementById('btnToggle');
        const sel = document.getElementById('selAmbulancia');
        const punto = document.getElementById('punto');
        const estadoTexto = document.getElementById('estadoTexto');
        const detalle = document.getElementById('detalle');
        const errorEl = document.getElementById('error');

        const btnNueva = document.getElementById('btnNueva');
        const btnEliminar = document.getElementById('btnEliminar');
        const formNueva = document.getElementById('formNueva');
        const btnCancelarNueva = document.getElementById('btnCancelarNueva');
        const mensajeGestion = document.getElementById('mensajeGestion');

        let timer = null;
        let ocupado = false;
        let enviados = 0;
        let wakeLock = null;

        if (!sel.value) btn.disabled = true;
        if (!sel.value) btnEliminar.disabled = true;

        function mostrarError(msg) {
            errorEl.textContent = msg || '';
            punto.classList.toggle('error', !!msg);
        }

        async function pedirPantallaEncendida() {
            try {
                if ('wakeLock' in navigator) wakeLock = await navigator.wakeLock.request('screen');
            } catch (e) { /* no es crítico */ }
        }
        document.addEventListener('visibilitychange', () => {
            if (timer && document.visibilityState === 'visible') pedirPantallaEncendida();
        });

        function obtenerPosicion() {
            return new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    maximumAge: 0,
                    timeout: 8000
                });
            });
        }

        async function enviarPunto() {
            if (ocupado) return;
            ocupado = true;
            try {
                const pos = await obtenerPosicion();
                const c = pos.coords;
                const r = await fetch('php/guardar_ubicacion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        idambulancia: parseInt(sel.value, 10),
                        lat: c.latitude,
                        lng: c.longitude,
                        precision: c.accuracy,
                        velocidad: c.speed
                    })
                });
                if (r.status === 401) throw new Error('Tu sesión expiró. Volvé a iniciar sesión.');
                const data = await r.json();
                if (!data.ok) throw new Error((data.error || 'No se pudo guardar el punto') + (data.detalle ? ' (' + data.detalle + ')' : ''));

                enviados++;
                mostrarError('');
                const hora = new Date().toLocaleTimeString('es-UY');
                detalle.textContent = `Puntos enviados: ${enviados} · Último: ${hora} · Precisión: ±${Math.round(c.accuracy)} m`;
            } catch (e) {
                if (e && e.code === 1) {
                    mostrarError('Permiso de ubicación denegado. Activalo en los ajustes del navegador.');
                    detener();
                } else if (e && e.code === 2) {
                    mostrarError('No se pudo obtener la ubicación. Revisá que el GPS esté activado.');
                } else if (e && e.code === 3) {
                    mostrarError('El GPS tardó demasiado en responder. Reintentando...');
                } else if (e instanceof TypeError) {
                    mostrarError('Sin conexión con el servidor. Reintentando...');
                } else {
                    mostrarError(e.message || 'Error desconocido');
                }
            } finally {
                ocupado = false;
            }
        }

        function iniciar() {
            if (!('geolocation' in navigator)) {
                mostrarError('Este navegador no soporta geolocalización.');
                return;
            }
            if (!window.isSecureContext) {
                mostrarError('La ubicación solo funciona con HTTPS (o en localhost). Abrí el sitio con https://');
                return;
            }
            enviados = 0;
            mostrarError('');
            sel.disabled = true;
            btn.textContent = 'Detener seguimiento';
            btn.classList.add('detener');
            punto.classList.add('activo');
            estadoTexto.textContent = 'Enviando ubicación';
            detalle.textContent = 'Esperando el primer punto GPS...';
            pedirPantallaEncendida();
            enviarPunto();
            timer = setInterval(enviarPunto, INTERVALO_MS);
        }

        function detener() {
            clearInterval(timer);
            timer = null;
            if (wakeLock) { wakeLock.release().catch(() => {}); wakeLock = null; }
            sel.disabled = false;
            btn.textContent = 'Iniciar seguimiento';
            btn.classList.remove('detener');
            punto.classList.remove('activo');
            estadoTexto.textContent = 'Seguimiento detenido';
        }

        btn.addEventListener('click', () => timer ? detener() : iniciar());

        // ---------- Alta y baja de ambulancias ----------
        async function llamarAmbulancias(datos) {
            const r = await fetch('php/ambulancias.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });
            if (r.status === 401) throw new Error('Tu sesión expiró. Volvé a iniciar sesión.');
            const data = await r.json().catch(() => ({ ok: false, error: 'Respuesta inválida del servidor' }));
            if (!data.ok) throw new Error(data.error || 'No se pudo completar la acción');
            return data;
        }

        btnNueva.addEventListener('click', () => {
            formNueva.hidden = !formNueva.hidden;
            mensajeGestion.textContent = '';
        });

        btnCancelarNueva.addEventListener('click', () => {
            formNueva.reset();
            formNueva.hidden = true;
        });

        formNueva.addEventListener('submit', async e => {
            e.preventDefault();
            const boton = formNueva.querySelector('button[type="submit"]');
            boton.disabled = true;
            try {
                await llamarAmbulancias({
                    accion: 'crear',
                    matricula: document.getElementById('nMatricula').value,
                    marca: document.getElementById('nMarca').value,
                    modelo: document.getElementById('nModelo').value
                });
                location.reload(); // la lista se vuelve a cargar desde el servidor
            } catch (err) {
                mensajeGestion.textContent = err.message;
                boton.disabled = false;
            }
        });

        btnEliminar.addEventListener('click', async () => {
            const id = parseInt(sel.value, 10);
            if (!id) return;

            if (timer) {
                mensajeGestion.textContent = 'Detené el seguimiento antes de eliminar una ambulancia.';
                return;
            }

            const nombre = sel.options[sel.selectedIndex].text.trim();
            const ok = confirm(
                `¿Eliminar la ambulancia ${nombre}?\n\n` +
                `Se borra también su historial de ubicaciones. Esta acción no se puede deshacer.`
            );
            if (!ok) return;

            try {
                await llamarAmbulancias({ accion: 'eliminar', idambulancia: id });
                location.reload();
            } catch (err) {
                mensajeGestion.textContent = err.message;
            }
        });
    })();
    </script>
</body>
</html>
