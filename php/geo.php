<?php
// Proxy hacia Geoapify: la clave queda en el servidor, nunca llega al navegador.
// Versión SIN cURL: usa file_get_contents (requiere allow_url_fopen=On).
// Reintenta automáticamente cuando la conexión se corta (señal débil).
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

require_once __DIR__ . '/config.php';

const INTENTOS_MAXIMOS = 3;   // cuántas veces se intenta si la conexión se corta
const TIMEOUT_SEGUNDOS = 15;  // espera máxima por intento

function geoapify(string $ruta, array $params): array
{
    global $geoapifyKey;
    $params['apiKey'] = $geoapifyKey;
    $url = 'https://api.geoapify.com/v1/' . $ruta . '?' . http_build_query($params);

    $contexto = stream_context_create(['http' => [
        'timeout' => TIMEOUT_SEGUNDOS,
        'ignore_errors' => true
    ]]);

    $cuerpo = false;
    $codigo = 0;

    for ($intento = 1; $intento <= INTENTOS_MAXIMOS; $intento++) {
        $cuerpo = @file_get_contents($url, false, $contexto);

        // Código HTTP de la respuesta (queda en $http_response_header)
        $codigo = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $codigo = (int)$m[1];
        }

        // Respuesta correcta, o error del servidor que no se arregla reintentando (clave, cuota...)
        if ($cuerpo !== false && $codigo === 200) break;
        if ($codigo !== 0) break;

        // Sin respuesta (conexión cortada): esperamos un poco y reintentamos
        if ($intento < INTENTOS_MAXIMOS) usleep(500000 * $intento);
    }

    if ($cuerpo === false || $codigo !== 200) {
        if ($codigo === 0) {
            throw new RuntimeException('No se pudo conectar con Geoapify (señal débil o cortada). Probá de nuevo.');
        }
        throw new RuntimeException("Geoapify respondió con error (HTTP $codigo)");
    }
    return json_decode($cuerpo, true) ?? [];
}

try {
    $accion = $_GET['accion'] ?? '';

    switch ($accion) {

        // Tocar el mapa -> nombre de la calle
        case 'reverse':
            $lat = filter_var($_GET['lat'] ?? null, FILTER_VALIDATE_FLOAT);
            $lon = filter_var($_GET['lon'] ?? null, FILTER_VALIDATE_FLOAT);
            if ($lat === false || $lon === false || abs($lat) > 90 || abs($lon) > 180) {
                throw new InvalidArgumentException('Coordenadas inválidas');
            }
            $r = geoapify('geocode/reverse', ['lat' => $lat, 'lon' => $lon, 'lang' => 'es']);
            echo json_encode([
                'ok' => true,
                'direccion' => $r['features'][0]['properties']['formatted'] ?? 'Ubicación seleccionada'
            ]);
            break;

        // Escribir una calle -> sugerencias (solo Uruguay, con sesgo a Montevideo)
        case 'autocomplete':
            $texto = trim($_GET['text'] ?? '');
            if (mb_strlen($texto) < 3) {
                echo json_encode(['ok' => true, 'resultados' => []]);
                break;
            }
            $r = geoapify('geocode/autocomplete', [
                'text'   => $texto,
                'filter' => 'countrycode:uy',
                'bias'   => 'proximity:-56.1645,-34.9011', // formato lon,lat
                'lang'   => 'es',
                'limit'  => 5
            ]);
            $resultados = [];
            foreach ($r['features'] ?? [] as $f) {
                $resultados[] = [
                    'texto' => $f['properties']['formatted'],
                    'lat'   => $f['properties']['lat'],
                    'lon'   => $f['properties']['lon']
                ];
            }
            echo json_encode(['ok' => true, 'resultados' => $resultados]);
            break;

        // Ruta entre dos puntos: origen=lat,lon  destino=lat,lon
        case 'ruta':
            $patron  = '/^-?\d{1,3}(\.\d+)?,-?\d{1,3}(\.\d+)?$/';
            $origen  = $_GET['origen'] ?? '';
            $destino = $_GET['destino'] ?? '';
            if (!preg_match($patron, $origen) || !preg_match($patron, $destino)) {
                throw new InvalidArgumentException('Coordenadas inválidas');
            }
            $r = geoapify('routing', [
                'waypoints' => $origen . '|' . $destino,
                'mode'      => 'drive',
                'units'     => 'metric',
                'lang'      => 'es'
            ]);
            $f = $r['features'][0] ?? null;
            if ($f === null) {
                throw new RuntimeException('No se encontró una ruta entre esos puntos');
            }
            $geo = $f['geometry'];
            $coords = $geo['type'] === 'MultiLineString'
                ? array_merge(...$geo['coordinates'])
                : $geo['coordinates'];

            // Geoapify devuelve [lon, lat]; Leaflet usa [lat, lon]
            $geometria = array_map(fn($c) => [$c[1], $c[0]], $coords);

            echo json_encode([
                'ok'          => true,
                'distancia_m' => $f['properties']['distance'],
                'duracion_s'  => $f['properties']['time'],
                'geometria'   => $geometria
            ]);
            break;

        default:
            throw new InvalidArgumentException('Acción no válida');
    }
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
