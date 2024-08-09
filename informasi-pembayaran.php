<?php
include './config/app.php';
include_once './config/session.php';
include './headers/siswa.php';
// Check if user is logged in
IsLoggedIn();

$query_tahun_ajaran = 'SELECT DISTINCT period FROM bills ORDER BY period';
$query_semester = 'SELECT DISTINCT semester FROM bills ORDER BY semester';

$tahun_ajaran_options = read($query_tahun_ajaran);
$semester_options = read($query_semester);

?>

<h2 class="my-4">
    Informasi Pembayaran
</h2>

<div class="d-flex flex-wrap">
    <div class="form-group col-6">
        <label for="tahun_ajaran">Tahun Ajaran:</label>
        <select name="tahun_ajaran" id="tahun_ajaran" class="form-control">
            <!-- <option selected disabled>Pilih Tahun Ajaran</option> -->
            <?php foreach ($tahun_ajaran_options as $option) { ?>
                <option value="<?php echo $option['period']; ?>" <?php echo $tahun_ajaran == $option['period'] ? 'selected' : ''; ?>><?php echo $option['period'] ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="form-group col-6">
        <label for="semester">Semester:</label>
        <select name="semester" id="semester" class="form-control">
            <!-- <option selected disabled>Pilih Semester</option> -->
            <?php foreach ($semester_options as $option) { ?>
                <option value="<?php echo $option['semester']; ?>" <?php echo $semester == $option['semester'] ? 'selected' : ''; ?>><?php echo $option['semester'] ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="form-group col-12">
        <button class="btn btn-primary w-100">Filter</button>
    </div>
</div>

<table class="table table-bordered my-4">
    <tbody id="data-siswa">

    </tbody>
</table>

<table class="table table-bordered my-4">
    <thead class="thead-dark text-center">
        <tr>
            <th>Bulan</th>
            <th>Tagihan</th>
            <th>Tunggakan</th>
            <th>Status</th>
            <th>Tanggal Pembayaran</th>
        </tr>
    </thead>
</table>