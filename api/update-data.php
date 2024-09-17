<?php

// Memasukkan konfigurasi aplikasi dari file eksternal
include_once '../config/app.php';

// Menetapkan header konten sebagai JSON
header('Content-Type: application/json');

// Mengambil data dari POST request
$id = $_POST['id']; // ID untuk pencarian data yang akan diupdate
$column = $_POST['column']; // Nama kolom yang akan diupdate
$value = $_POST['value']; // Nilai baru untuk kolom yang diupdate

// Menyusun query SQL untuk memperbarui data pada tabel 'bills'
$sql = "UPDATE bills SET $column = '$value' WHERE nis = '$id'";

// Menjalankan query SQL untuk melakukan update
$result = crud($sql); // Fungsi 'crud()' digunakan untuk mengeksekusi query

// Memeriksa hasil dari query
if ($result) {
    // Jika query berhasil, mengembalikan respon JSON dengan status sukses
    echo json_encode([
        'success' => true, // Menandakan bahwa update berhasil
        'message' => 'Data has been updated', // Pesan sukses
        'data' => $result // Data hasil update, jika ada
    ]);
} else {
    // Jika query gagal, mengembalikan respon JSON dengan status gagal
    echo json_encode([
        'success' => false, // Menandakan bahwa update gagal
        'message' => 'failed to update data' // Pesan gagal
    ]);
}
?>
