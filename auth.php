<?php
include_once './config/app.php';

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
    $check_password = (md5($inputPassword) === $hashed_password);
    
    if (!$check_password) {
        $_SESSION['error_message'] = 'Password salah';
        header('Location: '. $_SERVER['HTTP_REFERER']);
        exit;
    }

    $query = "SELECT `id`, `nis`, `class`, `role` FROM `users` WHERE `virtual_account` = '$inputUsername'";

    $results = read($query);
    $result = $results[0];

    $_SESSION['user_id'] = $result['id'];
    $_SESSION['nis'] = $result['nis'];
    $_SESSION['username'] = $inputUsername;
    $_SESSION['class'] = $result['class'];
    $_SESSION['role'] = $result['role'];

    if ($_SESSION['role'] == 'ADMIN' || $_SESSION['role'] == 'SUBADMIN' ){
        header('Location: rekap-siswa.php');
        exit();
    } else if ($_SESSION['role'] == 'STUDENT'){
        header('Location: beranda-siswa.php');
        exit();
    } else {
        header('Location: login.php');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}