<?php
// Memasukkan file konfigurasi aplikasi
include '../config/app.php';

// Menetapkan header untuk konten JSON
header('Content-Type: application/json');

// Menyusun query SQL untuk mengambil data periode yang unik dari tabel 'bills'
// Mengurutkan hasil berdasarkan periode
$query = 'SELECT DISTINCT period FROM bills ORDER BY period';

// Menjalankan query dan mendapatkan hasilnya menggunakan fungsi 'read'
$result = read($query);

// Mengubah hasil query menjadi format JSON dan mengirimkannya sebagai respons
echo json_encode($result);
?>
