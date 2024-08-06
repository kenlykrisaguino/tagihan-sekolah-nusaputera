<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pembayaran</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .content-center {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 70vh;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="text-center my-4">
            <img src="../assets/img/logo.png" alt="Logo" style="width: 50px; height: 50px;">
            <h1>Sistem Pembayaran</h1>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs justify-content-center" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'inputdata.php' || $current_page == 'index.php') ? 'active' : ''; ?>" href="inputdata.php">Input Data</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'editdata.php') ? 'active' : ''; ?>" href="editdata.php">Edit Data</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'rekapdata.php') ? 'active' : ''; ?>" href="rekap2.php">Rekap</a>
            </li>
            <!-- <li class="nav-item">
                <a class="nav-link" href="settings.html">Penjurnalan</a>
            </li> -->
            <li class="nav-item">
                <a class="nav-link" href="Logout.php">Logout</a>
            </li>
        </ul>