<?php

require_once '../config/midtrans/Midtrans.php';
require_once '../config/parse-env.php';
require_once '../config/app.php';

header('Content-Type: application/json');

use Midtrans\Config;
use Midtrans\CoreApi;

// Masukin Admin Log per bulan, jalan tiap awal bulan hari kerja

$admin_code = $tahun_ajaran.'-'.$semester.'-'.$month."-create-bills";

$read = "SELECT admin_code FROM administrations WHERE admin_code='$admin_code'";

$readResult = read($read);

if ($readResult) {
    echo json_encode(['message' => 'Tagihan sudah ada']);
    exit();
}

$isProduction = getenv('MIDTRANS_IS_PRODUCTION') == 0 ?  false : true;
$isSanitized = getenv('MIDTRANS_IS_SANITIZED') == 0 ?  false : true;
$is3ds = getenv('MIDTRANS_IS_3DS') == 0 ?  false : true;

Config::$serverKey = getenv('MIDTRANS_SERVER_KEY');
Config::$isProduction = $isProduction;
Config::$isSanitized = $isSanitized;
Config::$is3ds = $is3ds;

$data = [];

$sql = "SELECT
    MAX(b.trx_id) AS trx_id,
    b.student_name, b.parent_phone, b.virtual_account,
    SUM(CASE WHEN b.trx_status = 'waiting' OR b.trx_status = 'not paid' THEN b.trx_amount ELSE 0 END) AS monthly_total,
    COUNT(CASE WHEN b.trx_status = 'waiting' OR b.trx_status = 'not paid' THEN b.trx_amount ELSE NULL END) AS monthly_count,
    SUM(CASE WHEN b.trx_status = 'not paid' THEN b.late_bills ELSE 0 END) AS late_total,
    COUNT(CASE WHEN b.trx_status = 'not paid' THEN b.late_bills ELSE NULL END) AS late_count
FROM
    bills b
WHERE
    MONTH(b.payment_due) <= '$month'
GROUP BY
    b.student_name, b.parent_phone, b.virtual_account
";

$bills = read($sql);

if(empty($bills)){
    $response = array(
        'status' => false,
        'message' => 'Data tagihan bulan ini kosong'
    );
    echo json_encode($response);
    exit();
}

foreach ($bills as $bill){
    $date = date("c");
    $data[] = array(
        'payment_type' => 'bank_transfer',
        'transaction_details' => array(
            'order_id' => $bill['trx_id']."/".$date,
            'gross_amount' => $bill['monthly_total'] + $bill['late_total'], 
        ),
        'customer_details' => array(
            'first_name' => $bill['student_name'],
            'phone' => $bill['parent_phone']
        ),
        'bank_transfer' => array(
            'bank' => 'bni',
        ),
        'bni_va' => array(
            'va_number' => $bill['virtual_account'],
        ), 
    );
}

$chargeResponse = [];

foreach ($data as $charge) {
    try {
        $chargeResponse[] = CoreApi::charge($charge);
    } catch (Exception $e) {
        $response = [
            'status' => 'error',
            'message' => $e->getMessage(),

        ];
        echo json_encode($response);
        exit();
    }
}

$adminLog = "INSERT INTO administrations(admin_code, type) VALUES ('$admin_code', 'charge')";

crud($adminLog);

echo json_encode($chargeResponse);
