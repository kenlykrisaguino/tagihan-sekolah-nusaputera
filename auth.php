<?php
include_once './config/app.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];

    // Query untuk mencari pengguna dengan username dan password yang diberikan
    $query = "SELECT `password` FROM `users` WHERE `virtual_account` = '$inputUsername'";

    $results = read($query);

    if (count($results) < 1) {
        $_SESSION['error_message'] = 'User tidak ditemukan';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    $hashed_password = $results[0]['password'];
    $check_password = password_verify($inputPassword, $hashed_password);
    
    if (!$check_password) {
        $_SESSION['error_message'] = 'Password salah';
        header('Location: '. $_SERVER['HTTP_REFERER']);
        exit;
    }

    $query = "SELECT `id`, `nis`, `level` FROM `users` WHERE `virtual_account` = '$inputUsername'";

    $results = read($query);
    $result = $results[0];

    $_SESSION['user_id'] = $results['id'];
    $_SESSION['nis'] = $results['nis'];
    $_SESSION['username'] = $inputUsername;


    if ($result['level'] == 7){
        header('Location: input-data.php');
        exit();
    } else {
        header('Location: beranda-siswa.php');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}