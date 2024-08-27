<?php

include_once '../config/app.php';

header('Content-Type: application/json');

$period = isset($_GET['period']) ? $_GET['period'] : '';
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';
$month = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$level = isset($_GET['level']) ? $_GET['level'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';
$major = isset($_GET['major']) ? $_GET['major'] : '';
$nis = isset($_GET['nis']) ? $_GET['nis'] : '';

if ($month != '') {
    $tunggakan_query = "
        WHEN b.trx_status = 'not paid' THEN b.late_bills
        WHEN b.trx_status = 'late' THEN c.late_bills
    ";
} else {
    $tunggakan_query = "
        WHEN b.trx_status = 'not paid' THEN c.late_bills
        WHEN b.trx_status = 'late' THEN b.late_bills
    ";
}


$query_bank = "
    SELECT
        SUM(p.trx_amount) AS bank
    FROM payments p
    JOIN bills b ON p.bill_id = b.id
    JOIN classes c ON c.id = b.class
    WHERE TRUE
";

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

if ($month != '') {
    // Jika bulan dipilih, cek pembayaran di bulan tersebut
    $query_bank .= " AND MONTH(p.trx_timestamp) = '$month'";
    $query_tunggakan .= " AND MONTH(b.payment_due) = '$month'";
} else {
    // Jika bulan tidak dipilih, cek pembayaran di semester tersebut
    $query_bank .= $semester != '' ? " AND b.semester = '$semester'" : "";
    $query_tunggakan .= $semester != '' ? " AND b.semester = '$semester'" : "";
}

$query_bank .= $period != '' ? " AND b.period = '$period'" : "";
$query_bank .= $level != '' ? " AND c.level = '$level'" : "";
$query_bank .= $class != '' ? " AND c.name = '$class'" : "";
$query_bank .= $major != '' ? " AND c.major = '$major'" : "";
$query_bank .= $nis != '' ? " AND b.nis = '$nis'" : "";

$query_tunggakan .= $period != '' ? " AND b.period = '$period'" : "";
$query_tunggakan .= $level != '' ? " AND c.level = '$level'" : "";
$query_tunggakan .= $class != '' ? " AND c.name = '$class'" : "";
$query_tunggakan .= $major != '' ? " AND c.major = '$major'" : "";
$query_tunggakan .= $nis != '' ? " AND b.nis = '$nis'" : "";

// Eksekusi kedua query dan ambil hasilnya
$result_bank = read($query_bank)[0];
$result_tunggakan = read($query_tunggakan)[0];

// Hitung 'pendapatan' sebagai penjumlahan dari 'bank' dan 'tunggakan'
$pendapatan = $result_bank['bank'] + $result_tunggakan['tunggakan'];

// Output hasil dalam format JSON
echo json_encode(array(
    "status" => true,
    "data" => array(
        "bank" => $result_bank['bank'],
        "tunggakan" => $result_tunggakan['tunggakan'],
        "pendapatan" => $pendapatan
    ),
    "message" => "Hasil Penjurnalan berhasil didapat"
));
