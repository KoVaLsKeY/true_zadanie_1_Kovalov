<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['rola'] !== 'admin') {
    header('Location: index.php');
    exit;
}

require_once 'db/dbConfig.php';

$db = DATABASE;

try {
    $dsn = "mysql:host={$db['HOST']};port={$db['PORT']};dbname={$db['DBNAME']};charset=utf8";
    $pdo = new PDO($dsn, $db['USER_NAME'], $db['PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Chyba pri pripájaní: " . $e->getMessage());
}

$stmt = $pdo->query("SELECT id_user, meno, email, rola FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Všetci používatelia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
<?php include_once('assets_sablon/css/backButton.css')?>
</style>
</head>
<body class="container mt-5">

    <a href="admin_panel.php" class="back-button">← Späť do admin panelu</a>

    <h2 class="mb-4">Zoznam používateľov</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Meno</th>
                <th>Email</th>
                <th>Rola</th>
                <th>Akcia</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id_user']) ?></td>
                    <td><?= htmlspecialchars($user['meno']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['rola']) ?></td>
                    <td>
                        <?php if ($user['rola'] !== 'admin'): ?>
                            <form method="post" action="makeAdmin.php" onsubmit="return confirm('Určite chceš povýšiť tohto používateľa na administrátora?');">
                                <input type="hidden" name="user_id" value="<?= $user['id_user'] ?>">
                                <button type="submit" class="btn btn-warning btn-sm">Urobiť admina</button>
                            </form>
                        <?php else: ?>
                            <span class="text-muted">Admin</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
