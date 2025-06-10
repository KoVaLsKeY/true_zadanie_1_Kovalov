<?php
namespace App\Auth; // <-- ПЕРЕКОНАЙТЕСЯ, ЩО ЦЕЙ NAMESPACE ВСТАНОВЛЕНО ПРАВИЛЬНО!

// Підключення до конфігурації бази даних
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
     * Реєструє нового користувача в системі.
     * @param string $meno Ім'я користувача.
     * @param string $email Email користувача (унікальний).
     * @param string $heslo Пароль користувача.
     * @param string $rola Початкова роль користувача (за замовчуванням 'user').
     * @return string|true Повідомлення про помилку або true у разі успішної реєстрації.
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
     * Перевіряє облікові дані користувача та здійснює вхід.
     * @param string $email Email користувача.
     * @param string $heslo Пароль користувача.
     * @return bool True у разі успішного входу, false в іншому випадку.
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
     * Вихід користувача з системи.
     */
    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
    }

    /**
     * Перевіряє, чи користувач залогінений.
     * @return bool True, якщо користувач залогінений, false в іншому випадку.
     */
    public function isLoggedIn() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user']);
    }

    /**
     * Повертає дані залогіненого користувача.
     * @return array|null Дані користувача або null, якщо не залогінений.
     */
    public function getLoggedUser() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user'] ?? null;
    }

    /**
     * Метод для отримання з'єднання з базою даних.
     * Цей метод є необхідним для PermissionClass та інших класів,
     * яким потрібен доступ до БД через AuthClass.
     * @return PDO Об'єкт PDO-з'єднання.
     */
    public function getConnection() {
        return $this->conn;
    }

    public function __destruct() {
        $this->conn = null;
    }
}