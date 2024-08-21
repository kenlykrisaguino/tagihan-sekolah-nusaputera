<?php
require_once './config/app.php';
// Check if user is logged in
IsLoggedIn();
RoleAllowed(7) ? null : returnError();

include './headers/admin.php';

$query_tahun_ajaran = 'SELECT DISTINCT period FROM bills ORDER BY period';
$query_semester = 'SELECT DISTINCT semester FROM bills ORDER BY semester';

$tahun_ajaran_options = read($query_tahun_ajaran);
$semester_options = read($query_semester);
?>

<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Input Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="csvUploadForm" class="col-12 col-md-8">
                    <label for="file" class="d-block">Upload CSV</label>
                    <input type="file" name="file" id="file" accept=".csv" class="mb-2">
                    <div class="d-flex my-2">
                        <input type="submit" value="Upload" class="mr-2 btn btn-primary">
                        <input onclick="downloadCSV()" type="button" value="Download Template" class="ml-2 btn btn-outline-primary">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="d-flex my-4">
    <div class="col-9">
        <h2 class="">Input Data</h2>
    </div>
    <div class="col-3">        
        <button id="uploadBtn" class="btn btn-outline-primary w-100">Tambahkan</button>
    </div>
</div>

<div class="d-flex mb-4 flex-wrap">
    <!-- Form untuk filter berdasarkan bulan -->
    <div class="col-6">
        <div class="form-row">
            <div class="form-group col-9">
                <label for="bulan" class="d-block">Filter Bulan</label>
                <select class="form-control" id="filter-bulan" name="filter-bulan">
                    <option value="">-- Pilih Bulan --</option>
                    <?php
                    foreach ($months as $bulan => $nama_bulan) {
                        echo "<option value='$bulan'>$nama_bulan</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-3 align-self-end">
                <button type="submit" class="btn btn-primary" onclick="getData()">Filter</button>
                <!-- <a href="export_csv.php?bulan=<?= isset($_GET['bulan']) ? $_GET['bulan'] : '' ?>" class="btn btn-success">Export CSV</a> -->
            </div>
        </div>
    </div>
</div>

<table class="table table-bordered table-striped">
    <thead class="thead-dark">
        <tr>
            <th>Virtual Account</th>
            <th>Nama Pelanggan</th>
            <th>Jumlah Pembayaran</th>
            <th>Tanggal Pembayaran</th>
        </tr>
    </thead>
    <tbody id="input-data-table"></tbody>
</table>

<script>
    const getData = () => {
        var month = $('#filter-bulan').find(':selected').val();
        if (month == "") {
            var url = 'api/input-data.php'
        } else {
            var url = `api/input-data.php?month=${month}`
        }
        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log(data);
                $('#input-data-table').empty();
                data.data.forEach(trx => {
                    $('#input-data-table').append(`<?php include_once './tables/input-data.php'; ?>`);
                })

                if (data.data.length == 0) {
                    $('#input-data-table').append(
                        `<tr><td colspan="4" class="text-center">Tidak ada data yang ditemukan.</td></tr>`);
                }
            });
    }

    const downloadCSV = () => {
        $.ajax({
            url: 'api/template-payment.php',
            method: 'POST',
            xhrFields: {
                responseType: 'blob' // Important to receive a binary file
            },
            success: function(response) {
                var link = document.createElement('a');
                link.href = URL.createObjectURL(response);
                link.download = 'template_payment.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function(xhr, status, error) {
                console.error(xhr);
                console.error(status);
                console.error(error);
            }
        })
    }

    const uploadCSV = (e) => {
        e.preventDefault();

        var formData = new FormData();
        var fileInput = $('#file')[0].files[0];
        formData.append('input', fileInput);

        $.ajax({
            url: 'api/input-payment.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                console.log(response);
                if (response.status) {
                    $.toast({
                        heading: 'Berhasil',
                        text: 'Berhasil menambahkan data',
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
                    console.error(response.message);
                }
                getData();
            },
            error: function(xhr, status, error) {
                console.error(xhr);
                console.error(status);
                console.error(error);
                $.toast({
                    heading: 'Gagal',
                    text: 'Gagal menambahkan data',
                    showHideTransition: 'plain',
                    icon: 'error'
                })
                
            }
        });
    }

    $(document).ready(() => {
        getData();
        $('#csvUploadForm').on('submit', function(e) {
            uploadCSV(e);
        });
        $('#uploadBtn').on('click', function() {
            $('#uploadModal').modal('show');
        });
    });
</script>
