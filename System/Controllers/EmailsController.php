<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/System/Classes/Database.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/System/Classes/FormRequest.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/System/Classes/Email.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/System/Classes/Contact.php");

/**
 * A következő kódrészlet dolgozza fel az e-mail hozzáadás űrlapját
 * Az átadott kontakt azonosító és e-mail cím alapján elvégezzük a megflelő vizsgálatokat,
 * sikeres ellenőrzés esetén létrehozzuk az Email címet
 */

$request = new FormRequest(); // Az űrlap kérés objektumának létrehozása

if ($request->hasData()) { // Ellenőrizzük, hogy érkezett-e adat

    $errors = []; // Hibaüzenetek tárolására szolgáló tömb
    $messages = []; // Általános üzenetek tárolására szolgáló tömb

    try {
        $database = new Database();

        // Ha van kontakt azonosító az űrlapról, akkor megpróbáljuk betölteni a kontaktot
        if ($request->get("contact_id")) {
            $contact = new Contact($database, $request->get("contact_id"));
            if(!$contact){
                throw new Exception("A kontakt nem található!");
            }
        }else{
            throw new Exception("Nem érkezett kontakt azonosító!");
        }

        // Ha érkezett e-mail cím. akkor éátrehozunk egy üres Email objektumot.
        if ($request->get("email")) {
            $email = new Email($database, null);
        }else{
            throw new Exception("Nem érkezett e-mail cím!");
        }

        $email->email = $request->get("email");
        $email->contact_id = $contact->id;

        // A beállított adatok ellenőrzése
        $validate = $email->validateEmail($email);

        if ($validate !== true) {
            $errors = $validate; // Hozzáadjuk a hibákat a hiba tömbhöz
            throw new Exception("Érvénytelen, vagy hiányzó adatok az űrlapon!");
        }

        // E-mail létrehozása az adatbázisban
        $email->createEmail($email);

    } catch (Exception $error) {
        $errors["server"] = $error->getMessage(); // Hozzáadjuk a szerveroldali hibát
    }

    // Ha keletkeznek hibaüzenetek a kód futása során, azt egy session-ben adjuk vissza
    // Ha nincsenek hibaüzenetek, feltételezhető, hogy a művelet sikeres volt, így egy success GET üzenettel térünk vissza
    if (!empty($errors)) {
        session_start();
        $_SESSION["errors"] = $errors; // Hibaüzenetek tárolása a session-ben

        header("Location: /contacts.php?edit=".$contact->id);
    }else{
        header("Location: /contacts.php?edit=".$contact->id."&success=true");
    }

}

/**
 * A következő kódrészlet végrehajtja egy Email objektum törlését,
 * egyszerűen a GET metódusban átadott azonosító alapján meghívjuk
 * az Email osztály deleteEmail függvényét.
 *
 * Továbbá a contact_id-t is GET-el kapjujk meg, és annak megfelelően irányítjuk vissza
 * a szerkesztő felületre.
 */

if(isset($_GET["deleteEmail"])){

    $errors = []; // Hibaüzenetek tárolására szolgáló tömb
    $messages = []; // Általános üzenetek tárolására szolgáló tömb

    try {
        $database = new Database();

        $email = new Email($database, $_GET["deleteEmail"]);

        if(!$email){
            throw new Exception("A törlésre jelölt rekord nem található!");
        }

        $email->deleteEmail($email->id);

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
        header("Location: /contacts.php?edit=".$_GET["contactId"]."&success=true");
    }
}


?>
