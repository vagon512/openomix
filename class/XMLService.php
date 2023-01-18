<?php


class XMLService
{
    private $path;
    private $patientData = [];
    private $research = [];
    private $resultString = [];
    private $BirthDate;
    private $RequestNr;
    private $xmlBody;
    private $xmlPath;
    private $researchValue = [];

    public function setRequestNR($number)
    {
        $this->RequestNr = $number;
    }

    public function setBirthdate($birthdate)
    {
        $this->BirthDate = $birthdate;
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

    private function createXMLString($BirthDate, $RequestNr)
    {

        $this->BirthDate = strtotime($BirthDate);
        $this->BirthDate = date('d.m.Y', $this->BirthDate) . " 00:00:00.000";
        $currentDate = date("d.m.Y H:i:s");
//echo "in class", $this->BirthDate;
        $this->RequestNr = $RequestNr;
        $this->xmlBody = '<?xml version="1.0" encoding="Utf-8"?>' . "\r\n";
        $this->xmlBody .= '<Envelope SessionId="0" Date="' . $currentDate . '"><MethodCall Name="web-request-info">' . "\r\n";
        $this->xmlBody .= '<Params RequestNr="' . $this->RequestNr . '" BirthDateMode="true" BirthDate="' . $this->BirthDate . '" NeedResults="true">';
        $this->xmlBody .= '</Params></MethodCall></Envelope>';
//echo $this->RequestNr, " -- ", $this->BirthDate, "<br>";
        return $this->xmlBody;
    }

    private function sendQueryToLIS($BirthDate, $RequestNr)
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
        //$contents = simplexml_load_string($context);
//        $path = $this->setPathXML($BirthDate, $RequestNr);
//        $fh = fopen($path, 'w');
//        fwrite($fh, $contents);
//        fclose($fh);
        return $contents;
//        return true;
    }

    private function explodeString($value)
    {
        $value = preg_replace('/[^\d+\,\d+, ^\s]/', '', $value);
        $value = explode(' ', $value);
        foreach ($value as $item) {
            if (strlen(trim($item)) > 0  && trim($item)!==',') {
                $result[] = $item;
            }

        }

        return $result;
    }

    private function findNorms($value)
    {

        $value = str_replace('&#13;&#10', '', $value);

        if (strpos($value, ':') !== false) {
            $value = explode(':', $value);
            $value = $value[1];
            $result = $this->explodeString($value);
//            echo $value;

        }
        elseif (strpos($value, '&lt') !== false || strpos($value, '<') !== false) {
            $result = $this->explodeString($value);
//            echo $value;
        } elseif (strpos($value, '&gt') !== false || strpos($value, '>') !== false) {
            $result = $this->explodeString($value);
//            echo $value;
        } else {
            $result = $this->explodeString($value);
//            echo $value;
        }
//        $result = $this->explodeString($value);
        $lower = isset($result) ? str_replace(',', '.', $result[0]) : 0;
        $upper = isset($result) ? str_replace(',', '.', $result[count($result) - 1]): 0;

        $Norms[0] = round((float)$lower, 3);
        $Norms[1] = round((float)$upper, 3);

        return $Norms;
    }

