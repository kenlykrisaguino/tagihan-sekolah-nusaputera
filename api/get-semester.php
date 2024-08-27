<?php
include '../config/app.php';
header('Content-Type: application/json');

$query = 'SELECT DISTINCT semester FROM bills ORDER BY semester';
$result = read($query);

echo json_encode($result);
?>
