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

<div class="d-flex flex-wrap">
    <div class="form-group col-12">
        <label for="tahun_ajaran">Search</label>
        <input type="text" class="form-control" placeholder="Search Name" id="search" name="search"
            value="">
    </div>
    <div class="form-group col-6">
        <label for="tahun_ajaran">Tahun Ajaran:</label>
        <select name="tahun_ajaran" id="tahun_ajaran" class="form-control">
            <!-- <option selected disabled>Pilih Tahun Ajaran</option> -->
            <?php foreach ($tahun_ajaran_options[0] as $option) { ?>
                <option value="<?php echo $option; ?>" <?php echo $tahun_ajaran == $option ? 'selected' : ''; ?>><?php echo $option; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="form-group col-6">
        <label for="semester">Semester:</label>
        <select name="semester" id="semester" class="form-control">
            <!-- <option selected disabled>Pilih Semester</option> -->
            <?php foreach ($semester_options[0] as $option) { ?>
            <option value="<?php echo $option; ?>" <?php echo $semester == $option ? 'selected' : ''; ?>><?php echo $option; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="form-group col-12">
        <button class="btn btn-primary w-100">Filter</button>
    </div>
</div>

<div class="table-responsive" id="table">
    <table class="table">
        <thead>

        </thead>
    </table>
</div>