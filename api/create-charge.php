<?php

require_once '../config/midtrans/Midtrans.php';
require_once '../config/parse-env.php';
require_once '../config/app.php';

header('Content-Type: application/json');

use Midtrans\Config;
use Midtrans\CoreApi;

$monthQuery = "SELECT MIN(MONTH(payment_due)) AS month FROM bills WHERE trx_status = 'waiting'";

$curr_month_admin = read($monthQuery)[0]['month'];

$last_month_int = $curr_month_admin == 0 ? 12 : $curr_month_admin;

if ($curr_month_admin < 10) {
    $curr_month_admin = '0'. $curr_month_admin;
}

$admin_code = $tahun_ajaran.'-'.$semester.'-'.$curr_month_admin."-create-bills";

$read = "SELECT admin_code FROM administrations WHERE admin_code='$admin_code'";

$readResult = read($read);

if ($readResult) {
    echo json_encode([
        'status' => false,
        'message' => 'Tagihan sudah ada'
    ]);
    exit();
}

$isProduction = getenv('MIDTRANS_IS_PRODUCTION') == 0 ?  false : true;
$isSanitized = getenv('MIDTRANS_IS_SANITIZED') == 0 ?  false : true;
$is3ds = getenv('MIDTRANS_IS_3DS') == 0 ?  false : true;

Config::$serverKey = getenv('MIDTRANS_SERVER_KEY');
Config::$isProduction = $isProduction;
Config::$isSanitized = $isSanitized;
Config::$is3ds = $is3ds;

$expiredPayment = "SELECT midtrans_trx_id, trx_id FROM bills WHERE LOWER(trx_status) IN ('not paid') AND MONTH(payment_due) = '$last_month_int'";

$expiredPaymentResult = read($expiredPayment);

if(empty($expiredPaymentResult)){
    $expiredPayment = "SELECT midtrans_trx_id, trx_id FROM bills WHERE LOWER(trx_status) IN ('waiting') AND MONTH(payment_due) = '$last_month_int'";
    $expiredPaymentResult = read($expiredPayment);
}

$curr_smt = "";
$curr_month = "";
$expired = [];

foreach ($expiredPaymentResult as $trx){
    [$level, $year, $curr_smt, $curr_month, $nis] = explode('/', $trx['trx_id']);
    try{
        if($trx['midtrans_trx_id'] != null){
            $expired[] = CoreApi::expireTrx($trx['midtrans_trx_id']);
        }
    } catch (Exception $e){
        $result = array(
            'status' => false,
            'message' => 'Gagal melakukan pembatalan transaksi: '. $e->getMessage()
        );
        echo json_encode($result);
        exit();
    }
}

$month = $curr_smt == '1' ? (int)$curr_month + 6 : (int)$curr_month;

$data = [];

$sql = "SELECT
    MAX(b.trx_id) AS trx_id,
    CONCAT(c.va_prefix_name ,b.student_name) AS student_name, b.parent_phone, b.virtual_account, MAX(b.payment_due) AS payment_due,
    SUM(CASE WHEN b.trx_status = 'waiting' OR b.trx_status = 'not paid' THEN b.trx_amount ELSE 0 END) AS monthly_total,
    COUNT(CASE WHEN b.trx_status = 'waiting' OR b.trx_status = 'not paid' THEN b.trx_amount ELSE NULL END) AS monthly_count,
    SUM(CASE WHEN b.trx_status = 'not paid' THEN b.late_bills ELSE 0 END) AS late_total,
    COUNT(CASE WHEN b.trx_status = 'not paid' THEN b.late_bills ELSE NULL END) AS late_count,
    SUM(CASE WHEN b.trx_status = 'waiting' OR b.trx_status = 'not paid' THEN b.trx_amount ELSE 0 END) + SUM(CASE WHEN b.trx_status = 'not paid' THEN b.late_bills ELSE 0 END) AS total_charge
FROM
    bills b JOIN classes c on b.class = c.id
WHERE
    MONTH(b.payment_due) <= '$month'
GROUP BY
    CONCAT(c.va_prefix_name ,b.student_name), b.parent_phone, b.virtual_account
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

    $dueDate = new DateTime($bill['payment_due']);
    $nowDate = new DateTime();
    $interval = $dueDate->diff($nowDate);
    $interval = $interval->format('%a');

    $data[] = array(
        'payment_type' => 'bank_transfer',
        'transaction_details' => array(
            'order_id' => $bill['trx_id'].'/'.$date,
            'gross_amount' => $bill['total_charge'],
        ),
        'custom_expiry' => array(
            'unit' => 'days',
            'expiry_duration' => (int)$interval+1, 
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
        "custom_field1" => "Pembayaran ".$bill['student_name'] 
    );
}

$chargeResponse = [];

foreach ($data as $charge) {
    try {
        $chargeResponse[] = CoreApi::charge($charge);
    } catch (Exception $e) {
        $response = [
            'status' => false,
            'message' => $e->getMessage(),

        ];
        echo json_encode($response);
        exit();
    }
}

$adminLog = "INSERT INTO administrations(admin_code, type) VALUES ('$admin_code', 'charge')";

crud($adminLog);

$response = array(
    'status' => true,
    'message' => 'Berhasil menambahakan tagihan',
    'data' => $chargeResponse
);

echo json_encode($response);
