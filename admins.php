<?php
include_once 'include/header.php';
if($_SESSION['privileges'] != ADMIN_ACCES){
    echo "<div class='danger'> У Вас нет прав для работы в этом разделе</div>";
}
else{
    if(!isset($_GET['service'])){
    ?>
    <div class="mainPageDiv"><p class="mainPageLinks"><a href="admins.php?service=rules" >Работа с правилами</a></p></div>
    <div class="mainPageDiv"><p class="mainPageLinks"><a href="admins.php?service=users" >Работа с пользователями</a></p></div>
<?php
    }
    else{
        switch($_GET['service']) {
            case 'rules':
                include_once 'admin/rules.php';
                break;
            case 'users':
                include_once 'admin/users.php';
                break;
            default:
                unset($_GET);

        }
    }
}