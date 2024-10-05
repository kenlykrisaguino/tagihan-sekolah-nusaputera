<?php

include '../config/app.php';
header('Content-Type: application/json');

$name = $_GET['name'] ?? '';

$query = "SELECT nis, name FROM users WHERE name LIKE '%$name%' AND nis NOT IN ('0000', '0001') AND status = 'Active'";
$result = read($query);
echo json_encode($result);
