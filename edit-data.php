<?php
include './config/app.php';
include_once './config/session.php';
include './headers/admin.php';
// Check if user is logged in
IsLoggedIn();

$query_tahun_ajaran = 'SELECT DISTINCT period FROM bills ORDER BY period';
$query_semester = 'SELECT DISTINCT semester FROM bills ORDER BY semester';

$tahun_ajaran_options = read($query_tahun_ajaran);
$semester_options = read($query_semester);

?>
<h2 class="my-4">Data Penerimaan</h2>
<div class="d-flex flex-wrap">
    <div class="form-group col-12">
        <label for="tahun_ajaran">Search</label>
        <input type="text" class="form-control" oninput="getData()" placeholder="Search Name" id="search" name="search" value="">
    </div>
    <div class="form-group col-6">
        <label for="tahun_ajaran">Tahun Ajaran:</label>
        <select name="tahun_ajaran" id="tahun_ajaran" class="form-control">
            <!-- <option selected disabled>Pilih Tahun Ajaran</option> -->
            <?php foreach ($tahun_ajaran_options as $option) { ?>
            <option value="<?php echo $option['period']; ?>" <?php echo $tahun_ajaran == $option['period'] ? 'selected' : ''; ?>><?php echo $option['period']; ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="form-group col-6">
        <label for="semester">Semester:</label>
        <select name="semester" id="semester" class="form-control">
            <!-- <option selected disabled>Pilih Semester</option> -->
            <?php foreach ($semester_options as $option) { ?>
            <option value="<?php echo $option['semester']; ?>" <?php echo $semester == $option['semester'] ? 'selected' : ''; ?>><?php echo $option['semester']; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="form-group col-12">
        <button class="btn btn-primary w-100" onclick="getData()">Filter</button>
    </div>
</div>

<div class="table-responsive" id="table">
    <table class="table table-bordered table-striped" id="edit-table">

    </table>
</div>

<script>
    const getData = () => {
        var url = 'api/edit-data.php';
        var search = document.getElementById('search').value;
        var tahunAjaran = document.getElementById('tahun_ajaran').value;
        var semester = document.getElementById('semester').value;

        var params = new URLSearchParams({
            search: search,
            tahun_ajaran: tahunAjaran,
            semester: semester
        });

        url += '?' + params.toString();

        var rows = '';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log(data);
                $('#edit-table').empty();
                if (data.data.length === 0) {
                    $('#edit-table').append(`<?php include './tables/edit-header-kosong.php'; ?>`);
                    $('#edit-table').append(
                        '<tr><td colspan="6" class="text-center">Tidak ada data yang ditemukan.</td></tr>');
                } else {
                    if (semester === 'Gasal') {
                        $('#edit-table').append(`<?php include './tables/edit-header-gasal.php'; ?>`);
                        data.data.forEach(trx => {
                            rows += `<?php include './tables/edit-gasal.php'; ?>`;
                        });
                        $('#edit-table').append(`<tbody>${rows}</tbody>`);
                    } else {
                        $('#edit-table').append(`<?php include './tables/edit-header-genap.php'; ?>`);
                        data.data.forEach(trx => {
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

        var month = $(this).data('month');
        var payment = $(this).data('payment');

        $(this).addClass('cellEditing');
        $(this).html('<input type="text" value="' + originalContent + '" />');
        $(this).children().first().focus();

        $(this).children().first().keypress(function(e) {
            if (e.which === 13) { // Enter key pressed
                var newContent = $(this).val();
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
                        success: (response) => {
                            console.log(response);
                            if (response !== 'success') {
                                alert('Update failed');
                            }
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
                                alert('Update failed');
                            }
                        }
                    });
                }
            }
        });

        $(this).children().first().blur(function() {
            $(this).parent().text(originalContent);
            $(this).parent().removeClass('cellEditing');
        });
    }

    $(document).ready(() => {
        getData();
        $(document).on('dblclick', '.editable', editTable);
    });
</script>
