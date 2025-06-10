<?php
session_start();

// Zapnúť úplné zobrazenie chýb pre ladenie
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Pripojenie potrebných tried
require_once '../classes/AuthClass.php';
require_once '../classes/PermissionClass.php';
require_once '../classes/AdminClass.php';
require_once '../classes/SuperAdminClass.php';
require_once 'dbConfig.php';

// Použitie správnych menných priestorov
use App\Auth\AuthClass;
use App\Roles\AdminClass;
use App\Roles\SuperAdminClass;
use App\Permissions\PermissionClass; // Uistite sa, že toto "use" existuje

// Vytvorenie inštancií tried
$auth = new AuthClass();
$adminHandler = new AdminClass($auth);
$superAdminHandler = new SuperAdminClass($auth);
$permissions = new PermissionClass($auth); // Všeobecná trieda pre kontrolu povolení


if (!$auth->isLoggedIn() || !$permissions->hasRole(['admin', 'superadmin'])) {
    header('Location: ../index.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($userId && $action) {
        // Určite, ktorý handler použiť na vykonanie akcie.
        // Mal by to byť ten handler, ktorý má metódu akcie.
        // makeUserAdmin je v AdminClass, demoteAdmin a deleteUser v SuperAdminClass.

        $result = "Neznáma chyba."; // Nastavíme počiatočnú hodnotu

        switch ($action) {
            case 'make_admin':
                // Pre make_admin môžeme skontrolovať povolenie cez superAdminHandler
                // (pretože superadmin môže tiež robiť adminov)
                // A potom zavolať makeUserAdmin cez adminHandler,
                // ALEBO urobiť makeUserAdmin v SuperAdminClass a zavolať ho tam.
                // Lepšie je zavolať ho na objekte, ktorý má povolenie a metódu.

                // NAJLEPŠIE RIEŠENIE: urobiť makeUserAdmin v SuperAdminClass,
                // a potom zavolať $superAdminHandler->makeUserAdmin($userId);
                // ALEBO: uistite sa, že AdminClass::can() funguje správne,
                // ak ho volá AdminClass, ktorej userRole = superadmin.

                // PONÚKAM PRIDAŤ makeUserAdmin do SuperAdminClass a predefinovať ho.
                // ALEBO: aby AdminClass::makeUserAdmin() neskontrolovala svoj can(), ak je volaná superadminom.
                // Toto je zložité.

                // ALEBO: odovzdajte $adminHandler do $superAdminHandler, aby ho mohol použiť:
                // new SuperAdminClass($auth, $adminHandler); - to je príliš.

                // NAJLEPŠIE RIEŠENIE: uistite sa, že makeUserAdmin je volaný na objekte,
                // ktorého userRole zodpovedá volaniu can().

                // Ak je prihlásený super-administrátor, použite superAdminHandler pre všetko
                // Ak je prihlásený administrátor, použite adminHandler pre make_admin
                if ($permissions->getUserRole() === 'superadmin') {
                    $result = $superAdminHandler->makeUserAdmin($userId); // Voláme na superAdminHandler
                } elseif ($permissions->getUserRole() === 'admin') {
                    $result = $adminHandler->makeUserAdmin($userId); // Voláme na adminHandler
                } else {
                    $result = "Nedostatočné oprávnenia na pridelenie administrátorov.";
                }

                if ($result === true) {
                    $message = "Používateľ ID: {$userId} úspešne bol nastavený ako administrátor.";
                } else {
                    $message = "Chyba: " . $result;
                }

                break;

            case 'demote_admin':
                if ($superAdminHandler->can('demote_admin')) {
                    $result = $superAdminHandler->demoteAdmin($userId);
                    if ($result === true) {
                        $message = "Používateľ ID: {$userId} úspešne bol degradovaný na 'user'.";
                    } else {
                        $message = "Chyba: " . $result;
                    }
                } else {
                    $message = "Chyba: Nedostatočné oprávnenia na degradáciu administrátorov.";
                }
                break;

            case 'delete_user':
                if ($superAdminHandler->can('delete_users')) {
                    $result = $superAdminHandler->deleteUser($userId);
                    if ($result === true) {
                        $message = "Používateľ ID: {$userId} úspešne odstránený.";
                    } else {
                        $message = "Chyba: " . $result;
                    }
                } else {
                    $message = "Chyba: Nedostatočné oprávnenia na odstránenie používateľov.";
                }
                break;

            default:
                $message = "Neznáma akcia.";
                break;
        }
    } else {
        $message = "Neplatná požiadavka.";
    }
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
                    // --- Tlačidlo "Urobiť Adminom" ---
                    // Teraz kontrolujeme cez superAdminHandler, pretože správne spracuje roly 'admin' a 'superadmin'.
                    if ($superAdminHandler->can('make_user_admin') && $user['rola'] !== 'admin' && $user['rola'] !== 'superadmin'): ?>
                        <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>" onsubmit="return confirm('Naozaj chcete priradiť používateľa ID: <?= $user['id_user'] ?> ako administrátora?');">
                            <input type="hidden" name="user_id" value="<?= $user['id_user'] ?>">
                            <input type="hidden" name="action" value="make_admin">
                            <button type="submit" class="btn btn-sm btn-primary">Urobiť Adminom</button>
                        </form>
                    <?php endif; ?>

                    <?php
                    // --- Tlačidlo "Degradovať administrátora" (iba pre super-administrátora) ---
                    if ($superAdminHandler->can('demote_admin') && $user['rola'] === 'admin' && $user['id_user'] !== ($_SESSION['user']['id'] ?? null)) {
                        echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" onsubmit="return confirm(\'Naozaj chcete degradovať administrátora ID: ' . $user['id_user'] . ' na bežného používateľa?\');">';
                        echo '<input type="hidden" name="user_id" value="' . $user['id_user'] . '">';
                        echo '<input type="hidden" name="action" value="demote_admin">';
                        echo '<button type="submit" class="btn btn-sm btn-warning">Ponižiť Admina</button>';
                        echo '</form>';
                    }

                    // --- Tlačidlo "Vymazať" (iba pre super-administrátora) ---
                    if ($superAdminHandler->can('delete_users') && $user['id_user'] !== ($_SESSION['user']['id'] ?? null)) {
                        echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" onsubmit="return confirm(\'Ste si istí, že chcete odstrániť tohto používateľa?\');">';
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