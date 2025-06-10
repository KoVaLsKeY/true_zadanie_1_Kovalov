<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Správna cesta k AuthClass.php
require_once(__DIR__ . '/../../classes/AuthClass.php');

// Používame navrhnutý menný priestor pre AuthClass
use App\Auth\AuthClass;
// Vytvoríme inštanciu AuthClass
$auth = new AuthClass();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $heslo = $_POST['heslo'] ?? '';

    if ($auth->login($email, $heslo)) { // Používame objekt $auth
        header("Location: ../../stranky/index.php");
        exit;
    } else {
        echo "<p style='color:red;text-align:center;'>Nesprávny email alebo heslo.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Prihlásenie</title>
    <link rel="stylesheet" href="../../assets_sablon/css/vstup.css">
    <link rel="stylesheet" href="../../assets_sablon/css/backButton.css">
</head>
<body>
<a href="../../stranky/index.php" class="back-button">← Späť na stránku</a>

<form method="POST">
    <h2>Prihlásenie</h2>
    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required><br>

    <label for="heslo">Heslo:</label><br>
    <input type="password" id="heslo" name="heslo" required><br>

    <input type="submit" value="Prihlásiť sa">
    <p><a href="register.php">Nemáš účet? Zaregistruj sa</a></p>
</form>
</body>
</html>