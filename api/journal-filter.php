<?php

include_once '../config/app.php'; // Memasukkan konfigurasi aplikasi
header('Content-Type: application/json'); // Mengatur header untuk output JSON

// Mengambil parameter dari query string dengan nilai default kosong
$period = isset($_GET['period']) ? $_GET['period'] : '';
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';
$month = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$level = isset($_GET['level']) ? $_GET['level'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';
$major = isset($_GET['major']) ? $_GET['major'] : '';

// Menyiapkan filter berdasarkan parameter yang diterima
$filters = array(
    'period' => $period,
    'semester' => $semester,
    'month' => $month,
    'level' => $level,
    'class' => $class,
    'major' => $major,
);

// Query untuk mengambil data pengguna dari tabel 'users'
$query = "SELECT nis, name FROM users u WHERE TRUE AND u.role = 'STUDENT'";

// Query untuk mengambil data sejarah mahasiswa dari tabel 'student_history'
$history_query = "SELECT nis, name FROM student_history u WHERE TRUE";

// Menambahkan kondisi filter jika parameter 'period' ada
if ($period != '') {
    $query .= " AND period = '$period'";
    $history_query .= " AND period = '$period'";
}

// Menambahkan kondisi filter jika parameter 'semester' ada
if ($semester != '') {
    $query .= " AND semester = '$semester'";
    $history_query .= " AND semester = '$semester'";
}

// Menambahkan kondisi filter jika parameter 'month' ada
if ($month != '') {
    $query .= " AND MONTH(due_date) = '$month'";
    $history_query .= " AND MONTH(due_date) = '$month'";
}

// Menyiapkan query untuk mengambil ID kelas dari tabel 'classes'
$class_query = "SELECT id FROM classes WHERE TRUE";

// Menambahkan kondisi filter jika parameter 'level' ada
if ($level != '') {
    $class_query .= " AND level = '$level'";
}

// Menambahkan kondisi filter jika parameter 'class' ada
if ($class != '') {
    $class_query .= " AND name = '$class'";
}

// Menambahkan kondisi filter jika parameter 'major' ada
if ($major != '') {
    $class_query .= " AND major = '$major'";
}

// Membaca ID kelas dari query yang telah disiapkan
$classes = read($class_query);

if ($classes) {
    // Mengambil ID kelas dari hasil query
    $class_ids = array_column($classes, 'id');
    // Menambahkan filter ID kelas ke query utama
    $query .= " AND class IN (" . implode(',', $class_ids) . ")";
    $history_query .= " AND class IN (" . implode(',', $class_ids) . ")";
}

// Menjalankan query untuk mengambil data pengguna
$users = read($query);

// Menjalankan query untuk mengambil data sejarah mahasiswa
$history = read($history_query);

$data = [];

// Menggabungkan data pengguna dan sejarah mahasiswa
foreach ($users as $user) {
    if (!in_array($user['nis'], array_column($data, 'nis'))) {
        $data[] = $user;
    }
}

foreach ($history as $h) {
    if (!in_array($h['nis'], array_column($data, 'nis'))) {
        $data[] = $h;
    }
}

// Mengirimkan hasil dalam format JSON
echo json_encode(array(
    "status" => true,
    "message" => "Berhasil mendapat filter nama",
    "data" => $data
));
exit();
