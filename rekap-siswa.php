<?php
include './config/app.php';
// Check if user is logged in
IsLoggedIn();
RoleAllowed(7) ? null : returnError();
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
            <div class="row h-half">
                <div class="col-12">
                    <?php include './headers/nav-admin.php'?>
                </div>
                <div class="col-12">
                    <div class="d-flex my-4">
                        <div class="col-9">
                            <h2 class="">Data Siswa</h2>
                        </div>
                        <div class="col-3">
                            <button onclick="downloadStudent()" class="btn btn-outline-primary w-100">Download</button>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex flex-wrap my-4">
                        <div class="form-group col-12">
                            <label for="tahun_ajaran">Search</label>
                            <input type="search" class="form-control" oninput="getData()" placeholder="Search Name"
                                id="search" name="search" value="">
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label for="jenjang">Jenjang</label>
                            <select name="jenjang" id="jenjang" class="form-control" onchange="filterUser()">
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label for="tingkat">Tingkat</label>
                            <select name="tingkat" id="tingkat" class="form-control" onchange="filterUser()">
                                <option value='' selected>Semua Tingkat</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-4">
                            <label for="kelas">Kelas</label>
                            <select name="kelas" id="kelas" class="form-control" onchange="filterUser()">
                                <option value='' selected>Semua Kelas</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 h-half main-content">
                <div class="table-responsive" id="table">
                    <table class="table table-bordered table-striped custom-table" id="table-siswa">
                        <thead class="thead-dark">
                            <tr>
                                <?php echo $_SESSION['role'] == 'ADMIN' ? "<th>Action</th>" : "" ?>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>SPP</th>
                                <th>Jenjang</th>
                                <th>Tingkat</th>
                                <th>Kelas</th>
                                <th>No Telp</th>
                                <th>Email</th>
                                <th>No Ortu</th>
                                <th>Virtual Account</th>
                                <th>Pembayaran Terakhir</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="body-siswa"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let tableSiswa = new DataTable('#table-siswa', {
            paging: false,
            info: false,
            ordering: true,
            searching: false,
            serverSide: true,
            ajax: (data, callback) => {
                let dataOrder = data.order && data.order.length > 0 ? data.order[0].column : 0;
                let sortBy = data.columns[dataOrder]?.data ||
                    'nis';
                let sortDir = data.order && data.order.length > 0 ? data.order[0].dir : "asc";


                var url = 'api/get-students.php';
                var search = document.getElementById('search').value;
                var jenjang = document.getElementById('jenjang').value;
                var tingkat = document.getElementById('tingkat').value;
                var kelas = document.getElementById('kelas').value;

                var params = new URLSearchParams({
                    search: search,
                    jenjang: jenjang,
                    tingkat: tingkat,
                    kelas: kelas,
                    sortBy: sortBy,
                    sortDir: sortDir,
                });
                url += '?' + params.toString();

                fetch(url)
                    .then((response) => response.json())
                    .then((data) => {
                        console.log(data);
                        if (data.status) {
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
                    data: 'nis', // Assuming the student NIS is in the 'nis' column
                    render: function(data, type, row) {
                        return `
                    <td data-id="${data}" style="cursor: pointer;" onclick="deleteStudent(this)">
                        <svg data-id="${data}" style="cursor: pointer;" onclick="deleteStudent(this)" xmlns="http://www.w3.org/2000/svg" height="20" width="24" viewBox="0 0 640 512">
                            <path fill="#991111" d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304l91.4 0C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7L29.7 512C13.3 512 0 498.7 0 482.3zM472 200l144 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-144 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z"/>
                        </svg>
                    </td>
                `;
                    },
                    orderable: false
                },
                {
                    data: 'nis',
                    orderable: true
                },
                {
                    data: 'name',
                    orderable: true
                },
                {
                    data: 'monthly_bills',
                    orderable: true
                },
                {
                    data: 'level',
                    orderable: true
                },
                {
                    data: 'class',
                    orderable: true
                },
                {
                    data: 'major',
                    orderable: true
                },
                {
                    data: 'phone_number',
                    orderable: false
                },
                {
                    data: 'email_address',
                    orderable: false
                },
                {
                    data: 'parent_phone',
                    orderable: false
                },
                {
                    data: 'virtual_account',
                    orderable: false
                },
                {
                    data: 'latest_payment',
                    orderable: true
                },
                {
                    data: 'status',
                    orderable: false
                }
            ]
        })

        const downloadStudent = () => {
            window.location.href = 'api/download-students.php';
        }
        const refreshData = () => {
            getData();
            filterUser();
        }
        const showAddUser = () => {
            $('#tambahSiswa').modal('show');
        };

        const filterUser = () => {
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

        const getData = () => {
            var url = 'api/get-students.php';
            var search = document.getElementById('search').value;
            var jenjang = document.getElementById('jenjang').value;
            var tingkat = document.getElementById('tingkat').value;
            var kelas = document.getElementById('kelas').value;

            var params = new URLSearchParams({
                search: search,
                jenjang: jenjang,
                tingkat: tingkat,
                kelas: kelas
            });

            url += '?' + params.toString();

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    $('#body-siswa').empty();
                    console.log(url);
                    console.log(data);

                    if (data.data.length > 0) {
                        data.data.forEach(u => {
                            $('#body-siswa').append(`<?php include_once './tables/data-user.php'; ?>`);
                        });
                    } else {
                        $('#body-siswa').append(
                            `<tr><td colspan="10" class="text-center">Tidak ada data yang ditemukan.</td></tr>`);
                    }
                });
        }

        const showToast = (status, message) => {
            const icon = status == true ? 'success' : 'error'
            const heading = status == true ? 'Berhasil' : 'Gagal'
            $.toast({
                heading: heading,
                text: message,
                showHideTransition: 'plain',
                icon: icon
            })
        }

        const deleteStudent = (element) => {
            var id = $(element).data('id');
            console.log(id);
            $.ajax({
                url: 'api/delete-student.php',
                method: 'POST',
                data: {
                    id: id
                },
                success: function(response) {
                    console.log(response);
                    if (response.status) {
                        showToast(true, 'Data siswa berhasil dihapus.');
                        refreshData();
                    } else {
                        showToast(false, 'Gagal menghapus data siswa.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR);
                    console.log(textStatus);
                    console.log(errorThrown);
                }
            });
        }

        $(document).ready(() => {
            getData();
            filterUser();

            <?php
            
            if (isset($_SESSION['success'])) {
                echo 'showToast(true, "' . $_SESSION['success'] . '")';
                unset($_SESSION['success']);
            }
            
            if (isset($_SESSION['error'])) {
                echo 'showToast(false, "' . $_SESSION['error'] . '")';
                unset($_SESSION['error']);
            }
            ?>
        });
    </script>
</body>
