<?php
include_once '../config/app.php';
header('Content-Type: application/json');

$level = isset($_GET['level']) ? $_GET['level'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';

$level_query = 'SELECT DISTINCT level FROM classes';
$levels = read($level_query);

if($level != '') {
    $class_query = "SELECT DISTINCT name FROM classes WHERE level = '$level'";

    $classes = read($class_query);

    if($class != '') {
        $major_query = "SELECT DISTINCT major FROM classes WHERE level = '$level' AND name = '$class'";
        $majors = read($major_query);
    }
}

$data = array(
    'status' => true,
    'message' => 'Get Filter Classes successfull',
    'data' => array(
        'levels' => $levels ?? null,
        'classes'=> $classes ?? null,
        'majors' => $majors ?? null
    )
);

echo json_encode($data);