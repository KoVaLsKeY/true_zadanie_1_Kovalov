<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../classes/AuthClass.php';
require_once '../classes/PermissionClass.php';
require_once '../classes/AdminClass.php';
require_once '../classes/SuperAdminClass.php';
require_once 'dbConfig.php';

use App\Auth\AuthClass;
use App\Roles\AdminClass;
use App\Roles\SuperAdminClass;
use App\Permissions\PermissionClass;

$auth = new AuthClass();
$adminHandler = new AdminClass($auth);
$superAdminHandler = new SuperAdminClass($auth);
$permissions = new PermissionClass($auth); // Використовуємо його для getAllUsers()


if (!$auth->isLoggedIn() || !$permissions->hasRole(['admin', 'superadmin'])) {
    header('Location: ../index.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($userId && $action) {
        // Визначаємо, який handler використовувати для виконання дії.
        // Це має бути той handler, який має метод дії.
        // makeUserAdmin є в AdminClass, demoteAdmin і deleteUser в SuperAdminClass.

        $result = "Невідома помилка."; // Задаємо початкове значення

        switch ($action) {
            case 'make_admin':
                // Для make_admin, ми можемо перевірити дозвіл через superAdminHandler
                // (оскільки superadmin також може робити admin-ів)
                // а потім викликати makeUserAdmin через adminHandler,
                // АБО зробити makeUserAdmin в SuperAdminClass і викликати його там.
                // Краще викликати його на об'єкті, який має дозвіл і метод.

                if ($superAdminHandler->can('make_user_admin')) { // Перевірка дозволу
                    // Якщо поточний користувач супер-адмін, то викликаємо makeUserAdmin через superAdminHandler
                    // (якщо він містить makeUserAdmin, або якщо він успадковує його правильно)
                    // Або, якщо makeUserAdmin визначений тільки в AdminClass,
                    // потрібно обійти його внутрішню can() перевірку або передати правильний контекст.

                    // НАЙПРОСТІШЕ РІШЕННЯ: зробити makeUserAdmin в SuperAdminClass,
                    // і тоді викликати $superAdminHandler->makeUserAdmin($userId);
                    // АБО: зробити так, щоб AdminClass::can() працював коректно,
                    // якщо його викликає AdminClass, чий userRole = superadmin.

                    // ПРОПОНУЮ ДОДАТИ makeUserAdmin до SuperAdminClass і перевизначити його.
                    // Або, щоб AdminClass::makeUserAdmin() не перевіряв свою can() якщо викликається супер-адміном.
                    // Це складно.

                    // АБО: передайте $adminHandler до $superAdminHandler, щоб він міг його використовувати:
                    // new SuperAdminClass($auth, $adminHandler); - це занадто.

                    // НАЙКРАЩЕ РІШЕННЯ: зробити так, щоб makeUserAdmin був викликаний на об'єкті,
                    // чий userRole відповідає виклику can().

                    // Якщо залогінений супер-адмін, використовуємо superAdminHandler для всього
                    // Якщо залогінений адмін, використовуємо adminHandler для make_admin
                    if ($permissions->getUserRole() === 'superadmin') {
                        $result = $superAdminHandler->makeUserAdmin($userId); // Викликаємо на superAdminHandler
                    } elseif ($permissions->getUserRole() === 'admin') {
                        $result = $adminHandler->makeUserAdmin($userId); // Викликаємо на adminHandler
                    } else {
                        $result = "Недостатньо прав для призначення адміністраторів.";
                    }

                    if ($result === true) {
                        $message = "Користувача ID: {$userId} успішно зроблено адміністратором.";
                    } else {
                        $message = "Помилка: " . $result;
                    }

                } else {
                    $message = "Помилка: Недостатньо прав для призначення адміністраторів.";
                }
                break;

            case 'demote_admin':
                if ($superAdminHandler->can('demote_admin')) {
                    $result = $superAdminHandler->demoteAdmin($userId);
                    if ($result === true) {
                        $message = "Користувача ID: {$userId} успішно понижено до 'user'.";
                    } else {
                        $message = "Помилка: " . $result;
                    }
                } else {
                    $message = "Помилка: Недостатньо прав для пониження адміністраторів.";
                }
                break;

            case 'delete_user':
                if ($superAdminHandler->can('delete_users')) {
                    $result = $superAdminHandler->deleteUser($userId);
                    if ($result === true) {
                        $message = "Користувача ID: {$userId} успішно видалено.";
                    } else {
                        $message = "Помилка: " . $result;
                    }
                } else {
                    $message = "Помилка: Недостатньо прав для видалення користувачів.";
                }
                break;

            default:
                $message = "Невідома дія.";
                break;
        }
    } else {
        $message = "Недійсний запит.";
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
    exit;
}

if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}

$allUsers = $permissions->getAllUsers();
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Všetci používatelia</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        <?php include_once('../assets_sablon/css/backButton.css')?>
        .table-actions form {
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body class="container mt-5">

<a href="../admin/adminPanel.php" class="back-button">← Späť do admin panelu</a>

<h2 class="mb-4">Zoznam používateľov</h2>

<?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<table class="table table-bordered">
    <thead>
    <tr>
        <th>ID</th>
        <th>Meno</th>
        <th>Email</th>
        <th>Rola</th>
        <th>Akcie</th>
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
                <td class="table-actions">
                    <?php
                    // --- Кнопка "Urobiť Adminom" ---
                    // Тепер перевіряємо через superAdminHandler, оскільки він коректно обробить 'admin' та 'superadmin' ролі.
                    if ($superAdminHandler->can('make_user_admin') && $user['rola'] !== 'admin' && $user['rola'] !== 'superadmin'): ?>
                        <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>" onsubmit="return confirm('Určite хочете призначити користувача ID: <?= $user['id_user'] ?> адміністратором?');">
                            <input type="hidden" name="user_id" value="<?= $user['id_user'] ?>">
                            <input type="hidden" name="action" value="make_admin">
                            <button type="submit" class="btn btn-sm btn-primary">Urobiť Adminom</button>
                        </form>
                    <?php endif; ?>

                    <?php
                    // --- Кнопка "Ponižiť Admina" (Тільки для супер-адміна) ---
                    if ($superAdminHandler->can('demote_admin') && $user['rola'] === 'admin' && $user['id_user'] !== ($_SESSION['user']['id'] ?? null)) {
                        echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" onsubmit="return confirm(\'Určite хочете понизити адміністратора ID: ' . $user['id_user'] . ' до звичайного користувача?\');">';
                        echo '<input type="hidden" name="user_id" value="' . $user['id_user'] . '">';
                        echo '<input type="hidden" name="action" value="demote_admin">';
                        echo '<button type="submit" class="btn btn-sm btn-warning">Ponižiť Admina</button>';
                        echo '</form>';
                    }

                    // --- Кнопка "Vymazať" (Тільки для супер-адміна) ---
                    if ($superAdminHandler->can('delete_users') && $user['id_user'] !== ($_SESSION['user']['id'] ?? null)) {
                        echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" onsubmit="return confirm(\'Určite хочете видалити користувача ID: ' . $user['id_user'] . '?\');">';
                        echo '<input type="hidden" name="user_id" value="' . $user['id_user'] . '">';
                        echo '<input type="hidden" name="action" value="delete_user">';
                        echo '<button type="submit" class="btn btn-sm btn-danger">Vymazať</button>';
                        echo '</form>';
                    }
                    ?>
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
</body>
</html>