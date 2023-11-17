<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
    include("Services/PhotoService.php");
    if (!isset($_COOKIE["UserId"])) {
        header("Refresh:0;url=index.php");
    }
    if (!isset($_SESSION["PhotoId"])) {
        header("Refresh:0;url=Album.php");
    }
?>
<?php
    $photo = null;
    $album = null;
    $albums = null;
    if (isset($_SESSION["PhotoId"])) {
        $photo = GetPhotoById($_SESSION["PhotoId"]);
        if ($photo != null) {
            $album = GetAlbumById($photo["AlbumId"]);
        }
        $albums = GetAlbumsByUserId($_COOKIE["UserId"]);
    }
?>
<?php
    if (isset($_POST["btnSavePhoto"])) {
        $photo = UpdatePhoto($_SESSION["PhotoId"], $_POST["title"], $_POST["description"], $_POST["selectedAlbum"]);
        $album = GetAlbumById($photo["AlbumId"]);
    }
?>
<?php
    if (isset($_POST["btnDeletePhoto"])) {
        $photo = DeletePhotoById($_SESSION["PhotoId"]);
        unset($_SESSION["PhotoId"]);
        header("Refresh:0;url=Album.php");
    }
?>
<?php
if (isset($_POST["btnShare"])) {
    if (isset($_SESSION["PhotoId"]) && isset($_COOKIE["UserId"])) {
        $photoId = $_SESSION["PhotoId"];
        $createdBy = $_COOKIE["UserId"];
        $status = CreateStatus($_POST["status"], 0, 0, 0, $photoId, date("Y-m-d H:i:s"), $createdBy);
        if ($status === null) {
            // Обработка случая, когда не удалось опубликовать фото
            echo "Не удалось опубликовать фото из-за ошибки в базе данных. Пожалуйста, попробуйте еще раз.";
        } else {
            header("Refresh:0;url=Timeline.php");
        }
    } else {
        // Обработка случая, когда значения не определены
        echo "Ошибка: Необходимые значения не определены.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Фото</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <style>
        .list-group-item a {
            text-decoration: none;
            color: powderblue;
        }

        .btn-custom-dark {
            background-color: #1E404A;
            border-color: #1E404A;
            color: #ffffff;
        }

        .btn-custom-dark:hover {
            background-color: #0D2B32;
            border-color: #0D2B32;
            color: #ffffff;
        }
         .image-thumbnail {
             display: block;
             margin: 0 auto;
             max-height: 200px;
             max-width: 300px;
             object-fit: cover;
         }
    </style>
</head>

<body>
<?php include("Navbar.php") ?>
<div class="container m-4">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <a href="Album.php" class="btn btn-custom-dark my-3">Назад</a>
            <button class="btn btn-custom-dark" name="ShareStatusModal" data-bs-toggle="modal" data-bs-target="#ShareStatusModal">Поделиться фото</button>
            <div style="height: 90vh;">
                <?php
                if ($photo != null) {
                    $imgPath = $photo["Path"];
                    // Проверка наличия файла в папке uploads
                    if (!file_exists($imgPath)) {
                        echo '<h4>Фото было удалено с сервера</h4>';
                        echo '<img style="max-width: 100%; max-height: 100%;" src="Assets/Images/nophoto.jpg" alt="photo">';
                    } else {
                        echo '<a href="'.$imgPath.'"><img style="max-width: 100%; max-height: 100%;" src="'.$imgPath.'" alt="photo"></a>';
                    }
                } else {
                    echo "<h4>Нет фото</h4>";
                }
                ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h4>Описание фото</h4>
                        </div>
                        <div class="col">
                            <button class="btn" data-bs-toggle="modal" data-bs-target="#EditPhotoDetailsModal"><i class="fa fa-edit"></i></button>
                            <button class="btn" name="ConfirmDeleteModal" data-bs-toggle="modal" data-bs-target="#ConfirmDeleteModal"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php
                        if ($photo != null) {
                            echo '
                                            <li class=" list-group-item">
                                                <div>
                                                    <b>Название фото : </b> '.$photo["Title"].'
                                                </div>
                                            </li>
                                            <li class=" list-group-item">
                                                <div>
                                                    <b>Название альбома</b> : '.$album["Title"].'
                                                </div>
                                            </li>
                                            <li class=" list-group-item">
                                                <div>
                                                    <b>Дата загрузки</b> : '.$photo["CreatedAt"].'
                                                </div>
                                            </li>
                                            <li class=" list-group-item">
                                                <div>
                                                    <b>Описание</b> : '.$photo["Description"].'
                                                </div>
                                            </li>
                                        ';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Редактировать описание фото форма-->
<div class="modal fade" id="EditPhotoDetailsModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Редактировать описание</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="Photo.php" method="post" enctype="multipart/form-data">
                    <?php
                    echo '
                                    <div class="mb-3">
                                        <label class="form-label">Название фото</label>
                                        <input type="text" class="form-control" id="title" name="title" value="'.$photo["Title"].'">
                                    </div>
                                ';
                    ?>
                    <div class="mb-3">
                        <label for="selectedAlbum">Выбрать альбом</label>
                        <select name="selectedAlbum" id="selectedAlbum" class="form-control">
                            <!-- Варианты выбора альбома -->
                            <?php
                            echo '
                                            <option value="'.$album["Id"].'">'.$album["Title"].'</option>
                                        ';
                            ?>
                            <?php
                            foreach ($albums as $key => $values) {
                                echo '
                                                <option value="'.$values["Id"].'">'.$values["Title"].'</option>
                                            ';
                            }
                            ?>
                        </select>
                    </div>
                    <?php
                    echo '
                                    <div class="mb-3">
                                        <label class="form-label">Описание</label>
                                        <input type="text" class="form-control" id="description" name="description" value="'.$photo["Description"].'">
                                    </div>
                                ';
                    ?>
                    <button type="submit" class="btn btn-custom-dark" id="btnSavePhoto" name="btnSavePhoto">Сохранить</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Удаление фото форма -->
<div class="modal fade" id="ConfirmDeleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Подтвердить удаление</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="Photo.php" method="post" enctype="multipart/form-data">
                    <div class="row justify-content-center">
                        <div class="col-4">
                            <button type="submit" class="btn btn-danger" id="btnDeletePhoto" name="btnDeletePhoto">Подтвердить</button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Close">Отмена</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Форма поделиться -->
<div class="modal fade" id="ShareStatusModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Поделиться фото</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="Photo.php" method="post" enctype="multipart/form-data">
                    <div class="row justify-content-center">
                        <div class="mb-3">
                            <label for="status" class="form-label">Напишите что-нибудь</label>
                            <input type="text" class="form-control" id="status" name="status">
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-success" id="btnShare" name="btnShare">Поделиться</button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Close">Отмена</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include("Footer.php") ?>
</body>
</html>
