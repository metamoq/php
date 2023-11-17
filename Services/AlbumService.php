<?php
include("UserService.php");

function CreateAlbum(string $title, string $description, int $createdBy): ?array {
    $user = GetUserById($createdBy);
    if ($user == null) return null;
    $album = GetAlbumByTitleAndUserId($title, $createdBy);
    if ($album != null) return null;
    $createdAt = date('Y-m-d H:i:s', time());
    $query = "insert into albummodel(Title,Description,CreatedBy,CreatedAt) values ('$title','$description','$createdBy','$createdAt')";
    $queryResult = ExecuteQuery($query);
    if ($queryResult) return GetAlbumByTitleAndUserId($title, $createdBy);
    return null;
}

function UpdateAlbum(int $id , string $title, string $description, int $createdBy): ?array {
    $album = GetAlbumById($id);
    if ($album == null) return null;
    $user = GetUserById($createdBy);
    if ($user == null) return null;
    $query = "update albummodel set Title='$title',Description='$description' where Id='$id'";
    $queryResult = ExecuteQuery($query);
    if ($queryResult) return GetAlbumById($id);
    return null;
}

function DeleteAlbumById(int $id): ?array {
    $album = GetAlbumById($id);
    if ($album == null) return null;
    $query = "delete from albummodel where Id='$id'";
    $queryResult = ExecuteQuery($query);
    if ($queryResult) return $album;
    return null;
}

function GetAlbumById(int $id): ?array {
    $query = "select * from albummodel where Id='$id'";
    $queryResult = ExecuteQuery($query);
    if (mysqli_num_rows($queryResult) > 0) {
        $row = $queryResult->fetch_assoc();
        return $row;
    }
    return null;
}

function GetAlbumByTitleAndUserId(string $title, int $userId): ?array {
    $query = "select * from albummodel where Title='$title' and CreatedBy='$userId'";
    $queryResult = ExecuteQuery($query);
    if (mysqli_num_rows($queryResult) > 0) {
        $row = $queryResult->fetch_assoc();
        return $row;
    }
    return null;
}

function GetAlbumsByUserId(int $userId): ?array {
    $user = GetUserById($userId);
    if ($user == null) return null;
    $query = "select * from albummodel where CreatedBy='$userId'";
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

// Примеры использования функций:
//$ans = GetAlbumsByUserId(6);
//$ans = GetAlbumByTitle("my choice");
//$ans = CreateAlbum("varsity", "all varsity related photos", 6);
//$ans = UpdateAlbum(3, "new one", "updated", 6);
//$ans = DeleteAlbumById(1);
//print_r($ans != null? $ans : "null");