    public function getXMLContent($BirthDate, $RequestNr)
    {
        $xmlString = $this->sendQueryToLIS($BirthDate, $RequestNr);
        $xml = simplexml_load_string($xmlString);

//      $this->research['xmlstring'] = $myString;
        //$xml = simplexml_load_file($this->xmlPath);
//        echo "<pre>";
//        print_r($this->research);
//        echo "<pre>";
        if (isset($xml->MethodResponse->Error)) {
            $this->research['errores'] = "ОШИБКА: Заявка с номером {$RequestNr} не найдена. Проверьте правильность ввода номера заявки и даты рождения пациента";
            return $this->research;
        }
        elseif($xml->MethodResponse->Result->Request['Done'] == "false"){
            $this->research['errores'] = "ОШИБКА: Заявка с номером {$RequestNr} не закрыта. Дождитесь внесения всех результатов и попробуйте снова";
        }
        else {
            if (strlen(trim($xml->MethodResponse->Result->Request['BirthMonth'])) < 2) {
                $month = str_replace(trim($xml->MethodResponse->Result->Request['BirthMonth']), "0" . trim($xml->MethodResponse->Result->Request['BirthMonth']), trim($xml->MethodResponse->Result->Request['BirthMonth']));
            } else {
                $month = trim($xml->MethodResponse->Result->Request['BirthMonth']);
            }
            if (strlen(trim($xml->MethodResponse->Result->Request['BirthDay'])) < 2) {
                $day = str_replace(trim($xml->MethodResponse->Result->Request['BirthDay']), "0" . trim($xml->MethodResponse->Result->Request['BirthDay']), trim($xml->MethodResponse->Result->Request['BirthDay']));
            } else {
                $day = trim($xml->MethodResponse->Result->Request['BirthDay']);
            }

            $orderDate = trim($xml->MethodResponse->Result->Request['SampleDeliveryDate']);
            $orderDate = explode(' ', $orderDate);
            $orderDate = $orderDate[0];

            $this->research['patientLastName'] = trim($xml->MethodResponse->Result->Request['LastName']);
            $this->research['patientFirstName'] = trim($xml->MethodResponse->Result->Request['FirstName']);
            $this->research['patientMiddleName'] = trim($xml->MethodResponse->Result->Request['MiddleName']);
            $this->research['InternalNr'] = trim($xml->MethodResponse->Result->Request['InternalNr']);
            $this->research['patientEmail'] = trim($xml->MethodResponse->Result->Request['PatientEmailAddress']);
            $this->research['patientSex'] = trim($xml->MethodResponse->Result->Request['Sex'] == 2 ? 'Ж' : 'М');
            $this->research['patientBirthday'] = trim($xml->MethodResponse->Result->Request['BirthYear']) . '-' . $month . '-' . $day;
            $this->research['orderDate'] = $orderDate;

            $i = 0;

            foreach ($xml->MethodResponse->Result->Request->Responses->Item as $items) {

                foreach ($items->Works->Item as $research) {
//                    echo "<pre>";
//                    print_r($research);
//                    echo "</pre>";

                    if (isset($research['IncompleteNorms']) && !isset($research['Norms'])) {
//                        echo "1ЖЖЖЖЖ" . $research['IncompleteNorms'] . "<br>";

                        $textNorms = explode('|', '', $research['IncompleteNorms']);
                        $this->research[$i]['orders_comment'] = trim($textNorms[0]);

                        $norms = $this->findNorms($textNorms[0]);
//                        $lower = trim($norms[0]);
//                        $upper = trim($norms[1]);

                    } elseif (!isset($research['IncompleteNorms']) && !isset($research['Norms'])) {
                        $lower = 0;
                        $upper = 0;
                    } else {

//                        echo "3:::::: ".$research['Code']."<br>";
                        if (strlen($research['Norms']) > 20) {
                            if (strpos($research['Norms'], '|') !== false) {
//                                echo "3-1:::::: ".$research['Code']."<br>";
                                $explodeString = trim($research['Norms']);
                                $textNorms = explode('|', $explodeString);
                                $norms = $this->findNorms($textNorms[0]);
                            } else {
//                                echo "3-2:::::: ".$research['Code']."<br>";
                                $textNorms[0] = $research['Norms'];
                                $norms = $this->findNorms($textNorms[0]);

                            }

                            $this->research[$i]['orders_comment'] = trim($textNorms[0]);


                        } else {

                            $this->research[$i]['orders_comment'] = '';

                            $research['Norms'] = str_replace(" ", '', $research['Norms']);
                            $research['Norms'] = str_replace(" ", '', $research['Norms']);
                            $norms = explode('-', $research['Norms']);

                            if (strpos($norms[0], '<') !== false) {

                                $norms[0] = str_replace('<', '', $norms[0]);
                                $norms[1] = trim($norms[0]);
                                $norms[0] = 0;

                            }
//                            $lower = trim($norms[0]);
//                            $upper = trim($norms[1]);

                        }

                        }
                    $lower = trim($norms[0]);
                    $upper = trim($norms[1]);

                    $code = trim($research['Code']);
                    $name = trim($research['Name']);
                    $value = trim($research['Value']);
                    if(strpos($value, '<') !== false){
                        $position = strpos($value, '<');
                        $znak = substr($value, $position, 1);

                        $value = substr($value, $position+1);
                        $value = round((float)str_replace(',', '.', $value),4);
//                        echo "::".$znak."::".$value."<br>";
                    }
                    elseif(strpos($value, '>') !== false){
                        $position = strpos($value, '<');
                        $znak = substr($value, $position, 1);
                        $value = substr($value, $position+1);
                        $value = round((float)str_replace(',', '.', $value),4);
//                        echo "::".$znak."<br>";
                    }
                    else{
                        $value = round((float)str_replace(',', '.', $value),4);
                        $znak = '';
                    }

                        $unitName = trim($research['UnitName']);
                        $this->research[$i]['code'] = $code;
                        $this->research[$i]['name'] = $name;
                        $this->research[$i]['result'] = $value;
                        $this->research[$i]['znak'] = !empty($znak) ? $znak : '';
                        $this->research[$i]['ed'] = $unitName;
                        $this->research[$i]['lower'] = (float)str_replace(',', '.', $lower);;
                        $this->research[$i]['upper'] = (float)str_replace(',', '.', $upper);;
                        $this->research[$i]['sizeupper'] = strlen($upper);
                        $this->research[$i]['sizelower'] = strlen($lower);

                        $i++;
                    }

                }

            }

//        unlink($this->xmlPath);
        return $this->research;
    }

