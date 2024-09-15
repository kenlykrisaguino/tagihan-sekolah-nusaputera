<?php

include_once '../config/app.php';
header('Content-Type: application/json');

$period = isset($_GET['period']) ? $_GET['period'] : '';
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';
$month = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$level = isset($_GET['level']) ? $_GET['level'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';
$major = isset($_GET['major']) ? $_GET['major'] : '';

$filters = array(
    'period' => $period,
    'semester' => $semester,
    'month' => $month,
    'level' => $level,
    'class' => $class,
    'major' => $major,
);

$query = "SELECT nis, name FROM users u WHERE TRUE AND u.role = 'STUDENT'";
$history_query = "SELECT nis, name FROM student_history u WHERE TRUE";

if ($period!= '') {
    $query.= " AND period = '$period'";
    $history_query.= " AND period = '$period'";
}

if ($semester!= '') {
    $query.= " AND semester = '$semester'";
    $history_query.= " AND semester = '$semester'";
}

if ($month!= '') {
    $query.= " AND MONTH(due_date) = '$month'";
    $history_query.= " AND MONTH(due_date) = '$month'";
}

$class_query = "SELECT id FROM classes WHERE TRUE";

if ($level!= '') {
    $class_query.= " AND level = '$level'";
}

if ($class!= '') {
    $class_query.= " AND name = '$class'";
}

if ($major!= '') {
    $class_query.= " AND major = '$major'";
}

$classes = read($class_query);

if ($classes) {
    $class_ids = array_column($classes, 'id');
    $query.= " AND class IN (". implode(',', $class_ids). ")";
    $history_query.= " AND class IN (". implode(',', $class_ids). ")";
}

$users = read($query);

$history = read($history_query);

$data = [];

foreach ($users as $user) {
    if (!in_array($user['nis'], $data)) {
        $data[] = $user;
    }
}

foreach ($history as $h){
    if (!in_array($h['nis'], $data)) {
        $data[] = $h;
    }
}

echo json_encode(array(
    "status" => true,
    "message" => "Berhasil mendapat filter nama",
    "data" => $data
));
exit();
