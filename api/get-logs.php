<?php

include '../config/app.php';
header('Content-Type: application/json');


$query = "SELECT * from activity_log WHERE TRUE ORDER BY created_at DESC";

$result = read($query);

echo json_encode($result);