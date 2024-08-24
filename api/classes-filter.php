<?php
include_once '../config/app.php';
header('Content-Type: application/json');

$level = isset($_GET['level']) ? $_GET['level'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';
$major = isset($_GET['major']) ? $_GET['major'] : '';

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

$getClass = "SELECT id FROM classes WHERE TRUE ";

$getClass .= $level != "" ? " AND level = '$level' " :"";

$getClass.= $class!= ""? " AND name = '$class' " :"";

$getClass.= $major!= ""? " AND major = '$major' " :"";

$classID = read($getClass)[0]['id'];

$getStudents = "SELECT nis, name FROM users WHERE class = '$classID';";
$students = read($getStudents);

$data = array(
    'status' => true,
    'message' => 'Get Filter Classes successfull',
    'data' => array(
        'levels' => $levels ?? null,
        'classes'=> $classes ?? null,
        'majors' => $majors ?? null,
        'students' => $students ?? null
    )
);

echo json_encode($data);