<?php

// Memasukkan file konfigurasi aplikasi
include_once '../config/app.php';

// Menetapkan header untuk konten JSON
header('Content-Type: application/json');

// Mengambil parameter dari query string
$month = isset($_GET['month']) ? $_GET['month'] : '';
$period = isset($_GET['period']) ? $_GET['period'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'trx_timestamp'; // Kolom pengurutan default
$sort_direction = isset($_GET['sort_direction']) && strtolower($_GET['sort_direction']) === 'asc' ? 'ASC' : 'DESC'; // Arah pengurutan default

// Mendefinisikan array untuk konversi bulan dari format string menjadi angka
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

// Menentukan tahun ajaran berdasarkan tahun saat ini
$academic_year = substr($tahun_ajaran, -4);

// Menyesuaikan tahun ajaran jika bulan yang diminta berada di semester genap
if($month != ''){
    $monthnum = $month_to_num[$month];
    if ($monthnum >= 6){
        $academic_year -= 1;
    }
}

// Menyusun query SQL untuk mengambil data pembayaran
$sql = "SELECT 
payments.virtual_account, users.name AS user, payments.trx_amount, payments.trx_timestamp
FROM
payments INNER JOIN users ON payments.sender = users.id 
WHERE TRUE";

// Menambahkan kondisi filter berdasarkan bulan jika diberikan
if ($month != '') {
    $sql .= " AND MONTH(trx_timestamp) = '$month'";
}

// Menambahkan kondisi filter berdasarkan tahun ajaran jika diberikan
if ($period != '') {
    $sql .= " AND YEAR(trx_timestamp) = '$academic_year'";
}

// Menambahkan pengurutan berdasarkan kolom dan arah yang ditentukan
$sql .= " ORDER BY $sort_by $sort_direction";

// Menjalankan query dan mendapatkan hasilnya
$result = read($sql);

// Menyusun data respons dalam format JSON
$data = [
    'status' => true,
    'message' => 'Get Input Data',
    'data' => $result
];

// Mengubah data menjadi format JSON dan mengirimkan sebagai respons
echo json_encode($data);
?>
