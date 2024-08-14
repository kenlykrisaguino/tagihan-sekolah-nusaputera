<?php

include_once '../config/app.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['input'])) {
        $fileTmpPath = $_FILES['input']['tmp_name'];
        $fileName = $_FILES['input']['name'];

        $csvData = [];

        if (($handle = fopen($fileTmpPath, 'r')) !== false) {
            $headers = fgetcsv($handle); 
            while (($row = fgetcsv($handle)) !== false) {
                $csvData[] = array_combine($headers, $row); 
            }
            fclose($handle);
        }

        if (empty($csvData)) {
            echo json_encode([
                'status' => false,
                'message' => 'No data found in the CSV file.'
            ]);
            exit;
        }

        $nisList = array_column($csvData, 'nis');
        $nisList = array_unique($nisList);

        $userQuery = "SELECT l.name AS level, u.id, u.nis FROM users u JOIN levels l ON l.id = u.level WHERE u.nis IN ('" . implode("','", $nisList) . "')";
        $users = crud($userQuery);

        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user['nis']] = ["id"=>$user['id'], "level" => $user['level']];
        }

        $billQuery = "SELECT id, nis FROM bills WHERE nis IN ('" . implode("','", $nisList) . "') AND trx_status = 'waiting'";
        $bills = crud($billQuery);

        $billMap = [];
        foreach ($bills as $bill) {
            $billMap[$bill['nis']] = $bill['id'];
        }

        $values = [];

        foreach ($csvData as $data) {
            $nis = $data['nis'];
            $virtual_account = $data['virtual_account'];
            $trx_amount = $data['trx_amount'];
            $notes = $data['notes'];
            $trx_timestamp = $data['trx_timestamp'];

            if (isset($userMap[$nis]) && isset($billMap[$nis])) {
                $level = $userMap[$nis]['level'];
                $userId = $userMap[$nis]['id'];
                $billId = $billMap[$nis];
                $trx_id = generateTrxId($level, $nis);

                $values[] = "('$userId', '$virtual_account', '$billId', '$trx_id', '$trx_amount', '$notes', '$trx_timestamp')";
            }
        }

        if (!empty($values)) {
            $query = "INSERT INTO payments (sender, virtual_account, bill_id, trx_id, trx_amount, notes, trx_timestamp) VALUES ";
            $query .= implode(',', $values);

            if (crud($query)) {
                $updateQuery = "UPDATE bills SET trx_status = 'paid' WHERE nis IN ('". implode("','", $nisList). "') AND trx_status = 'waiting'";
                crud($updateQuery);
                $updateQuery = "UPDATE bills SET trx_status = 'late', late_bills = 0 WHERE nis IN ('". implode("','", $nisList). "') AND trx_status = 'not paid'";
                crud($updateQuery);
                echo json_encode([
                    'status' => true,
                    'message' => 'Payment has been added successfully',
                    'data' => $csvData
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Failed to insert data into the database.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'No matching unpaid bills found for the provided data.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'No file uploaded.'
        ]);
    }
} else {
    echo json_encode([
        'status' => false,
        'error' => 'Invalid request method.'
    ]);
}

function generateTrxId($level, $nis) {
    return $level . '/11/5/1/' . $nis;
}

?>
