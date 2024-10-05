<?php

include '../config/app.php';
header('Content-Type: application/json');

$nis = isset($_GET['nis']) ? $_GET['nis'] : '';

$query = "SELECT additional_fee_details FROM users WHERE nis = '$nis'";

$result = read($query)[0];

if($result['additional_fee_details']){
    $additional_payments = json_decode($result['additional_fee_details'], true);
    echo json_encode($additional_payments);
} else {
    echo json_encode([]);
}
exit();