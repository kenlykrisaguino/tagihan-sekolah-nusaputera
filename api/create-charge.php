<?php

require_once '../config/midtrans/Midtrans.php';
header('Content-Type: application/json');

\Midtrans\Config::$serverKey = 'YOUR_SERVER_KEY';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

$params = [
    'transaction_details' => [
        'order_id' => '',
        'gross_amount' => 10000,
    ],
    'item_details' => [
        [
            'id' => 'ITEM1',
            'price' => 10000,
            'quantity' => 1,
            'name' => 'Midtrans Bear',
        ],
    ],
    'customer_details' => [
        'first_name' => 'TEST',
        'last_name' => 'MIDTRANSER',
        'email' => 'test@midtrans.com',
        'phone' => '+628123456',
    ],
    'enabled_payments' => ['bri_va'],
    'bri_va' => [
        'va_number' => '47324297463927', 
    ]
];

$chargeResponse = \Midtrans\Charge::create($params);