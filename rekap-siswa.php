<?php
include './config/app.php';
// Check if user is logged in
IsLoggedIn();
RoleAllowed(7) ? null : returnError();
include './headers/admin.php';

?>

<div class="d-flex my-4">
    <div class="col-9">
        <h2 class="">Data Siswa</h2>
    </div>
</div>
<div class="d-flex flex-wrap my-4">
    <div class="form-group col-12">
        <label for="tahun_ajaran">Search</label>
        <input type="search" class="form-control" oninput="getData()" placeholder="Search Name" id="search"
            name="search" value="">
    </div>
    <div class="form-group col-4">
        <label for="jenjang">Jenjang</label>
        <select name="jenjang" id="jenjang" class="form-control" onchange="filterUser()">
        </select>
    </div>
    <div class="form-group col-4">
        <label for="tingkat">Tingkat</label>
        <select name="tingkat" id="tingkat" class="form-control" onchange="filterUser()">
            <option value='' selected>Semua Tingkat</option>
        </select>
    </div>
    <div class="form-group col-4">
        <label for="kelas">Kelas</label>
        <select name="kelas" id="kelas" class="form-control" onchange="filterUser()">
            <option value='' selected>Semua Kelas</option>
        </select>
    </div>
</div>

<div class="table-responsive" id="table">
    <table class="table table-bordered table-striped" id="table-siswa">
        <thead class="thead-dark">
            <tr>
                <th>NIS</th>
                <th>Nama</th>
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

<script>
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

    $(document).ready(() => {
        getData();
        filterUser();

        <?php

            if(isset($_SESSION['success'])){
                echo 'showToast(true, "'.$_SESSION['success'].'")';
                unset($_SESSION['success']);
            }
            
            if(isset($_SESSION['error'])){
                echo 'showToast(false, "'.$_SESSION['error'].'")';
                unset($_SESSION['error']);
            }
        ?>
    });
</script>


