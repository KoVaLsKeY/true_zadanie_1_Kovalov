<?php
namespace App\Roles; // Nový menný priestor pre role

require_once(__DIR__ . '/PermissionClass.php');
require_once(__DIR__ . '/AuthClass.php');
use App\Permissions\PermissionClass;
use App\Auth\AuthClass;

class AdminClass extends PermissionClass {
    public function __construct(AuthClass $authClass) {
        parent::__construct($authClass);
    }

    /**
     * Rozširuje povolenia pre administrátora.
     * @param string $permission Názov povolenia.
     * @return bool True, ak má užívateľ povolenie, false inak.
     */
    public function can($permission) {
        if ($this->userRole === 'admin') {
            switch ($permission) {
                case 'view_all_users':
                case 'view_all_questions':
                case 'edit_questions': // Umožňuje upravovať otázky
                case 'make_user_admin': // Umožňuje robiť užívateľov administrátormi
                    return true;
                default:
                    return false;
            }
        }
        // Ak to nie je administrátor, odovzdáme kontrolu základnej triede
        return parent::can($permission);
    }

    /**
     * Metóda na zmenu roly užívateľa na 'admin'.
     * @param int $userId ID užívateľa, ktorého je potrebné urobiť administrátorom.
     * @return string|true Správa o chybe alebo true v prípade úspechu.
     */
    public function makeUserAdmin($userId) {
        if (!$this->can('make_user_admin')) {
            return "Nedostatočné oprávnenia na vykonanie tejto akcie.";
        }

        // Kontrola, či užívateľ existuje a aká je jeho aktuálna rola
        $stmt = $this->conn->prepare("SELECT rola FROM users WHERE id_user = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            return "Používateľ s takýmto ID nebol nájdený.";
        }

        if ($user['rola'] === 'admin' || $user['rola'] === 'superadmin') {
            return "Tento používateľ je už administrátorom alebo super-administrátorom.";
        }

        try {
            $stmt = $this->conn->prepare("UPDATE users SET rola = 'admin' WHERE id_user = ?");
            $stmt->execute([$userId]);
            return true;
        } catch (PDOException $e) {
            return "Chyba pri zmene roly: " . $e->getMessage();
        }
    }
}