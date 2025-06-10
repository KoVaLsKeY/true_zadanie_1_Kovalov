<?php
session_start();

// Підключення класів з правильними шляхами
require_once '../classes/AuthClass.php';
require_once '../classes/PermissionClass.php'; // Базовий клас дозволів
require_once '../classes/AdminClass.php'; // Клас для адміна
require_once '../classes/SuperAdminClass.php'; // Клас для супер-адміна

// Використання правильних namespace
use App\Auth\AuthClass;
use App\Permissions\PermissionClass; // Використовуємо PermissionClass для перевірки ролей

$auth = new AuthClass();
// Для перевірки ролей "admin" або "superadmin", найкраще використовувати PermissionClass,
// оскільки його метод hasRole() вже вміє перевіряти масив ролей.
$permissions = new PermissionClass($auth);

// Перевірка, чи користувач залогінений ТА чи має він роль 'admin' АБО 'superadmin'.
// Якщо користувач НЕ залогінений АБО (НЕ має ролі 'admin' ТА НЕ має ролі 'superadmin'), тоді перенаправити.
if (!$auth->isLoggedIn() || !$permissions->hasRole(['admin', 'superadmin'])) {
    header('Location: ../stranky/index.php');
    exit;
}

// Завантаження CSS
// Важливо: Краще робити абсолютні шляхи або використовувати базовий шлях до CSS
// або перенести стилі в окремий .css файл і підключати його через <link>
// Якщо ви вже використовуєте PHP для включення CSS, переконайтесь, що шлях правильний
ob_start(); // Починаємо буферизацію виводу, щоб захопити CSS
include_once('../assets_sablon/css/adminPanel.css');
$adminPanelCss = ob_get_clean(); // Отримуємо вміст CSS

?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <style>
        <?php echo $adminPanelCss; // Виводимо CSS ?>
    </style>
</head>
<body>

<a href="../stranky/index.php" class="back-button">← Späť na hlavnú stránku</a>

<?php echo '<h1>Vitaj v Admin Paneli, ' . htmlspecialchars($_SESSION['user']['meno']) . '</h1>'; ?>

<a href="../db/allUsers.php" class="admin-button">Všetci používatelia</a>
<a href="../db/allQuestions.php" class="admin-button">Všetky otázky</a>

<?php
// Якщо користувач є SuperAdmin, показуємо кнопку для видалення користувачів
if ($permissions->isSuperAdmin()) {
    echo '<a href="delete_users.php" class="admin-button admin-button-danger">Vymazať používateľov</a>';
    echo '<a href="delete_questions_page.php" class="admin-button admin-button-danger">Vymazať otázky</a>';
}
// Кнопка для призначення адмінів буде на сторінці allUsers.php або makeAdmin.php
?>

</body>
</html>