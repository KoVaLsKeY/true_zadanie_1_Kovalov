<?php
session_start();

// Перевірка чи користувач адміністратор
if (!isset($_SESSION['user']) || $_SESSION['user']['rola'] !== 'admin') {
    header('Location: ../stranky/index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <style>
<?php include_once('../assets_sablon/css/adminPanel.css')?>
    </style>
</head>
<body>

    <a href="../stranky/index.php" class="back-button">← Späť na hlavnú stránku</a>

    <?php echo '<h1>Vitaj v Admin Paneli, ' . $_SESSION['user']['meno'] . '</h1>'; ?>

    <a href="../db/allUsers.php" class="admin-button">Všetci používatelia</a>
    <a href="../db/allQuestions.php" class="admin-button">Všetky otázky</a>

</body>
</html>