    //закидываем полученные данные по таблицам
    public function getReasearchData(array $researches)
    {
        //$researchData = $this->getXMLContent($BirthDate, $RequestNr);
        $researchData = $researches;
//        echo "<pre>";
//        print_r($researchData);
//        echo "</pre>";
        foreach ($researchData as $research) {
            if (!is_array($research)) {
                continue;
            }
//            echo $research['code']." === ".$research['znak']."----".strlen($research['znak'])."<br>";

            $research['result'] = str_replace(',', '.', $research['result']);
            $research['lower'] = str_replace(',', '.', $research['lower']);
            $research['upper'] = str_replace(',', '.', $research['upper']);

            //делаем предграницы на случай, если результат приближается к критичной границе
            //за предграницы принимаем значения на 15% ниже верхней границы
            // и на 15% больше нижней границы
            $upLowBorder = $research['upper'] - ($research['upper'] * (15 / 100));
            $lowUpowBorder = $research['lower'] - ($research['lower'] * (15 / 100));

            if (strpos($research['result'], '<') !== false) {
                $research['result'] = str_replace('<', '', $research['result']);
            }

            //надо думать!!!
            if ((double)$research['result'] < (double)$research['lower']) {
                $this->researchValue[$research['code']]['value'] = 'Понижено';
                $this->researchValue[$research['code']]['style'] = ' class="lower_value"';
                $this->researchValue[$research['code']]['match_lower'] = $research['code'];
            } elseif ((double)$research['result'] > $upLowBorder && (double)$research['result'] < (double)$research['upper'] && strlen($research['znak']) == 0) {
                $this->researchValue[$research['code']]['value'] = 'Опасность повышения';
                $this->researchValue[$research['code']]['style'] = ' class="near_border_value"';

            }
            elseif ((double)$research['result'] > (double)$research['lower'] && (double)$research['result'] < $lowUpowBorder && strlen($research['znak']) == 0) {
                $this->researchValue[$research['code']]['value'] = 'Опасность понижения';
                $this->researchValue[$research['code']]['style'] = ' class="near_border_value"';

            }
            elseif ((double)$research['result'] > (double)$research['upper']) {
                $this->researchValue[$research['code']]['value'] = 'Повышено';
                $this->researchValue[$research['code']]['style'] = ' class="upper_value"';
                $this->researchValue[$research['code']]['match_upper'] = $research['code'];
            }
            else {
                $this->researchValue[$research['code']]['value'] = 'Норма';
                $this->researchValue[$research['code']]['style'] = ' class="norm_value"';
            }

            if ($research['lower'] == 0.0 && $research['upper'] == 0.0) {
                $this->researchValue[$research['code']]['value'] = 'без нормы';
                $this->researchValue[$research['code']]['style'] = ' class="norm_value"';
            }

            if (strpos($research['lower'], '<',) !== false) {
                $this->researchValue[$research['code']]['value'] = 'текстовые значения';
                $this->researchValue[$research['code']]['style'] = ' class="norm_value"';
            }

        }

        return $this->researchValue;

    }


