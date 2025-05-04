<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['rola'] !== 'admin') {
    header('Location: index.php');
    exit;
}

require_once 'db/dbConfig.php';

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

// Якщо надіслано форму
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['sprava']);
    $answer = trim($_POST['odpoved']);

    $update = $pdo->prepare("UPDATE udaje SET sprava = ?, odpoved = ? WHERE id_otazky = ?");
    $update->execute([$question, $answer ?: null, $id]);

    header("Location: allQuestions.php");
    exit;
}

// Завантаження існуючого питання
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
            <label for="question" class="form-label">Otázka</label>
            <textarea name="sprava" id="sprava" class="form-control" rows="3" required><?= htmlspecialchars($questionData['sprava']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="answer" class="form-label">Odpoveď</label>
            <textarea name="odpoved" id="odpoved" class="form-control" rows="3"><?= htmlspecialchars($questionData['odpoved'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">Uložiť</button>
        <a href="allQuestions.php" class="btn btn-secondary">Späť</a>
    </form>
</body>
</html>
