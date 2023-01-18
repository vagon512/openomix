<?php
include_once 'include/config.php';
//include_once 'include/db.php';
session_start();

ob_start();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <title>Живой поиск</title>
    <!-- Подключаем библиотеку jQuery -->
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <!-- Подключаем наш файл скрптов -->
    <script type="text/javascript" src="script.js"></script>
    <!-- Подключаем наш файл стилей-->
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<form>
    <!-- Поле поиска -->
    <input type="hidden" id="sType" value="1">
    <label>
        <input type="text" id="search" placeholder="Поиск исследований" autocomplete="off"/>
    </label>
</form>
<form method="post">
    <input type="text" name="interpretationName" placeholder="наименование">
    <input type="radio" name="rule" value="Понижено"checked>Понижено <br>
    <input type="radio" name="rule" value="Повышено">Повышено <br>
    <textarea rows="15" cols="60" name="description"></textarea><br>
    <input type="text" name="comments" placeholder="комментарий">

<p>
    <b>введте код или название теста </b>
    Например: <i>n25-12 или лактат</i>
</p>

<!-- Контейнер для результатов поиска -->
<div id="display"></div>

<?php
function clearData(){
    session_destroy();
    unset($_GET);
    unset($_POST);
    header('location:livemenu.php');
}

if(isset($_GET['delItem']) && $_GET['delItem'] === 'y'){

    foreach ($_SESSION['test'] as $key => $item){

        if(trim($item['code']) == trim($_GET['code'])){
           // echo $key.' '.$item['code'].' '.$_GET['code'];
            unset($_SESSION['test'][$key]);
            header('location:livemenu.php');
        }
    }
}

if(isset($_GET['clear']) && $_GET['clear'] === 'y'){
    clearData();
}
if(isset($_SESSION['test'])){
    $i = count($_SESSION['test']);
}
else{
    $i = 0;
}

if (isset($_GET['name']) && isset($_GET['code'])) {

    $_SESSION['test'][$i]['name'] = $_GET['name'];
    $_SESSION['test'][$i]['code'] = $_GET['code'];

}
//include 'livemenu_selected.php';
if (isset($_SESSION['test'])) {
?>
<table>
<?php
    foreach ($_SESSION['test'] as $items) {

        ?>
        <tr><td><?= $items['code'] ?></td> <td><?= $items['name'] ?></td><td></td><td><a href="?delItem=y&code=<?= $items['code'] ?>"><button>-</button></a></td></tr>
        <?php

    }
    ?>
    <button type="submit">добавить</button>
</form>
<a href="livemenu.php?clear=y"><button>очистить</button></a>
</table>

    <?php

}

//формируем строку для записи в бд
if(isset($_POST['interpretationName'])){
    echo "jlk;l;";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    $intepretationString = ['name'=>$_POST['interpretationName'], 'interpretation'=>$_POST['description'], 'comments'=>$_POST['comments']];

    $query = "INSERT INTO interpretation (`id`, `name`, `interpretation`, `comments`) VALUES(0, :name, :interpretation, :comments)";
    $result = $pdo->prepare($query);
    if($result->execute($intepretationString)){
        foreach($_SESSION['test'] as $item){
            $codes .= $item['code'].', ';
        }
        $codes = substr($codes, 0, -2);

        $queryMaxID = "SELECT max(interpretation_id) as id FROM rules";
        $result = $pdo->prepare($queryMaxID);
        $result->execute([]);

        $maxIDs = $result->fetch(PDO::FETCH_ASSOC);

        $maxID = $maxIDs['id'];

        $queryUpdate = "UPDATE rules SET codes = :codes, rules = :rules WHERE interpretation_id = :maxid ";
        $update = ['codes' => $codes, 'rules' => $_POST['rule'], 'maxid' => $maxID];
        $result = $pdo->prepare($queryUpdate);
        if($result->execute($update)){
            clearData();
        }
        else{
            echo "jaslkjdfsjaldfjal;jlds";
        }
    }

//    var_dump($intepretationString);


//  $rulesString =
}

?>
</body>
</html>