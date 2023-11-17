<?php
session_start();
include("Services/PhotoService.php");

function showAlert($message): void {
    echo "<script>alert('$message')</script>";
}
/**
 * Sets a cookie with the given user ID and refreshes the page.
 *
 * @param int $userId The ID of the user.
 * @throws Exception If the cookie cannot be set.
 * @return void
 */
function setCookieAndRefresh(int $userId): void {
    $expiredTime = time() + (86400 * 30);
    setcookie("UserId", $userId, $expiredTime, "/");
    header("Refresh:0");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['btnSignUp'])) {
        if ($_POST['password'] != $_POST['confirmPassword']) {
            showAlert('Неверный пароль или почта.');
        } else {
            $user = CreateUser($_POST['firstName'], $_POST['lastName'], $_POST['email'], $_POST['password']);
            if (!$user) {
                showAlert('Проверьте правильность данных.');
            } else {
                try {
                    setCookieAndRefresh($user["Id"]);
                } catch (\Exception $e) {
                    showAlert('Ошибка при установке cookie и обновлении страницы');
                }
            }
        }
    } elseif (isset($_POST['btnLogIn'])) {
        $user = GetUserByEmail($_POST['email']);
        if (!$user) {
            showAlert('Пользователь не найден');
        } elseif ($user["Password"] != $_POST['password']) {
            showAlert('Неверный пароль или почта');
        } else {
            try {
                setCookieAndRefresh($user["Id"]);
            } catch (\Exception $e) {
                showAlert('Ошибка при установке cookie и обновлении страницы');
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-image: url('Assets/Images/index.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #1E404A;
        }
        .overlay {
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            animation: slideDown 2s ease forwards;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        .overlay h1,
        .overlay h4 {
            margin: 10px 0;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                top: -50%;
            }
            to {
                opacity: 1;
                top: 50%;
            }
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
        footer {
            background-color: #1E404A;
            color: white;
            position: fixed;
            bottom: 0;
            width: 100%;
            z-index: 9999; /* Установите значение z-index выше, чем у навбара, например, 9999 */
        }
    </style>
</head>

<body>
<?php include("Navbar.php") ?>
<div class="overlay">
    <h1>Добро пожаловать в онлайн-альбом с фотографиями</h1>
    <h4>Пожалуйста, зарегистрируйтесь и войдите, чтобы создать альбом и загрузить фотографии.</h4>
</div>
<!--Форма приветствия-->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const overlay = document.querySelector(".overlay");
        const loggedIn = <?php echo isset($_COOKIE["UserId"]) ? 'true' : 'false'; ?>;

        if (loggedIn) {
            overlay.innerHTML = '<h1>Добро пожаловать в онлайн-альбом с фотографиями</h1>';
        } else {
            overlay.innerHTML = '<h1>Добро пожаловать в онлайн-альбом с фотографиями</h1><h4>Пожалуйста, зарегистрируйтесь и войдите, чтобы создать альбом и загрузить фотографии.</h4>';
        }

        overlay.classList.add("show");
    });
</script>
<!-- Модальное окно для входа -->
<div class="modal fade" id="LogInModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Вход</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">Адрес электронной почты</label>
                        <input type="email" class="form-control" id="loginEmail" name="email" aria-describedby="emailHelp">
                        <div id="emailHelp" class="form-text">Мы никогда не передадим вашу почту никому.</div>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="loginPassword" name="password" autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn btn-custom-dark" id="btnLogIn" name="btnLogIn">Войти</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Модальное окно для регистрации -->
<div class="modal fade" id="signUpModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Регистрация</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="firstName" class="form-label">Имя</label>
                        <input type="text" class="form-control" id="firstName" name="firstName" autocomplete="given-name" required>
                    </div>
                    <div class="mb-3">
                        <label for="lastName" class="form-label">Фамилия</label>
                        <input type="text" class="form-control" id="lastName" name="lastName" autocomplete="family-name">
                    </div>
                    <div class="mb-3">
                        <label for="registerEmail" class="form-label">Адрес электронной почты</label>
                        <input type="email" class="form-control" id="registerEmail" name="email" aria-describedby="emailHelp" required>
                        <div id="emailHelp" class="form-text">Мы никогда не передадим вашу почту никому.</div>
                    </div>
                    <div class="mb-3">
                        <label for="registerPassword" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="registerPassword" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Подтвердите пароль</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required autocomplete="new-password">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="checkBox" name="checkBox" required>
                        <label class="form-check-label" for="checkBox">Я соглашаюсь с условиями</label>
                    </div>
                    <button type="submit" class="btn btn-custom-dark" id="btnSignUp" name="btnSignUp">Зарегистрироваться</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
<footer>
    <?php include("Footer.php") ?>
</footer>
</html>
