<?php

// Memuat konfigurasi aplikasi dan konfigurasi Fonnte (API untuk pesan)
require_once '../config/app.php';
require_once '../config/fonnte.php';

// Mengambil data JSON yang dikirim melalui input
$jsonData = file_get_contents('php://input');

// Mendekode data JSON menjadi array asosiatif
$apiResponse = json_decode($jsonData, true);

// Jika data JSON valid (tidak null)
if ($apiResponse !== null) {

    // Mengambil nilai dari trx_id yang dipecah berdasarkan '/'
    $trx_id = $apiResponse['order_id'];
    [$level, $year, $semester, $month, $nis, $timestamp] = explode('/', $trx_id);
    // Membentuk trxid untuk digunakan dalam query
    $trxid = $level . '/' . $year . '/' . $semester . '/' . $month . '/' . $nis;

    // Cek jika status kode adalah '201' (transaksi baru atau pending)
    if ($apiResponse['status_code'] == '201') {
        if ($apiResponse['transaction_status'] == 'pending') {
            // Mengambil ID transaksi Midtrans
            $midtrans_trx_id = $apiResponse['transaction_id'];

            // Query untuk memperbarui ID transaksi Midtrans pada tagihan
            $update_bills = "UPDATE bills set midtrans_trx_id = '$midtrans_trx_id' WHERE trx_id = '$trxid'";

            try {
                // Eksekusi query update
                $updateBillsResult = crud($update_bills);
            } catch (Exception $e) {
                // Jika terjadi error saat update, kirim respon JSON dengan pesan error
                $data = [
                   'status' => false,
                   'message' => 'Failed to update bills',
                   'error' => $e->getMessage()
                ];
                echo json_encode($data);
                exit();
            }

            // Jika berhasil, kirim respon sukses
            $data = [
                'status' => true,
                'message' => 'Updated Midtrans Transaction ID'
            ];
            echo json_encode($data);
            exit();
        }
    }

    // Jika status kode bukan '200', berarti transaksi gagal
    if ($apiResponse['status_code'] != '200') {
        $data = [
            'status' => false,
            'message' => 'Failed to process payment',
        ];
        echo json_encode($data);
        exit();
    }

    // Query untuk mengambil data pengguna berdasarkan nis (nomor induk siswa)
    $getUser = "SELECT u.id, u.nis, u.name, u.virtual_account, u.parent_phone FROM users u WHERE u.nis = '$nis'";
    $userResult = read($getUser);
    $user = $userResult[0];

    // Query untuk mengambil ID tagihan berdasarkan trx_id
    $getBills = "SELECT b.id FROM bills b WHERE b.trx_id = '$trxid'";
    $billsResult = read($getBills);
    $bill = $billsResult[0];

    // Mengambil jumlah transaksi dari response API
    $trx_amount = $apiResponse['payment_amounts'][0]['amount'];

    // Query untuk memasukkan data pembayaran baru ke tabel payments
    $insertPayment = "INSERT INTO payments 
        (sender, virtual_account, bill_id, trx_id, trx_amount, notes, trx_timestamp) 
    VALUES (
        '{$user['id']}', 
        '{$user['virtual_account']}', 
        '{$bill['id']}', 
        '{$trxid}', 
        '{$trx_amount}', 
        '', 
        '{$apiResponse['transaction_time']}'
    )";

    // Eksekusi query insert payment
    $payment = crud($insertPayment);
    if (!$payment) {
        // Jika insert gagal, kirim respon error
        echo json_encode([
            'status' => false,
            'message' => 'Failed to insert payment data',
        ]);
        exit();
    }

    // Query untuk memperbarui status tagihan dari 'waiting' menjadi 'paid'
    $updateBillsWaiting = "UPDATE bills SET trx_status = 'paid' WHERE nis = '$nis' AND trx_status = 'waiting'";
    $updateWaiting = crud($updateBillsWaiting);

    // Jika update gagal, kirim respon error
    if (!$updateWaiting) {
        echo json_encode([
            'status' => false,
            'message' => 'Failed to update bills waiting status',
        ]);
        exit();
    }

    // Query untuk memperbarui status tagihan dari 'not paid' menjadi 'late'
    $updateBillsUnpaid = "UPDATE bills SET trx_status = 'late', late_bills = 0 WHERE nis = '$nis' AND trx_status = 'not paid'";
    $updateUnpaid = crud($updateBillsUnpaid);

    // Jika update gagal, kirim respon error
    if (!$updateUnpaid) {
        echo json_encode([
            'status' => false,
            'message' => 'Failed to update bills unpaid status',
        ]);
        exit();
    }

    // Menghitung bulan semester berdasarkan input semester dan bulan
    $sem_month = $semester == '1' ? $month + 6 : $month;

    // Mendapatkan nama bulan dari fungsi getMonth
    $bill_month = getMonth($sem_month);
    // Memformat jumlah transaksi menjadi format rupiah
    $formattedAmount = formatToRupiah($trx_amount);
    $now = date('d-m-Y H:i:s');

    // Menentukan apakah semester Gasal atau Genap
    $semester_str = $semester == 1 ? 'Gasal' : 'Genap';

    // Menyusun pesan untuk orang tua terkait pembayaran
    $msg = [
        'target' => $user['parent_phone'],
        'message' => "Pembayaran untuk bulan *$bill_month* Semester *$semester_str* pada tahun ajaran $tahun_ajaran sebesar *$formattedAmount* berhasil! \n\n_Pembayaran diterima pada tanggal $now ._",
    ];

    // Mengirim pesan melalui API Fonnte
    sendMessage($msg);

    // Kirim respon sukses
    echo json_encode([
        'status' => true,
        'message' => 'Payment has been added successfully',
    ]);
} else {
    // Jika data JSON gagal diproses, kirim pesan error
    echo 'Failed to decode JSON or no data received.';
}

// Fungsi untuk mendapatkan nama bulan berdasarkan angka
function getMonth($month)
{
    $months = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];
    return $months[$month];
}
?>
