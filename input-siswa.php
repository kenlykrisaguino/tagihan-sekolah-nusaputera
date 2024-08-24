<?php
require_once './config/app.php';
// Check if user is logged in
IsLoggedIn();
RoleAllowed(7) ? null : returnError();

include './headers/admin.php';

?>

<h2 class="my-4">
    Input Siswa
</h2>

<form action="./upload-siswa-csv.php" method="POST" enctype="multipart/form-data">
    <label for="data" class="d-block">Upload CSV</label>
    <div class="d-flex my-2 w-100 justify-content-between">
        <input type="file" name="data" id="data" accept=".csv" class="mb-2">
        <div class="d-flex">
            <input type="submit" value="Upload" class="mr-2 btn btn-primary">
            <input onclick="downloadCSV()" type="button" value="Download Template" class="ml-2 btn btn-outline-primary">
        </div>
    </div>
</form>

<hr>

<h2 class="my-4">
    Input Form
</h2>

<form id="inputForm" action="./upload-siswa-form.php" class="w-100 d-flex flex-wrap">
    <div class="form-group col-12 col-md-4">
        <label for="nis">Nomor Induk Siswa</label>
        <input type="number" class="form-control" id="nis" name="nis" required>
    </div>
    <div class="form-group col-12 col-md-8">
        <label for="nama">Nama</label>
        <input type="text" class="form-control" id="nama" name="nama" required>
    </div>
    <div class="form-group col-12 col-md-4">
        <label for="level">Jenjang</label>
        <input type="text" class="form-control" id="level" name="level" required>
    </div>
    <div class="form-group col-12 col-md-4">
        <label for="class">Tingkat</label>
        <input type="text" class="form-control" id="class" name="class" required>
    </div>
    <div class="form-group col-12 col-md-4">
        <label for="major">Kelas</label>
        <input type="text" class="form-control" id="major" name="major" required>
    </div>
    <div class="form-group col-12">
        <label for="birth_date">Nomor Telepon</label>
        <input type="text" class="form-control" id="birth_date" name="birth_date" required>
    </div>
    <div class="form-group col-12">
        <label for="phone_number">Nomor Telepon</label>
        <input type="text" class="form-control" id="phone_number" name="phone_number">
    </div>
    <div class="form-group col-12">
        <label for="email_address">Alamat Email</label>
        <input type="text" class="form-control" id="email_address" name="email_address">
    </div>
    <div class="form-group col-12">
        <label for="parent_phone">Telepon Orang Tua</label>
        <input type="text" class="form-control" id="parent_phone" name="parent_phone" required>
    </div>
    <div class="form-group col-12">
        <label for="address">Alamat Rumah</label>
        <input type="text" class="form-control" id="address" name="address" required>
    </div>

    <div class="form-group col-12">
        <button type="submit" class="btn btn-primary">Tambahkan</button>
    </div>



<script>
    const downloadCSV = () => {
        $.ajax({
            url: 'api/template-siswa.php',
            method: 'POST',
            xhrFields: {
                responseType: 'blob' // Important to receive a binary file
            },
            success: function(response) {
                var link = document.createElement('a');
                link.href = URL.createObjectURL(response);
                link.download = 'template_siswa.csv';
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
    })
</script>