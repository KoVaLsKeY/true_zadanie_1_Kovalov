<?php
session_start();

require_once '../classes/AuthClass.php';
require_once '../classes/SuperAdminClass.php';

use App\Auth\AuthClass;
use App\Roles\SuperAdminClass;

$auth = new AuthClass();
$superAdminHandler = new SuperAdminClass($auth);

// Kontrola prístupu: iba super-administrátor môže mazať otázky
if (!$auth->isLoggedIn() || !$superAdminHandler->can('delete_questions')) {
    header('Location: ../stranky/index.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_id'])) {
    $questionId = (int)$_POST['question_id'];
    $result = $superAdminHandler->deleteQuestion($questionId);
    if ($result === true) {
        $message = "Otázka ID: {$questionId} úspešne odstránená.";
    } else {
        $message = "Chyba: " . $result;
    }
}

// Získanie všetkých otázok na zobrazenie (možno pridať metódu do AdminClass/PermissionClass pre toto)
// Pre zjednodušenie zatiaľ použijeme priame pripojenie k DB
require_once '../db/dbConfig.php';
$db = DATABASE;

try {
    $pdo = new PDO("mysql:host={$db['HOST']};port={$db['PORT']};dbname={$db['DBNAME']};charset=utf8", $db['USER_NAME'], $db['PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT id_otazky, sprava, odpoved FROM udaje");
    $allQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Chyba databázy: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Vymazať otázky</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
<h2>Vymazať otázky</h2>

<?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<table class="table table-striped">
    <thead>
    <tr>
        <th>ID</th>
        <th>Otázka</th>
        <th>Odpoveď</th>
        <th>Akcie</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($allQuestions)): ?>
        <?php foreach ($allQuestions as $question): ?>
            <tr>
                <td><?= htmlspecialchars($question['id_otazky']) ?></td>
                <td><?= htmlspecialchars($question['sprava']) ?></td>
                <td><?= htmlspecialchars($question['odpoved'] ?? 'N/A') ?></td>
                <td>
                    <form method="post" onsubmit="return confirm('Ste si istí, že chcete odstrániť túto otázku?');">
                        <input type="hidden" name="question_id" value="<?= $question['id_otazky'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Vymazať</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="4">Žiadne otázky na zobrazenie.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
<a href="adminPanel.php" class="btn btn-secondary">Späť do Admin Panela</a>
</body>
</html>