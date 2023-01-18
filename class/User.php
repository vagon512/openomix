<?php


class User
{
    private $userName;
    private $userSirname;
    private $userPatromymic;
    private $userLogin;
    private $userPasswd;
    private $userBirthday;
    private $userEmail;
    private $userPhone;
    private $userPrivileges = 3;
    private $formatString;
    private $userData = [];
    private $errores = [];
    private $salt;
    private $uid;


    private function delTags($myString){
        $this->formatString = trim(strip_tags($myString));
        return $this->formatString;
    }

    public function setSalt($salt){
        $this->salt = (string) $salt;
    }

    public function setUid($uid = 0){
        $this->uid = $uid;
    }

    public function setUserName($userName){
        $this->userName = $this->delTags($userName);

    }

//    public function getUserName(){
//        return $this->userName;
//    }

    public function setUserSirname($userSirname){
        $this->userSirname = $this->delTags($userSirname);
    }

    public function setUserPatronymic($userPatronimyc){
        $this->userPatromymic = $this->delTags($userPatronimyc);
    }

    private function setUserPhone($userPhone){
        $this->userPhone = "7".$userPhone;
    }

    public function setUserLogin($userLogin){
        $this->userLogin = $this->delTags($userLogin);
    }

    private function setUserPasswd($userPasswd){
        $this->userPasswd = $userPasswd;
    }

    public function setUserBirthday($userBirthday){
        $this->userBirthday = $this->delTags($userBirthday);
    }

    public function getBirthday(){
        return $this->userBirthday;
    }

    private function setUserEmail($userEmail){
        $this->userEmail = $this->delTags($userEmail);
    }

    public function setUserPrivileges($userPrivileges=3){
        $this->userPrivileges = $userPrivileges;
    }
//блок с проверками данных

    public function checkPassword($passwd, $retypepasswd){
        //пароль должен быть не короче 6 символов
        //должен содержать строчные, прописные и цифры
        $pattern = '((?=.*[A-Z])(?=.*[a-z])(?=.*\d).{7,21})';//' ^.*(?=.{7,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$ ';
        if(!preg_match($pattern,$passwd)){
            $this->errores['password'] = 'Пароль должен содержать латинские строчные и прописные буквы, цифры и спецсимволы. Должен быть от 7 до 21 символа';
            return false;
        }
        if($passwd != $retypepasswd){
            $this->errores['retype'] = 'Введенные пароли не совпадают';
            return false;
        }
        $passwd .= $this->salt;
        $passwd = password_hash($passwd, PASSWORD_DEFAULT);
        $this->setUserPasswd($passwd);
        return true;
    }

    public function checkEmail($email){
        if(filter_var($email, FILTER_SANITIZE_EMAIL)){
            $this->setUserEmail($email);
            return true;
        }
        $this->errores['email'] = 'Неверный формат адреса электронной почты';
        return false;
    }

    public function checkPhoneNumber($phone){
        if(!preg_match("/^[0-9]{10,10}+$/", $phone)){
            $this->errores['phone'] = 'формат телефона неверный';
            return false;
        }
        $this->setUserPhone($phone);
        return true;
    }

    public function getErrores(){
        return $this->errores;
    }
    public function clearErrores(){
       unset($this->errores)  ;
    }

//регистрация пользователя, если все ОК
    private function registration($pdo){
        $verify = password_hash($this->userEmail.$this->userLogin, PASSWORD_DEFAULT);
        $login = password_hash($this->userLogin, PASSWORD_DEFAULT);
        $email = password_hash($this->userEmail, PASSWORD_DEFAULT);
        $registrationData = array('id'=>0, 'sirname'=>$this->userSirname, 'name'=>$this->userName, 'patron'=>$this->userPatromymic,
            'login'=>$this->userLogin, 'email'=>$this->userEmail, 'birthday'=>$this->userBirthday,
            'passwd'=>$this->userPasswd, 'salt'=>$this->salt, 'privileges'=>$this->userPrivileges, 'phone'=>$this->userPhone, 'activate'=>0, 'acode'=>$verify);
        $queryInsertUserData = "INSERT INTO users (user_id, user_sirname, user_name, user_patronymic,
                                                   user_login, user_email, user_birthday, user_password, salt, user_privileges, user_phone, activation_status, activation_code)
                                        VALUES (:id, :sirname, :name, :patron, :login, :email, :birthday, :passwd, :salt, :privileges, :phone, :activate, :acode)";
        $result = $pdo->prepare($queryInsertUserData);
        $activationLink = SITE_NAME.'activation.php?verify='.$verify.'&ul='.$login.'&ue='.$email;
        if($result->execute($registrationData)){
            $to = '<'.$registrationData['email'].'>';
            $mailSubject = 'активация профиля';
            $message = "Вы получили это письмо, так как зарегистрировались на портале omixsense.\n
                        для аактивации профиля <a href='".$activationLink."'>нажмите здесь</a>
                        Или скопируйте и вставте в адресную строку браузера ссылку: <br>$activationLink";
            $headers  = "Content-type: text/html; charset=utf-8 \r\n";
            $headers .= "From: <activate@omixsense.ru>\r\n";
            mail($to, $mailSubject, $message, $headers);

            return true;
        }
        else{
            return false;
        }

    }

    public function checkUserData($pdo){
        $data = ['login'=>$this->userLogin, 'email'=>$this->userEmail];

        $query = 'SELECT * FROM users WHERE user_login = :login OR user_email = :email';
        $result = $pdo->prepare($query);
        $result->execute($data);
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);

        if( count($rows) < 1){

            $this->registration($pdo);
            return true;
        }

        return false;
    }

    public function verification($pdo, $verification, $userLogin, $userEmail){

        $data = ['verify'=>$verification];
        $query = 'SELECT * FROM users WHERE activation_code = :verify';
        $row = $pdo->prepare($query);
        $row->execute($data);
            $result = $row->fetch(PDO::FETCH_ASSOC);

            if(password_verify($result['user_email'], $userEmail) && password_verify($result['user_login'], $userLogin)){
                //апдейтим запись пользователя
                $row = $pdo->prepare("UPDATE users SET activation_status=1, activation_code='' WHERE user_login = :login");
                if($row->execute(['login'=>$result['user_login']])){
                return true;
                }
            }
            return false;

    }
