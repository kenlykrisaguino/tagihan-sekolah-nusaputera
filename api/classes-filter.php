<?php
// Memasukkan file konfigurasi aplikasi
include_once '../config/app.php';
// Mengatur header agar output berformat JSON
header('Content-Type: application/json');

// Mengambil parameter dari URL jika ada, dan menetapkan default sebagai string kosong jika tidak ada
$level = isset($_GET['level']) ? $_GET['level'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';
$major = isset($_GET['major']) ? $_GET['major'] : '';

// Query untuk mendapatkan daftar level yang berbeda dari tabel classes
$level_query = 'SELECT DISTINCT level FROM classes';
// Menjalankan query untuk mendapatkan data level
$levels = read($level_query);

// Jika parameter level diberikan, ambil data kelas sesuai dengan level yang dipilih
if ($level != '') {
    // Query untuk mendapatkan kelas berdasarkan level
    $class_query = "SELECT DISTINCT name FROM classes WHERE level = '$level'";
    // Menjalankan query untuk mendapatkan data kelas
    $classes = read($class_query);

    // Jika parameter kelas diberikan, ambil data major (jurusan) sesuai dengan level dan kelas yang dipilih
    if ($class != '') {
        // Query untuk mendapatkan jurusan berdasarkan level dan kelas
        $major_query = "SELECT DISTINCT major FROM classes WHERE level = '$level' AND name = '$class'";
        // Menjalankan query untuk mendapatkan data major
        $majors = read($major_query);
    }
}

// Inisialisasi variabel classID sebagai string kosong
$classID = '';

// Jika parameter level diberikan, ambil data ID kelas
if ($level != '') {
    // Query untuk mendapatkan ID kelas berdasarkan level, kelas, dan major
    $getClass = "SELECT id FROM classes WHERE TRUE ";
    
    // Menambahkan kondisi untuk filter berdasarkan level
    $getClass .= $level != "" ? " AND level = '$level' " : "";
    // Menambahkan kondisi untuk filter berdasarkan kelas
    $getClass .= $class != "" ? " AND name = '$class' " : "";
    // Menambahkan kondisi untuk filter berdasarkan major (jika ada)
    $getClass .= $major != "" ? " AND major = '$major' " : "";
    
    // Mendapatkan semua ID kelas dalam bentuk array
    $classIDs = array_column(read($getClass), 'id');
}

// Query untuk mendapatkan data siswa, dengan kondisi nis tidak sama dengan '0000' (mungkin ini adalah nis khusus atau placeholder)
$getStudents = "SELECT nis, name FROM users WHERE nis != '0000'";
// Jika terdapat data ID kelas, tambahkan kondisi untuk memfilter siswa berdasarkan kelas yang dipilih
if (!empty($classIDs)) {
    // Menggabungkan ID kelas menjadi string yang bisa digunakan dalam query IN
    $classIDList = implode(',', $classIDs);
    // Menambahkan kondisi untuk filter berdasarkan kelas
    $getStudents .= " AND class IN ($classIDList)";
}

// Menjalankan query untuk mendapatkan data siswa
$students = read($getStudents);

// Menyusun data hasil query untuk dikembalikan sebagai response JSON
$data = array(
    'status' => true, // Status sukses
    'message' => 'Get Filter Classes successful', // Pesan sukses
    'c' => $classID, // Menyimpan ID kelas yang digunakan (jika ada)
    'data' => array(
        'levels' => $levels ?? null, // Data level (atau null jika tidak ada)
        'classes'=> $classes ?? null, // Data kelas (atau null jika tidak ada)
        'majors' => $majors ?? null, // Data major (jurusan) (atau null jika tidak ada)
        'students' => $students ?? null // Data siswa (atau null jika tidak ada)
    )
);

// Mengembalikan response dalam format JSON
echo json_encode($data);
?>
