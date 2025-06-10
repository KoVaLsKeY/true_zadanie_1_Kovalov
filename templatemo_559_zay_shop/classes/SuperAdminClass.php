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
            // Táto podmienka je potrebná, aby sa predišlo situácii,
            // keď by super-administrátor mohol programovo vytvoriť iného super-administrátora,
            // ak by ste ešte stále mali metódu makeUserSuperAdmin() niekde.
            // Ak metóda makeUserSuperAdmin() VÔBEC NEEXISTUJE,
            // potom tento if-blok nie je pre funkčnosť kritický,
            // ale je dobrý pre jasnosť povolení.
            if ($permission === 'make_user_superadmin') {
                return false;
            }
            return true; // Super-administrátor má všetky ostatné povolenia (vrátane make_user_admin, demote_admin, delete_users)
        }
        return parent::can($permission); // Odovzdáme AdminClass
    }
    public function makeUserAdmin($userId) {
        // Kontrola povolenia pomocou can() SUPER-ADMINISTRÁTORA
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
        } catch (\PDOException $e) { // Zmenené na \PDOException pre globálny menný priestor
            return "Chyba pri zmene roly: " . $e->getMessage();
        }
    }
    /**
     * Metóda na degradáciu roly administrátora na 'user'.
     * Táto metóda by mala byť dostupná iba super-administrátorovi.
     * @param int $userId ID užívateľa, ktorého je potrebné degradovať.
     * @return bool|string True v prípade úspechu, inak chybová správa.
     */
    public function demoteAdmin(int $userId) { // Túto deklaráciu ponechávame
        // Kontrola, či má aktuálny užívateľ rolu super-administrátora (cez can)
        if (!$this->can('demote_admin')) { // Kontrola povolenia
            return "Nedostatočné oprávnenia na degradáciu administrátorov.";
        }

        // Kontrola, či užívateľ, ktorého sa snažia degradovať, nie je aktuálne prihlásený super-administrátor
        if ($userId === ($_SESSION['user']['id'] ?? null) && $this->userRole === 'superadmin') {
            return "Nemôžete degradovať samého seba.";
        }

        try {
            // Kontrola, či užívateľ s takýmto ID existuje a je administrátorom
            $stmt = $this->conn->prepare("SELECT rola FROM users WHERE id_user = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!$user) {
                return "Používateľ s ID {$userId} nebol nájdený.";
            }

            if ($user['rola'] !== 'admin') {
                return "Používateľ ID: {$userId} nie je administrátorom.";
            }
            // Dodatočná kontrola, aby super-administrátor nemohol degradovať iného super-administrátora prostredníctvom tejto funkcie.
            // Ak chcete povoliť degradáciu super-administrátora, budete potrebovať inú logiku.
            if ($user['rola'] === 'superadmin') {
                return "Nemôžete degradovať super-administrátora na užívateľa priamo. Toto vyžaduje samostatnú logiku.";
            }


            // Vykonanie aktualizácie roly
            $stmt = $this->conn->prepare("UPDATE users SET rola = 'user' WHERE id_user = ? AND rola = 'admin'");
            $stmt->execute([$userId]);

            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                return "Nepodarilo sa degradovať administrátora (možno už nie je adminom).";
            }
        } catch (PDOException $e) {
            return "Chyba databázy pri degradácii: " . $e->getMessage();
        }
    }

    /**
     * Metóda na odstránenie užívateľa.
     * @param int $userId ID užívateľa, ktorého je potrebné odstrániť.
     * @return string|true Správa o chybe alebo true v prípade úspechu.
     */
    public function deleteUser($userId) {
        if (!$this->can('delete_users')) {
            return "Nedostatočné oprávnenia na vykonanie tejto akcie.";
        }

        // Zákaz odstránenia vlastného účtu super-administrátorom
        $loggedUser = $this->authClass->getLoggedUser();
        if ($loggedUser && $loggedUser['id'] == $userId) {
            return "Nemôžete odstrániť svoj vlastný účet.";
        }

        try {
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id_user = ?");
            $stmt->execute([$userId]);
            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                return "Používateľ s takýmto ID nebol nájdený.";
            }
        } catch (PDOException $e) {
            return "Chyba pri odstraňovaní užívateľa: " . $e->getMessage();
        }
    }

    /**
     * Metóda na odstránenie otázky.
     * @param int $questionId ID otázky, ktorú je potrebné odstrániť.
     * @return string|true Správa o chybe alebo true v prípade úspechu.
     */
    public function deleteQuestion($questionId) {
        if (!$this->can('delete_questions')) {
            return "Nedostatočné oprávnenia na vykonanie tejto akcie.";
        }

        try {
            $stmt = $this->conn->prepare("DELETE FROM udaje WHERE id_otazky = ?");
            $stmt->execute([$questionId]);
            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                return "Otázka s takýmto ID nebola nájdená.";
            }
        } catch (PDOException $e) {
            return "Chyba pri odstraňovaní otázky: " . $e->getMessage();
        }
    }
}