<?php

include_once '../config/app.php'; 

header('Content-Type: application/json');

// get the id from post request and delete users
$id = $_POST['id'];

// get user data and move it to history

$getUser = "SELECT
nis, name, class,
phone_number, email_address, parent_phone,
virtual_account, period, semester
FROM users WHERE nis = '$id'";

$userData = read($getUser)[0] ?? null;

if (!$userData) {
    $output = [
        'status' => false,
        'message' => 'Invalid Student Number',
        'data' => $userData
    ];
    echo json_encode($output);
    exit();
}

$data = [
    'nis' => $userData['nis'],
    'name' => $userData['name'],
    'class' => $userData['class'],
    'phone_number' => $userData['phone_number'],
    'email_address' => $userData['email_address'],
    'parent_phone' => $userData['parent_phone'],
    'virtual_account' => $userData['virtual_account'],
    'period' => $userData['period'],
    'semester' => $userData['semester'],
    'updated_at' => date('Y-m-d H:i:s')
];

// insert user data to history table
$historyQuery = "INSERT INTO student_history(
    nis, name, class, phone_number, email_address,
    parent_phone, virtual_account, period, semester, updated_at)
    VALUES 
    ('{$data['nis']}', '{$data['name']}', '{$data['class']}',
    '{$data['phone_number']}', '{$data['email_address']}',
    '{$data['parent_phone']}', '{$data['virtual_account']}',
    '{$data['period']}', '{$data['semester']}', '{$data['updated_at']}')";
    
if(!crud($historyQuery)){
    $output = [
        'status' => false,
        'message' => 'Failed to insert student to history table',
        'data' => $userData
    ];
    echo json_encode($output);
    exit();
}


$sql = "UPDATE users SET status = 'Inactive' WHERE nis = '$id'";

$result = crud($sql);

$output = [];
if($result) {
    $output = [
        'status' => true,
        'message' => 'Successfully deleted student',
        'data' => $result
    ];
} else {
    $output = [
        'status' => false,
        'message' => 'Failed to delete student',
        'data' => $result
    ];
}

echo json_encode($output);

?>