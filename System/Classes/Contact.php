<?php
class Contact {

    public $id;
    public $name;
    public $phone;
    public $address;

    /**
     * Az osztály konstruktor függvénye
     *
     * Átadjuk a Database osztályt, és a kontakt azonosítót.
     * Amennyiben érkezik azonosító, a inicializálás a loadContactData segédfüggvényben történik.
     * Ha azonosító nélkül kerül példányosításra az osztály, üres adatokkal töltjük fel.
     *
     * @param int|null $id A rekord azonosítója az adatbázisban, vagy null érték üres objektum létrehozásához
     * @param Database $db Az adatbázis osztály példánya
     */
    public function __construct(Database $db, $id) {
        $this->db = $db;
        if(!is_null($id)){
            $this->loadContactData($id);
        }else{
            $this->id = null;
            $this->name = null;
            $this->phone = null;
            $this->address = null;
        }
    }

    /**
     * Inicializáló segédfüggvény
     *
     * Ez a függvény lekérdezi az adatbázisból a megadott kontakt azonosítóhoz
     * tartozó adatokat, és beállítja az osztály attribútumait.
     *
     * @param int $id A rekord azonosítója az adatbázisban
     * @throws Exception Kivételkezés, ha adatbázis kapcsolódási vagy lekérdezési hiba történik,
     * vagy ha nincs találat az adatbázisban az adott ID-re.
     */
    private function loadContactData($id) {
        try {
            $query = "SELECT id, name, phone, address FROM contacts WHERE id = :id";
            $result = $this->db->select($query, [":id" => $id]);

            if (!empty($result)) {
                $contact = $result[0];
                $this->id = $contact["id"];
                $this->name = $contact["name"];
                $this->phone = $contact["phone"];
                $this->address = $contact["address"];
            } else {
                throw new Exception("Nincs találat az adatbázisban.");
            }
        } catch (Exception $error) {
            throw new Exception("Hiba a kontakt adat betöltésekor: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény visszaadja az összes kontaktot az adatbázisból.
     *
     * @param Database $db Az adatbázis osztály példánya.
     * @return array Az összes kontaktot tartalmazó tömb.
     * @throws Exception Kivételkezelés, ha adatbázis lekérdezési hiba történik.
     */
    public static function getAll(Database $db) {
        try {
            $query = "SELECT * FROM contacts";
            $contacts = $db->select($query);

            return $contacts;
        } catch (Exception $error) {
            throw new Exception("Hiba a kontaktok lekérdezésekor: " . $error->getMessage());
        }
    }


    /**
     * Ez a függvény elmenti a létrehozott kontakt objektumot az adatbázisba.
     *
     * @param Contact $contact A mentésre küldött kontakt objektum.
     * @throws Exception Kivételkezés, ha adatbázis kapcsolódási vagy lekérdezési hiba történik,
     * vagy ha nincs találat az adatbázisban az adott azonosítóra vonatkozóan.
     */
    public function createContact(Contact $contact) {
        try {
            $dataToInsert = [
                "name" => $contact->getName(),
                "phone" => $contact->getPhone(),
                "address" => $contact->getAddress(),
            ];

            $insertedRows = $this->db->insert("contacts", $dataToInsert);

            if ($insertedRows > 0) {
                return true;
            } else {
                throw new Exception("Hiba történt az adatbázisba való beszúrás során.");
            }
        } catch (Exception $error) {
            throw new Exception("Hiba a kontakt létrehozása során: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény frissíti a kontakt adatait az adatbázisban, a megadott objektum adataival.
     *
     * @param Contact $contact A frissítendő kontakt objektum.
     * @return bool Sikeres frissítés esetén true, ellenkező esetben false a visszatérési érték.
     * @throws Exception Kivételkezelés, ha adatbázis hiba történik.
     */
    public function updateContact(Contact $contact) {
        try {
            $query = "UPDATE contacts SET name = :name, phone = :phone, address = :address WHERE id = :id";
            $params = [
                ":id" => $contact->getId(),
                ":name" => $contact->getName(),
                ":phone" => $contact->getPhone(),
                ":address" => $contact->getAddress(),
            ];

            $updatedRows = $this->db->action($query, $params);

            if ($updatedRows > 0) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $error) {
            throw new Exception("Hiba a kontakt frissítésekor: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény Törli a kontaktot az adatbázisból a megadott azonosító alapján.
     *
     * @param int $id A törlésre jelölt kontakt azonosítója.
     * @return bool Sikeres törlés esetén true, ellenkező esetben false a visszatérési érték.
     * @throws Exception Kivételkezelés, ha adatbázis hiba történik.
     */
    public function deleteContact($id) {
        try {
            $query = "DELETE FROM contacts WHERE id = :id";
            $params = [":id" => $id];

            $deletedRows = $this->db->action($query, $params);

            if ($deletedRows > 0) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $error) {
            throw new Exception("Hiba a kontakt törlésekor: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény beállítja a kontakt azonosítóját.
     *
     * @return int A kontakt azonosítója
     */
    public function setId($id){
        $this->id = $id;
    }

    /**
     * Ez a függvény beállítja a kontakt nevét.
     *
     * @return string A kontakt neve
     */
    public function setName($name){
        $this->name = $name;
    }

    /**
     * Ez a függvény beállítja a kontakt telefonszámát.
     *
     * @return string A kontakt telefonszáma
     */
    public function setPhone($phone){
        $this->phone = $phone;
    }

    /**
     * Ez a függvény beállítja a kontakt címét.
     *
     * @return string A kontakt címe
     */
    public function setAddress($address){
        $this->address = $address;
    }

    /**
     * Ez a függvény visszaadja a kontakt azonosítót.
     *
     * @return int A kontakt azonosítója
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Ez a függvény visszaadja a kontakt nevét.
     *
     * @return string A kontakt neve
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Ez a függvény visszaadja a kontakt telefonszámát.
     *
     * @return string A kontakt telefonszáma
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * Ez a függvény visszaadja a kontakt címét.
     *
     * @return string A kontakt címe
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * Ez a függvény ellenőrzi a kontakt tulajdonságait és visszaadja a vizsgálat eredményét.
     *
     * @param Contact $contact A vizsgálandó kontakt objektum.
     * @return bool|array Ha a tulajdonságok megfelelnek a kritériumoknak, true értékkel tér vissza
     *                    Ellenkező esetben visszaadja a hibaüzeneteket tartalmazó tömböt.
     */
    public function validateContact(Contact $contact) {
        $errors = [];

        if (empty($contact->name)) {
            $errors["name"] = "A név megadása kötelező.";
        }

        if (empty($contact->phone)) {
            $errors["phone"] = "A telefonszám megadása kötelező.";
        }

        if (empty($contact->address)) {
            $errors["address"] = "A cím megadása kötelező.";
        }

        return empty($errors) ? true : $errors;
    }

}
?>
