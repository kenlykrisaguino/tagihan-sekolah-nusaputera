<?php

// Memasukkan file konfigurasi aplikasi
include_once '../config/app.php';

// Menetapkan header untuk konten JSON
header('Content-Type: application/json');

// Mengambil parameter dari query string, dengan nilai default jika tidak ada
$search = isset($_GET['search']) ? $_GET['search'] : null;
$level = isset($_GET['jenjang']) ? $_GET['jenjang'] : null;
$class = isset($_GET['tingkat']) ? $_GET['tingkat'] : null;
$major = isset($_GET['kelas']) ? $_GET['kelas'] : null;
$sort_by = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'virtual_account';
$sort_direction = isset($_GET['sortDir']) && strtolower($_GET['sortDir']) === 'asc' ? 'ASC' : 'DESC'; 

// Membangun query SQL untuk mendapatkan data pengguna
$sql = "SELECT
    u.nis, u.name, c.level AS level, c.name AS class, c.major, u.phone_number,
    u.email_address, u.parent_phone, u.virtual_account, 
    MAX(p.trx_timestamp) AS latest_payment, u.status
    FROM users u
    INNER JOIN classes c ON u.class = c.id
    LEFT JOIN payments p ON u.id = p.sender
    WHERE u.role != 'ADMIN' AND u.status = 'Active'
    ";

// Menambahkan filter pencarian jika ada parameter 'search'
if ($search) {
    $sql .= " AND (u.name LIKE '%$search%' OR u.email_address LIKE '%$search%')";
}

// Menambahkan filter level jika ada parameter 'jenjang'
if ($level) {
    $sql .= " AND c.level = '$level'";
}

// Menambahkan filter kelas jika ada parameter 'tingkat'
if ($class) {
    $sql .= " AND c.name = '$class'";
}

// Menambahkan filter major jika ada parameter 'kelas'
if ($major) {
    $sql .= " AND c.major = '$major'";
}

// Menambahkan pengelompokkan dan pengurutan hasil query
$sql .= "GROUP BY 
    u.nis, u.name, c.level, c.name, c.major, u.phone_number,
    u.email_address, u.parent_phone, u.virtual_account, u.status
    ORDER BY $sort_by $sort_direction;";

// Menjalankan query dan mendapatkan hasilnya
$result = read($sql);

// Menyiapkan data untuk dikirim dalam format JSON
$data = [
    'status' => true,
    'message' => 'Get Input Data',
    'data' => $result
];

// Mengirimkan data dalam format JSON
echo json_encode($data);

?>
