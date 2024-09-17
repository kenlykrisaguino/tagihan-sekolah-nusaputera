<?php

// Memasukkan konfigurasi aplikasi dan library yang diperlukan
include_once '../config/app.php';
include '../config/fonnte.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Memeriksa apakah file diunggah
    if (isset($_FILES['input'])) {
        $fileTmpPath = $_FILES['input']['tmp_name'];
        $fileName = $_FILES['input']['name'];

        $csvData = [];

        // Membaca file CSV dan menyimpan data dalam array
        if (($handle = fopen($fileTmpPath, 'r')) !== false) {
            $headers = fgetcsv($handle); // Mengambil header kolom
            while (($row = fgetcsv($handle)) !== false) {
                $csvData[] = array_combine($headers, $row); // Menggabungkan header dengan nilai baris
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

        // Menyiapkan variabel untuk menyimpan data yang akan dimasukkan
        $values = [];
        $msgData = [];

        // Memproses setiap baris data CSV
        foreach ($csvData as $data) {
            $nis = $data['nis'];
            $virtual_account = $data['virtual_account'];
            $trx_amount = $data['trx_amount'];
            $notes = $data['notes'];
            $trx_timestamp = $data['trx_timestamp'];

            // Memastikan bahwa data terkait dengan NIS valid
            if (isset($userMap[$nis]) && isset($billMap[$nis])) {
                $level = $userMap[$nis]['level'];
                $userId = $userMap[$nis]['id'];
                $billId = $billMap[$nis]['id'];
                $trx_id = generateTrxId($level, $nis); // Menghasilkan ID transaksi

                $parentPhone = $userMap[$nis]['parent_phone'];
                $bill_month = $months[str_pad($billMap[$nis]['bill_month'], 2, '0', STR_PAD_LEFT)];
                $formattedAmount = formatToRupiah($trx_amount); // Format jumlah pembayaran
                $now = date('d-m-Y H:i:s'); // Mendapatkan tanggal dan waktu saat ini

                // Menyiapkan data untuk dimasukkan ke database
                $values[] = "('$userId', '$virtual_account', '$billId', '$trx_id', '$trx_amount', '$notes', '$trx_timestamp')";

                // Menyiapkan data pesan untuk dikirim
                $msgData[] = [
                    'target' => $parentPhone,
                    'message' => "Pembayaran untuk bulan *$bill_month* Semester *$semester* pada tahun ajaran $tahun_ajaran sebesar *$formattedAmount* berhasil! \n\n_Pembayaran diterima pada tanggal $now ._",
                    'delay' => '1'
                ];
            }
        }

        $messages = json_encode($msgData); // Mengubah data pesan menjadi JSON

        if (!empty($values)) {
            // Menyiapkan dan menjalankan query untuk memasukkan data pembayaran
            $query = "INSERT INTO payments (sender, virtual_account, bill_id, trx_id, trx_amount, notes, trx_timestamp) VALUES ";
            $query .= implode(',', $values);

            if (crud($query)) {
                // Menandai tagihan sebagai sudah dibayar
                $updateQuery = "UPDATE bills SET trx_status = 'paid' WHERE nis IN ('". implode("','", $nisList). "') AND trx_status = 'waiting'";
                crud($updateQuery);
                // Menandai tagihan sebagai terlambat
                $updateQuery = "UPDATE bills SET trx_status = 'late', late_bills = 0 WHERE nis IN ('". implode("','", $nisList). "') AND trx_status = 'not paid'";
                crud($updateQuery);

                // Mengirim pesan notifikasi
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

// Fungsi untuk menghasilkan ID transaksi berdasarkan level dan NIS
function generateTrxId($level, $nis) {
    return "$level/11/5/1/$nis";
}
