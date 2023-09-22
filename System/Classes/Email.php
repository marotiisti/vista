<?php
class Email {

    public $id;
    public $email;
    public $contact_id;

    /**
     * Az osztály konstruktor függvénye
     *
     * Átadjuk a Database osztályt, és az email azonosítót.
     * Amennyiben érkezik azonosító, a inicializálás a loadEmailData segédfüggvényben történik.
     * Ha azonosító nélkül kerül példányosításra az osztály, üres adatokkal töltjük fel.
     *
     * @param int|null $id A rekord azonosítója az adatbázisban, vagy null érték üres objektum létrehozásához
     * @param Database $db Az adatbázis osztály példánya
     */
    public function __construct(Database $db, $id) {
        $this->db = $db;
        if(!is_null($id)){
            $this->loadEmailData($id);
        }else{
            $this->id = null;
            $this->email = null;
            $this->contact_id = null;
        }
    }

    /**
     * Inicializáló segédfüggvény
     *
     * Ez a függvény lekérdezi az adatbázisból a megadott email azonosítóhoz
     * tartozó adatokat, és beállítja az osztály attribútumait.
     *
     * @param int $id A rekord azonosítója az adatbázisban
     * @throws Exception Kivételkezés, ha adatbázis kapcsolódási vagy lekérdezési hiba történik,
     * vagy ha nincs találat az adatbázisban az adott ID-re.
     */
    private function loadEmailData($id) {
        try {
            $query = "SELECT * FROM emails WHERE id = :id";
            $result = $this->db->select($query, [":id" => $id]);

            if (!empty($result)) {
                $company = $result[0];
                $this->id = $company["id"];
                $this->email = $company["email"];
                $this->contact_id = $company["contact_id"];
            } else {
                throw new Exception("Nincs találat az adatbázisban.");
            }
        } catch (Exception $error) {
            throw new Exception("Hiba az email cím betöltésekor: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény visszaadja az összes e-mail címet az adatbázisból.
     *
     * @param Database $db Az adatbázis osztály példánya.
     * @return array Az összes e-mailt tartalmazó tömb.
     * @throws Exception Kivételkezelés, ha adatbázis lekérdezési hiba történik.
     */
    public static function getAll(Database $db) {
        try {
            $query = "SELECT * FROM emails";
            $companies = $db->select($query);

            return $companies;
        } catch (Exception $error) {
            throw new Exception("Hiba az e-mailek lekérdezésekor: " . $error->getMessage());
        }
    }


    /**
     * Ez a függvény elmenti a létrehozott e-mail objektumot az adatbázisba.
     *
     * @param Email $email A mentésre küldött cég objektum.
     * @throws Exception Kivételkezés, ha adatbázis kapcsolódási vagy lekérdezési hiba történik,
     * vagy ha nincs találat az adatbázisban az adott azonosítóra vonatkozóan.
     */
    public function createEmail(Email $email) {
        try {
            $dataToInsert = [
                "email" => $email->getEmail(),
                "contact_id" => $email->getContactId(),
            ];

            $insertedRows = $this->db->insert("emails", $dataToInsert);

            if ($insertedRows > 0) {
                return true;
            } else {
                throw new Exception("Hiba történt az adatbázisba való beszúrás során.");
            }
        } catch (Exception $error) {
            throw new Exception("Hiba az e-mail cím létrehozása során: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény frissíti az e-mail cím adatait az adatbázisban, a megadott objektum adataival.
     *
     * @param Email $email A frissítendő cég objektum.
     * @return bool Sikeres frissítés esetén true, ellenkező esetben false a visszatérési érték.
     * @throws Exception Kivételkezelés, ha adatbázis hiba történik.
     */
    public function updateEmail(Email $email) {
        try {
            $query = "UPDATE emails SET email = :email, contact_id = :contact_id WHERE id = :id";
            $params = [
                ":id" => $email->getId(),
                ":email" => $email->getEmail(),
                ":contact_id" => $email->getContactId(),
            ];

            $updatedRows = $this->db->action($query, $params);

            if ($updatedRows > 0) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $error) {
            throw new Exception("Hiba az e-mail cím frissítésekor: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény Törli az e-mail címet az adatbázisból a megadott azonosító alapján.
     *
     * @param int $id A törlésre jelölt email azonosítója.
     * @return bool Sikeres törlés esetén true, ellenkező esetben false a visszatérési érték.
     * @throws Exception Kivételkezelés, ha adatbázis hiba történik.
     */
    public function deleteEmail($id) {
        try {
            $query = "DELETE FROM emails WHERE id = :id";
            $params = [":id" => $id];

            $deletedRows = $this->db->action($query, $params);

            if ($deletedRows > 0) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $error) {
            throw new Exception("Hiba az e-mail cím törlésekor: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény ellenőrzi, hogy az e-mail cím egyedi-e
     *
     * @param Email $email A vizsgálandó e-mail objektum.
     * @return bool Ha az e-mail cím egyedi akkor true, egyébként false a visszatérési érték
     * @throws Exception Kivételkezelés, ha adatbázis lekérdezési hiba történik.
     */
    private function isUnique(Email $email) {
        try {
            $query = "SELECT id FROM emails WHERE email = :email"; // Szerkesztés lehetőségét meghagyva, vizsgálahatjuk a későbbiekben: AND contact_id <> :contact_id
            $params = [
                ":email" => $email->email,
                //":contact_id" => $email->contact_id,
            ];

            $emails = $this->db->select($query, $params);

            if(empty($emails)){
                return true;
            }else{
                return false;
            }

        } catch (Exception $error) {
            throw new Exception("Hiba a vizsgálat során: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény beállítja az e-mail azonosítóját.
     *
     * @return int Az e-mail azonosítója
     */
    public function setId($id){
        $this->id = $id;
    }

    /**
     * Ez a függvény beállítja az e-mail cím értékét.
     *
     * @return string Az e-mail cím értéke
     */
    public function setEmail($email){
        $this->email = $email;
    }

    /**
     * Ez a függvény beállítja az e-mail címhez kapcsolódó kontakt azonosítóját.
     *
     * @return int A kontakt azonosítója
     */
    public function setContactId($contactId){
        $this->contact_id = $contactId;
    }

    /**
     * Ez a függvény visszaadja az e-mail azonosítót.
     *
     * @return int Az e-mail azonosítója
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Ez a függvény visszaadja az e-mail értékét.
     *
     * @return string Az e-mail értéke
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Ez a függvény visszaadja az e-mail címhez kapcsolódó kontakt azonosítóját.
     *
     * @return int A kontakt azonosítója
     */
    public function getContactId() {
        return $this->contact_id;
    }

    /**
     * Ez a függvény ellenőrzi az e-mail cím tulajdonságait és visszaadja a vizsgálat eredményét.
     * Itt vizsgáljuk hogy, az e-mail cím egyedi e, az isUnique függvénnyel.
     * Illetve azt is, hogy az e-mail cím formátuma megfelelő-e.
     *
     * @param Email $email A vizsgálandó company objektum.
     * @return bool|array Ha a tulajdonságok megfelelnek a kritériumoknak, true értékkel tér vissza
     *                    Ellenkező esetben visszaadja a hibaüzeneteket tartalmazó tömböt.
     */
    public function validateEmail(Email $email) {
        $errors = [];

        if (!filter_var($email->email, FILTER_VALIDATE_EMAIL)) {
            $errors["email"] = "Az e-mail cím formátuma helytelen.";
        }

        if(!$this->isUnique($email)){
            $errors["email"] = "Már létezik ilyen e-mail cím a rendszerben.";
        }

        if (empty($this->contact_id)) {
            $errors["email"] = "A kontakt kiválasztása kötelező.";
        }

        return empty($errors) ? true : $errors;
    }

}
?>
