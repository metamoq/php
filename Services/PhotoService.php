<?php
include("AlbumService.php");
function UploadPhotoDetailsToDatabase($title, $path, $uploadedBy, $albumId, $description) : ?array {
    // Проверяем, существует ли фотография с таким путем
    $photo = GetPhotoByPath($path);
    if ($photo != null) return null;

    // Проверяем, существует ли пользователь и альбом
    $user = GetUserById($uploadedBy);
    $album = GetAlbumById($albumId);
    if ($user == null || $album == null) return null;

    // Получаем текущую дату и время
    $createdAt = date('Y-m-d H:i:s', time());

    // Подготавливаем запрос для вставки данных в базу данных
    $query = "INSERT INTO photomodel (Title, Path, UploadedBy, AlbumId, CreatedAt, Description) VALUES ('$title', '$path', '$uploadedBy', '$albumId', '$createdAt', '$description')";

    // Выполняем запрос к базе данных
    $queryResult = ExecuteQuery($query);

    // Проверяем, успешно ли выполнен запрос
    if ($queryResult) {
        // Если успешно, возвращаем информацию о фотографии
        return GetPhotoByPath($path);
    } else {
        // Если произошла ошибка, возвращаем null
        return null;
    }
}

function UpdatePhoto($id, $title, $description, $albumId): ?array {
    $photo = GetPhotoById($id);
    if ($photo == null) return null;
    if ($albumId == null) return null;
    $query = "update photomodel set Title='$title', Description='$description', AlbumId='$albumId' where Id='$id'";
    $queryResult = ExecuteQuery($query);
    if ($queryResult) return GetPhotoById($id);
    return null;
}
function DeletePhotoById($id): ?array  {
    $photo = GetPhotoById($id);
    if ($photo == null) return null;
    // $db = new Database();
    // $connection = $db->Connect();
    $query = "delete from photomodel where Id='$id'";
    // $queryResult = mysqli_query($connection, $query);
    $queryResult = ExecuteQuery($query);
    DeletePhotoFromFileByPath($photo["Path"]);
    if ($queryResult) return $photo;
    return null;
}
function DeletePhotosByAlbumId($albumId): ?array  {
    $album = GetAlbumById($albumId);
    if ($album == null) return null;
    $photos = GetPhotosByAlbumId($albumId);
    if ($photos == null) return null;
    $query = "delete from photomodel where AlbumId='$albumId'";
    $queryResult = ExecuteQuery($query);
    foreach ($photos as $key => $value) {
        DeletePhotoFromFileByPath($value["Path"]);
    }
    if ($queryResult) return $photos;
    return null;
}
function GetPhotoById($id): ?array {
    $query = "SELECT * FROM photomodel WHERE Id='$id'";
    $queryResult = ExecuteQuery($query);
    if ($queryResult && mysqli_num_rows($queryResult) > 0) {
        $photo = $queryResult->fetch_assoc();
        return $photo;
    } else {
        // Обработка ошибки или отсутствия данных
        error_log("Error fetching photo data for ID: $id");
        return null;
    }
}
function GetPhotoByPath($path): ?array  {
    $query = "select * from photomodel where Path='$path'";
    $queryResult = ExecuteQuery($query);
    if (mysqli_num_rows($queryResult) > 0) {
        $photo = $queryResult->fetch_assoc();
        return $photo;
    }
    return null;
}
function GetPhotosByUserId($userId): ?array  {
    $query = "select * from photomodel where UploadedBy='$userId'";
    $queryResult = ExecuteQuery($query);
    if (mysqli_num_rows($queryResult) > 0) {
        $arr = array();
        while ($row = $queryResult->fetch_assoc()) {
            $arr[] = $row;
        }
        return $arr;
    }
    return null;
}
function GetPhotosByAlbumId($albumId): ?array  {
    $query = "select * from photomodel where AlbumId='$albumId'";
    $queryResult = ExecuteQuery($query);
    if (mysqli_num_rows($queryResult) > 0) {
        $arr = array();
        while ($row = $queryResult->fetch_assoc()) {
            $arr[] = $row;
        }
        return $arr;
    }
    return null;
}
function DeletePhotoFromFileByPath($path): ?array {
    unlink($path);
    return null;
}

/**
 * @throws Exception
 */
