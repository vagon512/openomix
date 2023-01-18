<?php
if(isset($_POST) && !empty($_POST)){

//require_once "$_SERVER[DOCUMENT_ROOT]/xls/Classes/PHPExcel/IOFactory.php";
//include_once 'docs/interpretation.php';
    $service = new XMLService();

    //if(isset($_POST['RequestNr']) && isset($_POST['BirthDate'])) {
        $BirthDate = strtotime($_POST['BirthDate']);

        $currentDate = date("d.m.Y H:i:s");
        $RequestNr = $_POST['RequestNr'];



        $string = $service->getXMLContent($_POST['BirthDate'], $RequestNr);
//        echo "<pre>";
//        print_r($string);
//        echo "</pre>";
        if(isset($string['errores'])){

            die($string['errores']);
        }
        $researchData = $service->getReasearchData($string);

        $patient = $service->searchPatient($pdo, $string['patientFirstName'], $string['patientLastName'], $string['patientMiddleName'], $string['patientBirthday'],
            $string['patientInternalNr'], $string['patientSex'], $_SESSION['id'], $string['InternalNr']);

        $service->writeResearch($pdo, $patient[0]['id_patient'], $string['InternalNr'], $string);

    //}
//    echo "<pre>";
//    print_r($string);
////    print_r($researchData);
//    echo "</pre>";

}
if(isset($string)){
    ?>
<table class="show_result">
    <tr>
        <td class="result_head">Код</td>
        <td class="result_head">Наименование</td>
        <td class="result_head">Значение</td>
        <td class="result_head">Резлуьтат</td>
        <td class="result_head">Нижняя граница</td>
        <td class="result_head">Верхняя граница</td>
        <td class="result_head">Комментарий</td>
    </tr>
    <?php
//    echo "<pre>";
//    print_r($string);
//    echo "</pre>";

    foreach($string as $value){
        if (!is_array($value)) {
            continue;
        }
        ?>
        <tr><td><?= $value['code']?></td><td><?= $value['name']?></td><td class="result"><?= $value['znak'].$value['result']/*." ". $value['ed']*/ ?></td>
            <td><?php

                foreach($researchData as $key=>$research){
                    if($value['code'] == $key){
                        ?>
                        <div <?= $research['style'] ?>><?= $research['value']; ?></div>

                <?php
                    }
                }

                ?></td>
            <td class="result"><?= $value['lower']?></td><td class="result"><?= $value['upper']?></td><td><?= $value['orders_comment']?></td></tr>
    <?php
    }
    ?>
</table>
<?php
}
else{
    ?>
    <div>Результаты не загружени. Данных для отображения нет</div>
    <?php
}

//print_r($string);
?>


