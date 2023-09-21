<?php
/**
 * Az űrlapok post kéréseit kezelő osztály
 *
 * A FormRequest osztály segítségével kérdezem le a beküldött űrlap adatokat,
 * itt lehetőség lenne biztonsági ellenőrzéseket implementálni, pl. csrf token vizsgálat, stb...
 */
class FormRequest {

    private $data;

    /**
     * Az osztály konstruktor függvénye
     *
     * Az osztály inicializálása során beolvassa az űrlap POST adatokat
     * és elérhetővé teszi őket a többi függvény számára.
     */
    public function __construct() {
        $this->data = $_POST;
    }

    /**
     * Ez a függvény ellenőrzi, hogy az űrlap adatok tartalmaznak-e bármit.
     *
     * @return bool Ha az űrlap adatok nem üresek true, ellenkező esetben false a visszatérési érték.
     */
    public function hasData() {
        return !empty($this->data);
    }

    /**
     * Ez a függvény adja vissza az űrlap adatokat, kulcs alapján, vagy üres értéket, ha a kulcs nem létezik.
     *
     * @param string $key A kulcs, amely alapján lekérdezzük az űrlap adatokat.
     * @param mixed $default Az alapértelmezett érték, amit visszaad, ha a kulcs nem létezik.
     * @return mixed Az űrlap adatokat vagy az alapértelmezett értéket adja vissza.
     */
    public function get($key, $default = null) {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

}
?>
