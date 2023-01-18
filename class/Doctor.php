<?php


class Doctor
{
    public static function PatientList($pdo, $doctorID)
    {
        $querySelectPatients = "SELECT patient.*, count(order_id) AS pod FROM patient JOIN match_orders ON id_patient = patient_id WHERE patient_doctor = :doctor GROUP BY patient.id_patient ORDER BY patient.patient_sirname";
        $result = $pdo->prepare($querySelectPatients);
        $result->execute(['doctor'=>2]);
        return $patients = $result->fetchAll(PDO::FETCH_ASSOC);

    }

    public static function PatientOrderList($pdo, $pid){
        $querySelectPatientOrders = "SELECT * FROM match_orders WHERE patient_id = :pid";
        $result = $pdo->prepare($querySelectPatientOrders);
        $result->execute(['pid'=>$pid]);
        return $patientOrdersList = $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function OrdersContent($pdo, array $ordersID){
        $query = "SELECT patient_orders.*, tests.name FROM `patient_orders` join tests on tests.code = patient_orders.code WHERE order_id = :oid0";
        for($i=1; $i<count($ordersID); $i++){
            $query .= " OR order_id = :oid".$i;
        }
        $query .= " ORDER BY order_id";

        $i = 0;
        $data = [];
        foreach ($ordersID as $order){
            $key = 'oid'.$i;
            $data[$key] = $order;
            $i++;
        }

        $result = $pdo->prepare($query);
        $result->execute($data);
        return $ordersContent = $result->fetchAll(PDO::FETCH_ASSOC);
    }
}