<?php
require_once './config/app.php';
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
        <div class="position-fixed" style="bottom: 20px; right: 20px; z-index:5000;">
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
            <div class="row h-half">
                <?php include './headers/nav-admin.php'?>
                <div class="col-12">
                    <div class="d-flex my-4">
                        <div class="col-9">
                            <h2 class="">Input Data</h2>
                        </div>
                        <div class="col-3">
                            <button id="uploadBtn" class="btn btn-outline-primary w-100">Tambahkan</button>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex mb-4">
                        <div class="form-group col-6">
                            <label for="bulan" class="d-block">Filter Bulan</label>
                            <select class="form-control" id="filter-bulan" name="filter-bulan" onchange="getData()">
                                <option value="" selected>Pilih Bulan</option>
                                <?php
                                foreach ($months as $bulan => $nama_bulan) {
                                    echo "<option value='$bulan'>$nama_bulan</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-6">
                            <label for="tahun_ajaran">Tahun Ajaran:</label>
                            <select name="tahun_ajaran" id="tahun_ajaran" class="form-control" onchange="getData()">
                                <!-- <option selected disabled>Pilih Tahun Ajaran</option> -->
                                <?php foreach ($tahun_ajaran_options as $option) { ?>
                                <option value="<?php echo $option['period']; ?>" <?php echo $tahun_ajaran == $option['period'] ? 'selected' : ''; ?>><?php echo $option['period']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 h-half main-content">

                <div class="table-responsive" style="max-height: 50vh;">
                    <table class="table table-bordered table-striped" id="payment-table">
                        <thead class="thead-dark">
                            <tr>
                                <th>Virtual Account</th>
                                <th>Nama Pelanggan</th>
                                <th>Jumlah Pembayaran</th>
                                <th>Tanggal Pembayaran</th>
                            </tr>
                        </thead>
                        <tbody id="input-data-table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let paymentTable = new DataTable('#payment-table', {
            paging: false,
            info: false,
            ordering: true,
            searching: false,
            serverSide: true,
            ajax: (data, callback) => {
                let dataOrder = data.order[0].column ?? 3;

                let sortBy = data.columns[dataOrder].data;
                let sortDir = data.order[0].dir ?? "asc";

                var month = $('#filter-bulan').find(':selected').val();
                var period = $('#tahun_ajaran').find(':selected').val();
                if (month == "") {
                    var url = `api/input-data.php?sort_by=${sortBy}&sort_direction=${sortDir}`
                } else {
                    var url =
                        `api/input-data.php?month=${month}&period=${period}&sort_by=${sortBy}&sort_direction=${sortDir}`
                }

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            data.data.forEach(trx => {
                                trx.trx_amount = formatToIDR(trx.trx_amount);
                            });
                            callback({
                                data: data.data
                            });
                        } else {
                            callback({
                                data: []
                            });
                        }
                    });
            },
            columns: [{
                    data: 'virtual_account'
                },
                {
                    data: 'user'
                },
                {
                    data: 'trx_amount'
                },
                {
                    data: 'trx_timestamp'
                }
            ]
        });

        const refreshData = () => {
            getData();
        }

        const getData = () => {
            var month = $('#filter-bulan').find(':selected').val();
            var period = $('#tahun_ajaran').find(':selected').val();
            var sort_by = paymentTable.order()[0][0]; // Get sorting column index
            var sort_direction = paymentTable.order()[0][1]; // Get sorting direction

            if (sort_by == 0) sort_by = 'virtual_account';
            else if (sort_by == 1) sort_by = 'user';
            else if (sort_by == 2) sort_by = 'trx_amount';
            else if (sort_by == 3) sort_by = 'trx_timestamp';

            if (month == "") {
                var url = `api/input-data.php?sort_by=${sort_by}&sort_direction=${sort_direction}`
            } else {
                var url =
                    `api/input-data.php?month=${month}&period=${period}&sort_by=${sort_by}&sort_direction=${sort_direction}`
            }
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    $('#input-data-table').empty();
                    data.data.forEach(trx => {
                        $('#input-data-table').append(`<?php include_once './tables/input-data.php'; ?>`);
                    })

                    if (data.data.length == 0) {
                        $('#input-data-table').append(
                            `<tr><td colspan="4" class="text-center">Tidak ada data yang ditemukan.</td></tr>`);
                    }
                });
        }

        const downloadCSV = () => {
            $.ajax({
                url: 'api/template-payment.php',
                method: 'POST',
                xhrFields: {
                    responseType: 'blob' // Important to receive a binary file
                },
                success: function(response) {
                    var link = document.createElement('a');
                    link.href = URL.createObjectURL(response);
                    link.download = 'template_payment.csv';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },
                error: function(xhr, status, error) {
                    console.error(xhr);
                    console.error(status);
                    console.error(error);
                }
            })
        }

        const uploadCSV = (e) => {
            showLoader(true);
            e.preventDefault();

            var formData = new FormData();
            var fileInput = $('#file')[0].files[0];
            formData.append('input', fileInput);

            $.ajax({
                url: 'api/input-payment.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                    if (response.status) {
                        $.toast({
                            heading: 'Berhasil',
                            text: 'Berhasil menambahkan data',
                            showHideTransition: 'plain',
                            icon: 'success'
                        })
                        showLoader(false);
                        $('#uploadModal').modal('hide');
                    } else {
                        $.toast({
                            heading: 'Gagal',
                            text: response.message,
                            showHideTransition: 'plain',
                            icon: 'error'
                        })
                        showLoader(false);
                        console.error(response.message);
                    }
                    getData();
                },
                error: function(xhr, status, error) {
                    console.error(xhr);
                    console.error(status);
                    console.error(error);
                    $.toast({
                        heading: 'Gagal',
                        text: 'Gagal menambahkan data',
                        showHideTransition: 'plain',
                        icon: 'error'
                    })
                    showLoader(false);
                }
            });
        }

        $(document).ready(() => {
            getData();
            $('#csvUploadForm').on('submit', function(e) {
                uploadCSV(e);
            });
            $('#uploadBtn').on('click', function() {
                $('#uploadModal').modal('show');
            });
        });
    </script>

    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Input Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="csvUploadForm" class="col-12 col-md-8">
                        <label for="file" class="d-block">Upload CSV</label>
                        <input type="file" name="file" id="file" accept=".csv" class="mb-2">
                        <div class="d-flex my-2">
                            <input type="submit" value="Upload" class="mr-2 btn btn-primary">
                            <input onclick="downloadCSV()" type="button" value="Download Template"
                                class="ml-2 btn btn-outline-primary">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
