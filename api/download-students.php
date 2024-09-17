<?php

// Memasukkan konfigurasi aplikasi
include_once '../config/app.php'; 

// Mengatur header untuk file CSV dan mendefinisikan nama file unduhan
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=students.csv');

// Query SQL untuk mengambil data mahasiswa aktif
$sql = "
    SELECT 
        u.nis, 
        u.name, 
        c.level AS jenjang, 
        c.name AS tingkat, 
        c.major AS kelas, 
        u.address AS alamat, 
        u.birthdate, 
        u.phone_number, 
        u.email_address, 
        u.parent_phone
    FROM users u
    JOIN classes c ON u.class = c.id
    WHERE u.status = 'Active'
";

// Menjalankan query dan menyimpan hasilnya
$result = read($sql);

// Membuka aliran output untuk menulis file CSV
$output = fopen('php://output', 'w');

// Menulis header kolom ke file CSV
fputcsv($output, [
    'nis', 'name', 'jenjang', 'tingkat', 'kelas', 'alamat', 
    'birthdate', 'phone_number', 'email_address', 'parent_phone'
]);

// Menulis setiap baris data ke file CSV
foreach ($result as $row){
    fputcsv($output, $row);
}

// Menutup aliran output
fclose($output);

?>
