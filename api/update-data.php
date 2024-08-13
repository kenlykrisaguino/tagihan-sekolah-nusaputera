<?php

include_once '../config/app.php';

header('Content-Type: application/json');

$id = $_POST['id'];
$column = $_POST['column'];
$value = $_POST['value'];

$sql = "UPDATE bills SET $column = '$value' WHERE nis = '$id'";

$result = crud($sql);

if ($result) {
    echo json_encode(['success' => true, 'message'=>'Data has been updated', 'data' => $result]);
} else {
    echo json_encode(['success' => false]);
}
