<?php
session_start();

require_once '../classes/AuthClass.php';
require_once '../classes/SuperAdminClass.php';

use App\Auth\AuthClass;
use App\Roles\SuperAdminClass;

$auth = new AuthClass();
$superAdminHandler = new SuperAdminClass($auth);

// Перевірка доступу: тільки супер-адмін може видаляти користувачів
if (!$auth->isLoggedIn() || !$superAdminHandler->can('delete_users')) {
    header('Location: ../stranky/index.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_user'])) {
    $userId = (int)$_POST['id_user'];
    $result = $superAdminHandler->deleteUser($userId);
    if ($result === true) {
        $message = "Користувач ID: {$userId} успішно видалений.";
    } else {
        $message = "Помилка: " . $result;
    }
}

// Отримання всіх користувачів для відображення
$allUsers = $superAdminHandler->getAllUsers(); // Метод з PermissionClass
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Vymazať používateľov</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
<h2>Vymazať používateľov</h2>

<?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<table class="table table-striped">
    <thead>
    <tr>
        <th>ID</th>
        <th>Meno</th>
        <th>Email</th>
        <th>Rola</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($allUsers)): ?>
        <?php foreach ($allUsers as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id_user']) ?></td>
                <td><?= htmlspecialchars($user['meno']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['rola']) ?></td>
                <td>
                    <?php if ($user['id_user'] !== $_SESSION['user']['id']): // Не дозволяємо видаляти самого себе ?>
                        <form method="post" onsubmit="return confirm('Ste si istí, že chcete odstrániť tohto používateľa?');">
                            <input type="hidden" name="user_id" value="<?= $user['id_user'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Vymazať</button>
                        </form>
                    <?php else: ?>
                        <span class="text-muted">Nemôžete sa vymazať</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="5">Žiadni používatelia na zobrazenie.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
<a href="adminPanel.php" class="btn btn-secondary">Späť do Admin Panela</a>
</body>
</html>