<?php
session_start();

// Перевірка чи користувач адміністратор
if (!isset($_SESSION['user']) || $_SESSION['user']['rola'] !== 'admin') {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 100px;
        }

        .admin-button {
            display: inline-block;
            padding: 15px 30px;
            margin: 20px;
            font-size: 18px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 10px;
            text-decoration: none;
        }

        .admin-button:hover {
            background-color: #218838;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
        }

        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <a href="index.php" class="back-button">← Späť na hlavnú stránku</a>

    <?php echo '<h1>Vitaj v Admin Paneli, ' . $_SESSION['user']['meno'] . '</h1>'; ?>

    <a href="allUsers.php" class="admin-button">Všetci používatelia</a>
    <a href="allQuestions.php" class="admin-button">Všetky otázky</a>

</body>
</html>
