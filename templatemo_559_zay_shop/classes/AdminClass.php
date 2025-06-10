<?php
namespace App\Roles; // Новий namespace для ролей

require_once(__DIR__ . '/PermissionClass.php');
require_once(__DIR__ . '/AuthClass.php');
use App\Permissions\PermissionClass;
use App\Auth\AuthClass;

class AdminClass extends PermissionClass {
    public function __construct(AuthClass $authClass) {
        parent::__construct($authClass);
    }

    /**
     * Розширює дозволи для адміністратора.
     * @param string $permission Назва дозволу.
     * @return bool True, якщо користувач має дозвіл, false в іншому випадку.
     */
    public function can($permission) {
        if ($this->userRole === 'admin') {
            switch ($permission) {
                case 'view_all_users':
                case 'view_all_questions':
                case 'edit_questions': // Дозволяє редагувати питання
                case 'make_user_admin': // Дозволяє робити користувачів адмінами
                    return true;
                default:
                    return false;
            }
        }
        // Якщо це не адмін, передаємо перевірку базовому класу
        return parent::can($permission);
    }

    /**
     * Метод для зміни ролі користувача на 'admin'.
     * @param int $userId ID користувача, якого потрібно зробити адміністратором.
     * @return string|true Повідомлення про помилку або true у разі успіху.
     */
    public function makeUserAdmin($userId) {
        if (!$this->can('make_user_admin')) {
            return "Недостатньо прав для виконання цієї дії.";
        }

        // Перевіряємо, чи існує користувач і його поточна роль
        $stmt = $this->conn->prepare("SELECT rola FROM users WHERE id_user = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return "Користувача з таким ID не знайдено.";
        }

        if ($user['rola'] === 'admin' || $user['rola'] === 'superadmin') {
            return "Цей користувач вже є адміністратором або супер-адміністратором.";
        }

        try {
            $stmt = $this->conn->prepare("UPDATE users SET rola = 'admin' WHERE id_user = ?");
            $stmt->execute([$userId]);
            return true;
        } catch (PDOException $e) {
            return "Помилка при зміні ролі: " . $e->getMessage();
        }
    }
}