<?php
if (!isset($_GET['qr'])) {
    die("Error: No se especificó ningún código QR.");
}
$qr_path = htmlspecialchars($_GET['qr']);
$titulo = isset($_GET['titulo']) ? htmlspecialchars($_GET['titulo']) : 'Documento Escaneable';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir QR - <?php echo $titulo; ?></title>
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
            max-width: 450px;
        }
        h1 {
            color: #2a2a2b;
            margin-bottom: 20px;
            font-size: 1.6rem;
            word-wrap: break-word;
        }
        img {
            border: 2px solid #eef0f3;
            border-radius: 10px;
            margin-bottom: 25px;
            max-width: 100%;
            height: auto;
        }
        .btn-imprimir {
            display: inline-block;
            padding: 12px 25px;
            background-color: #3a4dbf;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            width: 100%;
            box-sizing: border-box;
            transition: background-color 0.3s;
        }
        .btn-imprimir:hover {
            background-color: #2a2a2b;
        }
        
        @media print {
            body { 
                background-color: white; 
                justify-content: flex-start; 
                padding-top: 50px; 
            }
            .qr-card { 
                box-shadow: none; 
                padding: 0; 
            }
            .btn-imprimir { 
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="qr-card">
        <h1><?php echo $titulo; ?></h1>
        <img src="<?php echo $qr_path; ?>" alt="Código QR del Documento">
        <br>
        <button onclick="window.print();" class="btn-imprimir"><i class="fa-solid fa-print"></i> Imprimir QR</button>
    </div>
</body>
</html>