<?php
include '../config/app.php';
header('Content-Type: application/json');

$query = 'SELECT DISTINCT period FROM bills ORDER BY period';
$result = read($query);

echo json_encode($result);
?>
