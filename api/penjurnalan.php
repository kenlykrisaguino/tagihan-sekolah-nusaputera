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

// menambahkan 1 bulan
$month = $month == '' ? '' : ((int)$month % 12) + 1;

// Menentukan query untuk tunggakan berdasarkan bulan
$tunggakan_query = $month !== '' ?
    "WHEN b.trx_status = 'not paid' THEN b.late_bills
     WHEN b.trx_status = 'late' THEN c.late_bills" :
    "WHEN b.trx_status = 'not paid' THEN c.late_bills
     WHEN b.trx_status = 'late' THEN b.late_bills";

// Query untuk total pembayaran dari bank
$query_bank = "
    SELECT
        SUM(p.trx_amount) AS bank
    FROM payments p
    JOIN bills b ON p.bill_id = b.id
    JOIN classes c ON c.id = b.class
    WHERE TRUE AND
    b.trx_status IN ('paid', 'late') 
";

// Query untuk total tunggakan
$query_tunggakan = "
    SELECT
        SUM(CASE 
            $tunggakan_query
            ELSE 0 
        END) AS tunggakan
    FROM bills b
    JOIN classes c ON c.id = b.class
    WHERE TRUE
";

// Menambahkan filter untuk bulan jika ada
if ($month !== '') {
    $query_bank .= " AND MONTH(b.payment_due) = '$month'";
    $query_tunggakan .= " AND MONTH(b.payment_due) = '$month'";
} else {
    // Jika bulan tidak dipilih, cek pembayaran di semester tersebut
    $query_bank .= $semester !== '' ? " AND b.semester = '$semester'" : "";
    $query_tunggakan .= $semester !== '' ? " AND b.semester = '$semester'" : "";
}

// Menambahkan filter lainnya ke query
$query_bank .= $period !== '' ? " AND b.period = '$period'" : "";
$query_bank .= $level !== '' ? " AND c.level = '$level'" : "";
$query_bank .= $class !== '' ? " AND c.name = '$class'" : "";
$query_bank .= $major !== '' ? " AND c.major = '$major'" : "";
$query_bank .= $nis !== '' ? " AND b.nis = '$nis'" : "";

$query_tunggakan .= $period !== '' ? " AND b.period = '$period'" : "";
$query_tunggakan .= $level !== '' ? " AND c.level = '$level'" : "";
$query_tunggakan .= $class !== '' ? " AND c.name = '$class'" : "";
$query_tunggakan .= $major !== '' ? " AND c.major = '$major'" : "";
$query_tunggakan .= $nis !== '' ? " AND b.nis = '$nis'" : "";

// Eksekusi kedua query dan ambil hasilnya
$result_bank = read($query_bank)[0] ?? ['bank' => 0];
$result_tunggakan = read($query_tunggakan)[0] ?? ['tunggakan' => 0];

// Hitung 'pendapatan' sebagai penjumlahan dari 'bank' dan 'tunggakan'
$pendapatan = $result_bank['bank'];
// $pendapatan = $result_bank['bank'] + $result_tunggakan['tunggakan'];

// Output hasil dalam format JSON
echo json_encode([
    "status" => true,
    "data" => [
        "bank" => $result_bank['bank'],
        "tunggakan" => $result_tunggakan['tunggakan'],
        "pendapatan" => $pendapatan
    ],
    "message" => "Hasil Penjurnalan berhasil didapat"
]);
