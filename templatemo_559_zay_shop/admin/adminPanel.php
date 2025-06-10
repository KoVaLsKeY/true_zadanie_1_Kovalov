<?php
session_start();

// Pripojenie tried so správnymi cestami
require_once '../classes/AuthClass.php';
require_once '../classes/PermissionClass.php'; // Základná trieda povolení
require_once '../classes/AdminClass.php'; // Trieda pre administrátora
require_once '../classes/SuperAdminClass.php'; // Trieda pre super-administrátora

// Použitie správnych menných priestorov
use App\Auth\AuthClass;
use App\Permissions\PermissionClass; // Používame PermissionClass na kontrolu rolí

$auth = new AuthClass();
// Na kontrolu rolí "admin" alebo "superadmin" je najlepšie použiť PermissionClass,
// pretože jeho metóda hasRole() už vie kontrolovať pole rolí.
$permissions = new PermissionClass($auth);

// Kontrola, či je užívateľ prihlásený A či má rolu 'admin' ALEBO 'superadmin'.
// Ak užívateľ NIE JE prihlásený ALEBO (NEMA rolu 'admin' A NEMA rolu 'superadmin'), potom presmerovať.
if (!$auth->isLoggedIn() || !$permissions->hasRole(['admin', 'superadmin'])) {
    header('Location: ../stranky/index.php');
    exit;
}

// Načítanie CSS
// Dôležité: Lepšie je použiť absolútne cesty alebo základnú cestu k CSS
// alebo presunúť štýly do samostatného súboru .css a pripojiť ho cez <link>
// Ak už používate PHP na vloženie CSS, uistite sa, že cesta je správna
ob_start(); // Začíname bufferovanie výstupu, aby sme zachytili CSS
include_once('../assets_sablon/css/adminPanel.css');
$adminPanelCss = ob_get_clean(); // Získame obsah CSS

?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <style>
        <?php echo $adminPanelCss; // Vypíšeme CSS ?>
    </style>
</head>
<body>

<a href="../stranky/index.php" class="back-button">← Späť na hlavnú stránku</a>

<?php echo '<h1>Vitaj v Admin Paneli, ' . htmlspecialchars($_SESSION['user']['meno']) . '</h1>'; ?>

<a href="../db/allUsers.php" class="admin-button">Všetci používatelia</a>
<a href="../db/allQuestions.php" class="admin-button">Všetky otázky</a>

<?php
// Ak je užívateľ SuperAdmin, zobrazíme tlačidlo na odstránenie užívateľov
if ($permissions->isSuperAdmin()) {
    echo '<a href="delete_users.php" class="admin-button admin-button-danger">Vymazať používateľov</a>';
    echo '<a href="delete_questions_page.php" class="admin-button admin-button-danger">Vymazať otázky</a>';
}
// Tlačidlo na pridelenie administrátorov bude na stránke allUsers.php alebo makeAdmin.php
?>

</body>
</html>