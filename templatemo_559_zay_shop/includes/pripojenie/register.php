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
    <link rel="stylesheet" href="../../assets_sablon/css/vstup.css">
    <title>Registrácia</title>
</head>
<body>
<?php if (isset($sprava) && $sprava !== ''): ?>
    <div class="message-box">
        <?= $sprava ?>
    </div>
<?php endif; ?>

<form method="POST">
    <h2>Registrácia</h2> <label for="meno">Meno:</label><br>
    <input type="text" id="meno" name="meno" required><br> <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required><br> <label for="heslo">Heslo:</label><br>
    <input type="password" id="heslo" name="heslo" required><br> <input type="submit" value="Registrovať sa">
    <p><a href="login.php">Už máš účet? Prihlás sa</a></p>
</form>
</body>
</html>