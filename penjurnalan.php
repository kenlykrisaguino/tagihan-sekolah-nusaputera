<?php
include './config/app.php';
// Check if user is logged in
IsLoggedIn();
RoleAllowed(7) ? null : returnError();

$query_tahun_ajaran = "SELECT DISTINCT period FROM bills ORDER BY period";
$query_semester = "SELECT DISTINCT semester FROM bills ORDER BY semester";

$tahun_ajaran_options = read($query_tahun_ajaran);
$semester_options = read($query_semester);

include './headers/admin.php';
?>

<h2 class="my-4">
    Penjurnalan
</h2>

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
        <select name="jenjang" id="jenjang" class="form-control" onchange="filterUser()">
            <option value='' selected>Semua Jenjang</option>
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
    <div class="form-group col-12">
        <label for="nis">Nama</label>
        <select name="nis" id="nis" class="form-control" onchange="filterUser()">
            <option value='' selected>Semua Siswa/i</option>
        </select>
    </div>
</div>

<div class="px-3">
    <table class="table table-bordered my-4">
        <tbody>
            <tr><td class="w-50">Bank</td><td id="data-pemasukan"></td></tr>
            <tr><td class="w-50">Tunggakan</td><td id="data-tunggakan"></td></tr>
            <tr class="font-weight-bold text-right"><td class="w-50">Pendapatan</td><td id="data-keseluruhan"></td></tr>
        </tbody>
    </table>
</div>

<script>
    const refreshData = () => {
        getData();
        filterMonth();
        filterUser();
    }

    const filterMonth = () => {
        getData();
        var semester = document.getElementById('semester').value;
        var month = document.getElementById('month').value;

        var months = [
            [
                {
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
            [
                {
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
                },{
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

    const filterUser = () => {
        getData();

        var url = 'api/classes-filter.php';
        var jenjang = document.getElementById('jenjang').value;
        var tingkat = document.getElementById('tingkat').value;
        var kelas = document.getElementById('kelas').value;
        var nis = document.getElementById('nis').value;

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

                $('#nis').empty();
                $('#nis').append("<option value='' selected>Semua Siswa/i</option>");

                if (data.data.students != null) {
                    data.data.students.forEach((l) => {
                        if (l.name!= null) {
                            $('#nis').append(
                                `<option value="${l.nis}" ${l.nis == nis? 'selected' : ''}>${l.name}</option>`
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
        filterUser();
        filterMonth();
    })
</script>