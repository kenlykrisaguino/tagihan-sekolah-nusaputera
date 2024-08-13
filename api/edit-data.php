<?php

include_once '../config/app.php';

header('Content-Type: application/json');

$search = isset($_GET['search']) ? $_GET['search'] : '';
$tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '';
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';

if($semester == 'Genap') {
    $sql_semester = "
    SUM(CASE WHEN MONTH(b.payment_due) = 1 THEN b.trx_amount ELSE 0 END) AS Januari,
    SUM(CASE WHEN MONTH(b.payment_due) = 2 THEN b.trx_amount ELSE 0 END) AS Februari,
    SUM(CASE WHEN MONTH(b.payment_due) = 3 THEN b.trx_amount ELSE 0 END) AS Maret,
    SUM(CASE WHEN MONTH(b.payment_due) = 4 THEN b.trx_amount ELSE 0 END) AS April,
    SUM(CASE WHEN MONTH(b.payment_due) = 5 THEN b.trx_amount ELSE 0 END) AS Mei,
    SUM(CASE WHEN MONTH(b.payment_due) = 6 THEN b.trx_amount ELSE 0 END) AS Juni,
    SUM(CASE WHEN MONTH(b.payment_due) = 1 THEN b.late_bills ELSE 0 END) AS LateJanuari,
    SUM(CASE WHEN MONTH(b.payment_due) = 2 THEN b.late_bills ELSE 0 END) AS LateFebruari,
    SUM(CASE WHEN MONTH(b.payment_due) = 3 THEN b.late_bills ELSE 0 END) AS LateMaret,
    SUM(CASE WHEN MONTH(b.payment_due) = 4 THEN b.late_bills ELSE 0 END) AS LateApril,
    SUM(CASE WHEN MONTH(b.payment_due) = 5 THEN b.late_bills ELSE 0 END) AS LateMei,
    SUM(CASE WHEN MONTH(b.payment_due) = 6 THEN b.late_bills ELSE 0 END) AS LateJuni";
} else {
    $sql_semester = "
    SUM(CASE WHEN MONTH(b.payment_due) = 7 THEN b.trx_amount ELSE 0 END) AS Juli,
    SUM(CASE WHEN MONTH(b.payment_due) = 8 THEN b.trx_amount ELSE 0 END) AS Agustus,
    SUM(CASE WHEN MONTH(b.payment_due) = 9 THEN b.trx_amount ELSE 0 END) AS September,
    SUM(CASE WHEN MONTH(b.payment_due) = 10 THEN b.trx_amount ELSE 0 END) AS Oktober,
    SUM(CASE WHEN MONTH(b.payment_due) = 11 THEN b.trx_amount ELSE 0 END) AS November,
    SUM(CASE WHEN MONTH(b.payment_due) = 12 THEN b.trx_amount ELSE 0 END) AS Desember,
    SUM(CASE WHEN MONTH(b.payment_due) = 7 THEN b.late_bills ELSE 0 END) AS LateJuli,
    SUM(CASE WHEN MONTH(b.payment_due) = 8 THEN b.late_bills ELSE 0 END) AS LateAgustus,
    SUM(CASE WHEN MONTH(b.payment_due) = 9 THEN b.late_bills ELSE 0 END) AS LateSeptember,
    SUM(CASE WHEN MONTH(b.payment_due) = 10 THEN b.late_bills ELSE 0 END) AS LateOktober,
    SUM(CASE WHEN MONTH(b.payment_due) = 11 THEN b.late_bills ELSE 0 END) AS LateNovember,
    SUM(CASE WHEN MONTH(b.payment_due) = 12 THEN b.late_bills ELSE 0 END) AS LateDesember";
}




$sql = "SELECT
    b.nis, 
    b.virtual_account,
    b.student_name, b.level, b.parent_phone, b.period,
    SUM(CASE WHEN b.trx_status = 'not paid' OR b.trx_status = 'waiting' THEN b.trx_amount ELSE 0 END) + (SELECT SUM(late_bills) FROM bills WHERE bills.nis = b.nis) AS penerimaan, 
    $sql_semester, 
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

$users = read($sql);

$totalsql = "SELECT
    SUM(CASE WHEN b.trx_status = 'not paid' OR b.trx_status = 'waiting' THEN b.trx_amount ELSE 0 END) + (SELECT SUM(late_bills) FROM bills WHERE bills.nis = b.nis) AS penerimaan,
    (SELECT SUM(late_bills) FROM bills WHERE bills.nis = b.nis) AS tunggakan
FROM 
    bills b
HAVING 
    b.period = '$tahun_ajaran' AND 
    b.semester = '$semester'
";

$total = read($sql);

$data = [
    'status' => 'OK',
    'message' => 'Get Input Data',
    'query' => $sql,
    'data' => [
        "users" => $users,
        "total" => $total[0]
    ]
];

echo json_encode($data);