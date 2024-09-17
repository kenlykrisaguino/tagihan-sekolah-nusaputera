<?php

// Memasukkan konfigurasi aplikasi dari file eksternal
include_once '../config/app.php';

// Menetapkan header konten sebagai JSON
header('Content-Type: application/json');

// Mengambil data dari POST request
$id = $_POST['id']; // ID untuk pencarian data yang akan diupdate
$column = $_POST['column']; // Nama kolom yang akan diupdate
$value = $_POST['value']; // Nilai baru untuk kolom yang diupdate
$tahunAjaran = $_POST['tahunAjaran']; // Tahun ajaran untuk filter data
$semester = $_POST['semester']; // Semester untuk filter data
$month = $_POST['month']; // Bulan untuk filter data

// Mendefinisikan array yang memetakan nama bulan dalam bahasa Indonesia ke angka bulan
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

// Menyusun query SQL untuk memeriksa data yang ada berdasarkan ID, semester, tahun ajaran, dan bulan
$checkQuery = "SELECT
    trx_amount,  // Kolom jumlah transaksi
    payment_due  // Kolom jatuh tempo pembayaran
FROM
    bills
WHERE
    nis = '$id' AND
    semester = '$semester' AND
    period = '$tahunAjaran' AND
    MONTH(payment_due) = '{$indonesianMonths[$month]}'";

// Menjalankan query SQL untuk memeriksa data
$checkResult = read($checkQuery);

// Jika hasil query tidak kosong, lanjutkan dengan update data
if (isset($checkResult) && count($checkResult) != 0) {
    $due = $checkResult[0]['payment_due']; // Mengambil tanggal jatuh tempo dari hasil query
    // Menyusun query SQL untuk memperbarui data
    $sql = "UPDATE
        bills
    SET
        $column = '$value',  // Memperbarui kolom dengan nilai baru
        payment_due = '$due' // Menjaga agar tanggal jatuh tempo tetap sama
    WHERE
        nis = '$id' AND
        semester = '$semester' AND
        period = '$tahunAjaran' AND
        MONTH(payment_due) = '{$indonesianMonths[$month]}'";
} 

// Menjalankan query SQL untuk memperbarui data
$result = crud($sql);

// Menyiapkan data respons JSON untuk dikembalikan
$data = [
    'status' => $result, // Status operasi update
    'message' => $result ? "Berhasil mengupdate pembayaran bulan $month" : "Gagal" // Pesan sukses atau gagal
];

// Mengembalikan data dalam format JSON
echo json_encode($data);
?>
