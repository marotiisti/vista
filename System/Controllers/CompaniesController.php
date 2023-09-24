<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/System/Classes/Database.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/System/Classes/FormRequest.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/System/Classes/Contact.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/System/Classes/Company.php");

class CompaniesController {

    /**
     * Ez a függvény visszaadja az összes céget az adatbázisból, a company osztályon keresztül.
     *
     * @return array Az összes céget tartalmazó tömb.
     * @throws Exception Kivételkezés, ha adatbázis hiba történik a lekérdezés során.
     */
    public function getTable() {

        try {
            // Adatbázis kapcsolat létrehozása
            $database = new Database();

            // Cégek lekérése az adatbázisból
            $companies = Company::getAll($database);

            // A cégek visszaadása
            return $companies;
        } catch (Exception $error) {
            throw new Exception("Hiba a cégek lekérdezésekor: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény visszaadja a szerkeszteni kívánt céget az adatbázisból, a company osztályon keresztül.
     *
     * @return Company A cég objektum
     * @throws Exception Kivételkezés, ha adatbázis hiba történik a lekérdezés során.
     */
    public function getCompanyData($id) {

        try {
            // Adatbázis kapcsolat létrehozása
            $database = new Database();

            // Cég lekérése az adatbázisból
            $company = new Company($database, $id);

            // A kontaktok visszaadása
            return $company;
        } catch (Exception $error) {
            throw new Exception("Hiba a cég lekérdezésekor: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény visszaadja az összes kontaktot az adatbázisból, a kontakt osztályon keresztül.
     *
     * @return array Az összes kontaktot tartalmazó tömb.
     * @throws Exception Kivételkezés, ha adatbázis hiba történik a lekérdezés során.
     */
    public function getContacts() {

        try {
            // Adatbázis kapcsolat létrehozása
            $database = new Database();

            // Kontaktok lekérése az adatbázisból
            $contacts = Contact::getAll($database);

            // A kontaktok visszaadása
            return $contacts;
        } catch (Exception $error) {
            throw new Exception("Hiba a kontaktok lekérdezésekor: " . $error->getMessage());
        }
    }

}

/**
 * A következő kódrészlet dolgozza fel a cégek űrlapját
 * A kérés tartalmazó azonosító megléte alapján döntünk, hogy egy meglévő
 * cég módosítását hajtjuk e végre vagy pedig egy új céget hozunk létre.
 */

$request = new FormRequest(); // Az űrlap kérés objektumának létrehozása

if ($request->hasData()) { // Ellenőrizzük, hogy érkezett-e adat

    $errors = []; // Hibaüzenetek tárolására szolgáló tömb
    $messages = []; // Általános üzenetek tárolására szolgáló tömb

    try {
        $database = new Database();

        if ($request->get("id")) {
            // Ha van azonosító az űrlapról, akkor egy meglévő cég objektumot töltünk be
            $company = new Company($database, $request->get("id"));
        } else {
            // Ha nincs azonosító az űrlapon, akkor új céget hozunk létre
            $company = new Company($database, null);
        }

        // Űrlapadatok beolvasása és a cég tulajdonságainak beállítása
        $company->name = $request->get("name");
        $company->address = $request->get("address");
        $company->contact_id = $request->get("contact_id");

        // A beállított adatok ellenőrzése
        $validate = $company->validateCompany($company);

        if ($validate !== true) {
            $errors = $validate; // Hozzáadjuk a hibákat a hiba tömbhöz
            throw new Exception("Érvénytelen, vagy hiányzó adatok az űrlapon!");
        }

        // Cég frissítése vagy létrehozása az adatbázisban
        if ($company->id) {
            $company->updateCompany($company);
        } else {
            $company->createCompany($company);
        }

    } catch (Exception $error) {
        $errors["server"] = $error->getMessage(); // Hozzáadjuk a szerveroldali hibát
    }

    // Ha keletkeznek hibaüzenetek a kód futása során, azt egy session-ben adjuk vissza
    // Ha nincsenek hibaüzenetek, feltételezhető, hogy a művelet sikeres volt, így egy success GET üzenettel térünk vissza
    if (!empty($errors)) {
        session_start();
        $_SESSION["errors"] = $errors; // Hibaüzenetek tárolása a session-ben

        header("Location: /companies.php");
    }else{
        header("Location: /companies.php?success=true");
    }

}

/**
 * A következő kódrészlet végrehajtja egy cég objektum törlését,
 * egyszerűen a GET metódusban átadott azonosító alapján meghívjuk
 * a Company osztály deleteCoompany függvényét.
 */

if(isset($_GET["delete"])){

    $errors = []; // Hibaüzenetek tárolására szolgáló tömb
    $messages = []; // Általános üzenetek tárolására szolgáló tömb

    try {
        $database = new Database();

        $company = new Company($database, $_GET["delete"]);

        if(!$company){
            throw new Exception("A törlésre jelölt rekord nem található!");
        }

        $company->deleteCompany($company->id);

    } catch (Exception $error) {
        $errors["server"] = $error->getMessage();
    }

    // Ha keletkeznek hibaüzenetek a kód futása során, azt egy session-ben adjuk vissza
    // Ha nincsenek hibaüzenetek, feltételezhető, hogy a művelet sikeres volt, így egy success GET üzenettel térünk vissza
    if (!empty($errors)) {
        session_start();
        $_SESSION["errors"] = $errors; // Hibaüzenetek tárolása a session-ben

        header("Location: /companies.php");
    }else{
        header("Location: /companies.php?success=true");
    }
}


?>
