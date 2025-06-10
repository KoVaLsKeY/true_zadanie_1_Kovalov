<?php
namespace App\Auth; // <-- UISTITE SA, ŽE TENTO NÁZVOVÝ PRIESTOR JE NASTAVENÝ SPRÁVNE!

// Pripojenie k databázovej konfigurácii
require_once(__DIR__ . '/../db/dbConfig.php');

use PDO;
use PDOException;

class AuthClass {
    private $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        try {
            $this->conn = new PDO(
                'mysql:host='.DATABASE['HOST'].';dbname='.DATABASE['DBNAME'].';port='.DATABASE['PORT'],
                DATABASE['USER_NAME'],
                DATABASE['PASSWORD'],
                $options
            );
        } catch (PDOException $e) {
            die("Chyba pripojenia: " . $e->getMessage());
        }
    }

    /**
     * Registruje nového užívateľa v systéme.
     * @param string $meno Meno užívateľa.
     * @param string $email Email užívateľa (jedinečný).
     * @param string $heslo Heslo užívateľa.
     * @param string $rola Počiatočná rola užívateľa (predvolene 'user').
     * @return string|true Správa o chybe alebo true v prípade úspešnej registrácie.
     */
    public function register($meno, $email, $heslo, $rola = 'user') {
        $check = $this->conn->prepare("SELECT 1 FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            return "Tento email je už zaregistrovaný.";
        }

        $sql = "INSERT INTO users (meno, email, heslo, rola) VALUES (:meno, :email, :heslo, :rola)";
        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->execute([
                ':meno' => $meno,
                ':email' => $email,
                ':heslo' => password_hash($heslo, PASSWORD_DEFAULT),
                ':rola' => $rola
            ]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return "Tento email je už zaregistrovaný.";
            } else {
                return "Chyba pri registrácii: " . $e->getMessage();
            }
        }
    }

    /**
     * Kontroluje prihlasovacie údaje užívateľa a vykonáva prihlásenie.
     * @param string $email Email užívateľa.
     * @param string $heslo Heslo užívateľa.
     * @return bool True v prípade úspešného prihlásenia, false inak.
     */
    public function login($email, $heslo) {
        $sql = "SELECT id_user, meno, email, heslo, rola FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($heslo, $user['heslo'])) {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user'] = [
                'id' => $user['id_user'],
                'meno' => $user['meno'],
                'email' => $user['email'],
                'rola' => $user['rola']
            ];
            return true;
        }
        return false;
    }

    /**
     * Odhlásenie užívateľa zo systému.
     */
    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
    }

    /**
     * Kontroluje, či je užívateľ prihlásený.
     * @return bool True, ak je užívateľ prihlásený, false inak.
     */
    public function isLoggedIn() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user']);
    }

    /**
     * Vracia údaje prihláseného užívateľa.
     * @return array|null Údaje užívateľa alebo null, ak nie je prihlásený.
     */
    public function getLoggedUser() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user'] ?? null;
    }

    /**
     * Metóda na získanie pripojenia k databáze.
     * Táto metóda je potrebná pre triedu PermissionClass a iné triedy,
     * ktoré potrebujú prístup k DB cez AuthClass.
     * @return PDO Objekt PDO pripojenia.
     */
    public function getConnection() {
        return $this->conn;
    }

    public function __destruct() {
        $this->conn = null;
    }
}