<?php
include './config/session.php';
include './config/app.php';

IsLoggedIn();

if ($_SESSION['level'] == 7){
    header('Location: rekap-siswa.php');
    exit();
} else if ($_SESSION['level'] < 7){
    header('Location: beranda-siswa.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}