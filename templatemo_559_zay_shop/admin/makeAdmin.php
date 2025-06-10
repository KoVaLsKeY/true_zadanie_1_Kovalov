<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['rola'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
    die('Neplatné ID používateľa.');
}

require_once '../db/dbConfig.php';
$db = DATABASE;

try {
    $pdo = new PDO("mysql:host={$db['HOST']};port={$db['PORT']};dbname={$db['DBNAME']};charset=utf8", $db['USER_NAME'], $db['PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Перевірка, що цільовий користувач — не адміністратор
    $stmt = $pdo->prepare("SELECT rola FROM users WHERE id_user = ?");
    $stmt->execute([$_POST['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die('Používateľ neexistuje.');
    }

    if ($user['rola'] === 'admin') {
        die('Nie je možné meniť rolu iného administrátora.');
    }

    // Оновлення ролі
    $update = $pdo->prepare("UPDATE users SET rola = 'admin' WHERE id_user = ?");
    $update->execute([$_POST['user_id']]);

    header('Location: db/allUsers.php');
    exit;
} catch (PDOException $e) {
    die("Chyba databázy: " . $e->getMessage());
}
