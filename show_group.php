<?php

if (isset($string)){
foreach($string as $sKey=>$value){
    if(!is_array($value)){
        continue;
    }
    foreach($researchData as $key => $item){
        if($value['code'] == $key){
            $string[$sKey]['rValue'] = $item['value'];
            $string[$sKey]['style'] = $item['style'];
        }
    }
}
//    echo "<pre>";
//    print_r($string);
//    echo "</pre>";
$querySelectGroups = "SELECT 
                         big_block.name as group_name, 
                         small_block.name as subgroup_name, 
                         unit_block.name as unit, 
                         unit_list as list 
                      FROM `big_block` 
                          JOIN small_block on big_block.bid = small_block.bid 
                          JOIN unit_block on small_block.sid = unit_block.sid";

$result = $pdo->prepare($querySelectGroups);
$result->execute();
while($rows = $result->fetch(PDO::FETCH_ASSOC)){
    $groupName[] = $rows['group_name'];
    $subgroupName[] = $rows['subgroup_name'];
    $unit[$rows['group_name']][$rows['subgroup_name']][$rows['unit']] = $rows['list'];
//    echo "<pre>";
//    print_r($unit);
//    echo "</pre>";
}
$groupName = array_unique($groupName);
$subgroupName = array_unique($subgroupName);
?>
<table class="show_result">

    <?php
    foreach($unit as $unitKey => $items){
        foreach ($groupName as $gname){
            if($unitKey == $gname){
                ?>
                <tr>
                    <td colspan = 5 ><div class="gname"><?= $gname?></div></td>
                </tr>
                <?php
                foreach ($items as $itemKey=>$item){
                    foreach ($subgroupName as $sgName){
                        if($itemKey == $sgName){
                            ?>
                            <tr>
                                <td colspan =5 ><div class="sgname"><?= $sgName?></div></td>
                            </tr>
                            <?php
                            foreach($item as $valueKey => $value){
                                ?>
                                <tr>
                                    <td colspan="5" ><div class="unitname"><?= $valueKey?></div></td></tr>
                                <tr><td class="result_head">Наименование</td><td class="result_head">Значение</td><td class="result_head">Результат</td><td class="result_head">Нижняя граница</td><td class="result_head">Верхняя граница</td></tr>
                                <?php
                                foreach($string as $item){
                                    if(!is_array($item)){
                                        continue;
                                    }
                                    if(strpos($value, $item['code'])!==false){
                                        ?>

                                        <tr>
                                            <td  ><?= $item['name'] ?></td>
                                            <td class="result"><?= $item['result'] ?></td>
                                            <td <?= $item['style'] ?>><?= $item['rValue'] ?></td>
                                            <td class="result"><?= $item['lower'] ?></td>
                                            <td class="result"><?= $item['upper'] ?></td>
                                        </tr>

                                        <?php
                                    }
                                }
                                ?>
                                <tr><td colspan="5" class="empty">&nbsp;</td></tr>
                                    <?php

                            }
                        }
                    }
                }
            }
        }
    }

    ?>
</table>

<?php

}
