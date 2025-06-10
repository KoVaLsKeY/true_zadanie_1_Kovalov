<?php
namespace App\Permissions;
// ... ostatné use
use App\Auth\AuthClass; // Nezabudnite pripojiť AuthClass
use PDO; // Pre PDO

class PermissionClass {
    protected $authClass;
    protected $userRole;
    protected $conn; // Pripojenie k DB

    public function __construct(AuthClass $authClass) {
        $this->authClass = $authClass;
        $loggedUser = $this->authClass->getLoggedUser();
        $this->userRole = $loggedUser['rola'] ?? 'guest'; // Nastavíme rolu alebo 'guest' predvolene

        // Skontrolujte, či AuthClass má metódu getConnection()
        // alebo odovzdajte PDO pripojenie iným spôsobom.
        // Predpokladáme, že AuthClass má prístup k $conn
        $this->conn = $this->authClass->getConnection();
    }

    /**
     * Kontroluje, či má aktuálny užívateľ potrebnú rolu.
     * @param string|array $requiredRoles Jedna rola alebo pole rolí, ktoré sú potrebné pre prístup.
     * @return bool True, ak má užívateľ jednu z potrebných rolí, false inak.
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
     * Príklad kontroly konkrétneho povolenia.
     * Túto metódu je možné rozšíriť pre podrobnejšie povolenia.
     * @param string $permission Názov povolenia (napríklad 'edit_posts', 'delete_users').
     * @return bool True, ak má užívateľ povolenie, false inak.
     */
    public function can($permission) {
        // Základná logika povolení pre bežného užívateľa
        switch ($this->userRole) {
            case 'user':
                return in_array($permission, ['view_own_posts']); // Bežný užívateľ môže iba prezerať svoje príspevky
            default:
                return false;
        }
    }

    /**
     * Kontroluje, či je aktuálny užívateľ administrátorom.
     * @return bool
     */
    public function isAdmin() {
        return $this->userRole === 'admin';
    }

    /**
     * Kontroluje, či je aktuálny užívateľ super-administrátorom.
     * @return bool
     */
    public function isSuperAdmin() {
        return $this->userRole === 'superadmin';
    }

    // Metódy na správu užívateľov, ktoré je možné rozšíriť
    public function getAllUsers() {
        if (!$this->conn) {
            // Spracovanie chyby: pripojenie k databáze chýba
            error_log("Chyba: Pripojenie k databáze nebolo nastavené v PermissionClass.");
            return [];
        }
        try {
            $stmt = $this->conn->query("SELECT id_user, meno, email, rola FROM users");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Chyba DB pri získavaní užívateľov: " . $e->getMessage());
            return [];
        }
    }

    // Táto metóda bude predefinovaná v AdminClass a SuperAdminClass
    public function makeUserAdmin($userId) {
        return "Nedostatočné oprávnenia na vykonanie tejto akcie.";
    }

    // Tieto metódy budú pridané do SuperAdminClass
    public function deleteUser($userId) {
        return "Nedostatočné oprávnenia na vykonanie tejto akcie.";
    }

    public function deleteQuestion($questionId) {
        return "Nedostatočné oprávnenia na vykonanie tejto akcie.";
    }
    public function getUserRole() {
        return $this->userRole;
    }
}