function UploadPhotos($files, $uploadedBy, $albumId, $description): ?array {

    // Validate $files is an array
    if(!is_array($files)) {
        throw new Exception('$files must be an array');
    }
    $uploadSuccess = [];

    foreach ($files['name'] as $key => $fileName) {
        $fileTmpName = $files['tmp_name'][$key];
        $fileSize = $files['size'][$key];
        $fileError = $files['error'][$key];
        $fileType = $files['type'][$key];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $allowedExtensions = array('jpg', 'jpeg', 'png');

        if (in_array($fileExtension, $allowedExtensions)) {
            if ($fileError == 0) {
                if ($fileSize < 5000000) {
                    $fileNewName = uniqid('', true) . "." . $fileExtension;
                    $fileDestination = 'uploads/' . $fileNewName;

                    if (move_uploaded_file($fileTmpName, $fileDestination)) {
                        $res = UploadPhotoDetailsToDatabase($fileName, $fileDestination, $uploadedBy, $albumId, $description);

                        if ($res !== null) {
                            $uploadSuccess[] = $res;
                        } else {
                            // Handle database insertion error
                            $uploadSuccess[] = null;
                        }
                    } else {
                        // Handle file move error
                        $uploadSuccess[] = null;
                    }
                } else {
                    // Handle file size error
                    $uploadSuccess[] = null;
                }
            } else {
                // Handle file upload error
                $uploadSuccess[] = null;
            }
        } else {
            // Handle invalid file type error
            $uploadSuccess[] = null;
        }
    }

    return $uploadSuccess;
}


// $ans = GetPhotosByUserId(6);
//$ans = GetPhotosByAlbumId(4);
//$ans = GetPhotoByPath("C:Mamp/uploads/62cd0545741097.81661418.jpg");
//$ans = GetPhotoById(2);
//$ans = UpdatePhoto(2, "hello", "this is edited", 3);
//$ans = DeletePhotoById(2);
//$ans = DeletePhotosByAlbumId(4);
// print_r($ans != null? $ans : "null");
/********************Status Functionalities******************/
function CreateStatus($status, $numOfLikes, $numOfDislikes, $numOfComments, $photoId, $createdAt, $createdBy): ?int {
    global $CONNECTION;

    // Проверяем соединение с базой данных
    if ($CONNECTION->connect_error) {
        error_log("Connection failed: " . $CONNECTION->connect_error);
        return null;
    }

    // Устанавливаем часовой пояс Минска
    date_default_timezone_set('Europe/Minsk');

    error_log("Status: $status");
    error_log("NumOfLikes: $numOfLikes");
    error_log("NumOfDislikes: $numOfDislikes");
    error_log("NumOfComments: $numOfComments");
    error_log("PhotoId: $photoId");
    error_log("CreatedAt: $createdAt");
    error_log("CreatedBy: $createdBy");

    $query = "INSERT INTO statusmodel(Status, NumberOfLikes, NumberOfDislikes, NumberOfComments, PhotoId, CreatedAt, UserId) VALUES (?, ?, ?, ?, ?, STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s'), ?)";

    // Подготавливаем и выполняем SQL-запрос
    $stmt = $CONNECTION->prepare($query);
    if (!$stmt) {
        error_log("Error preparing SQL statement: " . $CONNECTION->error);
        return null;
    }

    $stmt->bind_param("ssiiiss", $status, $numOfLikes, $numOfDislikes, $numOfComments, $photoId, $createdAt, $createdBy);
    $result = $stmt->execute();

    // Проверяем успешность выполнения запроса
    if ($result === false) {
        error_log("Error executing SQL statement: " . $stmt->error);
        $stmt->close();
        return null;
    }

    // Проверяем количество затронутых строк
    if ($stmt->affected_rows > 0) {
        $stmt->close();
        return 1;
    } else {
        error_log("No rows affected.");
        $stmt->close();
        return null;
    }
}


//function GetAllStatusByUserId($userId): ?array{
    //$query = "select * from statusmodel where UserId='$userId'";
    //$queryResult = ExecuteQuery($query);
   //if (mysqli_num_rows($queryResult) > 0) {
       // $arr = array();
       //while ($row = $queryResult->fetch_assoc()) {
           // $arr[] = $row;
        //}
       // return $arr;
    //}
    // null;
//}
function GetAllStatus(): ?array {
    global $CONNECTION;
    $query = "SELECT statusmodel.*, COUNT(commentmodel.Id) AS NumOfComments 
              FROM statusmodel 
              LEFT JOIN commentmodel ON statusmodel.Id = commentmodel.StatusId 
              GROUP BY statusmodel.Id 
              ORDER BY statusmodel.Id DESC";

    $queryResult = ExecuteQuery($query);

    if ($queryResult) {
        $arr = array();
        while ($row = $queryResult->fetch_assoc()) {
            $arr[] = $row;
        }
        return $arr;
    } else {
        error_log("Error fetching status data: " . mysqli_error($CONNECTION));
        return null;
    }
}



