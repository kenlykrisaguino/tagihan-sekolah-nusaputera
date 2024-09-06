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
    <link rel="stylesheet" href="assets/css/loader.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/jquery.toast.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.min.css">

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/jquery.toast.js"></script>
    <script>
        const showLoader = (status) => {
            if (status) {
                $('#loader').removeClass('d-none');
            } else {
                $('#loader').addClass('d-none');
            }
        };

        const formatToIDR = (number) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(number);
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="text-center my-4">
            <img src="assets/img/logo.png" alt="Logo" style="width: 50px; height: 50px;">
            <h1>Sistem Pembayaran</h1>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs justify-content-center" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'beranda-siswa.php' ? 'active' : ''; ?>" href="beranda-siswa.php">Beranda</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'pembayaran.php' ? 'active' : ''; ?>" href="pembayaran.php">Pembayaran</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="Logout.php">Logout</a>
            </li>
        </ul>
