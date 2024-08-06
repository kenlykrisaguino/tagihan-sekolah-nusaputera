<?php
include './config/app.php';
include_once './config/session.php';
include './header/admin.php';
// Check if user is logged in
IsLoggedIn();

$query_tahun_ajaran = 'SELECT DISTINCT tahun_ajaran FROM tagihan ORDER BY tahun_ajaran';
$query_semester = 'SELECT DISTINCT semester FROM tagihan ORDER BY semester';

$tahun_ajaran_options = read($query_tahun_ajaran);
$semester_options = read($query_semester);
?>

<h2 class="my-4">Input Data</h2>

<div class="d-flex mb-4 flex-wrap">
    <form action="csvinput.php" method="post" enctype="multipart/form-data" class="col-12 col-lg-6">
        <label for="file" class="d-block">Upload CSV File</label>
        <input type="file" name="file" id="file" accept=".csv" class="mb-2">
        <input type="submit" name="submit" value="Upload" class="btn btn-primary">
    </form>

    <!-- Form untuk filter berdasarkan bulan -->
    <div class="col-12 col-lg-6">
        <div class="form-row">
            <div class="form-group col-9">
                <label for="bulan" class="d-block">Filter Bulan</label>
                <select class="form-control" id="bulan" name="bulan">
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
                        $selected = $bulan_arr[$month] == $nama_bulan ? 'selected' : '';
                        echo "<option value='$bulan' $selected>$nama_bulan</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-3 align-self-end">
                <button type="submit" class="btn btn-primary">Filter</button>
                <!-- <a href="export_csv.php?bulan=<?= isset($_GET['bulan']) ? $_GET['bulan'] : '' ?>" class="btn btn-success">Export CSV</a> -->
            </div>
        </div>
    </div>
</div>
