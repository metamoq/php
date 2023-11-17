<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include("Services/PhotoService.php");
if (!isset($_COOKIE["UserId"])) {
    header("Refresh:0;url=index.php");
}
if (isset($_POST["UploadPhotoModal"])) {
    unset($_SESSION["AlbumId"]);
}
if (isset($_POST["CreateAlbumModal"])) {
    unset($_SESSION["AlbumId"]);
    $errors = [];
}

if (isset($_POST["btnUpdateAlbum"])) {
    $album = UpdateAlbum($_SESSION["AlbumId"], $_POST["title"], $_POST["description"], $_COOKIE["UserId"]);
    if ($album == null) {
        echo '
                <script>alert("Album Update failed.")</script>
            ';
    } else {
        unset($_SESSION["AlbumId"]);
        header("Refresh:0;url=Album.php");
    }
}

if (isset($_POST["btnCreateAlbum"])) {
    unset($_SESSION["AlbumId"]);
    $album = CreateAlbum($_POST["title"], $_POST["description"], $_COOKIE["UserId"]);
    if ($album == null) {
        echo '
                <script>alert("Album creation failed.")</script>
            ';
    } else {
        header("Refresh:0;url=Album.php");
    }
}

try {
    if (isset($_POST["UploadPhotoModal"])) {
        if (isset($_FILES["images"]) && is_array($_FILES["images"])) {
            $albumId = $_POST["SelectedAlbum"] ?? null;
            $description = $_POST["description"] ?? "";
            $uploadedBy = $_COOKIE["UserId"];
            $uploadedPhotos = UploadPhotos($_FILES["images"], $uploadedBy, $albumId, $description);

            if ($uploadedPhotos === null) {
                $errors[] = "Ошибка при загрузке фотографий. Пожалуйста, попробуйте снова.";
            }
        } else {
            $errors[] = "Не удалось загрузить файлы.";
        }
    }
} catch (Exception $e) {
    $errors[] = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Edit</title>
    <!-- CSS only -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <style>
        .btn-custom-dark {
            background-color: #1E404A;
            border-color: #1E404A;
            color: #ffffff;
        }

        .btn-custom-back {
            background-color: #1E404A;
            border-color: #1E404A;
            color: #ffffff;
            text-decoration: none; /* Убедились, что ссылка не будет подчеркнута */
            padding: 10px 20px; /* Строим отступы по выбору */
            display: inline-block; /* Делаем кнопку блочным элементом */
        }

        .btn-custom-back:hover {
            background-color: #0D2B32;
            border-color: #0D2B32;
            color: #ffffff;
        }

    </style>
</head>

<body>
<?php include("Navbar.php") ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <a href="Album.php" class="btn btn-custom-back">Назад</a>
            <div class="card my-5">
                <!-- Форма изменения альбома -->
                <?php
                if (isset($_POST["UpdateAlbumModal"]) || isset($_SESSION["AlbumId"])) {
                    if (isset($_POST["UpdateAlbumModal"])) {
                        $_SESSION["AlbumId"] = $_POST["UpdateAlbumModal"];
                    }
                    $album = GetAlbumById($_SESSION["AlbumId"]);
                    if ($album == null) {
                        echo '<script>alert("Ошибка альбома")</script>';
                    } else {
                        ?>
                        <form action="AddEdit.php" method="post" enctype="multipart/form-data">
                            <div class="card-header">
                                <h4 class="form-group m-3">Изменить детали альбома</h4>
                            </div>
                            <div class="card-body">
                                <div class="form-group m-3">
                                    <label for="title" class="form-label">Название альбома</label>
                                    <input type="text" class="form-control" id="title" name="title" aria-describedby="emailHelp" value="<?= $album["Title"] ?>">
                                </div>
                                <div class="form-group m-3">
                                    <label for="description" class="form-label">Описание</label>
                                    <input type="text" class="form-control" id="description" name="description" value="<?= $album["Description"] ?>">
                                </div>
                                <button type="submit" class="btn btn-custom-dark m-3" id="btnUpdateAlbum" name="btnUpdateAlbum">Обновить</button>
                            </div>
                        </form>
                        <?php
                    }
                }
                ?>
                <!-- Форма создания альбома -->
                <?php
                if (isset($_POST["CreateAlbumModal"])) {
                    ?>
                    <form action="AddEdit.php" method="post" enctype="multipart/form-data">
                        <div class="card-header">
                            <h4 class="form-group m-3">Создать альбом</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group m-3">
                                <label class="form-label">Название альбома</label>
                                <input type="text" class="form-control" id="title" name="title" aria-describedby="emailHelp">
                            </div>
                            <div class="form-group m-3">
                                <label class="form-label">Описание</label>
                                <input type="text" class="form-control" id="description" name="description">
                            </div>
                            <button type="submit" class="btn btn-custom-dark" id="btnCreateAlbum" name="btnCreateAlbum">Создать</button>
                        </div>
                    </form>
                    <?php
                }
                ?>
                <!-- Форма загрузки фото -->
                <?php
                if (isset($_POST["UploadPhotoModal"])) {
                    $albums = GetAlbumsByUserId($_COOKIE["UserId"]);
                    ?>
                    <form action="AddEdit.php" method="post" enctype="multipart/form-data">
                        <div class="card-header">
                            <h4 class="form-group m-3">Загрузить фото</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group m-3">
                                <label for="SelectedAlbum" class="form-label">Выберите альбом</label>
                                <select id="SelectedAlbum" name="SelectedAlbum" class="form-control">
                                    <option value="">Выберите альбом....</option>
                                    <?php
                                    if (is_array($albums) && count($albums) > 0) {
                                        foreach ($albums as $key => $values) {
                                        ?>
                                        <option value="<?= $values["Id"] ?>"><?= $values["Title"] ?></option>
                                        <?php
                                    }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group m-3">
                                <label class="form-label btn btn-custom-dark">
                                    Выберите файл
                                    <input required name="images[]" type="file" multiple style="display: none;">
                                </label>
                            </div>
                            <button type="submit" class="btn btn-custom-dark" id="btnUploadPhoto" name="UploadPhotoModal">Загрузить</button>
                        </div>
                    </form>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php include("Footer.php") ?>
</body>
</html>
