<?php
include_once 'include/header.php';
include_once 'class/User.php';
$user = new User();
//для активации используется логин, почта и код верификации
if(isset($_GET['verify']) && isset($_GET['ul']) && isset($_GET['ue'])){

   if($user->verification($pdo, $_GET['verify'], $_GET['ul'], $_GET['ue'] )){
       echo "<p>Вы подтвердили свою запись</p>";
       echo "<a href='signin.php?page=signin' >Войти</a> в профиль";
   }
   else{
       echo "неверные данные для активации";
   }
}

include_once 'include/footer.php';