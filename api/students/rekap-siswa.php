<?php

include_once '../../config/app.php';

header('Content-Type: application/json');

$virtual_account = isset($_GET['user']) ? $_GET['user'] : '';

$sql = "SELECT
    u.nis, u.name, u.virtual_account,
    u.parent_phone, u.period, u.semester,
    CONCAT(COALESCE(c.level, ''), ' ', COALESCE(c.name, ''), ' ', COALESCE(c.major, '')) AS level, c.monthly_bills, c.late_bills,
    SUM(
        CASE 
            WHEN b.trx_status = 'paid' 
            OR b.trx_status = 'late' 
            OR b.trx_status = 'inactive'
            THEN 
                0 
            WHEN b.trx_status = 'not paid'
            THEN 
                b.trx_amount + c.late_bills
            ELSE 
                b.trx_amount
        END
    ) AS total_bills,
    (
        SELECT 
            DATE_FORMAT(MAX(p2.trx_timestamp), '%d %M %Y') 
        FROM 
            payments p2 
        WHERE 
            p2.sender = u.id
    ) AS last_payment
FROM
    users u
    JOIN classes c ON u.class = c.id
    JOIN bills b ON u.nis = b.nis
WHERE 
    b.virtual_account = '$virtual_account'
GROUP BY
    u.nis, u.name, u.virtual_account,
    c.name, c.monthly_bills
";


$result = read($sql);

$data = [
    'status' => true,
    'message' => 'Get Student Recap Success',
    'data' => $result[0]
];

echo json_encode($data);