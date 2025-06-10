<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Правильний шлях до AuthClass.php
require_once(__DIR__ . '/../../classes/AuthClass.php');

// Використовуємо запропонований namespace для AuthClass
use App\Auth\AuthClass;
// Створюємо екземпляр AuthClass
$auth = new AuthClass();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $heslo = $_POST['heslo'] ?? '';

    if ($auth->login($email, $heslo)) { // Використовуємо об'єкт $auth
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
</head>
<body>
<h2 style="text-align:center;">Prihlásenie</h2>
<form method="POST" style="max-width: 400px; margin: auto;">
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Heslo:</label><br>
    <input type="password" name="heslo" required><br><br>

    <input type="submit" value="Prihlásiť sa">
</form>
<p style="text-align:center;"><a href="register.php">Nemáš účet? Zaregistruj sa</a></p>
</body>
</html>