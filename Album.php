<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include("Services/PhotoService.php");
if (!isset($_COOKIE["UserId"])) {
    header("Refresh:0;url=index.php");
}
?>
<?php
if(isset($_POST["btnSelectedAlbum"])) {
    $_SESSION["AlbumId"] = $_POST["btnSelectedAlbum"];
}
?>
<?php
if (isset($_POST["btnAllPhotos"])) {
    unset($_SESSION["AlbumId"]);
}
?>

<?php
if (isset($_POST["btnDeleteAlbum"]) && isset($_SESSION["AlbumId"])) {
    $deletedPhotos = DeletePhotosByAlbumId($_SESSION["AlbumId"]);
    $album = DeleteAlbumById($_SESSION["AlbumId"]);
    unset($_SESSION["AlbumId"]);
}
?>
<?php
if (isset($_POST["btnViewPhoto"])) {
    $_SESSION["PhotoId"] = $_POST["btnViewPhoto"];
    header("Refresh:0;url=Photo.php");
}
?>


<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <title>Альбом</title>
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <style>
        body {
            margin: 0;
            padding-bottom: 60px; /* Добавили отступ снизу, равный высоте футера */
        }
        footer {
            background-color: #1E404A;
            color: white;
            padding: 10px;
            position: sticky;
            bottom: 0;
            width: 100%;
        }
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

        .btn-icon {
            margin-right: 5px;
        }

        .image-thumbnail {
            height: 200px;
            object-fit: cover;
            position: sticky;
        }

        .btn-group {
            position: relative;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
        }

        .btn-group .btn {
            margin-right: 5px;
        }

        .comment-count {
            margin-left: 10px;
        }
    </style>
</head>

