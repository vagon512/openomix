<?php

include "include/db.php";

if (isset($_POST['searchType']) && $_POST['searchType'] == 1) {
//echo $_POST['search'];
// Помещаем поисковой запрос в переменной
$Name = $_POST['search'];


// Запрос для выбора из базы данных
$Query = "SELECT code, name FROM tests WHERE name LIKE :name OR code LIKE :name LIMIT 15";

//Производим поиск в базе данных
    $row = $pdo->prepare($Query);
    $row->execute(['name' => "%".$Name."%"]);



// Создаем список для отображения результатов
echo '<ul>';

    //Перебираем результаты из базы данных
    while ($Result = $row->fetch(PDO::FETCH_ASSOC)) {

    ?>
    <!-- Создаем элементы списка. При клике на результат вызываем функцию обработчика fill() из файла "script.js". В параметре передаем найденное имя-->

    <li onclick='fill("<?php echo $Result['name']; ?>")'>
        <a href="?code=<?php echo $Result['code']; ?> &name= <?php echo $Result['name']; ?>">
            <?php echo $Result['code']." ".$Result['name']; ?>
        </a>
    </li>

    <?php
    }
}
?>

</ul>

<?php

if (isset($_POST['searchType']) && $_POST['searchType'] == 2) {

// Помещаем поисковой запрос в переменной
    $Name = $_POST['search'];
echo $Name;

// Запрос для выбора из базы данных
    $Query = "SELECT * FROM drugs_name WHERE rus_name LIKE :name OR eng_name LIKE :name LIMIT 8";

//Производим поиск в базе данных
    $row = $pdo->prepare($Query);
    $row->execute(['name' => "%".$Name."%"]);



// Создаем список для отображения результатов
    echo '<ul>';

    //Перебираем результаты из базы данных
    while ($Result = $row->fetch(PDO::FETCH_ASSOC)) {

        ?>
        <!-- Создаем элементы списка. При клике на результат вызываем функцию обработчика fill() из файла "script.js". В параметре передаем найденное имя-->

        <li onclick='fill("<?php echo $Result['rus_name']; ?>")'>
            <a href="?service=tlm&code=<?php echo $Result['id_name']; ?> &rusName= <?php echo $Result['rus_name']; ?>&engName= <?php echo $Result['eng_name']; ?>">
                <?php echo $Result['rus_name']." ".$Result['eng_name']; ?>
            </a>
        </li>

        <?php
    }
}
?>

</ul>