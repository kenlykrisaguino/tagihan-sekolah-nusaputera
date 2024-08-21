<?php

function IsLoggedIn() {
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }
}

function RoleAllowed($level = null) {
    if ($_SESSION['role'] == 'ADMIN') {
        return $level === null ? false : true;
    } else {
        return $level === null ? true : false;
    }
}