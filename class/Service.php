<?php


class Service
{
    private $extension;
    private $fileName;
    private $tmpFileName;
    private $path;
    private $allow = ['xls', 'xlsx'];
    private $patientData = [];
    private $research = [];
    private $resultString = [];
    private $BirthDate;
    private $RequestNr;
    private $xmlBody;
    private $xmlPath;

    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    public function setFileName($filename)
    {
        $this->fileName = $filename;
    }

    public function setTmpFileName($tmpFileName)
    {
        $this->tmpFileName = $tmpFileName;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function checkFile()
    {
        if (!in_array($this->extension, $this->allow)) {
            return false;
        }
        return true;
    }

    public function addFile()
    {
        $path = $this->path . $this->fileName;
        if (move_uploaded_file($this->tmpFileName, $path)) {
            return true;
        }
        return false;
    }

    protected function readUplodedFile()
    {

        $excel = PHPExcel_IOFactory::load($this->path . $this->fileName);
        $excel->setActiveSheetIndex(0);
        $sheet = $excel->getActiveSheet();
        foreach ($sheet->toArray() as $key => $row) {
            if ($key < 2) {
                continue;
            }

            if (strpos($row[4], '<') == 1) {
                $upper = trim(substr($row[4], 1));
            } elseif (strpos($row[4], '>') == 1) {
                $lower = trim(substr($row[4], 1));
            } else {
                $ref = explode('-', $row[4]);
                $lower = trim($ref[0]);
                $upper = trim($ref[1]);
            }
            $lower = isset($lower) ? str_replace(',', '.', $lower) : 0.0;
            $upper = isset($upper) ? str_replace(',', '.', $upper) : 0.0;

            $this->research[$i]['code'] = $row[0];
            $this->research[$i]['name'] = $row[1];
            $this->research[$i]['result'] = str_replace(',', '.', $row[2]);
            $this->research[$i]['ed'] = $row[3];
            $this->research[$i]['lower'] = (double)$lower;
            $this->research[$i]['upper'] = (double)$upper;

            $i++;

        }
        return $this->research;
    }

    protected function getReasearchData($mode, array $researchData)
    {
        switch ($mode){
            case 'file':

//        if($this->addFile()){
                $research = $this->readUplodedFile();
//        }

                foreach ($research as $key => $value) {//надо думать!!!
                    if ($value['result'] < $value['lower']) {
                        $this->research[$key]['value'] = 'Понижено';
                        $this->research[$key]['style'] = 'background-color: #00bfff; color: #fa8072; font-weight: bold';
                        $this->research[$key]['match_lower'] = $value['code'];
                    } elseif ($value['result'] > $value['upper']) {
                        $this->research[$key]['value'] = 'Повышено';
                        $this->research[$key]['style'] = ' background-color: #8b0000; color: #008b8b; font-weight: bold';
                        $this->research[$key]['match_upper'] = $value['code'];
                    } else {
                        $this->research[$key]['value'] = 'Норма';
                        $this->research[$key]['style'] = ' background-color: #3cb371; color: #000000; font-weight: bold';
                    }
                }
                break;
            case 'xml':
                foreach($researchData as $research){
                    if(!is_array($research)){
                        continue;
                    }
                    //надо думать!!!
                        if ($research['result'] < $research['lower']) {
                            $this->research[$key]['value'] = 'Понижено';
                            $this->research[$key]['style'] = 'background-color: #00bfff; color: #fa8072; font-weight: bold';
                            $this->research[$key]['match_lower'] = $research['code'];
                        } elseif ($research['result'] > $research['upper']) {
                            $this->research[$key]['value'] = 'Повышено';
                            $this->research[$key]['style'] = ' background-color: #8b0000; color: #008b8b; font-weight: bold';
                            $this->research[$key]['match_upper'] = $research['code'];
                        } else {
                            $this->research[$key]['value'] = 'Норма';
                            $this->research[$key]['style'] = ' background-color: #3cb371; color: #000000; font-weight: bold';
                        }

                }

                break;

        }
        return $this->research;

    }

    public function getResearch($mode, array $researchData)
    {
        //$mode = $mode;
        return $this->getReasearchData($mode, $researchData);
    }

    public function searchMatchResearch($pdo, $mode, array $researchData)
    {
        $researches = $this->getResearch($mode, $researchData);

        $query = "SELECT codes, rules.rules, name, interpretation.interpretation, comments FROM `rules` join interpretation on rules.interpretation_id = interpretation.id";
        $result = $pdo->prepare($query);
        $result->execute();
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($rows); $i++) {
            if ($rows[$i]['codes'] == $rows[$i + 1]['codes']) {
                $interpretations[$i]['codes'] = $rows[$i]['codes'];
                $interpretations[$i]['name'] = $rows[$i]['name'];
                $interpretations[$i]['rules_plus'] = $rows[$i]['rules'];
                $interpretations[$i]['rules_minus'] = $rows[$i + 1]['rules'];
                $interpretations[$i]['interpretation_plus'] = $rows[$i]['interpretation'];
                $interpretations[$i]['interpretation_minus'] = $rows[$i + 1]['interpretation'];
                $interpretations[$i]['comments'] = $rows[$i]['comments'];

            }
        }

        //делаем массивы для сравнения с превышениями и с понижениями

        foreach ($researches as $value) {
            if (isset($value['match_upper'])) {
                $upper[] = $value['match_upper'];
            }
            if (isset($value['match_lower'])) {
                $lower[] = $value['match_lower'];
            }
        }

        //приступаем к формированию интерпритаций
        $i = 0;
        $resultString = [];
        foreach ($interpretations as $value) {

            $ourCodes = explode(',', $value['codes']);
            foreach ($ourCodes as $key => $code) {
                $ourCodes[$key] = trim($code);
            }

//если повышеные показатели
            if (count(array_intersect($ourCodes, $upper)) == count($ourCodes) /*|| count(array_intersect($ourCodes, $arrUpper)) == 0*/) {
                $this->resultString[$i]['interpretation'] = $value['interpretation_plus'];
                $this->resultString[$i]['interpretation_name'] = $value['name'];
                $this->resultString[$i]['research_code'] = $value['codes'];
                $i++;

            } elseif (count(array_intersect($ourCodes, $lower)) == count($ourCodes) /*|| count(array_intersect($ourCodes, $arrLower)) == 0*/) {
                $this->resultString[$i]['interpretation'] = $value['interpretation_minus'];
                $this->resultString[$i]['interpretation_name'] = $value['name'];
                $this->resultString[$i]['research_code'] = $value['codes'];
                $i++;
            }
        }
        foreach ($this->resultString as $key => $value) {
            $i = 0;
            $codes = explode(',', $value['research_code']);
            foreach ($codes as $keys => $code) {
                $codes[$keys] = trim($code);
            }
            foreach ($researches as $res) {
                if (in_array($res['code'], $codes)) {
                    $this->resultString[$key][$i]['code'] = $res['code'];
                    $this->resultString[$key][$i]['result'] = $res['result'];
                    $this->resultString[$key][$i]['name'] = $res['name'];
                    $this->resultString[$key][$i]['value'] = $res['result'] < $res['lower'] ? 'понижено' : 'повышено';
                    $i++;
                }
            }
        }

        return $this->resultString;

    }

