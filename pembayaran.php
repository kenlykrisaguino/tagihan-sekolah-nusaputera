<?php
include './config/app.php';
// Check if user is logged in
IsLoggedIn();
RoleAllowed() ? null : returnError();
include './headers/siswa.php';

$username = $_SESSION['username'];

$query_tahun_ajaran = "SELECT DISTINCT period FROM bills WHERE virtual_account = '$username' ORDER BY period";
$query_semester = "SELECT DISTINCT semester FROM bills WHERE virtual_account = '$username' ORDER BY semester";

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
        <button class="btn btn-primary w-100" id="filter-btn">Filter</button>
    </div>
</div>

<table class="table table-bordered my-4">
    <tbody id="data-siswa">

    </tbody>
</table>

<div class="table-responsive">
    <table class="table table-bordered my-4">
        <thead class="thead-dark text-center">
            <tr>
                <th>Bulan</th>
                <th>Tagihan</th>
                <th>Tunggakan</th>
                <th>Pembayaran</th>
                <th>Status</th>
                <th>Tanggal Pembayaran</th>
            </tr>
        </thead>
        <tbody id="trx-body">
        </tbody>
    </table>
</div>

<script>
    const getData = () => {
        var url = 'api/students/student-payment.php';
        var tahunAjaran = document.getElementById('tahun_ajaran').value;
        var semester = document.getElementById('semester').value;

        var params = new URLSearchParams({
            user: "<?php echo $username ?>",
            tahun_ajaran: tahunAjaran,
            semester: semester
        });

        url += '?' + params.toString();

        var rows = '';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log(data);
                siswa = data.data.user;
                payment = data.data.trx;
                $('#data-siswa').empty();
                $('#data-siswa').append(`<?php include_once './tables/beranda-siswa.php'?>`);
                
                $('#trx-body').empty();
                if(payment.length > 0) {
                    payment.forEach(trx => {
                        $('#trx-body').append(`<?php include_once './tables/pembayaran-siswa.php'?>`);
                    })
                } else {
                    $('#trx-body').append(
                        `<tr><td colspan="6" class="text-center">Tidak ada data pembayaran</td></tr>`
                    );
                }

            
            });
    }

    $(document).ready(() => {
        getData();
        $(document).on('click', '#filter-btn', getData);
    });
</script>