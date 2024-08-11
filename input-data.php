<?php
include './config/app.php';
include_once './config/session.php';
include './headers/admin.php';
// Check if user is logged in
IsLoggedIn();

$query_tahun_ajaran = 'SELECT DISTINCT period FROM bills ORDER BY period';
$query_semester = 'SELECT DISTINCT semester FROM bills ORDER BY semester';

$tahun_ajaran_options = read($query_tahun_ajaran);
$semester_options = read($query_semester);
?>

<h2 class="my-4">Input Data</h2>

<div class="d-flex mb-4 flex-wrap">
    <form id="csvUploadForm" class="col-12 col-lg-6">
        <label for="file" class="d-block">Upload CSV File</label>
        <input type="file" name="file" id="file" accept=".csv" class="mb-2">
        <input type="submit" value="Upload" class="btn btn-primary">
    </form>

    <!-- Form untuk filter berdasarkan bulan -->
    <div class="col-12 col-lg-6">
        <div class="form-row">
            <div class="form-group col-9">
                <label for="bulan" class="d-block">Filter Bulan</label>
                <select class="form-control" id="filter-bulan" name="filter-bulan">
                    <option value="">-- Pilih Bulan --</option>
                    <?php
                    // Daftar bulan
                    $bulan_arr = [
                        '01' => 'Januari',
                        '02' => 'Februari',
                        '03' => 'Maret',
                        '04' => 'April',
                        '05' => 'Mei',
                        '06' => 'Juni',
                        '07' => 'Juli',
                        '08' => 'Agustus',
                        '09' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ];
                    
                    foreach ($bulan_arr as $bulan => $nama_bulan) {
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
        if (month == ""){
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
                    $('#input-data-table').append(`<?php include_once './tables/input-data.php'?>`);
                })

                if (data.data.length == 0) {
                    $('#input-data-table').append(`<tr><td colspan="4" class="text-center">Tidak ada data yang ditemukan.</td></tr>`);
                }
            });
    }

    const uploadCSV = (e) => {
        e.preventDefault(); 

        var formData = new FormData();
        var fileInput = $('#file')[0].files[0];
        formData.append('input', fileInput); 

        $.ajax({
            url: 'api/csv-input.php', 
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#uploadStatus').html('<div class="alert alert-success">File uploaded successfully!</div>');
                console.log(response);
            },
            error: function(xhr, status, error) {
                $('#uploadStatus').html('<div class="alert alert-danger">Error occurred while uploading the file.</div>');
                console.error(xhr.responseText);
            }
        });
    }

    $(document).ready( () => {
        getData();
        $('#csvUploadForm').on('submit', function(e) {
            uploadCSV(e);
        });
    });
</script>