    public function dropFile()
    {
        $path = $this->path . $this->fileName;
        if (file_exists($path)) {
            unlink($path);
        }

    }

//поиск пациента и работа с его данными
    private function setPatientData($name, $sirname, $lastName, $birthday, $orders, $sex, $doctor)
    {
        $this->patientData['name'] = $name;
        $this->patientData['sirname'] = $sirname;
        $this->patientData['lastName'] = $lastName;
        $this->patientData['birthday'] = $birthday;
        $this->patientData['orders'] = $orders;
        $this->patientData['sex'] = $sex;
        $this->patientData['doctor'] = $doctor;
        return $this->patientData;
    }

    public function writeResearch($pdo, int $pid, int $oid, $mode)
    {
        $research = $this->getResearch($mode);
        if ($this->searchMatchOrders($pdo, $pid, $oid)) {
            foreach ($research as $value) {

                $data = ['pid' => $pid, 'oid' => $oid, 'code' => $value['code'], 'value' => $value['result']];

                echo "<br>";
                $queryInsertResearch = "INSERT INTO patient_orders (`patient_id`, `order_id`, `code`, `value`) values(:pid, :oid, :code, :value)";
                $result = $pdo->prepare($queryInsertResearch);
                $result->execute($data);
            }
            $this->matchOrders($pdo, $pid, $oid);
        }

    }

