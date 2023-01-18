<div class="form-width-outer">
    <div class="form-width-inner">
        <form  method="post" action="cabinet.php?service=tlm">

            <input type="text" name="RequestNr" placeholder="номер заявки">
            дата рождения<input type="date" name="BirthDate">

            <button type="submit">Загрузить</button>
        </form>
    </div>
</div>

<?php
//загрузка данных из ЛИС
$service = new XMLService();
$BirthDate = strtotime($_POST['BirthDate']);

$currentDate = date("d.m.Y H:i:s");
$RequestNr = $_POST['RequestNr'];



$string = $service->getXMLContent($_POST['BirthDate'], $RequestNr);
//echo "<pre>";
//print_r($string);
//echo "</pre>";
$drugsIDName = [];
$i = 0;
foreach ($string as $value){
    if(!is_array($value)){
        continue;
    }

    $querySelectDrugsIDName = "SELECT id_name FROM drugs_name WHERE rus_name LIKE :rusName";
    $result = $pdo->prepare($querySelectDrugsIDName);
    $result->execute(['rusName' => "%".$value['name']."%"]);
    $ID = $result->fetchColumn();
    if( strlen($ID) > 0){
        $drugsIDName[$i]['id_name'] = $ID ;
        $drugsIDName[$i]['result']  = $value['result'];
        $drugsIDName[$i]['ed']  = $value['ed'];
        $i++;
    }

}

//echo "<pre>";
//print_r($drugsIDName);
//echo "</pre>";
if(count($drugsIDName) > 0){

foreach ($drugsIDName as $idName){

     $querySelectDrugsData = "SELECT drugs_class_name, rus_name, eng_name, drugs_data.* 
                                         FROM drugs_data 
                                             JOIN drugs_name ON drugs_name.id_name=drugs_data.id_name 
                                             JOIN drugs_class ON drugs_class.id_class = drugs_name.id_class 
                                         WHERE drugs_data.id_name=:id_name";
                    $result = $pdo->prepare($querySelectDrugsData);
                    $result->execute(['id_name' => $idName['id_name']]);
                    $drugsRow = $result->fetchAll(PDO::FETCH_ASSOC);
//print_r($drugsRow);
                    $coefDRCMiddle = (float)trim(str_replace(',', '.', $drugsRow[0]['coeff_drc_middle']));
                    $bossage = (float)$idName['result'];

                    $drugExpectedConcentration = $coefDRCMiddle*$bossage*1000;
                    ?>

    <table class="tlm">
        <tr><td colspan="5" class="big_result">Мониторинг по препарату <?= $drugsRow[0]['rus_name'].'('.$idName['result'].' '.$idName['ed'].')' ?>  </td> </tr>
        <tr>
            <td>Класс:</td>
            <td colspan="4"><?= $drugsRow[0]['drugs_class_name'] ?></td>
        </tr>
        <tr>
            <td>Преараты (рус/англ):</td>
            <td colspan="4"><?= $drugsRow[0]['rus_name']."/".$drugsRow[0]['eng_name'] ?></td>
        </tr>
        <tr>
            <td>Уровень рекомендации</td>
            <td colspan="4"><?= $drugsRow[0]['level_tlm'] ?></td>
        </tr>
        <tr>
            <td>Терапевтический диапазон</td>
            <td colspan="4"><?= $drugsRow[0]['terapevt_range'] ?></td>
        </tr>
        <tr>
            <td>Уровень предосторожности</td>
            <td colspan="4"><?= $drugsRow[0]['danger_level'] ?></td>
        </tr>
        <tr>
            <td>Коэффициент пересчета</td>
            <td colspan="4"><?= $drugsRow[0]['scalling_ration'] ?></td>
        </tr>
        <tr>
            <td>Биодоступность</td>
            <td colspan="4"><?= $drugsRow[0]['t1_2'] ?></td>
        </tr>
        <tr>
            <td>T1/2</td>
            <td colspan="4"><?= $drugsRow[0]['biodostupnost'] ?></td>
        </tr>
        <tr>
            <td>CL/F&plusmn;SD</td>
            <td colspan="4"><?= $drugsRow[0]['cl_f_sd'] ?></td>
        </tr>
        <tr>
            <td>&Delta;t</td>
            <td>Коэффициент DRC(сред)</td>
            <td>Коэффициент DRC(мин)</td>
            <td>Коэффициент DRC(макс)</td>
            <td>Ожидаемая концентрация</td>
        </tr>
        <tr>
            <td><?= $drugsRow[0]['delta_t'] ?></td>
            <td><?= $drugsRow[0]['coeff_drc_middle'] ?></td>
            <td><?= $drugsRow[0]['coeff_drc_min'] ?></td>
            <td><?= $drugsRow[0]['coeff_drc_max'] ?></td>
            <td><?= $drugExpectedConcentration ?> мкг.</td>
        </tr>
        <tr>
            <td>Ферменты и транспортеры:</td>
            <td colspan="4"><?= $drugsRow[0]['ferments_transporter'] ?></td>
        </tr>
        <tr>
            <td>Метаболиты:</td>
            <td colspan="4"><?= $drugsRow[0]['merabolites'] ?></td>
        </tr><tr>
            <td>Соотношение метаболиты/исходные препараты:</td>
            <td colspan="4"><?= $drugsRow[0]['metabolites_ratio'] ?></td>
        </tr>
        <tr>
            <td>Коментраии:</td>
            <td colspan="4"><?= $drugsRow[0]['comments'] ?></td>
        </tr>
        <tr>
            <td>Источники:</td>
            <td colspan="4"></td>
        </tr>


    </table>
<?php


}
}
else{
    ?>
    <div><p>Нет данных для мониторинга</p></div>
<?php
}
?>

