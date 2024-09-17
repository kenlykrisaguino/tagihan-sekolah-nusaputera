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
                            onclick="notifyParents('day_before')">Nofity 2nd</a></li>
                    <li><a class="dropdown-item" id="m-notify" href="#"
                            onclick="notifyParents('day_after')">Nofity 3rd</a></li>
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
                    <h2 class="mt-4 mb-3">Data Penerimaan</h2>
                    <div class="d-flex flex-wrap">
                        <div class="form-group col-4">
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
                    </div>
                </div>
                <div class="col-12">
                    <div id="total-table"></div>
                </div>
            </div>
            <div class="row ml-2 w-100 h-2_5">

                <div class="d-flex my-1 justify-content-end">
                    <button id="toggle-button" type="button" class="btn btn-outline-secondary btn-sm"
                        onclick="hiddenToggle()">Hide
                        Data</button>
                </div>
                <div class="table-responsive" id="table">
                    <table class="table table-bordered table-striped custom-table" id="edit-table">

                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let isHidden = true;

        const hiddenToggle = () => {
            isHidden = !isHidden;

            const toggleButton = document.getElementById('toggle-button');
            if (isHidden) {
                toggleButton.textContent = 'Show Data';
            } else {
                toggleButton.textContent = 'Hide Data';
            }

            getData();
        }

        const refreshData = async () => {
            try {
                await getTahunAjaranOptions();
                await getSemesterOptions();
                getData();
            } catch (error) {
                console.error('Error refreshing data:', error);
            }
        }

        const getTahunAjaranOptions = () => {
            $.ajax({
                url: 'api/get-tahun-ajaran.php',
                type: 'GET',
                success: (data) => {
                    const tahunAjaranSelect = $('#tahun_ajaran');
                    tahunAjaranSelect.empty();
                    data.forEach(option => {
                        tahunAjaranSelect.append(new Option(option.period, option.period));
                    });
                }
            });
        }

        const getSemesterOptions = () => {
            $.ajax({
                url: 'api/get-semester.php',
                type: 'GET',
                success: (data) => {
                    const semesterSelect = $('#semester');
                    semesterSelect.empty();
                    data.forEach(option => {
                        semesterSelect.append(new Option(option.semester, option.semester));
                    });
                }
            });
        }

        const getData = () => {
            var url = 'api/edit-data.php';
            var search = document.getElementById('search').value;
            var tahunAjaran = document.getElementById('tahun_ajaran').value;
            var semester = document.getElementById('semester').value;

            var params = new URLSearchParams({
                search: search,
                tahun_ajaran: tahunAjaran,
                semester: semester,
                hidden: isHidden ? 1 : 0 // Pass the hidden state as a parameter
            });

            url += '?' + params.toString();

            var rows = '';

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    $('#edit-table').empty();
                    $('#total-table').empty();
                    if (data.data.users.length == 0) {
                        $('#edit-table').append(`<?php include './tables/edit-header-kosong.php'; ?>`);
                        $('#edit-table').append(
                            '<tr><td colspan="6" class="text-center">Tidak ada data yang ditemukan.</td></tr>');
                    } else {
                        users = data.data.users;
                        total = data.data.total;

                        $('#total-table').append(`<?php include './tables/penerimaan-tunggakan.php'; ?>`);

                        if (semester === 'Gasal') {
                            $('#edit-table').append(`<?php include './tables/edit-header-gasal.php'; ?>`);
                            users.forEach(trx => {
                                rows += `<?php include './tables/edit-gasal.php'; ?>`;
                            });
                            $('#edit-table').append(`<tbody>${rows}</tbody>`);
                        } else {
                            $('#edit-table').append(`<?php include './tables/edit-header-genap.php'; ?>`);
                            users.forEach(trx => {
                                rows += `<?php include './tables/edit-genap.php'; ?>`;
                            });
                            $('#edit-table').append(`<tbody>${rows}</tbody>`);
                        }
                    }
                });
        }

        <?php if($_SESSION['role'] == 'ADMIN'):?>
        const editTable = function() {
            var originalContent = $(this).text();
            var dataId = $(this).data('id');
            var column = $(this).data('column');

            var month = $(this).data('month');
            var payment = $(this).data('payment');

            if (payment === true) {
                originalContent = fromIDRtoNum(originalContent);
            }

            $(this).addClass('cellEditing');

            $(this).html('<input type="text" value="' + originalContent + '" />');

            $(this).children().first().focus();

            $(this).children().first().keypress(function(e) {
                if (e.which === 13) { // Enter key pressed
                    var newContent = $(this).val();
                    if (payment === true) {
                        newContent = formatToIDR(newContent);
                    }
                    $(this).parent().text(newContent);
                    $(this).parent().removeClass('cellEditing');

                    if (payment === true) {
                        $.ajax({
                            url: 'api/update-payment.php',
                            type: 'POST',
                            data: {
                                id: dataId,
                                column: column,
                                value: fromIDRtoNum(newContent),
                                month: month,
                                tahunAjaran: document.getElementById('tahun_ajaran').value,
                                semester: document.getElementById('semester').value,
                            },
                            success: (data) => {
                                if (!data.status) {
                                    $.toast({
                                        heading: 'Gagal',
                                        text: `Gagal mengupdate data pembayaran bulan ${month}`,
                                        showHideTransition: 'plain',
                                        icon: 'error'
                                    })
                                } else {
                                    $.toast({
                                        heading: 'Berhasil',
                                        text: `Berhasil mengupdate data pembayaran bulan ${month}`,
                                        showHideTransition: 'plain',
                                        icon: 'success'
                                    })
                                }
                                getData();
                            }
                        });
                    } else {
                        $.ajax({
                            url: 'api/update-data.php',
                            type: 'POST',
                            data: {
                                id: dataId,
                                column: column,
                                value: newContent
                            },
                            success: (data) => {
                                console.log(data);

                                if (!data.data) {
                                    $.toast({
                                        heading: 'Gagal',
                                        text: `Gagal mengupdate data`,
                                        showHideTransition: 'plain',
                                        icon: 'error'
                                    })
                                } else {
                                    $.toast({
                                        heading: 'Berhasil',
                                        text: `Berhasil mengupdate data`,
                                        showHideTransition: 'plain',
                                        icon: 'success'
                                    })
                                }
                                getData();
                            }
                        });
                    }
                }
            });

            $(this).children().first().blur(function() {
                if (payment === true) {
                    originalContent = formatToIDR(originalContent);
                }
                $(this).parent().text(originalContent);
                $(this).parent().removeClass('cellEditing');
            });
        }
        <?php endif ?>

        const statusColor = (status) => {
            if (status === 'paid') {
                return 'txt-green';
            } else if (status === 'late') {
                return 'txt-yellow';
            } else if (status === 'not paid') {
                return 'txt-red';
            } else if (status === 'waiting') {
                return '';
            } else if (status === 'disabled') {
                return 'txt-disabled';
            } else {
                return 'txt-grey';
            }
        }

        $(document).ready(() => {
            refreshData();
            $(document).on('dblclick', '.editable', editTable);
        });
    </script>
</body>
