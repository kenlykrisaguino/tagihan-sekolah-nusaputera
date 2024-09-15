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

<body>
    <div class="container">
        <div class="position-fixed d-none" id="loader">
            <span class="loader"></span>
        </div>
        <div class="position-fixed" style="bottom: 20px; right: 20px; z-index:1000;">
            <div class="btn-group dropup">
                <button type="button" class="btn btn-outline-primary btn-floating rounded-circle" data-mdb-ripple-init
                    data-mdb-ripple-color="dark" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php include_once __DIR__ . '\icons\gear.svg'; ?>
                </button>
                <ul class="dropdown-menu mb-2">
                    <li><a class="dropdown-item" id="m-check-bills" onclick="checkBills()" href="#">Check
                            Bills</a></li>
                    <li><a class="dropdown-item" id="m-create-bills" onclick="createBills()" href="#">Create
                            Bills</a></li>
                    <li><a class="dropdown-item" id="m-notify" href="#" onclick="createCharge()">Create
                            Charge</a></li>
                    <li><a class="dropdown-item" id="m-notify" href="#"
                            onclick="notifyParents('first_day')">Nofity 1st</a></li>
                    <li><a class="dropdown-item" id="m-notify" href="#"
                            onclick="notifyParents('week_before')">Nofity 2nd</a></li>
                    <li><a class="dropdown-item" id="m-notify" href="#"
                            onclick="notifyParents('day_before')">Nofity 3rd</a></li>
                </ul>
            </div>
        </div>
        <div class="row h-screen">
            <div class="row h-3_5">
                <div class="col-12">
                    <!-- Header -->
                    <div class="text-center my-4">
                        <img src="assets/img/logo.png" alt="Logo" style="width: 50px; height: 50px;">
                        <h1>Sistem Pembayaran</h1>
                    </div>

                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs justify-content-center" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'rekap-siswa.php' ? 'active' : ''; ?>" href="rekap-siswa.php">Siswa</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'input-siswa.php' ? 'active' : ''; ?>" href="input-siswa.php">Input Siswa</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'input-data.php' ? 'active' : ''; ?>" href="input-data.php">Input Data</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'edit-data.php' ? 'active' : ''; ?>" href="edit-data.php">Edit Data</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'rekap-data.php' ? 'active' : ''; ?>" href="rekap-data.php">Rekap</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'penjurnalan.php' ? 'active' : ''; ?>" href="penjurnalan.php">Jurnal</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="Logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
                <div class="col-12">
                    <h2 class="my-4">Data Rekap</h2>
                </div>
                <div class="col-12">
                    <div class="d-flex flex-wrap">
                        <div class="form-group col-12">
                            <label for="tahun_ajaran">Search</label>
                            <input type="search" class="form-control" oninput="getData()" placeholder="Search Name"
                                id="search" name="search" value="">
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
                </div>
            </div>
            <div class="col-12 h-2_5 main-content">

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
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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
            columns: [{
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
</body>
