<?php ?>
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ďakujeme</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #e0f7fa, #80deea);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .vdaka-container {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
        }

        h1 {
            color: #00796b;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.1em;
            margin: 10px 0;
            color: #333;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: white;
            background-color: #00796b;
            padding: 10px 20px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #004d40;
        }
    </style>
</head>
<body>
    <div class="vdaka-container">
        <h1>Ďakujeme!</h1>
        <p>Vaša odpoveď bola úspešne odoslaná.</p>
        <p>V priebehu najbližšieho času Vás bude kontaktovať náš manažér.</p>
        <a href="index.php">Späť na hlavnú stránku</a>
    </div>
</body>
</html>
