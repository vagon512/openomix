<?php
include '../include/db.php';
include '../class/User.php';
$querySelectUsers = "SELECT * FROM users";
$result = $pdo->prepare($querySelectUsers);
$result->execute([]);
$editUser = new User;
?>
<table>
    <tr>
        <td>Фамилия</td>
        <td>Имя</td>
        <td>Отчество</td>
        <td>Логин</td>
        <td>Почта</td>
        <td>Телефон</td>
        <td>Права</td>
        <td></td>
        <td></td>
    </tr>
<?php
while($row = $result->fetch(PDO::FETCH_ASSOC)){
    $users[$row['user_id']]['login'] = $row['user_login'];
    $users[$row['user_id']]['name'] = $row['user_name'];
    $users[$row['user_id']]['sirname'] = $row['user_sirname'];
    $users[$row['user_id']]['patronymic'] = $row['user_patronymic'];
    $users[$row['user_id']]['email'] = $row['user_email'];
    $users[$row['user_id']]['salt'] = $row['salt'];
    $users[$row['user_id']]['privileges'] = $row['user_privileges'];
    $users[$row['user_id']]['phone'] = $row['user_phone'];
    switch($row['user_privileges']) {
        case 1:
            $privileges = 'Admin';
            break;
        case 2:
            $privileges = "Doctor";
            break;
        case 3:
            $privileges = "unconventional";
            break;
        default:
            $privileges = "undefined";
            break;
    }
    ?>
    <tr>
        <td><?= $row['user_sirname']?></td>
        <td><?= $row['user_name']?></td>
        <td><?= $row['user_patronymic']?></td>
        <td><?= $row['user_login']?></td>
        <td><?= $row['user_email']?></td>
        <td><?= $row['user_phone']?></td>
        <td><?= $privileges ?></td>
        <td><a href="users.php?id=<?= $row['user_id']?>"><button>редактировавть</button></a></td>
        <td><a href="users.php?id=<?= $row['user_id']?>&chpsswd=y"><button>изменить пароль</button></a></td>
    </tr>
<?php
}
?>
</table>

<?php
if(isset($_GET['id']) && !isset($_GET['chpsswd'])){
    $userID = $_GET['id'];
    foreach($users as $key => $user){
        if($userID == $key){
            ?>
            <table>
                <tr>
                    <td>Фамилия</td>
                    <td>Имя</td>
                    <td>Отчество</td>
                    <td>Логин</td>
                    <td>Почта</td>
                    <td>Телефон</td>
                    <td>Права</td>
                    <td></td>
                </tr>
                <tr>
                    <form method="post">
                        <input type="hidden" name="changeUserData" value="true">
                    <td><input type="text" name="sirname" value="<?= $user['sirname'] ?>" ></td>
                    <td><input type="text" name="name" value="<?= $user['name'] ?>" ></td>
                    <td><input type="text" name="patronymic" value="<?= $user['patronymic'] ?>" ></td>
                    <td><input type="text" name="login" value="<?= $user['login'] ?>" ></td>
                    <td><input type="text" name="email" value="<?= $user['email'] ?>" ></td>
                    <td><input type="text" name="phone" value="<?= $user['phone'] ?>" ></td>
                    <td><input type="text" name="privileges" value="<?= $user['privileges'] ?>" ></td>
                    <td><button type="submit">изменить данные</button> </td>
                    </form>
                </tr>
            <?php
        }
    }
}

if(isset($_GET['id']) && isset($_GET['chpsswd'])){
    $userID = $_GET['id'];
    foreach($users as $key => $user){
        if($userID == $key){
            ?>
            <table>
            <tr>
                <td>Фамилия</td>
                <td>Имя</td>
                <td>Отчество</td>

            </tr>
                <tr>
                    <td><?= $user['sirname'] ?></td>
                    <td><?= $user['name'] ?></td>
                    <td><?= $user['patronymic'] ?></td>
                </tr>
            <tr>
                <form method="post">
                    <td><input type="text" name="passwd" placeholder="введите пароль"></td>
                    <td><input type="text" name="retypepasswd" placeholder="пароль еще раз"></td>
                    <input type="hidden" name="salt" value="<?= $user['salt'] ?>" >

                    <td><button type="submit">изменить пароль</button> </td>
                </form>
            </tr>
            <?php
        }
    }
}

if(isset($_POST['changeUserData']) && $_POST['changeUserData'] == true){
    $userData = ['user_id' => $userID,
                 'sirname' => $_POST['sirname'],
                 'patronymic' => $_POST['patronymic'],
                 'name' => $_POST['name'],
                 'login' => $_POST['login'],
                 'email' => $_POST['email'],
                 'phone' => $_POST['phone'],
                 'privileges' => $_POST['privileges']];

    $queryUpdateuser = "UPDATE users 
                        SET user_sirname = :sirname, user_name = :name, user_patronymic = :patronymic, 
                        user_login = :login, user_email = :email, user_phone = :phone, user_privileges = :privileges
                        WHERE user_id = :user_id";

    $result = $pdo->prepare($queryUpdateuser);
    $result->execute($userData);

//    $queryUpdateuser = "UPDATE users
//                        SET user_sirname = '".$userData['sirname']."', user_name = '".$userData['name']."', user_patronymic = '".$userData['patronymic']."',
//                        user_login = '".$userData['login']."', user_email = '".$userData['email']."', user_phone = '".$userData['phone']."', user_privileges = ".$userData['privileges']."
//                        WHERE user_id = ".$userData['user_id'];
//
//    echo $queryUpdateuser;
//    print_r($userData);
}