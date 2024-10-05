<?php
include '../config/app.php';
header('Content-Type: application/json');

$query = "SELECT * FROM additional_payment_category";
$additional_payment_categories = read($query);
echo json_encode($additional_payment_categories);
exit();