    public function searchPatient($pdo, $name, $sirname, $lastName, $birthday, $orders, $sex, $doctor, $oid, $mode)
    {
        $patientData = $this->setPatientData($name, $sirname, $lastName, $birthday, $orders, $sex, $doctor);
        $data = ['name' => $this->patientData['name'], 'lastName' => $this->patientData['lastName'],
            'sirname' => $this->patientData['sirname'], 'birthday' => $this->patientData['birthday']];
        $oid = $oid;
//        echo "<pre>";
//        print_r($patientData);
//        echo "</pre>";
        $querySearchPatient = "SELECT * FROM patient 
                                       WHERE 
                                             patient_name LIKE :name 
                                         AND patient_patronimyc LIKE :lastName 
                                         AND patient_sirname LIKE :sirname 
                                         AND patient_birthday LIKE :birthday";

        $result = $pdo->prepare($querySearchPatient);
        if ($result->execute($data)) {
            $row = $result->fetchAll(PDO::FETCH_ASSOC);

            if (count($row) == 0) {
                $data1 = $data;
                $data1['sex'] = $patientData['sex'];
                $data1['doctor'] = $patientData['doctor'];
                $queryInsertPatient = "INSERT INTO patient (`id_patient`, `patient_name`, `patient_patronimyc`,
                     `patient_sirname`, `patient_age`, `patient_birthday`, `patient_sex`,`patient_doctor`)
                     VALUES (0, :name, :lastName, :sirname, 0, :birthday, :sex, :doctor )";
                $result = $pdo->prepare($queryInsertPatient);
                $result->execute($data1);
                $row = $this->searchPatient($pdo, $patientData['name'], $patientData['sirname'], $patientData['lastName'], $patientData['birthday'],
                $patientData['orders'], $patientData['sex'], $patientData['doctor'], $oid, $mode);

            }

        }
        echo "<pre>";
        print_r($row);
        echo "</pre>";
        echo "!!!-- ".$oid;
        $this->writeResearch($pdo, $row[0]['id_patient'], $oid, $mode);

        return $row;
    }

    public function matchOrders($pdo, $pid, $oid)
    {
        $data = ['pid' => $pid, 'oid' => $oid];
//        $querySelectMatchOrders = "SELECT * FROM orders WHERE patient_id = :pid AND order_id = :oid";
//        $result = $pdo->prepare($querySelectMatchOrders);
//        $result->execute($data);
//        $row = $result->fetchAll(PDO::FETCH_ASSOC);
        if ($this->searchMatchOrders($pdo, $pid, $oid)) {
            $queryInsertMatchOrders = "INSERT INTO match_orders (`patient_id`, `order_id`) VALUES (:pid, :oid)";
            $result = $pdo->prepare($queryInsertMatchOrders);
            $result->execute($data);
            $row = $this->matchOrders($pdo, $data['pid'], $data['oid']);
            return true;
        }
        return false;
    }

    private function searchMatchOrders($pdo, $pid, $oid)
    {
        $data = ['pid' => $pid, 'oid' => $oid];
        $querySelectMatchOrders = "SELECT * FROM orders WHERE patient_id = :pid AND order_id = :oid";
        $result = $pdo->prepare($querySelectMatchOrders);
        $result->execute($data);
        $row = $result->fetchAll(PDO::FETCH_ASSOC);
        if (count($row) == 0) {
            return true;
        }
        return false;
    }
