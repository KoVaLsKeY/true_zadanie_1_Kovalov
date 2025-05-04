<?php
namespace user;
require_once('C:/xampp/htdocs/ProjectProject/templatemo_559_zay_shop/db/dbConfig.php');
use PDO;

class UserClass {
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

    // Реєстрація
    public function register($meno, $email, $heslo) {
        // ← Ось тут, одразу на початку методу:
        // Перевіряємо, чи існує такий email
        $check = $this->conn->prepare("SELECT 1 FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            return "Tento email je už zaregistrovaný.";  // Якщо знайшли — відразу повертаємо помилку
        }
    
        $sql = "INSERT INTO users (meno, email, heslo) VALUES (:meno, :email, :heslo)";
        $stmt = $this->conn->prepare($sql);
    
        try {
            $stmt->execute([
                ':meno' => $meno,
                ':email' => $email,
                ':heslo' => password_hash($heslo, PASSWORD_DEFAULT)
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
    
    
    
    

    // Перевірка логіну
    public function login($email, $heslo) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($heslo, $user['heslo'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'meno' => $user['meno'],
                'email' => $user['email'],
                'rola' => $user['rola']
            ];
            return true;
        }
        return false;
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    public function getLoggedUser() {
        return $_SESSION['user'] ?? null;
    }

    public function __destruct() {
        $this->conn = null;
    }
}
