<?php
session_start();

// Správna cesta k AuthClass.php (z rovnakého priečinka ako login.php)
require_once(__DIR__ . '/../../classes/AuthClass.php');

// Používame navrhnutý menný priestor pre AuthClass
use App\Auth\AuthClass;

// Vytvoríme inštanciu AuthClass
$auth = new AuthClass();

$sprava = ""; // Pre správu

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meno = $_POST['meno'] ?? '';
    $email = $_POST['email'] ?? '';
    $heslo = $_POST['heslo'] ?? '';

    if (!empty($meno) && !empty($email) && !empty($heslo)) {
        // Metóda register teraz akceptuje nepovinný argument $rola, predvolene 'user'
        $vysledok = $auth->register($meno, $email, $heslo); // Používame objekt $auth

        if ($vysledok === true) {
            $sprava = "<p style='color:green;text-align:center;'>Registrácia úspešná! <a href='login.php'>Prihlásiť sa</a></p>"; // Cesta k login.php
        } else {
            // htmlspecialchars chráni pred XSS
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
    <title>Prihlásenie</title>
    <link rel="stylesheet" href="../../assets_sablon/css/vstup.css">
    <link rel="stylesheet" href="../../assets_sablon/css/backButton.css">
</head>
<body>
<a href="../../stranky/index.php" class="back-button">← Späť na stránku</a>

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