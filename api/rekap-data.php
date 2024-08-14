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
    SUM(CASE WHEN b.trx_status = 'late' OR b.trx_status = 'not paid' THEN l.late_bills ELSE 0 END) AS tunggakan
    FROM 
        bills b
        JOIN levels l ON b.level = l.name
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