<?php

include_once '../config/app.php';

header('Content-Type: application/json');

$month = isset($_GET['month']) ? $_GET['month'] : '';
$period = isset($_GET['period']) ? $_GET['period'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'trx_timestamp'; // Default sort column
$sort_direction = isset($_GET['sort_direction']) && strtolower($_GET['sort_direction']) === 'asc' ? 'ASC' : 'DESC'; // Default direction

$month_to_num = array(
    '01' => 1,
    '02' => 2,
    '03' => 3,
    '04' => 4,
    '05' => 5,
    '06' => 6,
    '07' => 7,
    '08' => 8,
    '09' => 9,
    '10' => 10,
    '11' => 11,
    '12' => 12,
);

$academic_year = substr($tahun_ajaran, -4);

if($month != ''){
    $monthnum = $month_to_num[$month];
    if ($monthnum >= 6){
        $academic_year -= 1;
    }
}


$sql = "SELECT 
payments.virtual_account, users.name AS user, payments.trx_amount, payments.trx_timestamp
FROM
payments INNER JOIN users ON payments.sender = users.id 
WHERE TRUE";

if ($month != '') {
    $sql .= " AND MONTH(trx_timestamp) = '$month'";
}

if ($period != '') {
    $sql .= " AND YEAR(trx_timestamp) = '$academic_year'";
}

$sql .= " ORDER BY $sort_by $sort_direction";

$result = read($sql);

$data = [
    'status' => true,
    'message' => 'Get Input Data',
    'data' => $result
];

echo json_encode($data);