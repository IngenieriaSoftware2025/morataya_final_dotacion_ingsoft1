<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?= asset('images/cit.png') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
    <title>Sistema de Dotaciones - MINDEF</title>
    <style>
        body {
            background-color: #000000;
            color: #ffffff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .btn-red {
            background-color: #dc3545;
            border: none;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        
        .btn-red:hover {
            background-color: #c82333;
            color: white;
            text-decoration: none;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .text-center {
            text-align: center;
        }
        
        h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            color: #cccccc;
        }
        
        .logo-img {
            max-width: 200px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <?php echo $contenido; ?>
    
    <script src="<?= asset('build/js/app.js'); ?>"></script>
</body>
</html>