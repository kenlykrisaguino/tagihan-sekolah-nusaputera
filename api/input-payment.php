<?php

include_once '../config/app.php';
include '../config/fonnte.php';
require_once '../config/midtrans/Midtrans.php';

header('Content-Type: application/json');

use Midtrans\Config;
use Midtrans\CoreApi;

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

        $nisList = array_unique(array_column($csvData, 'nis'));

        $userBillQuery = "
            SELECT u.id AS user_id, u.nis, u.parent_phone, c.level, b.id AS bill_id, MONTH(b.payment_due) AS bill_month, b.midtrans_trx_id
            FROM users u
            JOIN classes c ON c.id = u.class
            LEFT JOIN bills b ON b.nis = u.nis AND b.trx_status = 'waiting'
            WHERE u.nis IN ('" . implode("','", $nisList) . "')
        ";
        $userBills = crud($userBillQuery);

        $userMap = [];
        $billMap = [];
        $midtransTrxIds = [];

        foreach ($userBills as $entry) {
            $nis = $entry['nis'];
            $userMap[$nis] = [
                "id" => $entry['user_id'], 
                "level" => $entry['level'], 
                "parent_phone" => $entry['parent_phone']
            ];
            if ($entry['bill_id']) {
                $billMap[$nis] = [
                    "id" => $entry['bill_id'],
                    "bill_month" => $entry['bill_month']
                ];
                $midtransTrxIds[] = $entry['midtrans_trx_id'];
            }
        }

        $values = [];
        $msgData = [];

        foreach ($csvData as $data) {
            $nis = $data['nis'];
            if (isset($userMap[$nis]) && isset($billMap[$nis])) {
                $user = $userMap[$nis];
                $bill = $billMap[$nis];

                $trx_id = generateTrxId($user['level'], $nis);
                $parentPhone = $user['parent_phone'];
                $bill_month = $months[str_pad($bill['bill_month'], 2, '0', STR_PAD_LEFT)];
                $formattedAmount = formatToRupiah($data['trx_amount']);
                $now = date('d-m-Y H:i:s');

                $values[] = "(
                    '{$user['id']}', 
                    '{$data['virtual_account']}', 
                    '{$bill['id']}', 
                    '$trx_id', 
                    '{$data['trx_amount']}', 
                    '{$data['notes']}', 
                    '{$data['trx_timestamp']}'
                )";

                $msgData[] = [
                    'target' => $parentPhone,
                    'message' => "Pembayaran untuk bulan *$bill_month* Semester *$semester* pada tahun ajaran $tahun_ajaran sebesar *$formattedAmount* berhasil! \n\n_Pembayaran diterima pada tanggal $now ._",
                    'delay' => '1'
                ];
            }
        }

        $errors = [];

        if (!empty($values)) {
            $query = "INSERT INTO payments 
                (sender, virtual_account, bill_id, trx_id, trx_amount, notes, trx_timestamp) 
                VALUES " . implode(',', $values);
            
            if (crud($query)) {
                Config::$serverKey = getenv('MIDTRANS_SERVER_KEY');
                Config::$isProduction = getenv('MIDTRANS_IS_PRODUCTION') == 1;
                Config::$isSanitized = getenv('MIDTRANS_IS_SANITIZED') == 1;
                Config::$is3ds = getenv('MIDTRANS_IS_3DS') == 1;

                foreach ($midtransTrxIds as $trx) {
                    try {
                        CoreApi::cancelTrx($trx);
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                        continue;
                    }
                }

                $updateQuery = "UPDATE bills 
                    SET trx_status = 'paid' 
                    WHERE nis IN ('" . implode("','", $nisList) . "') AND trx_status = 'waiting'";
                crud($updateQuery);

                $updateQueryLate = "UPDATE bills 
                    SET trx_status = 'late', late_bills = 0 
                    WHERE nis IN ('" . implode("','", $nisList) . "') AND trx_status = 'not paid'";
                crud($updateQueryLate);

                sendMessage(['data' => json_encode($msgData)]);

                echo json_encode([
                    'status' => true,
                    'message' => 'Payment has been added successfully',
                    'fonnte' => $msgData,
                    'data' => $csvData,
                    'errors' => $errors
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
                'message' => 'No valid bills found for payment.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'No file provided.'
        ]);
    }
} else {
    echo json_encode([
        'status' => false,
        'error' => 'Invalid request method.'
    ]);
}

function generateTrxId($level, $nis) {
    return "$level/11/5/1/$nis";
}

function formatToRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}