function UpdateStatus($id, $status, $numOfLikes, $numOfDislikes, $numOfComments): ?array {
    $query = "UPDATE statusmodel SET Status='$status', NumberOfLikes='$numOfLikes', NumberOfDislikes='$numOfDislikes', NumberOfComments='$numOfComments' WHERE Id='$id'";
    $queryResult = ExecuteQuery($query);
    if ($queryResult) return GetStatusById($id);
    return null;
}
// $ans = CreateStatus("hello", "0", "1", "1");
//$ans = GetAllStatusByUserId(1);
//print_r($ans != null? $ans : "null");
/********************Comments Functionalities******************/
function CreateComment($comment, $statusId, $whoCommented): ?int {
    $createdAt = date('Y-m-d H:i:s', time());
    $query = "insert into commentmodel(Comment, StatusId, CreatedAt, WhoCommented) VALUES ('$comment', '$statusId', '$createdAt', '$whoCommented')";
    $queryResult = ExecuteQuery($query);
    if ($queryResult) return 1;
    return null;
}
//function GetCommentsById($id): ?array {
    //$query = "select * from commentmodel where Id='$id'";
    //$queryResult = ExecuteQuery($query);
    //if (mysqli_num_rows($queryResult) > 0) {
       // $comment = $queryResult->fetch_assoc();
       // return $comment;
   // }
    //return null;
//}

//function GetLikeById($id) {
    //$query = "select * from likemodel where Id='$id'";
    //$queryResult = ExecuteQuery($query);
   // if (mysqli_num_rows($queryResult) > 0) {
     //   $like = $queryResult->fetch_assoc();
     //   return $like;
   // }
    //return null;
//}
/********************Like Functionalities******************/
function CreateLike($statusId, $whoLiked): ?int {
    $createdAt = date('Y-m-d H:i:s', time());
    $query = "INSERT INTO likemodel(StatusId, WhoLiked, CreatedAt) VALUES ('$statusId', '$whoLiked', '$createdAt')";
    $queryResult = ExecuteQuery($query);
    return $queryResult; // Возвращаем результат выполнения запроса
}

function GetLikeByStatusIdAndUserId($statusId, $userId): ?array {
    $query = "SELECT * FROM likemodel WHERE StatusId='$statusId' AND WhoLiked='$userId' ORDER BY Id DESC";
    $queryResult = ExecuteQuery($query);
    if (mysqli_num_rows($queryResult) > 0) {
        $like = $queryResult->fetch_assoc();
        return $like;
    }
    return null;
}

//function DeleteLike($statusId, $userId): ?int {
   // $query = "DELETE FROM likemodel WHERE StatusId='$statusId' AND WhoLiked='$userId'";
   // $queryResult = ExecuteQuery($query);
   // if ($queryResult) return 1;
    //return null;
//

function CreateDislike($statusId, $whoDisliked): ?int {
    $createdAt = date('Y-m-d H:i:s', time());
    $query = "INSERT INTO likemodel(StatusId, WhoLiked, CreatedAt) VALUES ('$statusId', '$whoDisliked', '$createdAt')";
    $queryResult = ExecuteQuery($query);
    if ($queryResult) return 1;
    return null;
}

function GetDislikeByStatusIdAndUserId($statusId, $userId): ?array {
    $query = "SELECT * FROM likemodel WHERE StatusId='$statusId' AND WhoDisliked='$userId' ORDER BY Id DESC";
    $queryResult = ExecuteQuery($query);
    if (mysqli_num_rows($queryResult) > 0) {
        $dislike = $queryResult->fetch_assoc();
        return $dislike;
    }
    return null;
}

//function DeleteDislike($statusId, $userId): ?int {
   // $query = "DELETE FROM likemodel WHERE StatusId='$statusId' AND WhoLiked='$userId'";
    //$queryResult = ExecuteQuery($query);
    //if ($queryResult) return 1;
    //return null;
//}

function GetStatusById($statusId): ?array {
    $query = "SELECT * FROM statusmodel WHERE Id='$statusId'";
    $queryResult = ExecuteQuery($query);
    if (mysqli_num_rows($queryResult) > 0) {
        return $queryResult->fetch_assoc();
    }
    return null;
}

function GetCommentsByStatusId($statusId): ?array {
    $query = "SELECT * FROM commentmodel WHERE StatusId='$statusId' ORDER BY Id DESC";
    $queryResult = ExecuteQuery($query);
    $arr = [];
    while ($row = $queryResult->fetch_assoc()) {
        $arr[] = $row;
    }
    return $arr;
}

// Функция для обновления количества лайков или дизлайков в статусе
//function UpdateStatusCount($statusId, $type): void {
    //$status = GetStatusById($statusId);
    //$numOfLikes = $status["NumberOfLikes"];
   // $numOfDislikes = $status["NumberOfDislikes"];
    //if ($type === 'likes') {
    //    $numOfLikes++;
   // } elseif ($type === 'dislikes') {
   //     $numOfDislikes++;
   // }
   // UpdateStatus(
       // $statusId,
       // $status["Status"],
        //$numOfLikes,
      //  $numOfDislikes,
      //  $status["NumberOfComments"]
    //);
//}

