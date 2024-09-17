<?php
// Memasukkan konfigurasi aplikasi
include '../config/app.php';

// Menetapkan header untuk konten JSON
header('Content-Type: application/json');

// Membuat query untuk mengambil nilai unik dari kolom 'semester' dan mengurutkannya
$query = 'SELECT DISTINCT semester FROM bills ORDER BY semester';

// Menjalankan query dan mendapatkan hasilnya
$result = read($query);

// Mengirimkan hasil query dalam format JSON
echo json_encode($result);
?>
