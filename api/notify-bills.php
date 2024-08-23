<?php

include_once '../config/app.php';
include_once '../config/fonnte.php';

header('Content-Type: application/json');

$currentDate = new DateTime();

$now = $currentDate->format('Y-m-d');

$currentDate->modify('first day of this month');

$dayOfWeek = $currentDate->format('N');

if ($dayOfWeek == 6) {
    $currentDate->modify('+2 days');
} elseif ($dayOfWeek == 7) {
    $currentDate->modify('+1 day');
}

$firstDate = $currentDate->format('Y-m-d');

$currentDate->modify('last day of this month');

$dayOfWeek = $currentDate->format('N');

if ($dayOfWeek == 6) {
    $currentDate->modify('-1 day');
} elseif ($dayOfWeek == 7) {
    $currentDate->modify('-2 days');
}

$lastDate = $currentDate->format('Y-m-d');

$currentDate->modify('-1 day');
$dayBefore = $currentDate->format('Y-m-d');
$currentDate->modify('-6 day');
$weekBefore = $currentDate->format('Y-m-d');

$months = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember"
];

$currentMonth = $months[intval($currentDate->format('m')) - 1];


$message = "Pembayaran SPP bulan $currentMonth ";
$msgValid = false;

if($now == $firstDate){
    $message .= "telah dibuka.";
    $msgValid = true;
} else if($now == $weekBefore){
    $message .= "akan berakhir minggu depan.";
    $msgValid = true;
} else if($now == $dayBefore){
    $message .= "akan berakhir besok.";
    $msgValid = true;
}

if(!$msgValid){
    $data = array(
        "status" => false,
        "message" => "Tidak mengirimkan notifikasi pembayaran"
    );
    echo json_encode($data);
    exit();
}

$query = "SELECT
b.virtual_account, b.student_name, 
b.parent_phone,
SUM(CASE WHEN b.trx_status = 'not paid' OR b.trx_status = 'waiting' THEN b.trx_amount ELSE 0 END) + SUM(CASE WHEN b.trx_status = 'not paid' THEN c.late_bills ELSE 0 END) as total_payment
FROM bills b
JOIN classes c ON b.class = c.id
WHERE b.payment_due = '$lastDate 23:59:59' AND b.trx_status = 'waiting' 
GROUP BY
b.virtual_account, b.student_name, 
b.parent_phone
";

$result = read($query);

$msgData = [];

foreach($result as $row){
    $msgData[] = array(
        'target' => $row['parent_phone'],
        'message' => $message." Diharapkan dapat membayar sebesar ".formatToRupiah($row['total_payment'])." ke nomor virtual account ".$row['virtual_account'],
        'delay' => '1'
    );
}

if(empty($msgData)){
    $data = array(
        "status" => false,
        "message" => "Tidak ada pembayaran yang harus dibayar",
    );
    echo json_encode($data);
    exit();
}

$messages = json_encode($msgData);

$a = sendMessage(array('data'=>$messages));


$data = array(
    "status" => true,
    "message" => "Notifikasi pembayaran spp bulan $currentMonth berhasil dikirim",
    "data" => $a,
    "s" => $msgData
);

echo json_encode($data);

function formatToRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}