<?php
/**
 * Az adatbázis kapcsolatot kezelő osztály.
 *
 * A Database osztályba szerveztem az adatbázis lekérdezések lebonyolítását,
 * a műveletek végrehajtása PDO segítségével történik.
 */
class Database {

    private $pdo;

    /**
     * Az alábbi osztályváltozókban állítsuk be az adatbázis
     * kapcsolódási adatait.
     */
    private $dsn = 'mysql:host=localhost;dbname=feladat'; // A szerver címe és az adatbázis neve
    private $username = 'root'; // Az adatbázis felhasználó neve
    private $password = ''; // A felhasználó jelszava

    /**
     * Az osztály konstruktor függvénye
     *
     * Létrehozza az adatbázis kapcsolatot,
     * az osztályváltozókban megadott DSN, felhasználónév és jelszó alapján.
     *
     * @throws Exception Kivételkezés ha adatbázis kapcsolódási hiba történik.
     */
    public function __construct() {
        try {
            $this->pdo = new PDO($this->dsn, $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $error) {
            throw new Exception("Adatbázis kapcsolódási hiba: " . $error->getMessage());
        }
    }

    /**
     * Select függvény
     *
     * Létrehoz egy előkészített SQL lekérdezést a megadott query alapján,
     * majd végrehajtja azt a paraméterekkel (ha vannak).
     *
     * @param string $query Az SQL lekérdezés.
     * @param array $params A paraméterek (opcionális).
     * @return array Az eredményhalmaz tömb formájában tér vissza.
     * @throws Exception Kivételkezelés, ha adatbázis lekérdezési hiba történik.
     */
    public function select($query, $params = []) {
        try {
            $statement = $this->pdo->prepare($query);
            $statement->execute($params);
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $error) {
            throw new Exception("Adatbázis lekérdezési hiba: " . $error->getMessage());
        }
    }

    /**
     * Insert függvény
     *
     * Beszúrja a megadott adatokat a megadott táblába
     *
     * @param string $table Az adatokat tartalmazó tábla neve.
     * @param array $data Az beszúrandó adatok asszociatív tömb formájában.
     * @return int A beszúrt sorok számát adja vissza.
     * @throws Exception Kivételkezelés, ha adatbázis beszúrási hiba történik.
     */
    public function insert($table, $data) {
        try {
            // Felépítjük az SQL lekérdezést a tábla nevével és az adatokkal
            $columns = implode(", ", array_keys($data));
            $values = ":" . implode(", :", array_keys($data));
            $query = "INSERT INTO $table ($columns) VALUES ($values)";

            // Előkészítjük és végrehajtjuk a lekérdezést
            $statement = $this->pdo->prepare($query);
            $result = $statement->execute($data);

            // Visszaadjuk a beszúrt sorok számát
            return $result ? $statement->rowCount() : 0;
        } catch (PDOException $error) {
            throw new Exception("Adatbázis mentési hiba: " . $error->getMessage());
        }
    }

    /**
     * Update függvény
     *
     * Frissíti a táblát a megadott query szerint.
     *
     * @param string $query Az adatbázis lekérdezés amit futtatni szeretnénk Ez lehet update vagy törlés is
     * @param array $params A feltételhez szükséges paraméterek tömbje.
     * @return int A frissített sorok számát adja vissza.
     * @throws Exception Kivételkezelés, ha adatbázis hiba történik.
     */
    public function action($query, $params = []) {
        try {
            // Az SQL parancs felépítve érkezik a $query változóban, így csak végrehajtjuk azt
            $statement = $this->pdo->prepare($query);
            $result = $statement->execute($params);

            // Visszaadjuk a frissített sorok számát
            return $result ? $statement->rowCount() : 0;
        } catch (PDOException $error) {
            throw new Exception("Adatbázis frissítési hiba: " . $error->getMessage());
        }
    }

}
?>
