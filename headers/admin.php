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
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.min.js"></script>
    
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
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

        const fromIDRtoNum = (string) => {
            let cleanedString = string.replace(/Rp\s?/g, '').replace(/,00/g, '').replace(/[,.]/g, '');

            let numberValue = parseInt(cleanedString, 10);
            return numberValue;
        }

        const createBills = () => {
            showLoader(true);
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
                        });
                        refreshData();
                    } else {
                        $.toast({
                            heading: 'Gagal',
                            text: response.message,
                            showHideTransition: 'plain',
                            icon: 'error'
                        });
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
                    });
                },
                complete: function() {
                    showLoader(false);
                }
            });
        }

        const createCharge = () => {
            showLoader(true);
            $.ajax({
                url: 'api/create-charge.php',
                type: 'GET',
                success: function(response) {
                    if (response.status) {
                        $.toast({
                            heading: 'Berhasil',
                            text: 'Berhasil mengirimkan tagihan ke siswa',
                            showHideTransition: 'plain',
                            icon: 'success'
                        })
                        showLoader(false);
                        refreshData();
                    } else {
                        $.toast({
                            heading: 'Gagal',
                            text: response.message,
                            showHideTransition: 'plain',
                            icon: 'error'
                        })
                        showLoader(false);
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
                    showLoader(false);
                }
            })
        }

        const checkBills = () => {
            showLoader(true);
            $.ajax({
                url: 'api/check-bills.php',
                type: 'GET',
                success: function(response) {
                    console.log(response);
                    if (response.status) {
                        $.toast({
                            heading: 'Success',
                            text: 'Cek tagihan berhasil, silahkan reload halaman untuk melihat perubahan',
                            showHideTransition: 'plain',
                            icon: 'success'
                        })
                        showLoader(false);
                        refreshData();
                    } else {
                        $.toast({
                            heading: 'Gagal',
                            text: response.message,
                            showHideTransition: 'plain',
                            icon: 'error'
                        })
                        showLoader(false);

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
                    showLoader(false);
                }
            })
        }

        const notifyParents = (forceNotify) => {
            showLoader(true);
            url = 'api/notify-bills.php?type='+forceNotify
            console.warn(url);
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    if (response.status) {
                        $.toast({
                            heading: 'Success',
                            text: response.message,
                            showHideTransition: 'plain',
                            icon: 'success'
                        })
                        showLoader(false);
                    } else {
                        $.toast({
                            heading: 'Gagal',
                            text: response.message,
                            showHideTransition: 'plain',
                            icon: 'error'
                        })
                        showLoader(false);

                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr);
                    console.error(status);
                    console.error(error);
                    $.toast({
                        heading: 'Gagal',
                        text: 'Terjadi kesalahan saat pengiriman notifikasi',
                        showHideTransition: 'plain',
                        icon: 'error'
                    })
                    showLoader(false);
                }
            })
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</head>


