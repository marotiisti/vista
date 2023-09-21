<?php
class Company {

    public $id;
    public $name;
    public $address;
    public $contact_id;

    public $contact_name; // Többtáblás lekérdezésből értéket kapó tulajdonság

    /**
     * Az osztály konstruktor függvénye
     *
     * Átadjuk a Database osztályt, és a clg azonosítót.
     * Amennyiben érkezik azonosító, a inicializálás a loadCompanyData segédfüggvényben történik.
     * Ha azonosító nélkül kerül példányosításra az osztály, üres adatokkal töltjük fel.
     *
     * @param int|null $id A rekord azonosítója az adatbázisban, vagy null érték üres objektum létrehozásához
     * @param Database $db Az adatbázis osztály példánya
     */
    public function __construct(Database $db, $id) {
        $this->db = $db;
        if(!is_null($id)){
            $this->loadCompanyData($id);
        }else{
            $this->id = null;
            $this->name = null;
            $this->address = null;
            $this->contact_id = null;
            $this->contact_name = null;
        }
    }

    /**
     * Inicializáló segédfüggvény
     *
     * Ez a függvény lekérdezi az adatbázisból a megadott cég azonosítóhoz
     * tartozó adatokat, és beállítja az osztály attribútumait.
     *
     * @param int $id A rekord azonosítója az adatbázisban
     * @throws Exception Kivételkezés, ha adatbázis kapcsolódási vagy lekérdezési hiba történik,
     * vagy ha nincs találat az adatbázisban az adott ID-re.
     */
    private function loadCompanyData($id) {
        try {
            $query = "SELECT companies.*, contacts.name AS contact_name FROM companies LEFT JOIN contacts ON companies.contact_id = companies.id WHERE companies.id = :id";
            $result = $this->db->select($query, [":id" => $id]);

            if (!empty($result)) {
                $company = $result[0];
                $this->id = $company["id"];
                $this->name = $company["name"];
                $this->address = $company["address"];
                $this->contact_id = $company["contact_id"];
                $this->contact_name = $company["contact_name"];
            } else {
                throw new Exception("Nincs találat az adatbázisban.");
            }
        } catch (Exception $error) {
            throw new Exception("Hiba a cég adat betöltésekor: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény visszaadja az összes céget az adatbázisból.
     *
     * @param Database $db Az adatbázis osztály példánya.
     * @return array Az összes céget tartalmazó tömb.
     * @throws Exception Kivételkezelés, ha adatbázis lekérdezési hiba történik.
     */
    public static function getAll(Database $db) {
        try {
            $query = "SELECT companies.*, contacts.name AS contact_name FROM companies LEFT JOIN contacts ON companies.contact_id = companies.id";
            $companies = $db->select($query);

            return $companies;
        } catch (Exception $error) {
            throw new Exception("Hiba a cégek lekérdezésekor: " . $error->getMessage());
        }
    }


    /**
     * Ez a függvény elmenti a létrehozott cég objektumot az adatbázisba.
     *
     * @param Company $company A mentésre küldött cég objektum.
     * @throws Exception Kivételkezés, ha adatbázis kapcsolódási vagy lekérdezési hiba történik,
     * vagy ha nincs találat az adatbázisban az adott azonosítóra vonatkozóan.
     */
    public function createCompany(Company $company) {
        try {
            $dataToInsert = [
                "name" => $company->getName(),
                "address" => $company->getAddress(),
                "contact_id" => $company->getContactId(),
            ];

            $insertedRows = $this->db->insert("companies", $dataToInsert);

            if ($insertedRows > 0) {
                return true;
            } else {
                throw new Exception("Hiba történt az adatbázisba való beszúrás során.");
            }
        } catch (Exception $error) {
            throw new Exception("Hiba a cég létrehozása során: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény frissíti a cég adatait az adatbázisban, a megadott objektum adataival.
     *
     * @param Company $company A frissítendő cég objektum.
     * @return bool Sikeres frissítés esetén true, ellenkező esetben false a visszatérési érték.
     * @throws Exception Kivételkezelés, ha adatbázis hiba történik.
     */
    public function updateCompany(Company $company) {
        try {
            $query = "UPDATE companies SET name = :name, address = :address, contact_id = :contact_id WHERE id = :id";
            $params = [
                ":id" => $company->getId(),
                ":name" => $company->getName(),
                ":address" => $company->getAddress(),
                ":contact_id" => $company->getContactId(),
            ];

            $updatedRows = $this->db->action($query, $params);

            if ($updatedRows > 0) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $error) {
            throw new Exception("Hiba a cég frissítésekor: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény Törli a céget az adatbázisból a megadott azonosító alapján.
     *
     * @param int $id A törlésre jelölt cég azonosítója.
     * @return bool Sikeres törlés esetén true, ellenkező esetben false a visszatérési érték.
     * @throws Exception Kivételkezelés, ha adatbázis hiba történik.
     */
    public function deleteCompany($id) {
        try {
            $query = "DELETE FROM companies WHERE id = :id";
            $params = [":id" => $id];

            $deletedRows = $this->db->action($query, $params);

            if ($deletedRows > 0) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $error) {
            throw new Exception("Hiba a cég törlésekor: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény beállítja a cég azonosítóját.
     *
     * @return int A cég azonosítója
     */
    public function setId($id){
        $this->id = $id;
    }

    /**
     * Ez a függvény beállítja a cég nevét.
     *
     * @return string A cég neve
     */
    public function setName($name){
        $this->name = $name;
    }

    /**
     * Ez a függvény beállítja a cég címét.
     *
     * @return string A cég címe
     */
    public function setAddress($address){
        $this->address = $address;
    }

    /**
     * Ez a függvény beállítja a céghez kapcsolódó kontakt azonosítóját.
     *
     * @return int A kontakt azonosítója
     */
    public function setContactId($contactId){
        $this->contact_id = $contactId;
    }

    /**
     * Ez a függvény beállítja a céghez kapcsolódó kontakt nevét.
     *
     * @return string A kontakt neve
     */
    public function setContactName($contactName){
        $this->contact_name = $contactName;
    }

    /**
     * Ez a függvény visszaadja a cég azonosítót.
     *
     * @return int A cég azonosítója
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Ez a függvény visszaadja a cég nevét.
     *
     * @return string A cég neve
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Ez a függvény visszaadja a cég címét.
     *
     * @return string A cég címe
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * Ez a függvény visszaadja a céghez kapcsolódó kontakt azonosítóját.
     *
     * @return int A kontakt azonosítója
     */
    public function getContactId() {
        return $this->contact_id;
    }

    /**
     * Ez a függvény visszaadja a céghez kapcsolódó kontakt nevét.
     *
     * @return string A kontakt neve
     */
    public function getContactName() {
        return $this->contact_name;
    }

    /**
     * Ez a függvény ellenőrzi a cég tulajdonságait és visszaadja a vizsgálat eredményét.
     *
     * @param Company $company A vizsgálandó company objektum.
     * @return bool|array Ha a tulajdonságok megfelelnek a kritériumoknak, true értékkel tér vissza
     *                    Ellenkező esetben visszaadja a hibaüzeneteket tartalmazó tömböt.
     */
    public function validateCompany(Company $company) {
        $errors = [];

        if (empty($company->name)) {
            $errors["name"] = "A név megadása kötelező.";
        }

        if (empty($company->address)) {
            $errors["address"] = "A cím megadása kötelező.";
        }

        if (empty($company->contact_id)) {
            $errors["contact_id"] = "A kontakt kiválasztása kötelező.";
        }

        return empty($errors) ? true : $errors;
    }

}
?>
