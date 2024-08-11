<?php

include_once '../../config/app.php';

header('Content-Type: application/json');

$tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '';
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';
$virtual_account = isset($_GET['user']) ? $_GET['user'] : '';

$sql = "SELECT
users.nis, users.name, users.virtual_account,
levels.name AS jenjang
FROM users INNER JOIN levels ON users.level = levels.id
WHERE users.virtual_account = '$virtual_account'
";

$users = read($sql);

$user = $users[0];
$user['tahun_ajaran'] = $tahun_ajaran;
$user['semester'] = $semester;

$sql = "SELECT 
    DATE_FORMAT(b.payment_due, '%M %Y') AS `month`,
    b.trx_amount AS `bills`,
    (CASE WHEN b.trx_status = 'paid' OR b.trx_status = 'waiting' THEN 0 ELSE l.late_bills END) AS `late_bills`,
    p.trx_amount AS `payment_amount`,
    b.trx_status AS `trx_status`,
    p.trx_timestamp AS `paid_at`
FROM 
    bills b
LEFT JOIN 
    payments p ON b.id = p.bill_id
JOIN 
    levels l ON b.level = l.name
WHERE 
    b.virtual_account = '$virtual_account' AND
    b.period = '$tahun_ajaran' AND
    b.semester = '$semester'
ORDER BY 
    b.payment_due ASC;
";

$trx = read($sql);

$data = [
    'status' => 'OK',
    'message' => 'Get Student Payment Data',
    'query' => $sql,
    'data' => [
        'user' => $user,
        'trx'  => $trx 
    ]
];

echo json_encode($data);