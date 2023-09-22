<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/System/Classes/Database.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/System/Classes/FormRequest.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/System/Classes/Contact.php");

class ContactsController {

    /**
     * Ez a függvény visszaadja az összes kontaktot az adatbázisból, a kontakt osztályon keresztül.
     *
     * @return array Az összes kontaktot tartalmazó tömb.
     * @throws Exception Kivételkezés, ha adatbázis hiba történik a lekérdezés során.
     */
    public function getTable() {

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

    /**
     * Ez a függvény visszaadja a szerkeszteni kívánt kontaktot az adatbázisból, a kontakt osztályon keresztül.
     *
     * @return Contact A kontakt objektum
     * @throws Exception Kivételkezés, ha adatbázis hiba történik a lekérdezés során.
     */
    public function getContactData($id) {

        try {
            // Adatbázis kapcsolat létrehozása
            $database = new Database();

            // Kontaktok lekérése az adatbázisból
            $contact = new Contact($database, $id);

            // A kontaktok visszaadása
            return $contact;
        } catch (Exception $error) {
            throw new Exception("Hiba a kontaktok lekérdezésekor: " . $error->getMessage());
        }
    }

    /**
     * Ez a függvény visszaadja a szerkeszteni kívánt kontakt összes e-mail címét akt osztályon keresztül.
     *
     * @return Contact A kontakt objektum
     * @throws Exception Kivételkezés, ha adatbázis hiba történik a lekérdezés során.
     */
    public function getContactEmails($id) {

        try {
            // Adatbázis kapcsolat létrehozása
            $database = new Database();

            // Kontaktok lekérése az adatbázisból
            $contact = new Contact($database, $id);

            // A kontakt e-mail címeinek lekérése
            $emails = $contact->getEmails();

            // A kontaktok visszaadása
            return $emails;
        } catch (Exception $error) {
            throw new Exception("Hiba a kontaktok lekérdezésekor: " . $error->getMessage());
        }
    }

}

/**
 * A következő kódrészlet dolgozza fel a kontaktok űrlapját
 * A kérés tartalmazó azonosító megléte alapján döntünk, hogy egy meglévő
 * kontakt módosítását hajtjuk e végre vagy pedig egy új kontaktot hozunk létre.
 */

$request = new FormRequest(); // Az űrlap kérés objektumának létrehozása

if ($request->hasData()) { // Ellenőrizzük, hogy érkezett-e adat

    $errors = []; // Hibaüzenetek tárolására szolgáló tömb
    $messages = []; // Általános üzenetek tárolására szolgáló tömb

    try {
        $database = new Database();

        if ($request->get("id")) {
            // Ha van azonosító az űrlapról, akkor egy meglévő kontakt objektumot töltünk be
            $contact = new Contact($database, $request->get("id"));
        } else {
            // Ha nincs azonosító az űrlapon, akkor új kontaktot hozunk létre
            $contact = new Contact($database, null);
        }

        // Űrlapadatok beolvasása és a kontakt tulajdonságainak beállítása
        $contact->name = $request->get("name");
        $contact->phone = $request->get("phone");
        $contact->address = $request->get("address");

        // A beállított adatok ellenőrzése
        $validate = $contact->validateContact($contact);

        if ($validate !== true) {
            $errors = $validate; // Hozzáadjuk a hibákat a hiba tömbhöz
            throw new Exception("Érvénytelen, vagy hiányzó adatok az űrlapon!");
        }

        // Kontakt frissítése vagy létrehozása az adatbázisban
        if ($contact->id) {
            $contact->updateContact($contact);
        } else {
            $contact->createContact($contact);
        }

    } catch (Exception $error) {
        $errors["server"] = $error->getMessage(); // Hozzáadjuk a szerveroldali hibát
    }

    // Ha keletkeznek hibaüzenetek a kód futása során, azt egy session-ben adjuk vissza
    // Ha nincsenek hibaüzenetek, feltételezhető, hogy a művelet sikeres volt, így egy success GET üzenettel térünk vissza
    if (!empty($errors)) {
        session_start();
        $_SESSION["errors"] = $errors; // Hibaüzenetek tárolása a session-ben

        header("Location: /contacts.php");
    }else{
        header("Location: /contacts.php?success=true");
    }

}

/**
 * A következő kódrészlet végrehajtja egy kontakt objektum törlését,
 * egyszerűen a GET metódusban átadott azonosító alapján meghívjuk
 * a Contact osztály deleteContact függvényét.
 */

if(isset($_GET["delete"])){

    $errors = []; // Hibaüzenetek tárolására szolgáló tömb
    $messages = []; // Általános üzenetek tárolására szolgáló tömb

    try {
        $database = new Database();

        $contact = new Contact($database, $_GET["delete"]);

        if(!$contact){
            throw new Exception("A törlésre jelölt rekord nem található!");
        }

        $contact->deleteContact($contact->id);

    } catch (Exception $error) {
        $errors["server"] = $error->getMessage();
    }

    // Ha keletkeznek hibaüzenetek a kód futása során, azt egy session-ben adjuk vissza
    // Ha nincsenek hibaüzenetek, feltételezhető, hogy a művelet sikeres volt, így egy success GET üzenettel térünk vissza
    if (!empty($errors)) {
        session_start();
        $_SESSION["errors"] = $errors; // Hibaüzenetek tárolása a session-ben

        header("Location: /contacts.php");
    }else{
        header("Location: /contacts.php?success=true");
    }
}


?>
