<?php

include_once '../../config/app.php';

header('Content-Type: application/json');

$tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '';
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';
$virtual_account = isset($_GET['user']) ? $_GET['user'] : '';

$sql = "SELECT
u.nis, u.name, u.virtual_account,
CONCAT(c.level, ' ',c.name, ' ', c.major) AS jenjang
FROM users u INNER JOIN classes c ON u.class = c.id
WHERE u.virtual_account = '$virtual_account'
";

$users = read($sql);

$user = $users[0];
$user['tahun_ajaran'] = $tahun_ajaran;
$user['semester'] = $semester;

$sql = "SELECT 
    DATE_FORMAT(b.payment_due, '%M %Y') AS `month`,
    b.trx_amount AS `bills`,
    (CASE WHEN b.trx_status = 'paid' OR b.trx_status = 'waiting' OR b.trx_status = 'inactive' THEN 0 ELSE l.late_bills END) AS `late_bills`,
    p.trx_amount AS `payment_amount`,
    b.trx_status AS `trx_status`,
    p.trx_timestamp AS `paid_at`
FROM 
    bills b
LEFT JOIN 
    payments p ON b.id = p.bill_id
JOIN 
    classes l ON b.class = l.id
WHERE 
    b.virtual_account = '$virtual_account' AND
    b.period = '$tahun_ajaran' AND
    b.semester = '$semester'
ORDER BY 
    b.payment_due ASC
";

$trx = read($sql);

$data = [
    'status' => true,
    'message' => 'Get Student Payment Data',
    'data' => [
        'user' => $user,
        'trx'  => $trx 
    ]
];

echo json_encode($data);