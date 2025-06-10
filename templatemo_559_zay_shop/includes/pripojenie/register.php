<?php
session_start();

// Правильний шлях до AuthClass.php (з тієї ж папки, що й login.php)
require_once(__DIR__ . '/../../classes/AuthClass.php');

// Використовуємо запропонований namespace для AuthClass
use App\Auth\AuthClass;

// Створюємо екземпляр AuthClass
$auth = new AuthClass();

$sprava = ""; // Для повідомлення

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meno = $_POST['meno'] ?? '';
    $email = $_POST['email'] ?? '';
    $heslo = $_POST['heslo'] ?? '';

    if (!empty($meno) && !empty($email) && !empty($heslo)) {
        // Метод register тепер приймає необов'язковий аргумент $rola, за замовчуванням 'user'
        $vysledok = $auth->register($meno, $email, $heslo); // Використовуємо об'єкт $auth

        if ($vysledok === true) {
            $sprava = "<p style='color:green;text-align:center;'>Registrácia úspešná! <a href='login.php'>Prihlásiť sa</a></p>"; // Шлях до login.php
        } else {
            // htmlspecialchars захищає від XSS
            $sprava = "<p style='color:red;text-align:center;'>" . htmlspecialchars($vysledok) . "</p>";
        }
    } else {
        $sprava = "<p style='color:red;text-align:center;'>Vyplň všetky polia.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrácia</title>
</head>
<body>
<h2 style="text-align:center;">Registrácia</h2>

<?= $sprava ?>

<form method="POST" style="max-width: 400px; margin: auto;">
    <label>Meno:</label><br>
    <input type="text" name="meno" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Heslo:</label><br>
    <input type="password" name="heslo" required><br><br>

    <input type="submit" value="Registrovať sa">
</form>

<p style="text-align:center;"><a href="login.php">Už máš účет? Prihlás sa</a></p>
</body>
</html>