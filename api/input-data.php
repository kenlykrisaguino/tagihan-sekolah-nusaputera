<?php

include_once '../config/app.php';

header('Content-Type: application/json');

$month = isset($_GET['month']) ? $_GET['month'] : '';


$sql = "SELECT 
payments.virtual_account, users.name AS user, payments.trx_amount, payments.trx_timestamp
FROM
payments INNER JOIN users ON payments.sender = users.id";

if ($month != '') {
    $sql .= " WHERE MONTH(trx_timestamp) = '$month'";
}

$result = read($sql);

$data = [
    'status' => true,
    'message' => 'Get Input Data',
    'data' => $result
];

echo json_encode($data);