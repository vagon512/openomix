<?php
require_once "include/header.php";
//require_once 'class/Service.php';
include_once "class/XMLService.php";
if (!isset($_SESSION['id'])) {
    ?>
    <div>
        <p>Для работы в кабинете неоходимо
            <a href="signin.php?page=signin">войти</a> или <a href="registration.php?page=registration">зарегистрироваться</a>
        </p>
    </div>
    <?php
} else {
    if (isset($_SESSION['activation']) && $_SESSION['activation'] == 0) {
        ?>
        <div class="danger"><p>У вас не подтвержденная учетная запись. Работа с модулями запрещена!</p>
            <p>Для подтверждения учетной записи перейдите по ссылке в письме, полученном при регистрации</p></div>
        <?php
        die;
    }

    if (isset($_GET['service']) && $_SESSION['privileges'] == DOCTOR_ACCES) {



            switch ($_GET['service']) {
                case 'metascrin':
                    ?>
                    <div class="menu1">
                    <br id="tab2"/><br id="tab3"/><br id="tab4"/>
                    <a href="#tab1">Загрузка результатов</a><a href="#tab2">Результат</a><a
                        href="#tab3">Интерпретация</a><a href="#tab4">Группировка</a>
                    <div><?php include_once 'services/metascrin.php'; ?></div>
                        <div><a name="tab2"></a><?php include_once 'result.php';
                            ?></div>
                        <div>
                            <?php  include_once 'show_result.php'; ?>

                        </div>
                        <div><?php include_once 'show_group.php'; ?></div>

                    </div>
                    <?php //include 'services/metascrin.php';
                    break;
                case 'tlm':
                    ?>
                    <div><?php include_once 'services/tlm.php'; ?></div>
                    <?php //include 'tlm.php';
                    break;
                case 'farm':
                    ?>
                    <div><?php include_once 'services/farm.php'; ?></div>
                    <?php
                    //include 'services/farm.php';
                    break;
            }
            ?>

        <?php
    } else {
        ?>
        <div class="danger"><p>У вас нет прав на использование ресурса!</p>
            <p>Запросите повышеие прав у администратора (<a href="#">см. инструкцию</a>)</p></div>
        <?php
    }

}
?>