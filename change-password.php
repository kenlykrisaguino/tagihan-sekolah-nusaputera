<?php

include_once './config/app.php';
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    returnError();
}

$old_password = $_POST['old_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['error_message'] = "Invalid Input";
    header('Location: '. $_SERVER['HTTP_REFERER']);
    exit;
}

$user_id = $_SESSION['user_id'];

$curr_pass = md5($old_password);

$query = "SELECT `password` FROM `users` WHERE `id` = $user_id AND `password` = '$curr_pass'";
echo $query;
$results = read($query);

if (count($results) < 1) {
    $_SESSION['error_message'] = "invalid password";
    header('Location: '. $_SERVER['HTTP_REFERER']);
    exit;
}

if ($new_password!= $confirm_password) {
    $_SESSION['error_message'] = "New password and confirm password do not match.";
    header('Location: '. $_SERVER['HTTP_REFERER']);
    exit;
}

$hashed_new_password = md5($new_password);

$query = "UPDATE `users` SET `password` = '$hashed_new_password' WHERE `id` = $user_id";

if (crud($query)) {
    $_SESSION['success_message'] = "Password has been changed successfully.";
    header('Location: '. $_SERVER['HTTP_REFERER']);
    exit;
}

$_SESSION['error_message'] = "Error while changing password";
header('Location: '. $_SERVER['HTTP_REFERER']);
exit;