<!--<div class="mainPageDiv">-->
<!--    <div class="search">-->
<!---->
<!--            <input type="hidden" id="sType" value="2">-->
<!---->
<!--        <label>Поиск препарата-->
<!--            <input type="text" id="search" name="drugName" autocomplete="off">-->
<!--        </label>-->
<!---->
<!--            --><?php //if(isset($_GET['code'])){
//
//                $codeName = $_GET['code'];
//                if(isset($_GET['drugВosage'])){
//
//                    $querySelectDrugsData = "SELECT drugs_class_name, rus_name, eng_name, drugs_data.*
//                                         FROM drugs_data
//                                             JOIN drugs_name ON drugs_name.id_name=drugs_data.id_name
//                                             JOIN drugs_class ON drugs_class.id_class = drugs_name.id_class
//                                         WHERE drugs_data.id_name=:id_name";
//                    $result = $pdo->prepare($querySelectDrugsData);
//                    $result->execute(['id_name' => $codeName]);
//                    $drugsRow = $result->fetchAll(PDO::FETCH_ASSOC);
//
//                    $coefDRCMiddle = (float)trim(str_replace(',', '.', $drugsRow[0]['coeff_drc_middle']));
//                    $bossage = (float)$_GET['drugВosage'];
//
//                    $drugExpectedConcentration = $coefDRCMiddle*$bossage*1000;
//
//                }
//
//                ?>
<!--            <p >Препарат: --><?//= $_GET['rusName'] ?><!--</p>-->
<!--            --><?php //}?>
<!--        <form action="../cabinet.php">-->
<!--            <input type="hidden" name="service" value="tlm">-->
<!--            <input type="hidden" name="code" value="--><?//= $codeName?><!--">-->
<!--            <input type="text" name="drugВosage" placeholder="дозировка в мг">-->
<!--            <button type="submit">Найти</button>-->
<!---->
<!--        </form>-->
<!--        <div style=" padding: 5px 0px">-->
<!--        <a href="../cabinet.php?service=tlm"><button>очистить</button></a>-->
<!--        </div>-->
<!--    </div>-->
<!--    <div id="display"></div>-->
<!--    <div class="">-->
<!---->
<!--        <table class="tlm">-->
<!--            <tr>-->
<!--                <td>Класс:</td>-->
<!--                <td colspan="4">--><?//= $drugsRow[0]['drugs_class_name'] ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>Преараты (рус/англ):</td>-->
<!--                <td colspan="4">--><?//= $drugsRow[0]['rus_name']."/".$drugsRow[0]['eng_name'] ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>Уровень рекомендации</td>-->
<!--                <td colspan="4">--><?//= $drugsRow[0]['level_tlm'] ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>Терапевтический диапазон</td>-->
<!--                <td colspan="4">--><?//= $drugsRow[0]['terapevt_range'] ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>Уровень предосторожности</td>-->
<!--                <td colspan="4">--><?//= $drugsRow[0]['danger_level'] ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>Коэффициент пересчета</td>-->
<!--                <td colspan="4">--><?//= $drugsRow[0]['scalling_ration'] ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>Биодоступность</td>-->
<!--                <td colspan="4">--><?//= $drugsRow[0]['t1_2'] ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>T1/2</td>-->
<!--                <td colspan="4">--><?//= $drugsRow[0]['biodostupnost'] ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>CL/F&plusmn;SD</td>-->
<!--                <td colspan="4">--><?//= $drugsRow[0]['cl_f_sd'] ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>&Delta;t</td>-->
<!--                <td>Коэффициент DRC(сред)</td>-->
<!--                <td>Коэффициент DRC(мин)</td>-->
<!--                <td>Коэффициент DRC(макс)</td>-->
<!--                <td>Ожидаемая концентрация</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>--><?//= $drugsRow[0]['delta_t'] ?><!--</td>-->
<!--                <td>--><?//= $drugsRow[0]['coeff_drc_middle'] ?><!--</td>-->
<!--                <td>--><?//= $drugsRow[0]['coeff_drc_min'] ?><!--</td>-->
<!--                <td>--><?//= $drugsRow[0]['coeff_drc_max'] ?><!--</td>-->
<!--                <td>--><?//= $drugExpectedConcentration ?><!-- мкг.</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>Ферменты и транспортеры:</td>-->
<!--                <td colspan="4">--><?//= $drugsRow[0]['ferments_transporter'] ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>Метаболиты:</td>-->
<!--                <td colspan="4">--><?//= $drugsRow[0]['merabolites'] ?><!--</td>-->
<!--            </tr><tr>-->
<!--                <td>Соотношение метаболиты/исходные препараты:</td>-->
<!--                <td colspan="4">--><?//= $drugsRow[0]['metabolites_ratio'] ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>Коментраии:</td>-->
<!--                <td colspan="4">--><?//= $drugsRow[0]['comments'] ?><!--</td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>Источники:</td>-->
<!--                <td colspan="4"></td>-->
<!--            </tr>-->
<!---->
<!---->
<!--        </table>-->
<!--    </div>-->
<!--    --><?php
//    //print_r($drugsRow);
//
//    function clearData(){
////        session_destroy();
//        unset($_GET);
//        unset($_POST);
////        header('location:tlm.php');
//    }
//    if(isset($_GET['clear']) && $_GET['clear'] == 'y'){
//        clearData();
//    }
//    ?>
<!--</div>-->
<!--</body>-->
<!--</html>-->
