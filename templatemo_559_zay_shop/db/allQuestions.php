<?php
session_start();

if (!isset($_SESSION['user']) || ($_SESSION['user']['rola'] !== 'superadmin' && $_SESSION['user']['rola'] !== 'admin')) {
    header('Location: ../index.php'); // Перенаправити на головну сторінку
    exit;
}

require_once '../db/dbConfig.php';

$db = DATABASE;

try {
    $pdo = new PDO("mysql:host={$db['HOST']};port={$db['PORT']};dbname={$db['DBNAME']};charset=utf8", $db['USER_NAME'], $db['PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT id_otazky, sprava, odpoved FROM udaje");
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Chyba databázy: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Všetky otázky</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
<?php include_once('../assets_sablon/css/backButton.css')?>
    </style>
</head>
<body class="container mt-5">

    <a href="../admin/adminPanel.php" class="back-button">← Späť do admin panelu</a>

    <h2 class="mb-4">Všetky otázky</h2>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Otázka</th>
                <th>Odpoveď</th>
                <th>Akcia</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($questions as $q): ?>
                <tr>
                    <td><?= htmlspecialchars($q['sprava']) ?></td>
                    <td><?= $q['odpoved'] ? htmlspecialchars($q['odpoved']) : '<em>Bez odpovede</em>' ?></td>
                    <td>
                        <a href="../admin/editQuestion.php?id=<?= $q['id_otazky'] ?>" class="btn btn-sm btn-warning">Upraviť</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
