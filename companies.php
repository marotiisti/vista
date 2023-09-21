<?php
    require_once($_SERVER['DOCUMENT_ROOT']."/System/Controllers/CompaniesController.php");

    // Hibaüzenetek esetén ki kell olvasnunk a session-ből az errors tömböt.
    session_start();
    $errors = isset($_SESSION["errors"]) ? $_SESSION["errors"] : array();

    // Töröljük a hibaüzeneteket a $_SESSION-ből, hogy csak egyszer jelenjenek meg.
    unset($_SESSION["errors"]);

    // Lekérjük a meglévő cégek listáját és eltároljuk a $tableData tömbben.
    $tableData = (new CompaniesController())->getTable();

    // Lekérjük az összes kontaktot és eltároljuk a $contactData tömbben.
    $contactData = (new CompaniesController())->getContacts();

    /** Ha érkezett paraméter GET metódussal, akkor
     * szerkesztő üzemmódba állítjuk a felületet, és betöltjük
     * a kontaktot azonosító alapján. Alapesetben a felület megtekintő üzzemódban van.
     **/
    $editor  = false;
    if(isset($_GET["edit"])){
        $companyData = (new CompaniesController())->getCompanyData($_GET["edit"]);
        if(!empty($companyData)){
            $editor = true;
        }
    }
?>

<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vista feladat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
</head>
<body class="d-flex flex-column h-100 bg-light">
    <div class="d-flex flex-column align-items-center justify-content-center vh-100 w-100">
        <div class="container">
            <div class="row mb-3">
                <div class="col-12">
                    <a role="button" class="btn btn-light border" href="index.php">Kezdőoldal</a>
                </div>
            </div>
            <div class="row">
                <div class="col-4">
                    <div class="card p-4 rounded border">
                        <h6 class="mb-3"><?= $editor ? 'Kontakt szerkesztése' : 'Kontakt létrehozása' ?></h6>
                        <form action="System/Controllers/CompaniesController.php" method="POST">
                            <input type="hidden" name="id" value="<?= $editor ? $companyData->id : '' ?>">
                            <div class="mb-3">
                                <label for="name" class="form-label">Cégnév</label>
                                <input type="text" class="form-control <?php if(!empty($errors["name"])){ echo "is-invalid"; } ?>" id="name" name="name" autocomplete="off" placeholder="Cégnév" value="<?= $editor ? $companyData->name : '' ?>">
                                <div class="invalid-feedback"><?php if(!empty($errors["name"])){ echo $errors["name"]; } ?></div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Cím</label>
                                <input type="text" class="form-control <?php if(!empty($errors["address"])){ echo "is-invalid"; } ?>" id="address" name="address" autocomplete="off" placeholder="Cím" value="<?= $editor ? $companyData->address : '' ?>">
                                <div class="invalid-feedback"><?php if(!empty($errors["address"])){ echo $errors["address"]; } ?></div>
                            </div>
                            <div class="mb-3">
                                <label for="contact_id" class="form-label">Kontakt</label>
                                <select class="form-select <?php if(!empty($errors["contact_id"])){ echo "is-invalid"; } ?>" aria-label="contact_id" name="contact_id">
                                    <option value="">Kiválasztás</option>
                                    <?php foreach ($contactData as $contact): ?>
                                        <option <?php if(isset($_GET["edit"]) && $companyData->contact_id == $contact["id"]): ?> selected <?php endif ?> value="<?= $contact["id"] ?>"><?= $contact["name"] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback"><?php if(!empty($errors["contact_id"])){ echo $errors["contact_id"]; } ?></div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success"><?= $editor ? 'Mentés' : 'Létrehozás' ?></button>
                            </div>

                            <?php if(isset($_GET["edit"])): ?>
                                <div class="text-center text-secondary mt-3">--- vagy ---</div>
                                <div class="d-grid gap-2 mt-3">
                                    <a role="button" class="btn btn-light border" href="companies.php">Új létrehozása</a>
                                </div>
                            <?php endif; ?>

                            <?php if(!empty($errors["server"])): ?>
                                <div class="my-3 alert alert-warning small" role="alert">
                                    <?= $errors["server"] ?>
                                </div>
                            <?php endif; ?>

                        </form>
                    </div>
                </div>
                <div class="col-8">
                    <?php if(isset($_GET["success"])): ?>
                        <div class="mb-3 alert alert-success small" role="alert">
                            Sikeres művelet!
                        </div>
                    <?php endif; ?>
                    <div class="card p-4 rounded border">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Cégnév</th>
                                    <th scope="col">Cím</th>
                                    <th scope="col">Műveletek</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tableData as $data): ?>

                                    <tr class="<?= ($editor && ($data["id"] == $_GET["edit"])) ? "bg-light fw-bold" : "bg-white" ?>">
                                        <th scope="row"><?= $data["id"] ?></th>
                                        <td><?= $data["name"] ?></td>
                                        <td><?= $data["address"] ?></td>
                                        <td>
                                            <a role="button" href="?edit=<?= $data["id"] ?>" class="btn btn-sm btn-warning">
                                                Szerkesztés
                                            </a>
                                            <a role="button" href="System/Controllers/CompaniesController.php?delete=<?= $data["id"] ?>" class="btn btn-sm btn-danger ms-2">
                                                Eltávolítás
                                            </a>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
