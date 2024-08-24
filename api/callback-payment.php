<?php

require_once '../config/app.php';
require_once '../config/fonnte.php';

$jsonData = file_get_contents('php://input');

$apiResponse = json_decode($jsonData, true);

if ($apiResponse !== null) {
    if($apiResponse['status_code'] != "200"){
        $data = [
            "status" => false,
            "message" => "Failed to process payment"
        ];
        echo json_encode($data);
        exit();
    }

    $trx_id = $apiResponse['order_id'];
    list($level, $year, $semester, $month, $nis, $timestamp) = explode('/', $trx_id);

    $getUser = "SELECT
        u.id, u.nis, u.name, u.virtual_account, u.parent_phone
    FROM users u
    WHERE u.nis = '$nis'";
    $userResult = read($getUser); 
    $user = $userResult[0];

    $trxid = $level."/". $year."/". $semester."/". $month."/". $nis;

    $getBills = "SELECT b.id FROM bills b WHERE b.trx_id = '$trxid'";
    $billsResult = read($getBills);
    $bill = $billsResult[0];

    $trx_amount = $apiResponse['payment_amounts'][0]['amount'];

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
    
    $payment = crud($insertPayment);
    if (!$payment) {
        echo json_encode(array(
            "status" => false,
            "message" => "Failed to insert payment data"
        ));
        exit();
    }

    $updateBillsWaiting = "UPDATE bills SET trx_status = 'paid' WHERE nis = '$nis' AND trx_status = 'waiting'";
    $updateWaiting = crud($updateBillsWaiting);

    if(!$updateWaiting){
        echo json_encode(array(
            "status" => false,
            "message" => "Failed to update bills waiting status"
        ));
        exit();
    }

    $updateBillsUnpaid = "UPDATE bills SET trx_status = 'late', late_bills = 0 WHERE nis = '$nis' AND trx_status = 'not paid'";
    $updateUnpaid = crud($updateBillsUnpaid);

    if(!$updateUnpaid){
        echo json_encode(array(
            "status" => false,
            "message" => "Failed to update bills unpaid status"
        ));
        exit();
    }

    $bill_month = getMonth($month);
    $formattedAmount = formatToRupiah($trx_amount);
    $now = date('d-m-Y H:i:s');

    $semester_str = $semester == 1 ? "Gasal" : "Genap";
    
    $msg = array(
        'target' => $user['parent_phone'],
        'message' => "Pembayaran untuk bulan *$bill_month* Semester *$semester_str* pada tahun ajaran $tahun_ajaran sebesar *$formattedAmount* berhasil! \n\n_Pembayaran diterima pada tanggal $now ._",
    );

    sendMessage($msg);

    

    echo json_encode(array(
        "status" => true,
        "message" => "Payment has been added successfully"
    ));

} else {
    echo "Failed to decode JSON or no data received.";
}

function getMonth($month) {
    $months = array(
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
        12 => 'Desember'
    );
    return $months[$month];
}

function formatToRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}
?>