<body>
<?php include("Navbar.php") ?>
<div class="container m-5">
    <div class="row">
        <!-- Боковая навигация -->
        <div class="col-md-4">
            <div class="card">
                <form class="card" method="post" action="AddEdit.php" enctype="multipart/form-data">
                    <button type="submit" class="btn btn-custom-dark btn-block btn-sm m-1" name="CreateAlbumModal">Создать альбом</button>
                </form>
                <div class="card-header">
                    <form method="post" action="Album.php">
                        <button type="submit" class="btn btn-light btn-custom-dark" name="btnAllPhotos">Все фото</button>
                    </form>
                </div>
                <ul class="list-group list-group-flush">
                    <?php
                    $albums = GetAlbumsByUserId($_COOKIE["UserId"]);
                    if ($albums != null) {
                        foreach ($albums as $key => $value) {
                            echo '
                                    <li class="list-group-item">
                                        <div class="row">
                                            <div class="col-md-9">
                                                <form method="post" action="Album.php">
                                                    <button class="btn btn-outline-dark" value=' . $value["Id"] . ' name="btnSelectedAlbum">' . $value["Title"] . '</button>
                                                </form>
                                            </div>
                                            <div class="col-md-1">
                                                <form  method="post" action="AddEdit.php">
                                                    <button class="btn btn-custom-dark" name="UpdateAlbumModal" value=' . $value["Id"] . '><i class="fa fa-edit"></i></button>
                                                </form>
                                            </div>
                                        </div> 
                                    </li>
                                ';
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
        <!-- Фотографии-->
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <?php
                        $AlbumDetails = null;
                        if (isset($_SESSION["AlbumId"])) {
                            $AlbumDetails = GetAlbumById($_SESSION["AlbumId"]);
                        }
                        ?>
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-9">
                                    <?php
                                    if ($AlbumDetails != null) {
                                        echo '
                                                        <div class="row">
                                                            <div class="col-md-9">
                                                                <h3> '.$AlbumDetails["Title"].' </h3>
                                                            </div>
                                                            <div>Создано '.$AlbumDetails["CreatedAt"].'</div>
                                                        </div>
                                                    ';
                                    } else {
                                        echo '
                                                       <h3> Все фото </h3> 
                                                    ';
                                    }
                                    ?>
                                </div>
                                <div class="col">
                                    <form method="post" action="AddEdit.php">
                                        <button type="submit" class="btn btn-custom-dark btn-block" name="UploadPhotoModal">Загрузить фото</button>
                                    </form>
                                    <?php
                                    if ($AlbumDetails != null) {
                                        echo '
                                                        <button type="button" class="btn btn-custom-dark btn-block my-2" name="ConfirmDeleteModal" data-bs-toggle="modal" data-bs-target="#ConfirmDeleteModal" value='.$AlbumDetails["Id"].'>Удалить альбом</button>
                                                    ';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6> <u>Описание</u> </h6>
                            <?php
                            if ($AlbumDetails != null) {
                                echo '
                                                <p>'.$AlbumDetails["Description"].'</p>
                                            ';
                            } else {
                                echo '
                                                <p>Все фото во всех альбомах</p>
                                            ';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
                $photos = GetPhotosByUserId($_COOKIE["UserId"]);
                if (isset($_SESSION["AlbumId"])) {
                    $photos = GetPhotosByAlbumId($_SESSION["AlbumId"]);
                }
                if ($photos == null) {
                    echo '<h4>В выбранном альбоме нет фотографий</h4>';
                } else {
                    echo '<div class="row">';
                    foreach ($photos as $key => $values) {
                        // Предположим, что $values["Path"] содержит полный путь к изображению
                        $fullPath = $values["Path"];
                        $relativePath = str_replace($_SERVER["DOCUMENT_ROOT"], "", $fullPath);
                        // Проверка наличия файла в папке uploads
                        if (!file_exists($fullPath)) {
                            echo '<p>Фото удалено с сервера</p>';
                            $relativePath = "Assets/Images/nophoto.jpg";
                        }
                        // Получение количества лайков
                        $userId = $_COOKIE["UserId"];
                        $numOfLikes = GetLikeByStatusIdAndUserId($values["Id"], $userId);
                        $numOfDislikes = GetDislikeByStatusIdAndUserId($values["Id"], $userId);
                        // Получение количества комментариев
                        $numOfComments = GetCommentsByStatusId($values["Id"]);
                        ?>
                        <div class="col-md-4">
                            <div class="card my-2">
                                <img class="image-thumbnail" src="<?php echo $relativePath; ?>" alt="Image">
                                <div class="card-body">
                                    <small><?php echo htmlspecialchars($values["Title"], ENT_QUOTES, 'UTF-8'); ?></small>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group" role="group" aria-label="Like Dislike">
                                        <!-- Комментируем участок кода для иконок лайков, дизлайков и комментариев можно добавить эту функциональность в миниатюрах-->
                                        <!--
                                        <button type="button" class="btn btn-light btn-icon" disabled>
                                            <i class="fas fa-thumbs-up"></i>
                                            <?php echo $numOfLikes ? count($numOfLikes) : 0; ?>
                                        </button>
                                        <button type="button" class="btn btn-light btn-icon" disabled>
                                            <i class="fas fa-thumbs-down"></i>
                                            <?php echo $numOfDislikes ? count($numOfDislikes) : 0; ?>
                                        </button>
                                        <button type="button" class="btn btn-light btn-icon comment-count" disabled>
                                            <i class="fas fa-comment"></i>
                                            <?php echo $values["NumberOfComments"] ?? 0; ?>
                                        </button> -->
                                    </div>
                                </div>
                                <form method="post" action="Album.php">
                                    <button type="submit" class="btn btn-outline-dark btn-custom-dark btn-sm m-1" name="btnViewPhoto" value="<?php echo htmlspecialchars($values["Id"], ENT_QUOTES, 'UTF-8'); ?>">Просмотр</button>
                                </form>
                            </div>
                        </div>
                        <?php
                    }
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
<!-- ConfirmDelete Модальное окно -->
<div class="modal fade" id="ConfirmDeleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Подтвердить удаление</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="Album.php" method="post" enctype="multipart/form-data">
                    <div class="row justify-content-center">
                        <div class="col-4">
                            <button type="submit" class="btn btn-danger btn-custom-dark" id="btnDeleteAlbum" name="btnDeleteAlbum">Подтвердить</button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-primary btn-custom-dark" data-bs-dismiss="modal" aria-label="Close">Отменить</button>
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
