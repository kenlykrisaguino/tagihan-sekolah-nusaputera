<?php

require_once '../config/midtrans/Midtrans.php';
require_once '../config/parse-env.php';
header('Content-Type: application/json');

use Midtrans\Config;
use Midtrans\CoreApi;

// Masukin Admin Log per bulan, jalan tiap awal bulan hari kerja


Config::$serverKey = getenv('MIDTRANS_SERVER_KEY');
Config::$isProduction = false;
Config::$isSanitized = true;
Config::$is3ds = true;

$params = array(
    'payment_type' => 'bank_transfer',
    'transaction_details' => array(
        'order_id' => 'YOUR_ORDER_ID',
        'gross_amount' => 10000, 
    ),
    'customer_details' => array(
        'first_name' => 'John',
        'phone' => '08123456789'
    ),
    'bank_transfer' => array(
        'bank' => 'bni',
        'account_number' => '1234567890'
    ),
);

$chargeResponse = CoreApi::charge($params);