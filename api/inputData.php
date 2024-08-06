<?php

include_once '../config/app.php';

$search = isset($_POST['search']) ? $_POST['search'] : '';
$tahun_ajaran = isset($_POST['tahun_ajaran']) ? $_POST['tahun_ajaran'] : '';
$semester = isset($_POST['semester']) ? $_POST['semester'] : '';

