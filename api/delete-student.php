<?php

// Memasukkan konfigurasi aplikasi
include_once '../config/app.php'; 

// Mengatur header konten sebagai JSON
header('Content-Type: application/json');

// Mengambil ID dari permintaan POST dan menghapus pengguna
$id = $_POST['id'];

// Query untuk mendapatkan data pengguna berdasarkan ID
$getUser = "SELECT
nis, name, class,
phone_number, email_address, parent_phone,
virtual_account, period, semester
FROM users WHERE nis = '$id'";

$userData = read($getUser)[0] ?? null;

// Memeriksa apakah data pengguna ditemukan
if (!$userData) {
    $output = [
        'status' => false,
        'message' => 'Invalid Student Number',
        'data' => $userData
    ];
    echo json_encode($output);
    exit(); // Menghentikan eksekusi lebih lanjut
}

// Menyiapkan data pengguna untuk dimasukkan ke tabel history
$data = [
    'nis' => $userData['nis'],
    'name' => $userData['name'],
    'class' => $userData['class'],
    'phone_number' => $userData['phone_number'],
    'email_address' => $userData['email_address'],
    'parent_phone' => $userData['parent_phone'],
    'virtual_account' => $userData['virtual_account'],
    'period' => $userData['period'],
    'semester' => $userData['semester'],
    'updated_at' => date('Y-m-d H:i:s') // Menambahkan timestamp saat ini
];

// Query untuk memasukkan data pengguna ke tabel history
$historyQuery = "INSERT INTO student_history(
    nis, name, class, phone_number, email_address,
    parent_phone, virtual_account, period, semester, updated_at)
    VALUES 
    ('{$data['nis']}', '{$data['name']}', '{$data['class']}',
    '{$data['phone_number']}', '{$data['email_address']}',
    '{$data['parent_phone']}', '{$data['virtual_account']}',
    '{$data['period']}', '{$data['semester']}', '{$data['updated_at']}')";
    
// Menjalankan query untuk memasukkan data ke tabel history
if(!crud($historyQuery)){
    $output = [
        'status' => false,
        'message' => 'Failed to insert student to history table',
        'data' => $userData
    ];
    echo json_encode($output);
    exit(); // Menghentikan eksekusi lebih lanjut
}

// Query untuk memperbarui status pengguna menjadi 'Inactive'
$sql = "UPDATE users SET status = 'Inactive' WHERE nis = '$id'";

// Menjalankan query untuk memperbarui status pengguna
$result = crud($sql);

// Menyiapkan output berdasarkan hasil operasi
$output = [];
if($result) {
    $output = [
        'status' => true,
        'message' => 'Successfully deleted student',
        'data' => $result
    ];
} else {
    $output = [
        'status' => false,
        'message' => 'Failed to delete student',
        'data' => $result
    ];
}

// Mengembalikan output dalam format JSON
echo json_encode($output);

?>
