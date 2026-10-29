// ===================================================================
// Trazabilidad de traslados
// - Formulario con autocompletado y selección tocando el mapa (Geoapify)
// - Rutas dibujadas en el mapa, con opción de eliminarlas
// - Al pasar el mouse sobre una ambulancia: conductor, paciente, insumos y ETA
// Requiere viajes.js cargado antes (usa `map` y `liveAmbulances`).
// ===================================================================
(function () {
    const ACTUALIZAR_TRASLADOS_MS = 15000;
    const ACTUALIZAR_ETA_MS = 60000;

    const modal = document.getElementById('trasladoModal');
    const form = document.getElementById('formSolicitarTraslado');
    const banner = document.getElementById('bannerMapa');
    const textoBanner = document.getElementById('textoBannerMapa');
    const selAmbulancia = document.getElementById('selAmbulanciaTraslado');
    const resumenRuta = document.getElementById('resumenRuta');

    const campos = {
        origen: {
            input: document.getElementById('direccionSalida'),
            lista: document.getElementById('sugSalida'),
            etiqueta: 'la SALIDA', lat: null, lon: null, timer: null
        },
        destino: {
            input: document.getElementById('direccionLlegada'),
            lista: document.getElementById('sugLlegada'),
            etiqueta: 'la LLEGADA', lat: null, lon: null, timer: null
        }
    };

    const sugerencias = { origen: new Map(), destino: new Map() };
    const traslados = new Map();  // idtraslado -> datos + capas de Leaflet
    const ubicaciones = {};       // idambulancia -> última posición recibida
    const eta = {};               // idtraslado -> segundos restantes
    let modoMapa = null;          // 'origen' | 'destino' | null
    let ultimaRuta = null;        // ruta calculada para el formulario
    let capaPreview = null;
    let versionRuta = 0;
    let rutaPromesa = null;       // ruta en curso (para que Enviar pueda esperarla)
    let ultimoError = null;       // error de la última ruta, si falló

    // ---------- utilidades ----------
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const fmtHora = d => d.toLocaleTimeString('es-UY', { hour: '2-digit', minute: '2-digit' });
    const fmtDuracion = s => s < 60 ? `${Math.round(s)} s` : `${Math.round(s / 60)} min`;
    const alerta = msg => window.alert(msg);

    async function api(url, opciones = {}) {
        const r = await fetch(url, { cache: 'no-store', ...opciones });
        const data = await r.json().catch(() => ({ ok: false, error: 'Respuesta inválida del servidor' }));
        if (!r.ok || !data.ok) throw new Error(data.error || 'Error de conexión');
        return data;
    }
    const geo = params => api('php/geo.php?' + new URLSearchParams(params));
    const postJSON = (url, cuerpo) => api(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(cuerpo)
    });

    // Distancia en línea recta (aprox.) en metros
    function distanciaM(lat1, lon1, lat2, lon2) {
        const R = 6371000, rad = x => x * Math.PI / 180;
        const dLat = rad(lat2 - lat1), dLon = rad(lon2 - lon1);
        const a = Math.sin(dLat / 2) ** 2 +
                  Math.cos(rad(lat1)) * Math.cos(rad(lat2)) * Math.sin(dLon / 2) ** 2;
        return 2 * R * Math.asin(Math.sqrt(a));
    }

    // ---------- modal ----------
    const abrirModal = () => modal.classList.add('active');
    const cerrarModal = () => modal.classList.remove('active');
    document.getElementById('ambulanceBtn').addEventListener('click', abrirModal);
    modal.addEventListener('click', e => { if (e.target === modal) cerrarModal(); });

    // ---------- direcciones: autocompletado ----------
    function fijar(clave, lat, lon) {
        campos[clave].lat = lat;
        campos[clave].lon = lon;
        actualizarRuta();
    }

    Object.entries(campos).forEach(([clave, c]) => {
        c.input.addEventListener('input', () => {
            // Si eligió una sugerencia de la lista, ya tenemos sus coordenadas
            const elegida = sugerencias[clave].get(c.input.value);
            if (elegida) return fijar(clave, Number(elegida.lat), Number(elegida.lon));

            // Texto libre: hay que volver a elegir y buscar sugerencias
            fijar(clave, null, null);
            clearTimeout(c.timer);
            c.timer = setTimeout(() => buscarSugerencias(clave), 350);
        });
    });

    async function buscarSugerencias(clave) {
        const c = campos[clave];
        const texto = c.input.value.trim();
        if (texto.length < 3) return;
        try {
            const { resultados } = await geo({ accion: 'autocomplete', text: texto });
            sugerencias[clave].clear();
            c.lista.innerHTML = '';
            resultados.forEach(r => {
                sugerencias[clave].set(r.texto, r);
                const op = document.createElement('option');
                op.value = r.texto;
                c.lista.appendChild(op);
            });
        } catch (e) {
            // Si falla, la persona puede elegir tocando el mapa
        }
    }

    // ---------- selección tocando el mapa ----------
    document.querySelectorAll('.btn-mapa').forEach(b => b.addEventListener('click', () => {
        modoMapa = b.dataset.campo;
        textoBanner.textContent = `Tocá el mapa para elegir ${campos[modoMapa].etiqueta}`;
        banner.hidden = false;
        cerrarModal();
    }));

    function terminarSeleccion() {
        modoMapa = null;
        banner.hidden = true;
        abrirModal();
    }
    document.getElementById('btnCancelarMapa').addEventListener('click', terminarSeleccion);

    map.on('click', async e => {
        if (!modoMapa) return;
        const clave = modoMapa;
        const c = campos[clave];
        const { lat, lng } = e.latlng;

        terminarSeleccion();
        c.input.value = 'Buscando dirección...';
        fijar(clave, lat, lng);

        try {
            const { direccion } = await geo({ accion: 'reverse', lat, lon: lng });
            c.input.value = direccion;
        } catch (err) {
            c.input.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        }
    });

    // ---------- ruta de vista previa ----------
    function quitarPreview() {
        if (capaPreview) { map.removeLayer(capaPreview); capaPreview = null; }
    }

    function actualizarRuta() {
        const o = campos.origen, d = campos.destino;
        const version = ++versionRuta; // siempre, para descartar respuestas viejas
        quitarPreview();
        ultimaRuta = null;
        ultimoError = null;
        rutaPromesa = null;

        if (o.lat == null || d.lat == null) {
            resumenRuta.textContent = '';
            return;
        }

        resumenRuta.textContent = 'Calculando ruta...';
        rutaPromesa = geo({
            accion: 'ruta',
            origen: `${o.lat},${o.lon}`,
            destino: `${d.lat},${d.lon}`
        }).then(r => {
            if (version !== versionRuta) return;
            ultimaRuta = r;
            capaPreview = L.polyline(r.geometria, { color: '#3a4dbf', weight: 5, dashArray: '8 8' }).addTo(map);
            map.fitBounds(capaPreview.getBounds(), { padding: [40, 40] });
            resumenRuta.textContent =
                `Distancia: ${(r.distancia_m / 1000).toFixed(1)} km · Tiempo estimado: ${fmtDuracion(r.duracion_s)}`;
        }).catch(e => {
            if (version !== versionRuta) return;
            ultimoError = e.message;
            resumenRuta.textContent = 'No se pudo calcular la ruta: ' + e.message;
        });
    }

    // ---------- ambulancia más cercana (automática) ----------
    function ambulanciaMasCercana(o) {
        let mejor = null, mejorDist = Infinity;
        Object.entries(ubicaciones).forEach(([id, u]) => {
            const d = distanciaM(o.lat, o.lon, u.lat, u.lng);
            if (d < mejorDist) { mejorDist = d; mejor = Number(id); }
        });
        return mejor;
    }

    // ---------- enviar formulario ----------
    function limpiarFormulario() {
        Object.values(campos).forEach(c => {
            c.input.value = '';
            c.lat = c.lon = null;
            c.lista.innerHTML = '';
        });
        sugerencias.origen.clear();
        sugerencias.destino.clear();
        form.reset(); // restaura conductor y ambulancia por defecto
        quitarPreview();
        ultimaRuta = null;
        resumenRuta.textContent = '';
    }

    form.addEventListener('submit', async e => {
        e.preventDefault();
        const o = campos.origen, d = campos.destino;

        if (o.lat == null || d.lat == null) {
            return alerta('Elegí la salida y la llegada desde la lista o tocando el mapa.');
        }

        // Si la ruta todavía se está calculando, esperamos a que termine
        while (rutaPromesa) {
            const pendiente = rutaPromesa;
            await pendiente;
            if (pendiente === rutaPromesa) break;
        }

        if (!ultimaRuta) {
            return alerta(ultimoError
                ? 'No se pudo calcular la ruta: ' + ultimoError
                : 'No hay ruta para esas direcciones. Revisá las direcciones y probá de nuevo.');
        }

        const idAmbulancia = selAmbulancia.value === 'auto'
            ? ambulanciaMasCercana(o)
            : Number(selAmbulancia.value);
        if (!idAmbulancia) {
            return alerta('No hay ambulancias con ubicación activa. Elegí una manualmente.');
        }

        const boton = form.querySelector('.btn-submit-traslado');
        boton.disabled = true;
        try {
            await postJSON('php/traslados.php', {
                accion: 'crear',
                idambulancia: idAmbulancia,
                origen_texto: o.input.value,
                destino_texto: d.input.value,
                origen_lat: o.lat, origen_lng: o.lon,
                destino_lat: d.lat, destino_lng: d.lon,
                geometria: ultimaRuta.geometria,
                distancia_m: ultimaRuta.distancia_m,
                duracion_s: ultimaRuta.duracion_s,
                tipo_traslado: document.getElementById('tipoTraslado').value,
                conductor: document.getElementById('datosConductor').value,
                paciente: document.getElementById('datosPaciente').value,
                insumos: document.getElementById('listaInsumos').value,
                nivel_necesidad: document.getElementById('nivelNecesidad').value,
                hora_solicitada: document.getElementById('horaLlegada').value || null
            });
            limpiarFormulario();
            cerrarModal();
            await cargarTraslados();
        } catch (err) {
            alerta(err.message);
        } finally {
            boton.disabled = false;
        }
    });

    // ---------- traslados en el mapa ----------
    const iconoDestino = L.divIcon({
        html: '<i class="fa-solid fa-location-dot" style="color:#ff2a2a;font-size:28px"></i>',
        className: 'custom-map-icon',
        iconSize: [28, 28],
        iconAnchor: [14, 28]
    });

    function dibujarTraslado(t) {
        const ruta = L.polyline(t.geometria, { color: '#3a4dbf', weight: 5, opacity: 0.85 }).addTo(map);
        ruta.bindPopup(() => contenidoRuta(t.idtraslado), { maxWidth: 260 });

        const destino = L.marker([t.destino_lat, t.destino_lng], { icon: iconoDestino })
            .bindTooltip(`Destino: ${esc(t.destino)}`)
            .addTo(map);

        traslados.set(t.idtraslado, { ...t, capas: [ruta, destino] });
    }

    function contenidoRuta(id) {
        const t = traslados.get(id);
        if (!t) return '';
        return `<b>${esc(t.matricula)}</b> · ${esc(t.tipo_traslado || 'Traslado')}<br>
            <b>Desde:</b> ${esc(t.punto_origen)}<br>
            <b>Hasta:</b> ${esc(t.destino)}
            <div class="acciones-ruta">
                <button type="button" class="btn-finalizar-ruta" onclick="finalizarTraslado(${id})">Finalizar</button>
                <button type="button" class="btn-eliminar-ruta" onclick="eliminarTraslado(${id})">Eliminar ruta</button>
            </div>`;
    }

    window.eliminarTraslado = id =>
        accionTraslado('eliminar', id, '¿Eliminar esta ruta? Dejará de mostrarse en el mapa.');
    window.finalizarTraslado = id =>
        accionTraslado('finalizar', id, '¿Marcar el traslado como finalizado?');

    async function accionTraslado(accion, id, pregunta) {
        if (!window.confirm(pregunta)) return;
        try {
            await postJSON('php/traslados.php', { accion, idtraslado: id });
            const t = traslados.get(id);
            if (t) t.capas.forEach(c => map.removeLayer(c));
            traslados.delete(id);
            delete eta[id];
            map.closePopup();
            refrescarTooltipsAbiertos();
        } catch (e) {
            alerta(e.message);
        }
    }

    async function cargarTraslados() {
        try {
            const { traslados: lista } = await api('php/traslados.php');
            const vigentes = new Set(lista.map(t => t.idtraslado));

            // Quitar los que ya no están activos (eliminados por otra persona, finalizados, etc.)
            for (const [id, t] of traslados) {
                if (!vigentes.has(id)) {
                    t.capas.forEach(c => map.removeLayer(c));
                    traslados.delete(id);
                    delete eta[id];
                }
            }
            // Agregar nuevos o actualizar datos sin redibujar
            lista.forEach(t => {
                const previo = traslados.get(t.idtraslado);
                if (previo) Object.assign(previo, t);
                else dibujarTraslado(t);
            });
        } catch (e) {
            console.error('No se pudieron cargar los traslados:', e);
        }
    }

    // ---------- datos de la ambulancia al pasar el mouse ----------
    function trasladoActivoDe(idAmbulancia) {
        for (const t of traslados.values()) {
            if (t.idambulancia === Number(idAmbulancia)) return t;
        }
        return null;
    }

    window.tooltipAmbulancia = function (id) {
        const u = ubicaciones[id];
        if (!u) return '';

        const cabecera = `<b>${esc(u.matricula)}</b>`;
        const pie = `<small>Velocidad: ${u.velocidad != null ? Math.round(u.velocidad * 3.6) + ' km/h' : '—'}
                     · Última señal: ${u.segundos} s</small>`;

        const t = trasladoActivoDe(id);
        if (!t) return `${cabecera}<br>Sin traslado asignado<br>${pie}`;

        const seg = eta[t.idtraslado];
        const llegada = seg != null
            ? `${fmtDuracion(seg)} (≈ ${fmtHora(new Date(Date.now() + seg * 1000))})`
            : 'Calculando…';

        return `${cabecera}<br>
            <b>Conductor:</b> ${esc(t.conductor || '—')}<br>
            <b>Paciente:</b> ${esc(t.paciente || '—')}<br>
            <b>Insumos:</b> ${esc(t.insumos || '—')}<br>
            <b>Destino:</b> ${esc(t.destino)}<br>
            <b>Tiempo estimado de llegada:</b> ${llegada}<br>${pie}`;
    };

    function refrescarTooltipsAbiertos() {
        Object.entries(liveAmbulances).forEach(([id, amb]) => {
            if (amb.marker.isTooltipOpen()) amb.marker.setTooltipContent(window.tooltipAmbulancia(id));
        });
    }

    // viajes.js llama esto cada vez que llegan posiciones nuevas
    window.onUbicacionesActualizadas = function (lista) {
        Object.keys(ubicaciones).forEach(k => delete ubicaciones[k]);
        lista.forEach(a => {
            ubicaciones[a.id] = {
                matricula: a.matricula, lat: a.lat, lng: a.lng,
                velocidad: a.velocidad, segundos: a.segundos
            };
        });
        refrescarTooltipsAbiertos();
    };

    // ETA: ruta desde la posición actual de cada ambulancia hasta el destino
    async function actualizarEta() {
        await Promise.all([...traslados.values()].map(async t => {
            const u = ubicaciones[t.idambulancia];
            if (!u) return;
            try {
                const r = await geo({
                    accion: 'ruta',
                    origen: `${u.lat},${u.lng}`,
                    destino: `${t.destino_lat},${t.destino_lng}`
                });
                eta[t.idtraslado] = r.duracion_s;
            } catch (e) {
                // conserva el último valor conocido
            }
        }));
        refrescarTooltipsAbiertos();
    }

    // ---------- arranque ----------
    cargarTraslados().then(() => setTimeout(actualizarEta, 3000));
    setInterval(cargarTraslados, ACTUALIZAR_TRASLADOS_MS);
    setInterval(actualizarEta, ACTUALIZAR_ETA_MS);
})();
