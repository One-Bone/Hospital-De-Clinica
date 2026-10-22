<?php
if (!isset($_GET['url'])) {
    die("Error: No se proporcionó ningún documento.");
}

$url_documento = $_GET['url'];
$titulo = isset($_GET['titulo']) ? htmlspecialchars($_GET['titulo']) : 'Documento';

$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($url_documento);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR - <?php echo $titulo; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f6f9;
            font-family: Arial, sans-serif;
        }
        .qr-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: #2a2a2b;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        img {
            border: 2px solid #eef0f3;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .btn-cerrar {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3a4dbf;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="qr-card">
        <h1><?php echo $titulo; ?></h1>

        <img src="<?php echo $qr_api_url; ?>" alt="Código QR">
        
        <br>
        <button onclick="window.close();" class="btn-cerrar"><i class="fa-solid fa-xmark"></i> Cerrar pestaña</button>
    </div>
</body>
</html>