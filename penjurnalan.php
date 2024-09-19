<?php
include './config/app.php';
// Check if user is logged in
IsLoggedIn();
RoleAllowed(7) ? null : returnError();

$query_tahun_ajaran = 'SELECT DISTINCT period FROM bills ORDER BY period';
$query_semester = 'SELECT DISTINCT semester FROM bills ORDER BY semester';

$tahun_ajaran_options = read($query_tahun_ajaran);
$semester_options = read($query_semester);

include './headers/admin.php';
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

                    <div class="d-flex my-4">
                        <div class="col-9">
                            <h2 class="">Penjurnalan</h2>
                        </div>
                        <div class="col-3">
                            <button onclick="downloadPDF()" class="btn btn-outline-primary w-100">Download</button>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="filter d-flex flex-wrap">
                        <div class="form-group col-4">
                            <label for="tahun_ajaran">Tahun Ajaran:</label>
                            <select name="tahun_ajaran" id="tahun_ajaran" class="form-control">
                                <!-- <option selected disabled>Pilih Tahun Ajaran</option> -->
                                <?php foreach ($tahun_ajaran_options as $option) { ?>
                                <option value="<?php echo $option['period']; ?>" <?php echo $tahun_ajaran == $option['period'] ? 'selected' : ''; ?>><?php echo $option['period']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group col-4">
                            <label for="semester">Semester:</label>
                            <select name="semester" id="semester" class="form-control">
                                <!-- <option selected disabled>Pilih Semester</option> -->
                                <?php foreach ($semester_options as $option) { ?>
                                <option value="<?php echo $option['semester']; ?>" <?php echo $semester == $option['semester'] ? 'selected' : ''; ?>><?php echo $option['semester']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group col-4">
                            <label for="month">Bulan:</label>
                            <select name="month" id="month" class="form-control" onchange="filterMonth()">

                            </select>
                        </div>
                        <div class="form-group col-4">
                            <label for="jenjang">Jenjang</label>
                            <select name="jenjang" id="jenjang" class="form-control" onchange="filterClass()">
                                <option value='' selected>Semua Jenjang</option>
                            </select>
                        </div>
                        <div class="form-group col-4">
                            <label for="tingkat">Tingkat</label>
                            <select name="tingkat" id="tingkat" class="form-control" onchange="filterClass()">
                                <option value='' selected>Semua Tingkat</option>
                            </select>
                        </div>
                        <div class="form-group col-4">
                            <label for="kelas">Kelas</label>
                            <select name="kelas" id="kelas" class="form-control" onchange="filterClass()">
                                <option value='' selected>Semua Kelas</option>
                            </select>
                        </div>
                        <div class="form-group col-12">
                            <label for="nis">Nama</label>
                            <select name="nis" id="nis" onchange="filterClass()">
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 h-2_5 main-content">

                <div class="px-3">
                    <table class="table table-bordered my-4">
                        <tbody>
                            <tr>
                                <td class="w-50">Bank</td>
                                <td id="data-pemasukan"></td>
                            </tr>
                            <tr class="d-none">
                                <td class="w-50">Denda</td>
                                <td></td>
                            </tr>
                            <tr class="font-weight-bold text-right">
                                <td class="w-50">Pendapatan</td>
                                <td id="data-keseluruhan"></td>
                            </tr>
                        </tbody>
                    </table>
                    <p>Total Denda : <span id="data-tunggakan" style="font-weight: bold;"></span></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const refreshData = () => {
            getData();
            filterMonth();
            filterClass();
            filterUser();
        }

        const downloadPDF = () => {
            var tahun_ajaran = document.getElementById('tahun_ajaran').value;
            var semester = document.getElementById('semester').value;
            var month = parseInt(document.getElementById('month').value, 10);
            var jenjang = document.getElementById('jenjang').value;
            var tingkat = document.getElementById('tingkat').value;
            var kelas = document.getElementById('kelas').value;
            var nis = document.getElementById('nis').value;

            var bank = document.getElementById('data-pemasukan').textContent.trim();
            var tunggakan = document.getElementById('data-tunggakan').textContent.trim();
            var pendapatan = document.getElementById('data-keseluruhan').textContent.trim();

            var monthNames = [
                "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"
            ];

            var monthName = monthNames[month - 1];
            $.ajax({
                url: './api/pdf/download-journal.php',
                method: 'POST',
                data: {
                    tahun_ajaran: tahun_ajaran,
                    semester: `${semester} ${monthName ?? ''}`,
                    kelas: `${jenjang} ${tingkat} ${kelas}`,
                    nis: nis,
                    bank: bank,
                    tunggakan: tunggakan,
                    total: pendapatan
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    // Convert the response into a Blob
                    var blob = new Blob([response], {
                        type: 'application/pdf'
                    });

                    // Create an object URL for the Blob
                    var url = window.URL.createObjectURL(blob);

                    // Create a link element and trigger the download
                    var link = document.createElement('a');
                    link.href = url;
                    link.download = 'journal.pdf';
                    document.body.appendChild(link);
                    link.click();

                    // Remove the link element after download
                    document.body.removeChild(link);

                    // Release the object URL after download
                    window.URL.revokeObjectURL(url);
                },

                error: function(xhr, status, error) {
                    console.error('Error generating PDF:', error);
                }
            });
        }



        const filterMonth = () => {
            getData();
            filterUser();
            var semester = document.getElementById('semester').value;
            var month = document.getElementById('month').value;

            var months = [
                [{
                        value: 1,
                        text: 'Januari'
                    },
                    {
                        value: 2,
                        text: 'Februari'
                    },
                    {
                        value: 3,
                        text: 'Maret'
                    },
                    {
                        value: 4,
                        text: 'April'
                    },
                    {
                        value: 5,
                        text: 'Mei'
                    },
                    {
                        value: 6,
                        text: 'Juni'
                    }
                ],
                [{
                        value: 7,
                        text: 'Juli'
                    },
                    {
                        value: 8,
                        text: 'Agustus'
                    },
                    {
                        value: 9,
                        text: 'September'
                    },
                    {
                        value: 10,
                        text: 'Oktober'
                    },
                    {
                        value: 11,
                        text: 'November'
                    }, {
                        value: 12,
                        text: 'Desember'
                    }
                ]
            ]

            $('#month').empty()
            $('#month').append("<option value='' selected>Semua Bulan</option>");

            var month_array = semester == 'Gasal' ? months[1] : months[0]

            month_array.forEach((m) => {
                $('#month').append(
                    `<option value="${m.value}" ${m.value == month? 'selected' : ''}>${m.text}</option>`
                );
            });
        }

        const filterClass = () => {
            getData();
            filterUser();

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


            console.log(url);
            $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    console.log(data);
                    $('#jenjang').empty();
                    $('#jenjang').append("<option value='' selected>Semua Jenjang</option>");

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
                    $('#tingkat').append("<option value='' selected>Semua Tingkat</option>");

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
                    $('#kelas').append("<option value='' selected>Semua Kelas</option>");

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

        const filterUser = () => {
            var tahun_ajaran = document.getElementById('tahun_ajaran').value;
            var semester = document.getElementById('semester').value;
            var bulan = document.getElementById('month').value;
            var jenjang = document.getElementById('jenjang').value;
            var tingkat = document.getElementById('tingkat').value;
            var kelas = document.getElementById('kelas').value;
            var nis = document.getElementById('nis').value;

            getData();

            var userParam = new URLSearchParams({
                period: tahun_ajaran,
                semester: semester,
                bulan: bulan,
                level: jenjang,
                class: tingkat,
                major: kelas,
            })
            userUrl = 'api/journal-filter.php';
            userUrl += '?' + userParam.toString();

            $.ajax({
                url: userUrl,
                method: 'GET',
                success: function(data) {
                    var $nis = $('#nis');
                    $nis.empty();
                    $nis.append("<option value='' selected>Semua Siswa/i</option>");

                    data.data.forEach((l) => {
                        if (l.name != null) {
                            $nis.append(
                                `<option value="${l.nis}" ${l.nis == nis ? 'selected' : ''}>${l.name}</option>`
                            );
                        }
                    });

                    if ($nis.hasClass('ts-control')) {
                        $nis[0].tomselect.clearOptions();
                        $nis[0].tomselect.load(function (callback) {
                            callback(data.data.map((l) => ({ value: l.nis, text: l.name })));
                        });
                    } else {
                        new TomSelect("#nis", {
                            create: false,
                            sortField: { field: "text", direction: "asc" },
                            maxOptions: 50, // Optional: adjust based on your needs
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown);

                }
            })
        }

        const getData = () => {
            var url = 'api/penjurnalan.php';
            var tahun_ajaran = document.getElementById('tahun_ajaran').value;
            var semester = document.getElementById('semester').value;
            var bulan = document.getElementById('month').value;
            var jenjang = document.getElementById('jenjang').value;
            var tingkat = document.getElementById('tingkat').value;
            var kelas = document.getElementById('kelas').value;
            var nis = document.getElementById('nis').value;

            var params = new URLSearchParams({
                period: tahun_ajaran,
                semester: semester,
                bulan: bulan,
                level: jenjang,
                class: tingkat,
                major: kelas,
                nis: nis,
            });

            url += '?' + params.toString();


            console.log(url);
            $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    console.log(data);
                    $('#data-pemasukan').html(formatToIDR(data.data.bank));
                    $('#data-tunggakan').html(formatToIDR(data.data.tunggakan));
                    $('#data-keseluruhan').html(formatToIDR(data.data.pendapatan));
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown);
                }
            })
        }

        $(document).ready(() => {
            getData();
            filterClass();
            filterMonth();
        })
    </script>
</body>