//тут происходит работа с апи ЛИС.
//формируем строку запроса
//отправляем форму
//получаем ответ
//обрабатываем ответ
    private function setPathXML($BirthDate, $RequestNr)
    {
        $this->xmlPath = 'xml/' . $RequestNr . '_' . $BirthDate . '.xml';
        return $this->xmlPath;
    }
    public function createXMLString($BirthDate, $RequestNr)
    {

        $this->BirthDate = strtotime($BirthDate);
        $this->BirthDate = date('d.m.Y', $this->BirthDate) . " 00:00:00.000";
        $currentDate = date("d.m.Y H:i:s");

        $this->RequestNr = $RequestNr;
        $this->xmlBody = '<?xml version="1.0" encoding="Utf-8"?>' . "\r\n";
        $this->xmlBody .= '<Envelope SessionId="0" Date="' . $currentDate . '"><MethodCall Name="web-request-info">' . "\r\n";
        $this->xmlBody .= '<Params RequestNr="' . $this->RequestNr . '" BirthDateMode="true" BirthDate="' . $this->BirthDate . '" NeedResults="true" ClientIp="127.0.0.1">';
        $this->xmlBody .= '</Params></MethodCall></Envelope>';
//echo $this->RequestNr, " -- ", $this->BirthDate, "<br>";
        return $this->xmlBody;
    }

    public function sendQueryToLIS($BirthDate, $RequestNr)
    {
        $this->createXMLString($BirthDate, $RequestNr);
        $post_options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => $this->xmlBody
            )
        );
        $context = stream_context_create($post_options);
        $contents = file_get_contents(EIP_URL, false, $context);
        $path = $this->setPathXML($BirthDate, $RequestNr);
        $fh = fopen($path, 'w');
        fwrite($fh, $contents);
        fclose($fh);
        return true;
    }

    public function getXMLContent($BirthDate, $RequestNr){
        $this->sendQueryToLIS($BirthDate, $RequestNr);
        $xml = simplexml_load_file($this->xmlPath);

        if(isset($xml->MethodResponse->Error)){
            $this->research['errores'] = "ОШИБКА: Заявка с номером {$xml->MethodResponse->Result->Request['InternalNr']} не найдена. Проверьте правильность ввода номера заявки и даты рождения пациента";
            return $this->research['errores'];
        }
        else{
            if(strlen(trim($xml->MethodResponse->Result->Request['BirthMonth'])) < 2){
                $month = str_replace(trim($xml->MethodResponse->Result->Request['BirthMonth']), "0".trim($xml->MethodResponse->Result->Request['BirthMonth']), trim($xml->MethodResponse->Result->Request['BirthMonth']));
            }
            else{
                $month = trim($xml->MethodResponse->Result->Request['BirthMonth']);
            }
            if(strlen(trim($xml->MethodResponse->Result->Request['BirthDay'])) < 2){
                $day = str_replace(trim($xml->MethodResponse->Result->Request['BirthDay']), "0".trim($xml->MethodResponse->Result->Request['BirthDay']), trim($xml->MethodResponse->Result->Request['BirthDay']));
            }
            else{
                $day = trim($xml->MethodResponse->Result->Request['BirthDay']);
            }

            $this->research['patientLastName'] = trim($xml->MethodResponse->Result->Request['LastName']);
            $this->research['patientFirstName'] = trim($xml->MethodResponse->Result->Request['FirstName']);
            $this->research['patientMiddleName'] = trim($xml->MethodResponse->Result->Request['MiddleName']);
            $this->research['InternalNr'] = trim($xml->MethodResponse->Result->Request['InternalNr']);
            $this->research['patientEmail'] = trim($xml->MethodResponse->Result->Request['PatientEmailAddress']);
            $this->research['patientSex'] = trim($xml->MethodResponse->Result->Request['Sex'] == 2 ? 'Ж' : 'М');
            $this->research['patientBirthday'] = trim($xml->MethodResponse->Result->Request['BirthYear']).'-'.$month.'-'.$day;

            $i = 0;

            foreach($xml->MethodResponse->Result->Request->Responses->Item as $items){

                foreach($items->Works->Item as $research){

                    $norms = explode('-', $research['Norms']);
                    $lower = trim($norms[0]);
                    $upper = trim($norms[1]);
                    $code = trim($research['Code']);
                    $name = trim($research['Name']);
                    $value = trim($research['Value']);
                    $unitName = trim($research['UnitName']);
                    $this->research[$i]['code'] = $code;
                    $this->research[$i]['name'] = $name;
                    $this->research[$i]['result'] = $value;
                    $this->research[$i]['ed'] = $unitName;
                    $this->research[$i]['lower'] = (double)$lower;
                    $this->research[$i]['upper'] = (double)$upper;

                    $i++;
                }

            }

        }
        return $this->research;
    }

}