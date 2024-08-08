<?php
include_once './config/app.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];

    // Enkripsi input password menggunakan SHA-512
    $hashedPassword = hash('sha512', $inputPassword);

    // Query untuk mencari pengguna dengan username dan password yang diberikan
    $query = "SELECT * FROM `user` WHERE `username` = '$inputUsername' AND `password` = '$hashedPassword'";

    $results = read($query);

    if (count($results) < 1) {
        $_SESSION['error_message'] = 'Data tidak valid';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    $_SESSION['username'] = $inputUsername;

    $result = $results[0];

    if ($result['tipe'] == 1){
        header('Location: input-data.php');
        exit();
    } else if ($result['tipe'] == 2) {
        header('Location: informasi-pembayaran.php');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}

$conn->close(); // Tutup koneksi
