<?php

// Memasukkan konfigurasi aplikasi dan library yang diperlukan
include_once '../config/app.php';
include '../config/fonnte.php'; // Memasukkan library Fonnte untuk pengiriman pesan
require_once '../config/midtrans/Midtrans.php'; // Memasukkan library Midtrans untuk pembayaran

// Menetapkan header konten sebagai JSON
header('Content-Type: application/json');

// Menggunakan namespace dari Midtrans
use Midtrans\Config;
use Midtrans\CoreApi;

// Memeriksa apakah request method adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Memeriksa apakah file diupload
    if (isset($_FILES['input'])) {
        // Mendapatkan informasi file
        $fileTmpPath = $_FILES['input']['tmp_name']; // Path sementara file
        $fileName = $_FILES['input']['name']; // Nama file

        // Menyiapkan array untuk menyimpan data CSV
        $csvData = [];
        // Membuka file CSV
        if (($handle = fopen($fileTmpPath, 'r')) !== false) {
            $headers = fgetcsv($handle); // Membaca header kolom dari file CSV
            // Membaca setiap baris data dan menggabungkan dengan header
            while (($row = fgetcsv($handle)) !== false) {
                $csvData[] = array_combine($headers, $row); 
            }
            fclose($handle); // Menutup file setelah membaca
        }

        // Memeriksa jika tidak ada data di file CSV
        if (empty($csvData)) {
            echo json_encode([
                'status' => false,
                'message' => 'No data found in the CSV file.' // Pesan error jika tidak ada data
            ]);
            exit; // Menghentikan eksekusi script
        }

        // Mengambil daftar NIS yang unik dari data CSV
        $nisList = array_unique(array_column($csvData, 'nis'));

        // Menyusun query SQL untuk mendapatkan data tagihan pengguna
        $userBillQuery = "
            SELECT u.id AS user_id, u.nis, u.parent_phone, c.level, b.id AS bill_id, MONTH(b.payment_due) AS bill_month, b.midtrans_trx_id
            FROM users u
            JOIN classes c ON c.id = u.class
            LEFT JOIN bills b ON b.nis = u.nis AND b.trx_status = 'waiting'
            WHERE u.nis IN ('" . implode("','", $nisList) . "')
        ";
        // Menjalankan query SQL
        $userBills = crud($userBillQuery);

        // Menyusun peta data pengguna dan tagihan
        $userMap = [];
        $billMap = [];
        $midtransTrxIds = [];

        foreach ($userBills as $entry) {
            $nis = $entry['nis'];
            // Mengatur bulan tagihan jika bulan = 1, set menjadi 12 (Desember)
            $billMonth = (int)$entry['bill_month'] - 1 == -1 ? 12 : (int)$entry['bill_month'] - 1;
            $userMap[$nis] = [
                "id" => $entry['user_id'], 
                "level" => $entry['level'], 
                "parent_phone" => $entry['parent_phone']
            ];
            if ($entry['bill_id']) {
                $billMap[$nis] = [
                    "id" => $entry['bill_id'],
                    "bill_month" => $billMonth
                ];
                $midtransTrxIds[] = $entry['midtrans_trx_id'];
            }
        }

        $values = [];
        $msgData = [];

        // Memproses setiap baris data CSV
        foreach ($csvData as $data) {
            $nis = $data['nis'];
            if (isset($userMap[$nis]) && isset($billMap[$nis])) {
                $user = $userMap[$nis];
                $bill = $billMap[$nis];

                $timestamp = $data['trx_timestamp'];
                // Mendapatkan bulan dari timestamp transaksi
                $month = (int)date('m', strtotime($timestamp));

                $trx_id = generateTrxId($user['level'], $nis, $month); // Menghasilkan trx_id
                $parentPhone = $user['parent_phone'];
                $bill_month = $months[str_pad($bill['bill_month'], 2, '0', STR_PAD_LEFT)]; // Format bulan tagihan
                $formattedAmount = formatToRupiah($data['trx_amount']); // Format jumlah transaksi ke Rupiah
                $now = date('d-m-Y H:i:s'); // Mendapatkan waktu sekarang

                // Menyusun nilai untuk dimasukkan ke database
                $values[] = "(
                    '{$user['id']}', 
                    '{$data['virtual_account']}', 
                    '{$bill['id']}', 
                    '$trx_id', 
                    '{$data['trx_amount']}', 
                    '{$data['notes']}', 
                    '{$data['trx_timestamp']}'
                )";

                // Menyusun data pesan untuk dikirim
                $msgData[] = [
                    'target' => $parentPhone,
                    'message' => "Pembayaran untuk bulan *$bill_month* Semester *$semester* pada tahun ajaran $tahun_ajaran sebesar *$formattedAmount* berhasil! \n\n_Pembayaran diterima pada tanggal $now ._",
                    'delay' => '1'
                ];
            }
        }

        $errors = [];

        // Jika ada data yang valid, lakukan insert ke database
        if (!empty($values)) {
            $query = "INSERT INTO payments 
                (sender, virtual_account, bill_id, trx_id, trx_amount, notes, trx_timestamp) 
                VALUES " . implode(',', $values);
            
            if (crud($query)) {
                // Konfigurasi Midtrans
                Config::$serverKey = getenv('MIDTRANS_SERVER_KEY');
                Config::$isProduction = getenv('MIDTRANS_IS_PRODUCTION') == 1;
                Config::$isSanitized = getenv('MIDTRANS_IS_SANITIZED') == 1;
                Config::$is3ds = getenv('MIDTRANS_IS_3DS') == 1;

                // Membatalkan transaksi Midtrans yang ada
                foreach ($midtransTrxIds as $trx) {
                    try {
                        CoreApi::cancelTrx($trx);
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage(); // Menangkap error jika pembatalan gagal
                        continue;
                    }
                }

                // Mengupdate status tagihan di database
                $updateQuery = "UPDATE bills 
                    SET trx_status = 'paid' 
                    WHERE nis IN ('" . implode("','", $nisList) . "') AND trx_status = 'waiting'";
                crud($updateQuery);

                $updateQueryLate = "UPDATE bills 
                    SET trx_status = 'late', late_bills = 0 
                    WHERE nis IN ('" . implode("','", $nisList) . "') AND trx_status = 'not paid'";
                crud($updateQueryLate);

                // Mengirim pesan pemberitahuan
                sendMessage(['data' => json_encode($msgData)]);

                // Mengembalikan respons JSON
                echo json_encode([
                    'status' => true,
                    'message' => 'Payment has been added successfully',
                    'fonnte' => $msgData,
                    'data' => $csvData,
                    'errors' => $errors
                ]);
            } else {
                // Jika gagal insert data ke database
                echo json_encode([
                    'status' => false,
                    'message' => 'Failed to insert data into the database.'
                ]);
            }
        } else {
            // Jika tidak ada tagihan yang valid ditemukan
            echo json_encode([
                'status' => false,
                'message' => 'No valid bills found for payment.'
            ]);
        }
    } else {
        // Jika tidak ada file yang diupload
        echo json_encode([
            'status' => false,
            'message' => 'No file provided.'
        ]);
    }
} else {
    // Jika metode request tidak valid
    echo json_encode([
        'status' => false,
        'error' => 'Invalid request method.'
    ]);
}

// Fungsi untuk menghasilkan trx_id berdasarkan level, NIS, dan bulan
function generateTrxID($level, $nis, $month){
    global $year;
    global $semester;

    // Mengambil dua digit terakhir dari tahun ajaran
    $trimmed_year = substr($year, -2);
    // Menentukan semester (1 untuk Gasal, 2 untuk Genap)
    $curr_semester = $semester == 'Gasal' ? "1" : "2";
    // Menghitung bulan semester (berdasarkan 6 bulan dalam semester)
    $semester_month = (($month-1) % 6)+1;

    // Menyusun trx_id berdasarkan level, tahun, semester, bulan, dan NIS
    $trx_id = "$level/$trimmed_year/$curr_semester/$semester_month/$nis";

    return $trx_id; // Mengembalikan trx_id yang dihasilkan
}
