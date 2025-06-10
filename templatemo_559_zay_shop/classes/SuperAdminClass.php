<?php
namespace App\Roles;

require_once(__DIR__ . '/AdminClass.php');

use App\Auth\AuthClass;

class SuperAdminClass extends AdminClass {
    public function __construct(AuthClass $authClass) {
        parent::__construct($authClass);
    }

    public function can($permission) {
        if ($this->userRole === 'superadmin') {
            // Ця умова потрібна, щоб уникнути ситуації,
            // коли superadmin міг би програмно створити іншого superadmin,
            // якщо ви все ще маєте makeUserSuperAdmin() метод десь.
            // Якщо методу makeUserSuperAdmin() взагалі НЕ ІСНУЄ,
            // тоді цей if-блок не є критичним для функціоналу,
            // але є хорошим для чіткості дозволів.
            if ($permission === 'make_user_superadmin') {
                return false;
            }
            return true; // Супер-адмін має всі інші дозволи (включаючи make_user_admin, demote_admin, delete_users)
        }
        return parent::can($permission); // Передаємо в AdminClass
    }
    public function makeUserAdmin($userId) {
        // Перевірка дозволу за допомогою can() СУПЕР-АДМІНА
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
        } catch (\PDOException $e) { // Змінено на \PDOException для глобального namespace
            return "Помилка при зміні ролі: " . $e->getMessage();
        }
    }
    /**
     * Метод для пониження ролі адміністратора до 'user'.
     * Цей метод має бути доступний лише супер-адміну.
     * @param int $userId ID користувача, якого потрібно понизити.
     * @return bool|string True у разі успіху, повідомлення про помилку в іншому випадку.
     */
    public function demoteAdmin(int $userId) { // Залишаємо це оголошення
        // Перевіряємо, чи поточний користувач є супер-адміном (через can)
        if (!$this->can('demote_admin')) { // Перевірка дозволу
            return "Недостатньо прав для пониження адміністраторів.";
        }

        // Перевіряємо, чи користувач, якого намагаються понизити, не є поточним залогіненим супер-адміном
        if ($userId === ($_SESSION['user']['id'] ?? null) && $this->userRole === 'superadmin') {
            return "Не можна понизити самого себе.";
        }

        try {
            // Перевіряємо, чи користувач з таким ID існує і є адміністратором
            $stmt = $this->conn->prepare("SELECT rola FROM users WHERE id_user = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user) {
                return "Користувача з ID {$userId} не знайдено.";
            }

            if ($user['rola'] !== 'admin') {
                return "Користувач ID: {$userId} не є адміністратором.";
            }
            // Додаткова перевірка, щоб супер-адмін не міг понизити іншого супер-адміна через цю функцію.
            // Якщо ви хочете дозволити пониження супер-адміна, вам знадобиться інша логіка.
            if ($user['rola'] === 'superadmin') {
                return "Не можна понизити супер-адміна до користувача безпосередньо. Це вимагає окремої логіки.";
            }


            // Виконуємо оновлення ролі
            $stmt = $this->conn->prepare("UPDATE users SET rola = 'user' WHERE id_user = ? AND rola = 'admin'");
            $stmt->execute([$userId]);

            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                return "Не вдалося понизити адміністратора (можливо, він вже не є адміном).";
            }
        } catch (PDOException $e) {
            return "Помилка бази даних при пониженні: " . $e->getMessage();
        }
    }

    /**
     * Метод для видалення користувача.
     * @param int $userId ID користувача, якого потрібно видалити.
     * @return string|true Повідомлення про помилку або true у разі успіху.
     */
    public function deleteUser($userId) {
        if (!$this->can('delete_users')) {
            return "Недостатньо прав для виконання цієї дії.";
        }

        // Заборона видалення власного облікового запису супер-адміном
        $loggedUser = $this->authClass->getLoggedUser();
        if ($loggedUser && $loggedUser['id'] == $userId) {
            return "Ви не можете видалити власний обліковий запис.";
        }

        try {
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id_user = ?");
            $stmt->execute([$userId]);
            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                return "Користувача з таким ID не знайдено.";
            }
        } catch (PDOException $e) {
            return "Помилка при видаленні користувача: " . $e->getMessage();
        }
    }

    /**
     * Метод для видалення питання.
     * @param int $questionId ID питання, яке потрібно видалити.
     * @return string|true Повідомлення про помилку або true у разі успіху.
     */
    public function deleteQuestion($questionId) {
        if (!$this->can('delete_questions')) {
            return "Недостатньо прав для виконання цієї дії.";
        }

        try {
            $stmt = $this->conn->prepare("DELETE FROM udaje WHERE id_otazky = ?");
            $stmt->execute([$questionId]);
            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                return "Питання з таким ID не знайдено.";
            }
        } catch (PDOException $e) {
            return "Помилка при видаленні питання: " . $e->getMessage();
        }
    }
}