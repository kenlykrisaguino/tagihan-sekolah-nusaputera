<?php

require_once '../config/midtrans/Midtrans.php';
require_once '../config/parse-env.php';
header('Content-Type: application/json');

\Midtrans\Config::$serverKey = getenv('MIDTRANS_SERVER_KEY');
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

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

$chargeResponse = \Midtrans\CoreApi::charge($params);