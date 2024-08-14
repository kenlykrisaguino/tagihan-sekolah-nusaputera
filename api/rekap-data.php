<?php

include_once '../config/app.php';

header('Content-Type: application/json');

$search = isset($_GET['search']) ? $_GET['search'] : '';
$tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '';
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';

$sql = "SELECT
    b.virtual_account,
    b.student_name, b.level, b.parent_phone,
    SUM(CASE WHEN b.trx_status = 'paid' OR b.trx_status = 'late' THEN b.trx_amount ELSE 0 END) AS penerimaan, 
    (SELECT SUM(late_bills) FROM bills WHERE bills.nis = b.nis) AS tunggakan
    FROM 
        bills b
    WHERE 
        b.period = '$tahun_ajaran' AND 
        b.semester = '$semester' AND
        b.student_name LIKE '%$search%'
    GROUP BY 
        b.nis, 
        b.virtual_account,
        b.student_name, b.level, b.parent_phone, b.period;
    ";
$result = read($sql);

$data = [
    'status' => 'OK',
    'message' => 'Get Input Data',
    'query' => $sql,
    'data' => $result
];

echo json_encode($data);