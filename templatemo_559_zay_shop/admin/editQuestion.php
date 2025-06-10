<?php
session_start();

require_once '../classes/AuthClass.php';
require_once '../classes/PermissionClass.php';
require_once '../classes/AdminClass.php';

use App\Auth\AuthClass;
use App\Roles\AdminClass; // Používame AdminClass na kontrolu povolenia

$auth = new AuthClass();
$adminPermissions = new AdminClass($auth);

// Kontrola, či je užívateľ prihlásený a má povolenie na úpravu otázok
if (!isset($_SESSION['user']) || ($_SESSION['user']['rola'] !== 'superadmin' && $_SESSION['user']['rola'] !== 'admin')) {
    header('Location: ../stranky/index.php'); // Presmerovanie na hlavnú stránku, ak nie je prístup
    exit;
}

require_once '../db/dbConfig.php';

$db = DATABASE;

try {
    $pdo = new PDO("mysql:host={$db['HOST']};port={$db['PORT']};dbname={$db['DBNAME']};charset=utf8", $db['USER_NAME'], $db['PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Chyba databázy: " . $e->getMessage());
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Neplatné ID otázky.");
}

$id = (int) $_GET['id'];

// Ak bol formulár odoslaný
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['sprava']);
    $answer = trim($_POST['odpoved']);

    $update = $pdo->prepare("UPDATE udaje SET sprava = ?, odpoved = ? WHERE id_otazky = ?");
    $update->execute([$question, $answer ?: null, $id]);

    header("Location: ../db/allQuestions.php");
    exit;
}

// Načítanie existujúcej otázky
$stmt = $pdo->prepare("SELECT sprava, odpoved FROM udaje WHERE id_otazky = ?");
$stmt->execute([$id]);
$questionData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$questionData) {
    die("Otázka neexistuje.");
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Upraviť otázku</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
<h2>Upraviť otázku</h2>

<form method="post">
    <div class="mb-3">
        <label for="sprava" class="form-label">Otázka</label>
        <textarea name="sprava" id="sprava" class="form-control" rows="3" required><?= htmlspecialchars($questionData['sprava']) ?></textarea>
    </div>
    <div class="mb-3">
        <label for="odpoved" class="form-label">Odpoveď</label>
        <textarea name="odpoved" id="odpoved" class="form-control" rows="3"><?= htmlspecialchars($questionData['odpoved'] ?? '') ?></textarea>
    </div>
    <button type="submit" class="btn btn-success">Uložiť</button>
    <a href="../db/allQuestions.php" class="btn btn-secondary">Späť</a>
</form>
</body>
</html>