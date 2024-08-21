<?php

include_once '../config/app.php';

header('Content-Type: application/json');

$search = isset($_GET['search']) ? $_GET['search'] : '';
$tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '';
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';
$month = isset($_GET['month']) ? $_GET['month'] : '';

$month_query = '';

if ($month != '' ){
    $month_query = "AND MONTH(b.payment_due) = $month";
}

$sql = "SELECT
    u.virtual_account,
    u.name AS student_name, CONCAT(c.level, ' ', c.name, ' ', c.major) AS class, u.parent_phone,
    SUM(CASE WHEN b.trx_status = 'paid' OR b.trx_status = 'late' THEN b.trx_amount ELSE 0 END) AS penerimaan, 
    SUM(CASE WHEN b.trx_status = 'late' OR b.trx_status = 'not paid' THEN c.late_bills ELSE 0 END) AS tunggakan
    FROM 
        bills b
        JOIN users u ON b.nis = u.nis
        JOIN classes c ON b.class = c.id
    WHERE 
        b.period = '$tahun_ajaran' AND 
        b.semester = '$semester' AND
        b.student_name LIKE '%$search%'
        $month_query
    GROUP BY 
        b.nis, 
        b.virtual_account,
        b.student_name, CONCAT(c.level, ' ', c.name, ' ', c.major), b.parent_phone, b.period;
    ";
$result = read($sql);

$data = [
    'status' => true,
    'message' => 'Get Input Data',
    'data' => $result
];

echo json_encode($data);