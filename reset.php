<?php
$page = $_GET['page'];
require_once "include/header.php";
require_once 'class/User.php';
$user = new User();
if(!isset($_GET['key']) && !isset($_POST['email']) && !isset($_GET['send']) && !isset($_POST['passwd'])){

?>
<div class="form-width-outer">
    <div class="form-width-inner">
        <form action="reset.php?page=reset&send=y" method="post">
            <input type="hidden" name="send" value="1">
            <label for="resetEmail">Введите email</label>
            <input type="email" name="email" id="resetEmail">
            <label for="resetLogin">Введите логин</label>
            <input type="text" name="login" id="resetlogin">
            <button type="submit">восстановить</button>
        </form>
    </div>
</div>
<?php
}

if(isset($_POST['email']) && !empty($_POST['email']) && isset($_POST['login']) && !empty($_POST['login'])) {


     if($user->searchUser($pdo, $_POST['email'], $_POST['login'])){
         echo '<div class="goodMessage">Проверьте почту. Вам было направлено письмо для восстановления пароля</div>';
         ?>
<!--         <div class="danger"><p>--><?//= $string ?><!--</p></div>-->
         <?php
         unset($user);
     }
}

//key3 - id пользователя
//key2 - salt
//key  - хэш старого пароля

if(isset($_GET['key']) && isset($_GET['key2']) && isset($_GET['key3'])){
    ?>
    <form action="reset.php?page=reset&id=<?= $_GET['key3'] ?>&slt=<?= $_GET['key2'] ?>" method="post">
        <label for="resetpswd">Пароль:</label>
        <input type="password" name="passwd" id="resetpswd">
        <label for="resetкpswd">Пароль еще раз:</label>
        <input type="password" name="retypepswd" id="resetrpswd">
        <button type="submit">запомнить</button>
    </form>
<?php
}
//print_r($_POST);
if(isset($_POST['passwd'])&& !empty($_POST['passwd']) && isset($_POST['retypepswd']) && !empty($_POST['retypepswd'])
&& isset($_GET['slt'])){

$user->setSalt($_GET['slt']);
$user->setUid($_GET['id']);
if($user->resetPassword($pdo, $_POST['passwd'], $_POST['retypepswd'])){
    ?>
    <div class="goodMessage">Пароль изменен. <a href="signin.php?page=signin">войдите в кабинет</a></div>
<?php
}
else{
    foreach($errores = $user->getErrores() as $error){
        ?>
        <div class="danger"><?= $error ?></div>
        <form action="reset.php?page=reset&id=<?= $_GET['key3'] ?>&slt=<?= $_GET['key2'] ?>" method="post">
            <label for="resetpswd">Пароль:</label>
            <input type="password" name="passwd" id="resetpswd">
            <label for="resetкpswd">Пароль еще раз:</label>
            <input type="password" name="retypepswd" id="resetrpswd">
            <button type="submit">запомнить</button>
        </form>
<?php
    }
}
}
?>

