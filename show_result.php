<?php

if (isset($string)) {


    $resultString = $service->searchMatchResearch($pdo, $researchData);
    if(empty($resultString)){
        echo "Отклонения не обнаружены или данных для интерпритации нет";
    }
//echo "<pre>";
//    print_r($string);
//    echo "</pre>";
//print_r($string);
    ?>

    <table class="show_result">

        <?php
        $i = 0;
        foreach ($resultString as $result) {

            foreach ($string as $value) {
                if (!is_array($value)) {
                    continue;
                }
//           echo $value['code'], ' = ', $result['research_code'], "<br>";
                if (strpos($result['research_code'], trim($value['code'])) !== false) {
//echo "is true {$value['name']}"."<br>";
                    $result[$i]['name'] = $value['name'];
                    $result[$i]['value'] = $value['result'];
                    $result[$i]['upper'] = $value['upper'];
                    $result[$i]['lower'] = $value['lower'];
                    $result[$i]['code'] = $value['code'];
                    if($value['result'] < $value['lower']){
                        $result[$i]['result'] = 'Понижено';
                    }
                    if($value['result'] > $value['upper']){
                        $result[$i]['result'] = 'Повышено';
                    }
                    $i++;

                }

            }
            ?>
            <tr>
                <td colspan="6" class="big_result"><?= $result['interpretation_name'] ?></td>
            </tr>
                <?php
            foreach ($result as $value) {
                if (!is_array($value)) {
                    continue;
                }
                ?>
                <tr>
                    <td class="inCode"><?= $value['code'] ?></td>
                    <td><?= $value['name'] ?></td>
                    <td class="result"><?= $value['result'] ?></td>
                    <td class="result"><?= $value['value'] ?></td>
                    <td class="result"><?= $value['lower'] ?></td>
                    <td class="result"><?= $value['upper'] ?></td>
                </tr>
                    <?php
            }
            ?>
            <td><td >Интерпретация:</td>
            <td colspan="5" class="result_interpretation">
                <?= $result['interpretation'] ?> </td>
            </tr>
            <tr><td colspan="6" class="empty">&nbsp;</td></tr>
            <?php

        }
        ?>

    </table>
    <?php

}
else{
    ?>
    <div>Результаты не загружени. Данных для интерпритации нет</div>
<?php
}

?>