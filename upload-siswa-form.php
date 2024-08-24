<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once './config/app.php';

    $nis = $_POST['nis'] ?? null;
    $name = $_POST['nama'] ?? null;
    $level = $_POST['level'] ?? null;
    $class = $_POST['class'] ?? null;
    $major = $_POST['major']?? null;
    $birthdate = $_POST['birth_date']?? null;
    $phone_number = $_POST['phone_number'] ?? "";
    $email_address = $_POST['email_address']?? "";
    $parent_phone = $_POST['parent_phone'] ?? null;
    $address = $_POST['address'] ?? null;

    $va = "988110562223" . $nis;
    $password = password_hash($nis, PASSWORD_DEFAULT);

    $classQuery = "SELECT id from classes WHERE level = '$level' AND name = '$class' AND major = '$major';";
    $class_id = read($classQuery)[0]['id']?? 1;

    $userQuery = "INSERT INTO users(
        nis, name, address,
        birthdate, status, class,
        phone_number, email_address, parent_phone,
        virtual_account, period, semester, password
    ) VALUES
    ('$nis', '$name', '$address', 
    '$birthdate', 'Active', '$class_id', 
    '$phone_number', '$email_address', '$parent_phone', 
    '$va', '$tahun_ajaran', '$semester', '$password')";
    if(crud($userQuery)){
        $_SESSION['success'] = "Berhasil menambahkan $name ke data siswa.";
        header('Location: ./rekap-siswa.php');
        exit;
    } 
    $_SESSION['error'] = 'Data gagal disimpan.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