    public function matchOrders($pdo, $pid, $oid, $orderDate)
    {
        $data = ['pid' => $pid, 'oid' => $oid, 'date' => $orderDate];

        if ($this->searchMatchOrders($pdo, $pid, $oid)) {
            $queryInsertMatchOrders = "INSERT INTO match_orders (`patient_id`, `order_id`, `order_date`) VALUES (:pid, :oid, :date)";
            $result = $pdo->prepare($queryInsertMatchOrders);
            $result->execute($data);

            return true;
        }

        return false;
    }

    private function searchMatchOrders($pdo, $pid, $oid)
    {
        $data = ['pid' => $pid, 'oid' => $oid];
        $querySelectMatchOrders = "SELECT * FROM match_orders WHERE patient_id = :pid AND order_id = :oid";
        $result = $pdo->prepare($querySelectMatchOrders);
        $result->execute($data);
        $row = $result->fetchAll(PDO::FETCH_ASSOC);
        if (count($row) == 0) {
            return true;
        }
        return false;
    }

    public function writeResearch($pdo, int $pid, int $oid, $researches)
    {
        $research = $researches;

        $orderData = $research['orderDate'];

        if ($this->searchMatchOrders($pdo, $pid, $oid)) {

            foreach ($research as $value) {
                if (!is_array($value)) {
                    continue;
                }

                $data = ['pid' => $pid, 'oid' => $oid, 'code' => $value['code'], 'value' => str_replace(',', '.', $value['result']),
                    'lower' => (double)(str_replace(',', '.', $value['lower'])), 'upper' => (double)(str_replace(',', '.', $value['upper'])),
                    'comment' => $value['orders_comment'], 'znak' => $value['znak']];

                $queryInsertResearch = "INSERT INTO patient_orders (`patient_id`, `order_id`, `code`, `value`, `lower`, `upper`, `comments`, `znak`) values(:pid, :oid, :code, :value, :lower, :upper, :comment, :znak)";
                $result = $pdo->prepare($queryInsertResearch);
                if ($result->execute($data)) {
                    $this->matchOrders($pdo, $pid, $oid,$orderData);
                }
            }

        }
    }

    //работа с данными пациента
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

    public function searchPatient($pdo, $name, $sirname, $lastName, $birthday, $orders, $sex, $doctor, $oid)
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
                    $patientData['orders'], $patientData['sex'], $patientData['doctor'], $oid);

            }

        }

        return $row;
    }

    //интерпретация результатов
    public function searchMatchResearch($pdo, array $researchData)
    {
        $researches = $researchData;

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
            if (isset($upper) && count(array_intersect($ourCodes, $upper)) == count($ourCodes) /*|| count(array_intersect($ourCodes, $arrUpper)) == 0*/) {
                $this->resultString[$i]['interpretation'] = $value['interpretation_plus'];
                $this->resultString[$i]['interpretation_name'] = $value['name'];
                $this->resultString[$i]['research_code'] = $value['codes'];
                $i++;

            } elseif (isset($lower) && count(array_intersect($ourCodes, $lower)) == count($ourCodes) /*|| count(array_intersect($ourCodes, $arrLower)) == 0*/) {
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

}
