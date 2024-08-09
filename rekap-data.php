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

<h2 class="my-4">Data Rekap</h2>

<div class="d-flex flex-wrap">
    <div class="form-group col-12">
        <label for="tahun_ajaran">Search</label>
        <input type="text" class="form-control" placeholder="Search Name" id="search" name="search" value="">
    </div>
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
        <button class="btn btn-primary w-100" onclick="getData()">Filter</button>
    </div>
</div>

<div class="table-responsive" id="table">
    <table class="table table-bordered table-striped" id="table-rekap">
        <thead class="thead-dark">
            <tr>
                <th>VA</th>
                <th>Nama</th>
                <th>Jenjang</th>
                <th>No. Ortu</th>
                <th>Pembayaran</th>
                <th>Tunggakan</th>
            </tr>
        </thead>
        <tbody id="body-rekap">
            <!-- Data will be loaded here -->
        </tbody>
    </table>
</div>

<script>
        const getData = () => {
        var url = 'api/rekap-data.php';
        var search = document.getElementById('search').value;
        var tahunAjaran = document.getElementById('tahun_ajaran').value;
        var semester = document.getElementById('semester').value;

        var params = new URLSearchParams({
            search: search,
            tahun_ajaran: tahunAjaran,
            semester: semester
        });

        url += '?' + params.toString();

        var rows = '';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                $('#body-rekap').empty();
                
                data.data.forEach(trx => {
                    $('#body-rekap').append(`<?php include_once './tables/rekap-data.php' ?>`);
                })
            });
    }

    $(document).ready(() => {
        getData();
    });
</script>