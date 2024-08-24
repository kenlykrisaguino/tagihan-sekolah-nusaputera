<?php

include_once '../config/app.php';

header('Content-Type: application/json');

$period = isset($_GET['period']) ? $_GET['period'] : '';
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';
$level = isset($_GET['level']) ? $_GET['level'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';
$major = isset($_GET['major']) ? $_GET['major'] : '';
$nis = isset($_GET['nis']) ? $_GET['nis'] : '';

$query = "SELECT
    SUM(CASE WHEN b.trx_status = 'paid' OR b.trx_status = 'late' THEN b.trx_amount ELSE 0 END) AS bank,
    SUM(CASE WHEN b.trx_status = 'not paid' THEN c.late_bills ELSE 0 END) AS tunggakan,
    SUM(CASE WHEN b.trx_status = 'paid' OR b.trx_status = 'late' THEN b.trx_amount ELSE 0 END) + SUM(CASE WHEN b.trx_status = 'not paid' THEN c.late_bills ELSE 0 END) AS pendapatan
    FROM bills b
    JOIN classes c ON c.id = b.class
    WHERE TRUE";

$query .= $period != '' ? " AND b.period = '$period'" : "";
$query .= $semester != '' ? " AND b.semester = '$semester'" : "";
$query .= $level != '' ? " AND c.level = '$level'" : "";
$query .= $class != '' ? " AND c.name = '$class'" : "";
$query .= $major != '' ? " AND c.major = '$major'" : "";
$query .= $nis != '' ? " AND b.nis = '$nis'" : "";


$result = read($query)[0];

echo json_encode(array(
    "status" => true,
    "data" => $result,
    "message" => "Hasil Penjurnalan berhasil didapat"
));

