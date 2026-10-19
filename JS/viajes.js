const montevideoCords = [-34.9011, -56.1645];
const hospitalClinicasCoords = [-34.8883, -56.1541];
const emergenciaOrigenCoords = [-34.9058, -56.1913];
const popupText = (key, fallback) => {
    const translation = window.getSiteTranslation?.(key);
    return translation && translation !== key ? translation : fallback;
};

// Montevideo Inicio
const map = L.map('map', {
    zoomControl: false
}).setView(montevideoCords, 13);

// Open Street Map Layer
L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri'
}).addTo(map);

// Hospital Icon
const hospitalIcon = L.divIcon({
        html: '<i class="fa-solid fa-hospital" style="color: #3a4dbf; font-size: 24px;"></i>',
        className: 'custom-map-icon',
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });

// Marker Hospital
const hospitalMarker = L.marker(hospitalClinicasCoords, { icon: hospitalIcon })
        .addTo(map)
        .bindPopup('<b>Hospital de Clínicas</b><br>Montevideo, Uruguay')
        .openPopup();

// Buttons Zoom In/Out
document.getElementById('zoomInBtn').addEventListener('click', () => map.zoomIn());
document.getElementById('zoomOutBtn').addEventListener('click', () => map.zoomOut());

// Trazabilidad y rutas

// Ambulance Icon
const ambulanceIcon = L.divIcon({
    html: '<i class="fa-solid fa-truck-medical" style="color: #ff0000; font-size: 24px;"></i>',
    className: 'custom-ambulance-icon',
    iconSize: [24, 24],
    iconAnchor: [12, 12]
});

// Leaflet Routing Create
const routingControl = L.Routing.control({
    waypoints: [
        L.latLng(emergenciaOrigenCoords[0], emergenciaOrigenCoords[1]),
        L.latLng(hospitalClinicasCoords[0], hospitalClinicasCoords[1])
    ],
    routeWhileDragging: true,
    addWaypoints: false,
    draggableWaypoints: false,
    fitSelectedRoutes: true,
    show: false
}).addTo(map);

// Simulate Real-Time
let ambulanceMarker = null;
let animationInterval = null;
let ambulancePopupKey = null;

window.addEventListener('site-language-changed', () => {
    hospitalMarker.setPopupContent(`<b>Hospital de Clínicas</b><br>${popupText('hospital_location', 'Montevideo, Uruguay')}`);
    if (ambulanceMarker && ambulancePopupKey) {
        ambulanceMarker.setPopupContent(ambulancePopupKey === 'ambulance_arrived'
            ? `<b>${popupText('ambulance_arrived', 'Ambulancia ha llegado al destino')}</b>`
            : `<b>${popupText('ambulance_in_transit', 'Ambulancia en tránsito')}</b><br>${popupText('transport_status', 'Estado: En traslado')}`);
    }
});

document.getElementById('ambulanceMap').addEventListener('click', () => {
    if (animationInterval) clearInterval(animationInterval);

    // Route Points
    routingControl.on('routesfound', function(e) {
        const routes = e.routes[0];
        const coordinates = routes.coordinates;
        let step = 0;

        if (ambulanceMarker) map.removeLayer(ambulanceMarker);

        ambulanceMarker = L.marker([coordinates[0].lat, coordinates[0].lng], { icon: ambulanceIcon }).addTo(map);
        ambulancePopupKey = 'ambulance_in_transit';
        ambulanceMarker.bindPopup(`<b>${popupText('ambulance_in_transit', 'Ambulancia en tránsito')}</b><br>${popupText('transport_status', 'Estado: En traslado')}`).openPopup();

        // Ambulance move across
        animationInterval = setInterval(() => {
            step += 2;
            if (step < coordinates.length) {
                const nextLatLng = L.latLng(coordinates[step].lat, coordinates[step].lng);
                ambulanceMarker.setLatLng(nextLatLng);
                map.panTo(nextLatLng);
            } else{
                clearInterval(animationInterval);
                ambulancePopupKey = 'ambulance_arrived';
                ambulanceMarker.bindPopup(`<b>${popupText('ambulance_arrived', 'Ambulancia ha llegado al destino')}</b>`).openPopup();
            }
        }, 300);
    });
});

// ===================================================================
// Seguimiento en vivo (lo que envían los conductores desde conductor.php)
// ===================================================================
const INTERVALO_CONSULTA_MS = 5000;   // cada cuánto se actualiza el mapa
const SENIAL_PERDIDA_SEG = 30;        // si el último punto es más viejo que esto, se marca como "sin señal"

const liveAmbulances = {}; // id -> { marker, polyline }

const liveRouteStyle = { color: '#3a4dbf', weight: 4, opacity: 0.8 };

function liveAmbulancePopup(a) {
    const velocidad = a.velocidad !== null ? `<br>${Math.round(a.velocidad * 3.6)} km/h` : '';
    return `<b>${a.matricula}</b>`
        + `<br>${popupText('last_signal', 'Última señal')}: ${a.segundos} s`
        + velocidad;
}

async function actualizarAmbulanciasEnVivo() {
    try {
        const resp = await fetch('php/obtener_ubicaciones.php', { cache: 'no-store' });
        if (!resp.ok) return;
        const data = await resp.json();
        if (!data.ok) return;

        const idsActuales = new Set();

        data.ambulancias.forEach(a => {
            idsActuales.add(a.id);
            const pos = [a.lat, a.lng];
            const sinSenial = a.segundos > SENIAL_PERDIDA_SEG;

            if (!liveAmbulances[a.id]) {
                liveAmbulances[a.id] = {
                    marker: L.marker(pos, { icon: ambulanceIcon }).addTo(map),
                    polyline: L.polyline(a.ruta, liveRouteStyle).addTo(map)
                };
                liveAmbulances[a.id].marker.bindPopup(liveAmbulancePopup(a));
            } else {
                liveAmbulances[a.id].marker.setLatLng(pos);
                liveAmbulances[a.id].marker.setPopupContent(liveAmbulancePopup(a));
                liveAmbulances[a.id].polyline.setLatLngs(a.ruta);
            }

            liveAmbulances[a.id].marker.setOpacity(sinSenial ? 0.45 : 1);
        });

        // Quitar del mapa las ambulancias que dejaron de enviar
        Object.keys(liveAmbulances).forEach(id => {
            if (!idsActuales.has(Number(id))) {
                map.removeLayer(liveAmbulances[id].marker);
                map.removeLayer(liveAmbulances[id].polyline);
                delete liveAmbulances[id];
            }
        });
    } catch (e) {
        console.error('No se pudo actualizar el seguimiento:', e);
    }
}

actualizarAmbulanciasEnVivo();
setInterval(actualizarAmbulanciasEnVivo, INTERVALO_CONSULTA_MS);