//получение данных пользователя при верном вводе логина и пароля
    public function getUserData($pdo, $userName, $userPassword){
        $data = array('email'=>$userName, 'login'=>$userName);
        $querySelectUser = "SELECT * FROM users WHERE user_email = :email OR user_login = :login";
        $row = $pdo->prepare($querySelectUser);
        $row->execute($data);
        $result = $row->fetch(PDO::FETCH_ASSOC);
        if(isset($result['user_login']) && password_verify($userPassword.$result['salt'], $result['user_password'])){
            $_SESSION['id']            = $result['user_id'];
            $_SESSION['name']          = $result['user_name'];
            $_SESSION['patronimyc']    = $result['user_patronymic'];
            $_SESSION['sirname']       = $result['user_sirname'];
            $_SESSION['login']         = $result['user_login'];
            $_SESSION['email']         = $result['user_email'];
            $_SESSION['privileges']    = $result['user_privileges'];
            $_SESSION['activation']    = $result['activation_status'];
            $_SESSION['errors']        = '';

        }
        else{
            $_SESSION['errors'] = 'Неверный логин или пароль';
        }

        if(!empty($_SESSION['errors'])){
            return false;
        }
        else{
            return true;
        }
    }


    public function logout(){
        session_regenerate_id();
        session_destroy();

    }

    public function searchUser($pdo, $email, $login)
    {
//        echo $email, $login;
        $querySearchUser = "SELECT * FROM users WHERE user_email LIKE :email AND user_login LIKE :login";
        $result = $pdo->prepare($querySearchUser);
        $result->execute(['email' => $email, 'login' => $login]);
        $userData = $result->fetch(PDO::FETCH_ASSOC);
        if (isset($userData) && ($userData) > 0) {
            $key3 = md5($userData['user_id']);
            $resetString = SITE_NAME . 'reset.php?page=reset&key=' . $userData['user_password'] . '&key2=' . $userData['salt'] . '&key3=' . $key3;
            $to = '<'.$email.'>';
            $mailSubject = 'восстановление пароля';
            $message = "Вы получили это письмо, так как восстановление пароля на портале omixsense.\n
                                для восстановления пароля <a href=" . $resetString . ">нажмите здесь</a>";
            $headers = "Content-type: text/html; charset=utf-8 \r\n";
            $headers .= "From: <activate@omixsense.ru>\r\n";
        } else {
            $resetString = "Пользователя с таким логином или email не найдено";
        }


        if(mail($to, $mailSubject, $message, $headers)){
            return true;
        }
        return false;
    }

    public function resetPassword($pdo, $password, $retypePassword){

        if($this->checkPassword($password, $retypePassword)){
            $data = ['id'=>$this->uid, 'salt'=>$this->salt, 'passwd'=>$this->userPasswd];
            $queryUpdatePassword = "UPDATE users SET user_password = :passwd, salt = :salt WHERE md5(user_id) = :id";
            $result = $pdo->prepare($queryUpdatePassword);
            if($result->execute($data)){
                return true;
            }
            return false;
        }
    }

    //простое логирование

    public function logs($pdo, $message){
        $logDate = date("Y-m-d");
        $logTime = date("H:i:s");
        $data = ['date'=>$logDate, 'time'=>$logTime, 'event'=>$message];

        $queryInsertEvent = "INSERT INTO logs (`log_date`, `log_time`, `log_event`) values(:date, :time, :event)";
        $result = $pdo->prepare($queryInsertEvent);
        if($result->execute($data)){
            return true;
        }
        return false;
    }

}



// Gfhjkm12#$90
//9852218052
