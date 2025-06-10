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

// --- ZAČIATOK VÝSTUPU NA LADENIE ---
echo '<pre>';
echo '<h3>DEBUG INFO (makeAdmin.php)</h3>';
echo 'Is Logged In: ' . var_export($auth->isLoggedIn(), true) . "\n";
echo '$_SESSION[\'user\'] data: ' . var_export($_SESSION['user'] ?? 'Not set', true) . "\n";
if (isset($_SESSION['user']['rola'])) {
    echo 'Current User Role (from Session): ' . htmlspecialchars($_SESSION['user']['rola']) . "\n";
} else {
    echo 'Current User Role (from Session): Not set' . "\n";
}
echo 'Current User Role (from PermissionClass): ' . var_export($permissions->getUserRole(), true) . "\n"; // Potrebné getUserRole() v PermissionClass
echo 'Can "make_user_admin" (AdminHandler): ' . var_export($adminHandler->can('make_user_admin'), true) . "\n";
echo 'Can "make_user_superadmin" (SuperAdminHandler): ' . var_export($superAdminHandler->can('make_user_superadmin'), true) . "\n";
echo 'Can "demote_admin" (SuperAdminHandler): ' . var_export($superAdminHandler->can('demote_admin'), true) . "\n";
echo 'Can "delete_users" (SuperAdminHandler): ' . var_export($superAdminHandler->can('delete_users'), true) . "\n";
echo '</pre>';
// --- KONIEC VÝSTUPU NA LADENIE ---


// Kontrola prístupu: iba administrátor alebo super-administrátor môžu prezerať túto stránku
if (!$auth->isLoggedIn() || !$permissions->hasRole(['admin', 'superadmin'])) {
    header('Location: ../stranky/index.php'); // Presmerovanie na hlavnú stránku, ak nie sú oprávnenia
    exit;
}

$message = ''; // Premenná na ukladanie správ pre používateľa

// Spracovanie POST požiadaviek (keď je stlačené tlačidlo)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($userId && $action) {
        // Kontrola akcií a príslušných povolení
        switch ($action) {
            case 'make_admin':
                if ($adminHandler->can('make_user_admin')) {
                    $result = $adminHandler->makeUserAdmin($userId);
                    if ($result === true) {
                        $message = "Používateľ ID: {$userId} úspešne bol nastavený ako administrátor.";
                    } else {
                        $message = "Chyba: " . $result;
                    }
                } else {
                    $message = "Chyba: Nedostatočné oprávnenia na pridelenie administrátorov.";
                }
                break;

            case 'make_superadmin':
                if ($superAdminHandler->can('make_user_superadmin')) {
                    $result = $superAdminHandler->makeUserSuperAdmin($userId);
                    if ($result === true) {
                        $message = "Používateľ ID: {$userId} úspešne bol nastavený ako super-administrátor.";
                    } else {
                        $message = "Chyba: " . $result;
                    }
                } else {
                    $message = "Chyba: Nedostatočné oprávnenia na pridelenie super-administrátorov.";
                }
                break;

            case 'demote_admin':
                // Iba super-administrátor môže degradovať iných administrátorov
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
                // Iba super-administrátor môže mazať používateľov
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

// Získanie všetkých používateľov na zobrazenie v tabuľke
$allUsers = $adminHandler->getAllUsers(); // Táto metóda by mala byť dostupná cez PermissionClass (alebo AdminClass)
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
<h2>Správa používateľov</h2>

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
                    // Dostupné pre administrátora alebo super-administrátora, ak užívateľ nie je administrátor alebo super-administrátor
                    if ($adminHandler->can('make_user_admin') && $user['rola'] !== 'admin' && $user['rola'] !== 'superadmin'): ?>
                        <form method="post">
                            <input type="hidden" name="user_id" value="<?= $user['id_user'] ?>">
                            <input type="hidden" name="action" value="make_admin">
                            <button type="submit" class="btn btn-sm btn-primary">Urobiť Adminom</button>
                        </form>
                    <?php endif; ?>

                    <?php
                    // --- Akcie dostupné iba super-administrátorovi ---
                    // Skontrolujeme, či má aktuálny užívateľ povolenie na degradáciu administrátorov (ktoré poskytuje iba super-administrátor)
                    if ($superAdminHandler->can('demote_admin')) { // <-- Postačuje skontrolovať jedno povolenie pre akcie super-administrátora
                        // Tlačidlo "Degradovať administrátora"
                        // Povolené je degradovať iba tých, ktorí sú 'admin', a nie seba
                        if ($user['rola'] === 'admin' && $user['id_user'] !== ($_SESSION['user']['id'] ?? null)) {
                            echo '<form method="post">';
                            echo '<input type="hidden" name="user_id" value="' . $user['id_user'] . '">';
                            echo '<input type="hidden" name="action" value="demote_admin">';
                            echo '<button type="submit" class="btn btn-sm btn-warning">Ponižiť Admina</button>';
                            echo '</form>';
                        }

                        // Tlačidlo "Urobiť SuperAdminom"
                        // Povolené je urobiť super-administrátorom, ak užívateľ nie je super-administrátor a nie je aktuálnym užívateľom
                        if ($user['rola'] !== 'superadmin' && $user['id_user'] !== ($_SESSION['user']['id'] ?? null) && $superAdminHandler->can('make_user_superadmin')) {
                            echo '<form method="post">';
                            echo '<input type="hidden" name="user_id" value="' . $user['id_user'] . '">';
                            echo '<input type="hidden" name="action" value="make_superadmin">';
                            echo '<button type="submit" class="btn btn-sm btn-success">Urobiť SuperAdminom</button>';
                            echo '</form>';
                        }

                        // Tlačidlo "Vymazať"
                        // Povolené je mazať, ak užívateľ nie je aktuálnym užívateľom a má povolenie delete_users
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