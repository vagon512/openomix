<?php
$page = $_GET['page'];
include_once  'class/User.php';
include_once 'include/header.php';

$user = new User();
?>

<div class="form-width-outer">
    <div class="form-width-inner">
        <form action="signin.php?page=signin" method="post">
            <input type="hidden" name="form" value="y">
            <input type="text" name="login" placeholder="логин или email">
            <input type="password" name="passwd" placeholder="Пароль">


            <button type="submit">Войти</button>
        </form>
        <div class="forget"><a href="reset.php?page=reset">забыли пароль</a></div>
    </div>
</div>
<?php
if (isset($_POST['form'])) {
    $login = $_POST['login'] ?? "";
    $password = $_POST['passwd'] ?? '';
    if (!$user->getUserData($pdo, $login, $password)) {
         ?>
<div class="danger"><p><?= $_SESSION['errors'] ?></p></div>
<?php
    } else {
        header('location:index.php');
        exit();
    }
}
include_once 'include/footer.php';
?>
