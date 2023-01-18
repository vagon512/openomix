<?php
$page = $_GET['page'];
require_once "include/header.php";
require_once "class/User.php";
$user = new User();
?>

<div class="form-width-outer">
    <div class="form-width-inner">
        <form action="registration.php?page=registration" method="post">
            <input type="text" name="sirname" placeholder="Фамилия">
            <input type="text" name="name" placeholder="Имя">
            <input type="text" name="patronymic" placeholder="Отчество">
            <input type="text" name="phone" placeholder="телефон (без 8)" maxlength="10">
            <input type="text" name="login" placeholder="логин">
            <input type="text" name="email" placeholder="E-mail">
            <input type="password" name="passwd" placeholder="Пароль">
            <input type="password" name="retypePasswd" placeholder="Пароль еще раз">
            День рождения:
            <input type="date" name="birthday" >

            <button type="submit">Регистрация</button>
        </form>
    </div>
    <div class="errores">
        <?php
        if(isset($_POST['name'])) {
            $salt = time();

            $user->setUserName($_POST['name']);
            $user->setUserSirname($_POST['sirname']);
            $user->setUserPatronymic($_POST['patronymic']);
            $user->setUserBirthday($_POST['birthday']);
            $user->setUserLogin($_POST['login']);
            $user->setSalt($salt);
            $user->checkPhoneNumber($_POST['phone']);
            $user->checkEmail($_POST['email']);
            $user->checkPassword($_POST['passwd'], $_POST['retypePasswd']);

            if (count($user->getErrores()) > 0) {
                foreach ($user->getErrores() as $key => $error) {
                    echo "<p>{$key} - {$error}</p>";
                    $user->logs($pdo, $error);
                }
                $user->clearErrores();
            } else {

                if ($user->checkUserData($pdo) == 1) {
                    echo "Registration succesfull";
                    $message = 'Registration succesfull. User: '.$_POST['sirname'].' '.$_POST['name'];
                    $user->logs($pdo, $message);

                }
                else{
                    echo "Такой логин или электронная почта уже используются. Укажите другие данные";
                    $message = 'Registration error. Попытка повторного использования почты или логина '.$_POST['email'].' '.$_POST['login'];
                    $user->logs($pdo, $message);
                }

            }

            //echo $user->getUserName();
            //echo $_POST['sirname'];

        }
        ?>
    </div>
</div>

