<?php
if (isset($_POST["btnLogOut"])) {
    setcookie("UserId", "", time() - 3600, "/");
    session_destroy();
    header("Refresh:0");
}
?>
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #1E404A; height: 70px">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">ФотоАльбом</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav col-md-9">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="index.php">Главная</a>
                </li>
                <?php
                    if (isset($_COOKIE["UserId"])) {
                        echo '
                            <li class="nav-item">
                                <a class="nav-link" href="Album.php">Альбомы</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="Timeline.php">Публикации</a>
                            </li>
                        ';
                    }
                ?>
            </ul>
            <ul class="navbar-nav col">
                <?php
                    if (!isset($_COOKIE["UserId"])) {
                        echo '
                            <li class="nav-item">
                                <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#LogInModal">Вход</button>   
                            </li>
                            <li class="nav-item mx-2">
                                <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#signUpModal">Регистрация</button>   
                            </li>
                        ';
                    } else {
                        $user = GetUserById($_COOKIE["UserId"]);
                        echo '
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                '.$user["FirstName"].'</a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                    <li><a class="dropdown-item" href="Profile.php">Настройки</a></li>
                                    <li>
                                        <form method="post" action="index.php">
                                            <button class="dropdown-item" name="btnLogOut">Выйти</button>
                                        </form>
                                    </li>
                                </ul>
                            </li> 
                      ';
                    }
                ?>
            </ul>
        </div>
    </div>
</nav>