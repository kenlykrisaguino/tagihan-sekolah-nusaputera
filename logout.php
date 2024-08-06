<?php
session_start(); // Mulai sesi

// Hancurkan semua data sesi
session_destroy();

// Alternatif: Hapus variabel sesi tertentu (misalnya, hanya informasi login)
// unset($_SESSION['username']);

// Pengalihan ke halaman login setelah logout
header("Location: login.php"); // Ubah ke halaman yang sesuai
exit();
