<?php
include './config/app.php';
// Check if user is logged in
IsLoggedIn();
RoleAllowed(7) ? null : returnError();
include './headers/admin.php';

$query_tahun_ajaran = 'SELECT DISTINCT period FROM bills ORDER BY period';
$query_semester = 'SELECT DISTINCT semester FROM bills ORDER BY semester';

$tahun_ajaran_options = read($query_tahun_ajaran);
$semester_options = read($query_semester);

?>

<h2 class="my-4">Data Rekap</h2>

<div class="d-flex flex-wrap">
    <div class="form-group col-12">
        <label for="tahun_ajaran">Search</label>
        <input type="search" class="form-control" oninput="getData()" placeholder="Search Name" id="search" name="search"
            value="">
    </div>
    <div class="form-group col-4">
        <label for="tahun_ajaran">Tahun Ajaran:</label>
        <select name="tahun_ajaran" id="tahun_ajaran" class="form-control" onchange="getData()">
            <!-- <option selected disabled>Pilih Tahun Ajaran</option> -->
            <?php foreach ($tahun_ajaran_options as $option) { ?>
            <option value="<?php echo $option['period']; ?>" <?php echo $tahun_ajaran == $option['period'] ? 'selected' : ''; ?>><?php echo $option['period']; ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="form-group col-4">
        <label for="semester">Semester:</label>
        <select name="semester" id="semester" class="form-control" onchange="getData()">
            <!-- <option selected disabled>Pilih Semester</option> -->
            <?php foreach ($semester_options as $option) { ?>
            <option value="<?php echo $option['semester']; ?>" <?php echo $semester == $option['semester'] ? 'selected' : ''; ?>><?php echo $option['semester']; ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="form-group col-4">
        <label for="semester">Bulan:</label>
        <select class="form-control" id="filter-bulan" name="filter-bulan" onchange="getData()">
            <option value="">Semua Bulan</option>
            <?php
            foreach ($months as $bulan => $nama_bulan) {
                echo "<option value='$bulan'>$nama_bulan</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group col-12 col-md-4">
        <label for="jenjang">Jenjang</label>
        <select name="jenjang" id="jenjang" class="form-control" onchange="filterLevel()">
            <option value='' selected>Semua Jenjang</option>
        </select>
    </div>
    <div class="form-group col-12 col-md-4">
        <label for="tingkat">Tingkat</label>
        <select name="tingkat" id="tingkat" class="form-control" onchange="filterLevel()">
            <option value='' selected>Semua Tingkat</option>
        </select>
    </div>
    <div class="form-group col-12 col-md-4">
        <label for="kelas">Kelas</label>
        <select name="kelas" id="kelas" class="form-control" onchange="filterLevel()">
            <option value='' selected>Semua Kelas</option>
        </select>
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
                <th>Denda</th>
            </tr>
        </thead>
        <tbody id="body-rekap">
            <!-- Data will be loaded here -->
        </tbody>
    </table>
</div>

<script>
    let recapTable = new DataTable('#table-rekap', {
        paging: false,
        info: false,
        ordering: true,
        searching: false,
        serverSide: true,
        ajax: (data, callback) => {
            let dataOrder = data.order && data.order.length > 0 ? data.order[0].column : 0;
            let sortBy = data.columns[dataOrder]?.data ||
            'virtual_account';
            let sortDir = data.order && data.order.length > 0 ? data.order[0].dir : "asc";

            var url = 'api/rekap-data.php';
            var search = document.getElementById('search').value;
            var tahunAjaran = document.getElementById('tahun_ajaran').value;
            var semester = document.getElementById('semester').value;
            var month = document.getElementById('filter-bulan').value;
            var jenjang = document.getElementById('jenjang').value;
            var tingkat = document.getElementById('tingkat').value;
            var kelas = document.getElementById('kelas').value;

            var params = new URLSearchParams({
                search: search,
                tahun_ajaran: tahunAjaran,
                semester: semester,
                month: month,
                level: jenjang,
                class: tingkat,
                major: kelas,
                sortBy: sortBy,
                sortDir: sortDir,
            });

            url += '?' + params.toString();

            console.log(url);

            fetch(url)
                .then((response) => response.json())
                .then((data) => {
                    if (data.status) {
                        data.data.forEach(trx => {
                            trx.penerimaan = formatToIDR(trx.penerimaan);
                            trx.tunggakan = formatToIDR(trx.tunggakan);
                        });
                        callback({
                            data: data.data
                        });
                    } else {
                        callback({
                            data: []
                        })
                    }
                });
        },
        columns: [
            {
                data: 'virtual_account',
            },
            {
                data: 'student_name'
            },
            {
                data: 'class'
            },
            {
                data: 'parent_phone'
            },
            {
                data: 'penerimaan'
            },
            {
                data: 'tunggakan'
            },
        ]
    });

    const refreshData = () => {
        getData();
        filterLevel();
    }

    const filterLevel = () => {
        getData();
        var url = 'api/classes-filter.php';
        var jenjang = document.getElementById('jenjang').value;
        var tingkat = document.getElementById('tingkat').value;
        var kelas = document.getElementById('kelas').value;

        var params = new URLSearchParams({
            level: jenjang,
            class: tingkat,
            major: kelas
        });
        url += '?' + params.toString();


        $.ajax({
            url: url,
            method: 'GET',
            success: function(data) {
                $('#jenjang').empty();
                $('#jenjang').append("<option selected value='' selected>Pilih Jenjang</option>");

                if (data.data.levels != null) {
                    data.data.levels.forEach((l) => {
                        if (l.level != null) {
                            $('#jenjang').append(
                                `<option value="${l.level}" ${l.level == jenjang ? 'selected' : ''}>${l.level}</option>`
                            );
                        }
                    });
                }

                $('#tingkat').empty();
                $('#tingkat').append("<option selected value='' selected>Pilih Tingkat</option>");

                if (data.data.classes != null) {
                    data.data.classes.forEach((l) => {
                        if (l.name != null) {
                            $('#tingkat').append(
                                `<option value="${l.name}" ${l.name == tingkat ? 'selected' : ''}>${l.name}</option>`
                            )
                        }
                    });
                }

                $('#kelas').empty();
                $('#kelas').append("<option selected value='' selected>Pilih Kelas</option>");

                if (data.data.majors != null) {
                    data.data.majors.forEach((l) => {
                        if (l.major != null) {
                            $('#kelas').append(
                                `<option value="${l.major}" ${l.major == kelas? 'selected' : ''}>${l.major}</option>`
                            );
                        }
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(errorThrown);
            }
        })
    }

    const getData = () => {
        var url = 'api/rekap-data.php';
        var search = document.getElementById('search').value;
        var tahunAjaran = document.getElementById('tahun_ajaran').value;
        var semester = document.getElementById('semester').value;
        var month = document.getElementById('filter-bulan').value;
        var jenjang = document.getElementById('jenjang').value;
        var tingkat = document.getElementById('tingkat').value;
        var kelas = document.getElementById('kelas').value;

        var params = new URLSearchParams({
            search: search,
            tahun_ajaran: tahunAjaran,
            semester: semester,
            month: month,
            level: jenjang,
            class: tingkat,
            major: kelas
        });

        url += '?' + params.toString();

        var rows = '';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                $('#body-rekap').empty();

                if (data.data.length > 0) {
                    data.data.forEach(trx => {
                        $('#body-rekap').append(`<?php include_once './tables/rekap-data.php'; ?>`);
                    })
                } else {
                    $('#body-rekap').append(
                        `<tr><td colspan="6" class="text-center">Tidak ada data yang ditemukan.</td></tr>`);
                }
            });
    }

    $(document).ready(() => {
        getData();
        filterLevel();
    });
</script>
