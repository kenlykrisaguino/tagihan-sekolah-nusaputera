<?php

include_once '../config/app.php';

header('Content-Type: application/json');

$id = $_POST['id'];
$column = $_POST['column'];
$value = $_POST['value'];
$tahunAjaran = $_POST['tahunAjaran'];
$semester = $_POST['semester'];
$month = $_POST['month'];

$indonesianMonths = [
    'Januari' => 1,
    'Februari' => 2,
    'Maret' => 3,
    'April' => 4,
    'Mei' => 5,
    'Juni' => 6,
    'Juli' => 7,
    'Agustus' => 8,
    'September' => 9,
    'Oktober' => 10,
    'November' => 11,
    'Desember' => 12,
];

$checkQuery = "SELECT
    trx_amount
FROM
    bills
WHERE
    nis = '$id' AND
    semester = '$semester' AND
    period = '$tahunAjaran' AND
    MONTH(payment_due) = '$indonesianMonths[$month]'";

$checkResult = read($checkQuery);

if (isset($checkResult) && count($checkResult) != 0) {
    // Kalau sudah ada transaktinya
    $sql = "UPDATE
        bills
    SET
        $column = '$value'
    WHERE
        nis = '$id' AND
        semester = '$semester' AND
        period = '$tahunAjaran' AND
        MONTH(payment_due) = '$indonesianMonths[$month]'";
} 

$result = crud($sql);

$data = [
    'status' => $result,
    'message' => $result ? "Berhasil mengupdate pembayaran bulan $month" : "Gagal"
];

echo json_encode($data);