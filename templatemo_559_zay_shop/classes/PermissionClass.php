<?php
namespace App\Permissions;
// ... інші use
use App\Auth\AuthClass; // Не забудьте підключити AuthClass
use PDO; // Для PDO

class PermissionClass {
    protected $authClass;
    protected $userRole;
    protected $conn; // З'єднання з БД

    public function __construct(AuthClass $authClass) {
        $this->authClass = $authClass;
        $loggedUser = $this->authClass->getLoggedUser();
        $this->userRole = $loggedUser['rola'] ?? 'guest'; // Встановлюємо роль або 'guest' за замовчуванням

        // Перевірте, що AuthClass має метод getConnection()
        // або передайте PDO з'єднання іншим способом.
        // Припускаємо, що AuthClass має доступ до $conn
        $this->conn = $this->authClass->getConnection();
    }

    /**
     * Перевіряє, чи має поточний користувач необхідну роль.
     * @param string|array $requiredRoles Одна роль або масив ролей, які необхідні для доступу.
     * @return bool True, якщо користувач має одну з необхідних ролей, false в іншому випадку.
     */
    public function hasRole($requiredRoles) {
        if (empty($this->userRole)) {
            return false;
        }

        if (is_string($requiredRoles)) {
            $requiredRoles = [$requiredRoles];
        }

        return in_array($this->userRole, $requiredRoles);
    }

    /**
     * Приклад перевірки конкретного дозволу.
     * Цей метод можна розширити для більш гранульованих дозволів.
     * @param string $permission Назва дозволу (наприклад, 'edit_posts', 'delete_users').
     * @return bool True, якщо користувач має дозвіл, false в іншому випадку.
     */
    public function can($permission) {
        // Базова логіка дозволів для звичайного користувача
        switch ($this->userRole) {
            case 'user':
                return in_array($permission, ['view_own_posts']); // Звичайний користувач може лише переглядати свої пости
            default:
                return false;
        }
    }

    /**
     * Перевіряє, чи є поточний користувач адміністратором.
     * @return bool
     */
    public function isAdmin() {
        return $this->userRole === 'admin';
    }

    /**
     * Перевіряє, чи є поточний користувач супер-адміністратором.
     * @return bool
     */
    public function isSuperAdmin() {
        return $this->userRole === 'superadmin';
    }

    // Методи для керування користувачами, які можуть бути розширені
    public function getAllUsers() {
        if (!$this->conn) {
            // Обробка помилки: з'єднання з базою даних відсутнє
            error_log("Помилка: З'єднання з базою даних не встановлено в PermissionClass.");
            return [];
        }
        try {
            $stmt = $this->conn->query("SELECT id_user, meno, email, rola FROM users");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Помилка БД при отриманні користувачів: " . $e->getMessage());
            return [];
        }
    }

    // Цей метод буде перевизначений в AdminClass та SuperAdminClass
    public function makeUserAdmin($userId) {
        return "Недостатньо прав для виконання цієї дії.";
    }

    // Ці методи будуть додані в SuperAdminClass
    public function deleteUser($userId) {
        return "Недостатньо прав для виконання цієї дії.";
    }

    public function deleteQuestion($questionId) {
        return "Недостатньо прав для виконання цієї дії.";
    }
    public function getUserRole() {
        return $this->userRole;
    }
}