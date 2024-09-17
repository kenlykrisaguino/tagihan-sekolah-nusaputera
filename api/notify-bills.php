<?php

include_once '../config/app.php';
include_once '../config/fonnte.php';

header('Content-Type: application/json');

$force_notify = $_GET['type'] ?? '';

$modify_month = new DateTime();

$now = $modify_month->format('Y-m-d');

$modify_month->modify('first day of this month');

$firstDate = $modify_month->format('Y-m-d');

$modify_month->modify('+8 days');
$dayBefore = $modify_month->format('Y-m-d');

$modify_month->modify('+2 days');
$dayAfter = $modify_month->format('Y-m-d');

$modify_month->modify('first day of next month');
$modify_month->modify('+9 days');

$lastDate = $modify_month->format('Y-m-d');

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
$currentDate = new DateTime();

$day_int = intval($currentDate->format('d'));
$month_int = intval($currentDate->format('m'));

$moreThanDue = $day_int > 10;

$currentMonth = $months[$moreThanDue ? $month_int - 1 : ($month_int - 2  == -1 ? 11 : $month_int -2)];

$message = "Pembayaran SPP bulan $currentMonth ";
$msgValid = false;

if($force_notify != ''){
    $ifFirstDate = $force_notify == 'first_day';
    $ifDayBefore = $force_notify == 'day_before';
    $ifDayAfter  = $force_notify == 'day_after';
} else {
    $ifFirstDate = $now == $firstDate;
    $ifDayBefore = $now == $dayBefore;
    $ifDayAfter  = $now == $dayAfter;
}

if($ifFirstDate){
    $message .= "akan berakhir di tanggal *$lastDate*. ";
    $msgValid = true;
} else if($ifDayBefore){
    $message .= "akan berakhir besok.";
    $msgValid = true;
} else if($ifDayAfter){
    $message .= "telah dibuka sampai tanggal *$lastDate*.";
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
b.virtual_account, CONCAT(c.va_prefix_name, b.student_name) AS student_name, 
b.parent_phone,
SUM(CASE WHEN b.trx_status = 'not paid' THEN c.late_bills ELSE 0 END) + SUM(CASE WHEN b.trx_status = 'waiting' OR b.trx_status = 'not paid' THEN b.trx_amount ELSE 0 END) as total_payment
FROM bills b
JOIN classes c ON b.class = c.id
WHERE b.payment_due <= '$lastDate 23:59:59' 
GROUP BY
b.virtual_account, CONCAT(c.va_prefix_name, b.student_name), 
b.parent_phone
";

$result = read($query);

$msgData = [];

foreach($result as $row){
    $msgData[] = array(
        'target' => $row['parent_phone'],
        'message' => $message." Diharapkan dapat membayar sebesar *".formatToRupiah($row['total_payment'])."* ke nomor virtual account *".$row['virtual_account']."* Atas Nama *".$row['student_name']."*",
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