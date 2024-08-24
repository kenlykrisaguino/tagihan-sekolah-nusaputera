<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
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

        foreach ($csvData as $data) {
            $nis = $data['nis'];
            $virtual_account = $data['virtual_account'];
            $trx_amount = $data['trx_amount'];
            $notes = $data['notes'];
            $trx_timestamp = $data['trx_timestamp'];

            if (isset($userMap[$nis]) && isset($billMap[$nis])) {
                $level = $userMap[$nis]['level'];
                $userId = $userMap[$nis]['id'];
                $billId = $billMap[$nis]['id'];
                $trx_id = generateTrxId($level, $nis);

                $parentPhone = $userMap[$nis]['parent_phone'];
                $bill_month = $months[str_pad($billMap[$nis]['bill_month'], 2, '0', STR_PAD_LEFT)];
                $formattedAmount = formatToRupiah($trx_amount);
                $now = date('d-m-Y H:i:s');

                $values[] = "('$userId', '$virtual_account', '$billId', '$trx_id', '$trx_amount', '$notes', '$trx_timestamp')";
                $msgData[] = [
                    'target' => $parentPhone,
                    'message' => "Pembayaran untuk bulan *$bill_month* Semester *$semester* pada tahun ajaran $tahun_ajaran sebesar *$formattedAmount* berhasil! \n\n_Pembayaran diterima pada tanggal $now ._",
                    'delay' => '1'
                ];
            }
        }

        $messages = json_encode($msgData);

        if (!empty($values)) {
            $query = "INSERT INTO payments (sender, virtual_account, bill_id, trx_id, trx_amount, notes, trx_timestamp) VALUES ";
            $query .= implode(',', $values);

            if (crud($query)) {
                $updateQuery = "UPDATE bills SET trx_status = 'paid' WHERE nis IN ('". implode("','", $nisList). "') AND trx_status = 'waiting'";
                crud($updateQuery);
                $updateQuery = "UPDATE bills SET trx_status = 'late', late_bills = 0 WHERE nis IN ('". implode("','", $nisList). "') AND trx_status = 'not paid'";
                crud($updateQuery);

                sendMessage(array('data' => $messages));
                echo json_encode([
                    'status' => true,
                    'message' => 'Payment has been added successfully',
                    'fonnte' => $messages,
                    'data' => $csvData
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Gagal memasukan data ke database'
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Tidak ada tagihan yang dapat dibayarkan.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Tidak ada file.'
        ]);
    }
} else {
    echo json_encode([
        'status' => false,
        'error' => 'Invalid request method.'
    ]);
}

function generateTrxId($level, $nis) {
    return `$level/11/5/1/$nis`;
}

function formatToRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}