<?php

include_once '../config/app.php';

header('Content-Type: application/json');

$bulan = isset($_GET['month']) ? $_GET['month'] : '';


$sql = "SELECT 
nis, virtual_account, customer_name, 
trx_amount, expired_date 
FROM 
tagihan ";

if ($bulan != '') {
    $sql .= "WHERE MONTH(expired_date) = '$bulan'";
}


$result = read($sql);

$data = [
    'status' => 'OK',
    'message' => 'Get Input Data',
    'data' => $result
];

echo json_encode($data);