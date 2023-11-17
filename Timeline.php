<?php
session_start();
include("Services/PhotoService.php");
$ShowAlert = false;
// Проверка, авторизован ли пользователь
if (!isset($_COOKIE["UserId"])) {
    header("Refresh:0;url=index.php");
    exit;
}
// Проверка, была ли нажата кнопка лайка
if (isset($_POST["btnLike"])) {
    $statusId = $_POST["statusId"];
    $userId = $_COOKIE["UserId"];
    // Получаем текущее количество лайков
    $status = GetStatusById($statusId);
    $numOfLikes = $status["NumberOfLikes"];
    // Проверяем, существует ли уже лайк от этого пользователя
    $existingLike = GetLikeByStatusIdAndUserId($statusId, $userId);
    // Проверяем, существует ли дизлайк от этого пользователя
    $existingDislike = GetDislikeByStatusIdAndUserId($statusId, $userId);
    // Если уже есть лайк от пользователя или дизлайк, выводим сообщение
    if ($existingLike || $existingDislike) {
        $ShowAlert = true;
        $AlertMessage = "Вы уже поставили оценку.";
    } else {
        // Если нет лайка или дизлайка, добавляем лайк
        CreateLike($statusId, $userId);
        $numOfLikes++; // Увеличиваем количество лайков
        // Обновляем статус с новым количеством лайков
        UpdateStatus($statusId, $status["Status"], $numOfLikes, $status["NumberOfDislikes"], $status["NumberOfComments"]);
    }
}
// Проверка, была ли нажата кнопка дизлайка
if (isset($_POST["btnDislike"])) {
    $statusId = $_POST["statusId"];
    $userId = $_COOKIE["UserId"];
    // Получаем текущее количество дизлайков
    $status = GetStatusById($statusId);
    $numOfDislikes = $status["NumberOfDislikes"];
    // Проверяем, существует ли уже дизлайк от этого пользователя
    $existingDislike = GetDislikeByStatusIdAndUserId($statusId, $userId);
    // Проверяем, существует ли лайк от этого пользователя
    $existingLike = GetLikeByStatusIdAndUserId($statusId, $userId);
    // Если уже есть дизлайк от пользователя или лайк, выводим сообщение
    if ($existingDislike || $existingLike) {
        $ShowAlert = true;
        $AlertMessage = "Вы уже поставили оценку.";
    } else {
        // Если нет дизлайка или лайка, добавляем дизлайк
        CreateDislike($statusId, $userId);
        $numOfDislikes++; // Увеличиваем количество дизлайков
        // Обновляем статус с новым количеством дизлайков
        UpdateStatus($statusId, $status["Status"], $status["NumberOfLikes"], $numOfDislikes, $status["NumberOfComments"]);
    }
}
// После обработки лайков и дизлайков, вы можете отобразить сообщение пользователю
$AlertMessage = ""; // Определение переменной $AlertMessage
if ($ShowAlert) {
    $AlertMessage = "Вы уже поставили оценку.";
    echo "<script>alert('$AlertMessage');</script>";
}
// Получаем все статусы, фотографии и пользователей
$allStatus = GetAllStatus();
$photos = array();
$users = array();
foreach ($allStatus as $key => $value) {
    $photos[] = GetPhotoById($value['PhotoId']);
    $users[] = GetUserById($value['UserId']);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimeLine</title>
    <!-- CSS only -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
          integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <style>
        .container {
            position: relative;
            text-align: center;
            color: #1E404A;
        }
        body {
            margin: 0;
            padding-bottom: 60px;
        }
        footer {
            background-color: #1E404A;
            color: white;
            padding: 10px;
            position: sticky;
            bottom: 0;
            width: 100%;
        }
    </style>
    <style>
        .image-thumbnail {
            height: 200px;
            width: 300px;
            object-fit: cover;
        }
        .list-group-item a {
            text-decoration: none;
            color: #1E404A;
        }
    </style>
</head>
<body>
<?php include("Navbar.php") ?>
<div class="container w-100 justify-content-center">
    <div class="row">
        <div class="col">
            <?php
            if ($ShowAlert) {
                echo '
                    <div class="alert alert-danger" role="alert">
                        Вы можете поставить лайк/дизлайк 1 раз!
                    </div>';
            }
            $iter = 0;
            foreach ($allStatus as $key => $value) {
                echo '
        <div class="card my-2">
            <div class="card-header">
                <div>' . $users[$iter]["FirstName"] . ' ' . $users[$iter]["LastName"] . ' разместил фото ' . $value["CreatedAt"] . '</div>
            </div>
            <div class="card-body">
                <p>' . $value['Status'] . '</p>';
                $imgPath = isset($photos[$iter]) ? $photos[$iter]["Path"] : "Assets/Images/nophoto.jpg";
                // Проверка наличия файла в папке uploads
                if (!file_exists($imgPath)) {
                    echo '<p>Фото было удалено с сервера</p>';
                    echo '<img class="image-thumbnail" src="Assets/Images/nophoto.jpg" alt="Card image cap">';
                } else {
                    echo '<img class="image-thumbnail" src="' . $imgPath . '" alt="Card image cap">';
                }
                echo '
            </div>
            <div class="card-header">
                <div class="row">';
                if (isset($photos[$iter])) {
                    echo '
                    <div class="col">
                        <form method="post" action="TimeLine.php">
                            <input type="hidden" name="statusId" value="' . $value["Id"] . '">
                            <button class="btn btn-white" name="btnLike">';
                    $existingLike = GetLikeByStatusIdAndUserId($value["Id"], $_COOKIE["UserId"]);
                    if ($existingLike) {
                        echo '<i class="fa fa-thumbs-up"></i> Лайк';
                    } else {
                        echo '<i class="fa fa-thumbs-up"></i> Лайк';
                    }
                    echo '</button>
                        </form>
                        <p>' . $value["NumberOfLikes"] . ' Лайк(ов).</p>
                    </div>
                    <div class="col">
                        <form method="post" action="TimeLine.php">
                            <input type="hidden" name="statusId" value="' . $value["Id"] . '">
                            <button class="btn btn-white" name="btnDislike">';
                    $existingDislike = GetDislikeByStatusIdAndUserId($value["Id"], $_COOKIE["UserId"]);
                    if ($existingDislike) {
                        echo '<i class="fa fa-thumbs-down"></i> Дизлайк';
                    } else {
                        echo '<i class="fa fa-thumbs-down"></i> Дизлайк';
                    }
                    echo '</button>
                        </form>
                        <p>' . $value["NumberOfDislikes"] . ' Дизлайк(ов).</p>
                    </div>
                    <div class="col">
                        <form method="post" action="Comments.php">
                            <button class="btn btn-white" name="btnComments" value="' . $value["Id"] . '">Комментировать <span class="badge bg-secondary">' . $value["NumOfComments"] . '</span></button>
                        </form>
                    </div>';
                } else {
                    echo '
                    <div class="col">
                        <p>Фотография была удалена пользователем из альбома</p>
                        <img class="image-thumbnail" src="Assets/Images/nophoto.jpg" alt="Card image cap">
                    </div>';
                }
                echo '
                </div>
            </div>
        </div>';
                $iter++;
            }
            ?>
        </div>
    </div>
</div>
<?php include("Footer.php") ?>
</body>
</html>