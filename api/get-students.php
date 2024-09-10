<?php

include_once '../config/app.php';

header('Content-Type: application/json');

$search = isset($_GET['search']) ? $_GET['search'] : null;
$level = isset($_GET['jenjang']) ? $_GET['jenjang'] : null;
$class = isset($_GET['tingkat']) ? $_GET['tingkat'] : null;
$major = isset($_GET['kelas']) ? $_GET['kelas'] : null;
$sort_by = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'virtual_account';
$sort_direction = isset($_GET['sortDir']) && strtolower($_GET['sortDir']) === 'asc' ? 'ASC' : 'DESC'; 

$sql = "SELECT
    u.nis, u.name, c.level AS level, c.name AS class, c.major, u.phone_number,
    u.email_address, u.parent_phone, u.virtual_account, 
    MAX(p.trx_timestamp) AS latest_payment, u.status
    FROM users u
    INNER JOIN classes c ON u.class = c.id
    LEFT JOIN payments p ON u.id = p.sender
    WHERE u.role != 'ADMIN' AND u.status = 'Active'
    ";

if ($search) {
    $sql.= " AND (u.name LIKE '%$search%' OR u.name LIKE '%$search%')";
}

if ($level) {
    $sql.= " AND c.level = '$level'";
}

if ($class) {
    $sql.= " AND c.name = '$class'";
}

if ($major) {
    $sql.= " AND c.major = '$major'";
}

$sql .= "GROUP BY 
    u.nis, u.name, c.level, c.name, c.major, u.phone_number,
    u.email_address, u.parent_phone, u.virtual_account, u.status
    ORDER BY $sort_by $sort_direction;";
$result = read($sql);

$data = [
    'status' => true,
    'message' => 'Get Input Data',
    'data' => $result
];

echo json_encode($data);