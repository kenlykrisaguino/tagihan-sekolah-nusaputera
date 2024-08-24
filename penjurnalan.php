<?php
include './config/app.php';
// Check if user is logged in
IsLoggedIn();
RoleAllowed(7) ? null : returnError();

$query_tahun_ajaran = "SELECT DISTINCT period FROM bills ORDER BY period";
$query_semester = "SELECT DISTINCT semester FROM bills ORDER BY semester";

$tahun_ajaran_options = read($query_tahun_ajaran);
$semester_options = read($query_semester);

include './headers/admin.php';
?>

<h2 class="my-4">
    Penjurnalan
</h2>

<div class="filter d-flex flex-wrap">
    <div class="form-group col-6">
        <label for="tahun_ajaran">Tahun Ajaran:</label>
        <select name="tahun_ajaran" id="tahun_ajaran" class="form-control">
            <!-- <option selected disabled>Pilih Tahun Ajaran</option> -->
            <?php foreach ($tahun_ajaran_options as $option) { ?>
            <option value="<?php echo $option['period']; ?>" <?php echo $tahun_ajaran == $option['period'] ? 'selected' : ''; ?>><?php echo $option['period']; ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="form-group col-6">
        <label for="semester">Semester:</label>
        <select name="semester" id="semester" class="form-control">
            <!-- <option selected disabled>Pilih Semester</option> -->
            <?php foreach ($semester_options as $option) { ?>
            <option value="<?php echo $option['semester']; ?>" <?php echo $semester == $option['semester'] ? 'selected' : ''; ?>><?php echo $option['semester']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="form-group col-6">
        <label for="jenjang">Jenjang</label>
        <select name="jenjang" id="jenjang" class="form-control" onchange="filterUser()">
            <option value='' selected>Semua Jenjang</option>
        </select>
    </div>
    <div class="form-group col-6">
        <label for="tingkat">Tingkat</label>
        <select name="tingkat" id="tingkat" class="form-control" onchange="filterUser()">
            <option value='' selected>Semua Tingkat</option>
        </select>
    </div>
    <div class="form-group col-6">
        <label for="kelas">Kelas</label>
        <select name="kelas" id="kelas" class="form-control" onchange="filterUser()">
            <option value='' selected>Semua Kelas</option>
        </select>
    </div>
    <div class="form-group col-6">
        <label for="name">Nama</label>
        <select name="name" id="name" class="form-control" onchange="filterUser()">
            <option value='' selected>Semua Siswa/i</option>
        </select>
    </div>
</div>

<div class="px-3">
    <table class="table table-bordered my-4">
        <tbody>
            <tr><td class="w-50">Bank</td><td id="data-pemasukan"></td></tr>
            <tr><td class="w-50">Tunggakan</td><td id="data-tunggakan"></td></tr>
            <tr class="font-weight-bold"><td class="w-50">Pendapatan</td><td id="data-keseluruhan"></td></tr>
        </tbody>
    </table>
</div>