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

$classID = '';

if ($level != ''){
    $getClass = "SELECT id FROM classes WHERE TRUE ";
    
    $getClass .= $level != "" ? " AND level = '$level' " : "";
    $getClass .= $class != "" ? " AND name = '$class' " : "";
    $getClass .= $major != "" ? " AND major = '$major' " : "";
    
    $classIDs = array_column(read($getClass), 'id');
}

$getStudents = "SELECT nis, name FROM users WHERE nis != '0000'";
if (!empty($classIDs)) {
    $classIDList = implode(',', $classIDs);
    $getStudents .= " AND class IN ($classIDList)";
}

$students = read($getStudents);

$data = array(
    'status' => true,
    'message' => 'Get Filter Classes successfull',
    'c' => $classID,
    'data' => array(
        'levels' => $levels ?? null,
        'classes'=> $classes ?? null,
        'majors' => $majors ?? null,
        'students' => $students ?? null
    )
);

echo json_encode($data);