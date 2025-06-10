<?php
session_start();

// Увімкнути повне відображення помилок для налагодження
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Підключення необхідних класів
require_once '../classes/AuthClass.php';
require_once '../classes/PermissionClass.php';
require_once '../classes/AdminClass.php';
require_once '../classes/SuperAdminClass.php';

// Використання правильних namespace
use App\Auth\AuthClass;
use App\Roles\AdminClass;
use App\Roles\SuperAdminClass;
use App\Permissions\PermissionClass; // Переконайтесь, що цей use є

// Створення екземплярів класів
$auth = new AuthClass();
$adminHandler = new AdminClass($auth);
$superAdminHandler = new SuperAdminClass($auth);
$permissions = new PermissionClass($auth); // Загальний клас для перевірки дозволів

// --- ПОЧАТОК НАЛАГОДЖУВАЛЬНОГО ВИВОДУ ---
echo '<pre>';
echo '<h3>DEBUG INFO (makeAdmin.php)</h3>';
echo 'Is Logged In: ' . var_export($auth->isLoggedIn(), true) . "\n";
echo '$_SESSION[\'user\'] data: ' . var_export($_SESSION['user'] ?? 'Not set', true) . "\n";
if (isset($_SESSION['user']['rola'])) {
    echo 'Current User Role (from Session): ' . htmlspecialchars($_SESSION['user']['rola']) . "\n";
} else {
    echo 'Current User Role (from Session): Not set' . "\n";
}
echo 'Current User Role (from PermissionClass): ' . var_export($permissions->getUserRole(), true) . "\n"; // Потрібен getUserRole() в PermissionClass
echo 'Can "make_user_admin" (AdminHandler): ' . var_export($adminHandler->can('make_user_admin'), true) . "\n";
echo 'Can "make_user_superadmin" (SuperAdminHandler): ' . var_export($superAdminHandler->can('make_user_superadmin'), true) . "\n";
echo 'Can "demote_admin" (SuperAdminHandler): ' . var_export($superAdminHandler->can('demote_admin'), true) . "\n";
echo 'Can "delete_users" (SuperAdminHandler): ' . var_export($superAdminHandler->can('delete_users'), true) . "\n";
echo '</pre>';
// --- КІНЕЦЬ НАЛАГОДЖУВАЛЬНОГО ВИВОДУ ---


// Перевірка доступу: тільки адміністратор або супер-адміністратор можуть переглядати цю сторінку
if (!$auth->isLoggedIn() || !$permissions->hasRole(['admin', 'superadmin'])) {
    header('Location: ../stranky/index.php'); // Перенаправлення на головну сторінку, якщо немає прав
    exit;
}

$message = ''; // Змінна для зберігання повідомлень користувачу

// Обробка POST-запитів (коли натискається кнопка)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($userId && $action) {
        // Перевірка дій та відповідних дозволів
        switch ($action) {
            case 'make_admin':
                if ($adminHandler->can('make_user_admin')) {
                    $result = $adminHandler->makeUserAdmin($userId);
                    if ($result === true) {
                        $message = "Користувача ID: {$userId} успішно зроблено адміністратором.";
                    } else {
                        $message = "Помилка: " . $result;
                    }
                } else {
                    $message = "Помилка: Недостатньо прав для призначення адміністраторів.";
                }
                break;

            case 'make_superadmin':
                if ($superAdminHandler->can('make_user_superadmin')) {
                    $result = $superAdminHandler->makeUserSuperAdmin($userId);
                    if ($result === true) {
                        $message = "Користувача ID: {$userId} успішно зроблено супер-адміністратором.";
                    } else {
                        $message = "Помилка: " . $result;
                    }
                } else {
                    $message = "Помилка: Недостатньо прав для призначення супер-адміністраторів.";
                }
                break;

            case 'demote_admin':
                // Тільки супер-адмін може понижувати інших адміністраторів
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
                // Тільки супер-адмін може видаляти користувачів
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
}

// Отримання всіх користувачів для відображення в таблиці
$allUsers = $adminHandler->getAllUsers(); // Цей метод має бути доступний через PermissionClass (або AdminClass)
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Správa používateľov</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .table-actions form {
            display: inline-block;
            margin-right: 5px;
        }
    </style>
</head>
<body class="container mt-5">
<h2>Správa používateľів</h2>

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
        <th>Акції</th>
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
                    // Доступна адміну або супер-адміну, якщо користувач не є адміном чи супер-адміном
                    if ($adminHandler->can('make_user_admin') && $user['rola'] !== 'admin' && $user['rola'] !== 'superadmin'): ?>
                        <form method="post">
                            <input type="hidden" name="user_id" value="<?= $user['id_user'] ?>">
                            <input type="hidden" name="action" value="make_admin">
                            <button type="submit" class="btn btn-sm btn-primary">Urobiť Adminom</button>
                        </form>
                    <?php endif; ?>

                    <?php
                    // --- Дії, доступні тільки супер-адміну ---
                    // Перевіряємо, чи поточний користувач має дозвіл на пониження адмінів (що дає тільки супер-адмін)
                    if ($superAdminHandler->can('demote_admin')) { // <-- Достатньо перевірити один дозвіл для супер-адмінських дій
                        // Кнопка "Ponižiť Admina"
                        // Дозволено понижувати тільки тих, хто є 'admin', і не самого себе
                        if ($user['rola'] === 'admin' && $user['id_user'] !== ($_SESSION['user']['id'] ?? null)) {
                            echo '<form method="post">';
                            echo '<input type="hidden" name="user_id" value="' . $user['id_user'] . '">';
                            echo '<input type="hidden" name="action" value="demote_admin">';
                            echo '<button type="submit" class="btn btn-sm btn-warning">Ponižiť Admina</button>';
                            echo '</form>';
                        }

                        // Кнопка "Urobiť SuperAdminom"
                        // Дозволено робити супер-адміном, якщо користувач не є супер-адміном і не є поточним користувачем
                        if ($user['rola'] !== 'superadmin' && $user['id_user'] !== ($_SESSION['user']['id'] ?? null) && $superAdminHandler->can('make_user_superadmin')) {
                            echo '<form method="post">';
                            echo '<input type="hidden" name="user_id" value="' . $user['id_user'] . '">';
                            echo '<input type="hidden" name="action" value="make_superadmin">';
                            echo '<button type="submit" class="btn btn-sm btn-success">Urobiť SuperAdminom</button>';
                            echo '</form>';
                        }

                        // Кнопка "Vymazať"
                        // Дозволено видаляти, якщо користувач не є поточним користувачем і має дозвіл delete_users
                        if ($superAdminHandler->can('delete_users') && $user['id_user'] !== ($_SESSION['user']['id'] ?? null)) {
                            echo '<form method="post" onsubmit="return confirm(\'Ste si istí, že chcete odstrániť tohto používateľa?\');">';
                            echo '<input type="hidden" name="user_id" value="' . $user['id_user'] . '">';
                            echo '<input type="hidden" name="action" value="delete_user">';
                            echo '<button type="submit" class="btn btn-sm btn-danger">Vymazať</button>';
                            echo '</form>';
                        }
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
<a href="adminPanel.php" class="btn btn-secondary">Späť do Admin Panela</a>
</body>
</html>