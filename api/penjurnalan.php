<?php

include_once '../config/app.php';

header('Content-Type: application/json');

// Mengambil parameter dari query string
$period = $_GET['period'] ?? '';
$semester = $_GET['semester'] ?? '';
$month = $_GET['bulan'] ?? '';
$level = $_GET['level'] ?? '';
$class = $_GET['class'] ?? '';
$major = $_GET['major'] ?? '';
$nis = $_GET['nis'] ?? '';

// Menyiapkan query untuk total bank, pendapatan, dan total denda
$query = "SELECT
    SUM(CASE
        WHEN b.trx_status IN ('paid', 'late') THEN b.trx_amount
        ELSE 0
    END) AS bank,
    SUM(CASE
        WHEN b.trx_status IN ('not paid' , 'late') THEN b.late_bills
        ELSE 0
    END) AS denda,
    SUM(CASE
        WHEN b.trx_status IN ('late') THEN b.stored_late_bills
        ELSE 0
    END) + SUM(CASE
        WHEN b.trx_status IN ('paid', 'late') THEN b.trx_amount
        ELSE 0
    END) AS pendapatan
    FROM bills b
    JOIN classes c ON b.class = c.id
    WHERE TRUE ";


// Menambahkan filter untuk bulan jika ada
if ($month !== '') {
    $query .= " AND MONTH(b.payment_due) = '$month'";
} else {
    // Jika bulan tidak dipilih, cek pembayaran di semester tersebut
    $query .= $semester !== '' ? " AND b.semester = '$semester'" : "";
}

// Menambahkan filter lainnya ke query
$query .= $period !== '' ? " AND b.period = '$period'" : "";
$query .= $level !== '' ? " AND c.level = '$level'" : "";
$query .= $class !== '' ? " AND c.name = '$class'" : "";
$query .= $major !== '' ? " AND c.major = '$major'" : "";
$query .= $nis !== '' ? " AND b.nis = '$nis'" : "";

// Eksekusi kedua query dan ambil hasilnya
$result = read($query)[0] ?? ['bank' => 0, 'denda' => 0, 'pendapatan' => 0];

// Output hasil dalam format JSON
echo json_encode([
    "status" => true,
    "data" => [
        "bank" => $result['bank'],
        "tunggakan" => $result['denda'],
        "pendapatan" => $result['pendapatan']
    ],
    "message" => "Hasil Penjurnalan berhasil didapat"
]);
