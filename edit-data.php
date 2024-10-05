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

$username = $_SESSION['username'];

?>

<body>
    <style>
        td.editable[data-column="virtual_account"],
        td.editable[data-column="student_name"],
        td[data-column="level"],
        th[data-column="virtual_account"],
        th[data-column="student_name"],
        th[data-column="level"]
        {
            position: -webkit-sticky;
            position: sticky;
            background-color: #fff;
            left: 0;
            z-index: 2;
        }

        td.editable[data-column="student_name"],
        th[data-column="student_name"]{
            left: 162px;
            z-index: 1;
        }

        td[data-column="level"],
        th[data-column="level"]{
            left: 252px;
            z-index: 1;
        }
    </style>
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
                    <?php include './headers/nav-admin.php'?>
                </div>
                <div class="col-12">
                    <div class="d-flex flex-wrap my-4">
                        <div class="col-12 col-md-7 text-md-left text-center">
                            <h2 class="">Edit Data</h2>
                        </div>
                        <div class="col-12 col-md-5 d-flex" style="justify-content: center;">
                            <div class="btn-group w-full" role="group" aria-label="Second group">
                                <button onclick="downloadUnpaidPDF()"  type="button" class="btn btn-secondary">Download PDF</button>
                                <button onclick="downloadUnpaidCSV()"  type="button" class="btn btn-secondary">Download CSV</button>
                            </div>
                        </div>
                    </div>

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

        const downloadUnpaidPDF = () => {
            $.ajax({
                url: './api/pdf/download-unpaid-bills.php',
                method: 'POST',
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    var blob = new Blob([response], {
                        type: 'application/pdf'
                    });

                    var url = window.URL.createObjectURL(blob);

                    var link = document.createElement('a');
                    link.href = url;
                    link.download = 'journal.pdf';
                    document.body.appendChild(link);
                    link.click();

                    document.body.removeChild(link);

                    window.URL.revokeObjectURL(url);
                },

                error: function(xhr, status, error) {
                    console.error('Error generating PDF:', error);
                }
            });
        }

        const downloadUnpaidCSV = () => {
            window.location.href = 'api/download-unpaid-csv.php';
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

        const editTable = function() {
            var originalContent = $(this).text();
            var dataId = $(this).data('id');
            var column = $(this).data('column');
            var updated_by = "<?php echo $username ?>";

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
                        console.log("data");

                        $.ajax({
                            url: 'api/update-payment.php',
                            type: 'POST',
                            data: {
                                updated_by: updated_by,
                                id: dataId,
                                column: column,
                                value: fromIDRtoNum(newContent),
                                month: month,
                                tahunAjaran: document.getElementById('tahun_ajaran').value,
                                semester: document.getElementById('semester').value,
                            },
                            success: (data) => {
                                console.log(data);
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
                            }, 
                            error: (xhr, status, error) => {
                                console.error('Error updating payment:', error);
                                console.error(xhr)
                            }
                        });
                    } else {
                        $.ajax({
                            url: 'api/update-data.php',
                            type: 'POST',
                            data: {
                                updated_by: updated_by,
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
