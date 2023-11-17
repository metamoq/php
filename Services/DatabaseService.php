<?php
class Database {
    private $Host = "localhost";
    private $UserName = "root";
    private $Password = "root";
    private $DbName = "albums";

    public function Connect() {
        return mysqli_connect($this->Host, $this->UserName, $this->Password, $this->DbName);
    }
}

$DB_OBJECT = new Database();
$CONNECTION = $DB_OBJECT->Connect();

function ExecuteQuery($query) {
    global $CONNECTION;
    error_log("Executing query: $query"); // Логируем запрос в файл ошибок
    return mysqli_query($CONNECTION, $query);
}


function Debug($data) {
    echo "<pre>";
    print_r(($data == null)? "null" : $data);
    echo "</pre>";
}

