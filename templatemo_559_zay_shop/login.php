<?php
session_start();
require_once('../classes/userClass.php');
use user\UserClass;

$user = new UserClass();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $heslo = $_POST['heslo'] ?? '';

    if ($user->login($email, $heslo)) {
        header("Location: ../index.php");
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
