<?php
require_once __DIR__.'/db.php';
session_start();
ob_start();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="style/menu.css">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/tabs.css">
    <?php  //if(isset($page ) && $page == 'signin' || $page == 'registration' || $page == 'reset' || isset($_GET['service'])){ ?>
        <link rel="stylesheet" href="style/form.css">
    <?php // }?>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <!-- Подключаем наш файл скрптов -->
        <script type="text/javascript" src="script.js"></script>
    <title>Omixsense</title>
</head>
<body>

<div id="block-body">
<header>



    <div class="logo">
        <a href="index.php">
            <span class="use"><img src="../pic/logo_bg.png"></span>
<!--            <span class="use">USE</span>-<span class="web">WEB</span>.ru-->
        </a>
<!--        <p>Разработка- это просто</p>-->
    </div>



    <div class="top-menu">
        <ul>
            <li><a class="clickMenu" href="index.php">Главная</a></li>
            <li><a href="about.php">О нас</a></li>
            <li><a href="contacts.php">Контакты</a></li>
            <?php if(isset($_SESSION['privileges']) && $_SESSION['privileges'] == ADMIN_ACCES){
                ?>
                <li><a href="admins.php">Администрирование</a></li>
            <?php
            } ?>
        </ul>
    </div>



    <div class="block-top-auth">
        <?php  if(!isset($_SESSION['id'])){ ?>
        <p><a href="signin.php?page=signin">Вход</a></p>
            <p><a href="registration.php?page=registration">Регистрация</a></p>
        <?php }
        else{
            ?>
            <a href="?page=logout">Выход</a></p>
        <?php }

        ?>

    </div>
      <?php
    if(isset($_GET['page']) && $_GET['page']=="logout" ){
        session_regenerate_id();
        session_destroy();
        unset($_GET);
        header('location:index.php');

    }
    ?>
</header>
    <?php
    if(isset($_SESSION['id'])){
        ?>
        <div class="user">
            <p>Пользователь:<?=$_SESSION['sirname'], " ", $_SESSION['name'], " ", $_SESSION['patronimyc'];  ?> </p>
        </div>
        <?php
    }
    ?>

<br>
