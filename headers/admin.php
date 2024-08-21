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
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/jquery.toast.css">

    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/jquery.toast.js"></script>
    <script>
        const formatToIDR = (number) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(number);
        }

        const fromIDRtoNum = (string) => {
            let cleanedString = string.replace(/Rp\s?/g, '').replace(/,00/g, '').replace(/[,.]/g, '');

            let numberValue = parseInt(cleanedString, 10);
            return numberValue;
        }

        const createBills = () => {
            $.ajax({
                url: 'api/create-bills.php',
                type: 'GET',
                success: function(response) {
                    if (response.status) {
                        $.toast({
                            heading: 'Berhasil',
                            text: 'Data tagihan berhasil dibuat, silahkan reload halaman untuk melihat perubahan',
                            showHideTransition: 'plain',
                            icon: 'success'
                        })
                    } else {
                        $.toast({
                            heading: 'Gagal',
                            text: response.message,
                            showHideTransition: 'plain',
                            icon: 'error'
                        })
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr);
                    console.error(status);
                    console.error(error);
                    $.toast({
                        heading: 'Gagal',
                        text: 'Terjadi kesalahan saat membuat tagihan',
                        showHideTransition: 'plain',
                        icon: 'error'
                    })
                }
            })
        }

        const checkBills = () => {
            $.ajax({
                url: 'api/check-bills.php',
                type: 'GET',
                success: function(response) {
                    if (response.status) {
                        $.toast({
                            heading: 'Success',
                            text: 'Cek tagihan berhasil, silahkan reload halaman untuk melihat perubahan',
                            showHideTransition: 'plain',
                            icon: 'success'
                        })
                    } else {
                        $.toast({
                            heading: 'Gagal',
                            text: response.message,
                            showHideTransition: 'plain',
                            icon: 'error'
                        })
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr);
                    console.error(status);
                    console.error(error);
                    $.toast({
                        heading: 'Gagal',
                        text: 'Terjadi kesalahan saat pengecekan tagihan',
                        showHideTransition: 'plain',
                        icon: 'error'
                    })
                }
            })
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>



</head>

<body>
    <div class="container">
        <div class="position-fixed" style="bottom: 20px; right: 20px;">
            <div class="btn-group dropup">
                <button type="button" class="btn btn-outline-primary btn-floating rounded-circle" data-mdb-ripple-init
                    data-mdb-ripple-color="dark" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php include_once dirname(__DIR__) . '\icons\gear.svg'; ?>
                </button>
                <ul class="dropdown-menu mb-2">
                    <li><a class="dropdown-item" id="m-check-bills" onclick="checkBills()" href="#">Check
                            Bills</a></li>
                    <li><a class="dropdown-item" id="m-create-bills" onclick="createBills()" href="#">Create
                            Bills</a></li>
                    <li><a class="dropdown-item" id="m-notify" href="#">Notify Parents</a></li>
                </ul>
            </div>
        </div>
        <!-- Header -->
        <div class="text-center my-4">
            <img src="assets/img/logo.png" alt="Logo" style="width: 50px; height: 50px;">
            <h1>Sistem Pembayaran</h1>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs justify-content-center" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'rekap-siswa.php' ? 'active' : ''; ?>" href="rekap-siswa.php">Siswa</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'input-data.php' ? 'active' : ''; ?>" href="input-data.php">Input Data</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'edit-data.php' ? 'active' : ''; ?>" href="edit-data.php">Edit Data</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'rekap-data.php' ? 'active' : ''; ?>" href="rekap-data.php">Rekap</a>
            </li>
            <!-- <li class="nav-item">
                <a class="nav-link" href="settings.html">Penjurnalan</a>
            </li> -->
            <li class="nav-item">
                <a class="nav-link" href="Logout.php">Logout</a>
            </li>
        </ul>
