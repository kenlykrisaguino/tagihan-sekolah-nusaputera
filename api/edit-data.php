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
    max(CASE WHEN MONTH(b.payment_due) = 1 THEN b.trx_status ELSE '' END) AS statusJanuari,
    max(CASE WHEN MONTH(b.payment_due) = 2 THEN b.trx_status ELSE '' END) AS statusFebruari,
    max(CASE WHEN MONTH(b.payment_due) = 3 THEN b.trx_status ELSE '' END) AS statusMaret,
    max(CASE WHEN MONTH(b.payment_due) = 4 THEN b.trx_status ELSE '' END) AS statusApril,
    max(CASE WHEN MONTH(b.payment_due) = 5 THEN b.trx_status ELSE '' END) AS statusMei,
    max(CASE WHEN MONTH(b.payment_due) = 6 THEN b.trx_status ELSE '' END) AS statusJuni,
    SUM(CASE WHEN MONTH(b.payment_due) = 1 THEN b.late_bills ELSE 0 END) AS LateJanuari,
    SUM(CASE WHEN MONTH(b.payment_due) = 2 THEN b.late_bills ELSE 0 END) AS LateFebruari,
    SUM(CASE WHEN MONTH(b.payment_due) = 3 THEN b.late_bills ELSE 0 END) AS LateMaret,
    SUM(CASE WHEN MONTH(b.payment_due) = 4 THEN b.late_bills ELSE 0 END) AS LateApril,
    SUM(CASE WHEN MONTH(b.payment_due) = 5 THEN b.late_bills ELSE 0 END) AS LateMei,
    SUM(CASE WHEN MONTH(b.payment_due) = 6 THEN b.late_bills ELSE 0 END) AS LateJuni";

    $academic_year = substr($tahun_ajaran, -4) - 1;

    $filter_total = "
    YEAR(p.trx_timestamp) = '$academic_year' AND
    MONTH(p.trx_timestamp BETWEEN 1 AND 6";
} else {
    $sql_semester = "
    SUM(CASE WHEN MONTH(b.payment_due) = 7  THEN b.trx_amount ELSE 0 END) AS Juli,
    SUM(CASE WHEN MONTH(b.payment_due) = 8  THEN b.trx_amount ELSE 0 END) AS Agustus,
    SUM(CASE WHEN MONTH(b.payment_due) = 9  THEN b.trx_amount ELSE 0 END) AS September,
    SUM(CASE WHEN MONTH(b.payment_due) = 10 THEN b.trx_amount ELSE 0 END) AS Oktober,
    SUM(CASE WHEN MONTH(b.payment_due) = 11 THEN b.trx_amount ELSE 0 END) AS November,
    SUM(CASE WHEN MONTH(b.payment_due) = 12 THEN b.trx_amount ELSE 0 END) AS Desember,
    max(CASE WHEN MONTH(b.payment_due) = 7  THEN b.trx_status ELSE '' END) AS statusJuli,
    max(CASE WHEN MONTH(b.payment_due) = 8  THEN b.trx_status ELSE '' END) AS statusAgustus,
    max(CASE WHEN MONTH(b.payment_due) = 9  THEN b.trx_status ELSE '' END) AS statusSeptember,
    max(CASE WHEN MONTH(b.payment_due) = 10 THEN b.trx_status ELSE '' END) AS statusOktober,
    max(CASE WHEN MONTH(b.payment_due) = 11 THEN b.trx_status ELSE '' END) AS statusNovember,
    max(CASE WHEN MONTH(b.payment_due) = 12 THEN b.trx_status ELSE '' END) AS statusDesember,
    SUM(CASE WHEN MONTH(b.payment_due) = 7  THEN b.late_bills ELSE 0 END) AS LateJuli,
    SUM(CASE WHEN MONTH(b.payment_due) = 8  THEN b.late_bills ELSE 0 END) AS LateAgustus,
    SUM(CASE WHEN MONTH(b.payment_due) = 9  THEN b.late_bills ELSE 0 END) AS LateSeptember,
    SUM(CASE WHEN MONTH(b.payment_due) = 10 THEN b.late_bills ELSE 0 END) AS LateOktober,
    SUM(CASE WHEN MONTH(b.payment_due) = 11 THEN b.late_bills ELSE 0 END) AS LateNovember,
    SUM(CASE WHEN MONTH(b.payment_due) = 12 THEN b.late_bills ELSE 0 END) AS LateDesember";
    
    $academic_year = substr($tahun_ajaran, -4) - 1;
    
    $filter_total = "
    YEAR(p.trx_timestamp) = '$academic_year' AND
    MONTH(p.trx_timestamp) BETWEEN 7 AND 12";
    
}

$sql = "SELECT
    b.nis, 
    b.virtual_account,
    b.student_name, b.level, b.parent_phone, b.period,
    SUM(CASE WHEN b.trx_status = 'paid' OR b.trx_status = 'late' THEN b.trx_amount ELSE 0 END) AS penerimaan, 
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
        b.student_name, b.level, 
        b.parent_phone, b.period;
    ";

$users = read($sql);

$totalsql = "SELECT
    SUM(CASE WHEN b.trx_status = 'paid' OR b.trx_status = 'late' THEN b.trx_amount ELSE 0 END) AS penerimaan, 
    SUM(CASE WHEN b.trx_status = 'late' OR b.trx_status = 'not paid' THEN l.late_bills ELSE 0 END) AS tunggakan
FROM
    bills b
    LEFT JOIN levels l ON b.level = l.name
WHERE 
    b.period = '$tahun_ajaran' AND 
    b.semester = '$semester'";



$total = read($totalsql);

$data = [
    'status' => true,
    'message' => 'Get Input Data',
    'q' => $sql,
    'data' => [
        "users" => $users,
        "total" => $total[0] ?? 0
    ]
];

echo json_encode($data);