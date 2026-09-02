const montevideoCords = [-34.9011, -56.1645];
const hospitalClinicasCoords = [-34.8883, -56.1541];
const emergenciaOrigenCoords = [-34.9058, -56.1913];

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
L.marker(hospitalClinicasCoords, { icon: hospitalIcon })
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

document.getElementById('ambulanceMap').addEventListener('click', () => {
    if (animationInterval) clearInterval(animationInterval);

    // Route Points
    routingControl.on('routesfound', function(e) {
        const routes = e.routes[0];
        const coordinates = routes.coordinates;
        let step = 0;

        if (ambulanceMarker) map.removeLayer(ambulanceMarker);

        ambulanceMarker = L.marker([coordinates[0].lat, coordinates[0].lng], { icon: ambulanceIcon }).addTo(map);
        ambulanceMarker.bindPopup('<b>Ambulancia en tránsito</b><br>Estado: En traslado').openPopup();

        // Ambulance move across
        animationInterval = setInterval(() => {
            step += 2;
            if (step < coordinates.length) {
                const nextLatLng = L.latLng(coordinates[step].lat, coordinates[step].lng);
                ambulanceMarker.setLatLng(nextLatLng);
                map.panTo(nextLatLng);
            } else{
                clearInterval(animationInterval);
                ambulanceMarker.bindPopup('<b>Ambulancia ha llegado al destino</b>').openPopup();
            }
        }, 300);
    });
});