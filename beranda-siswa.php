<?php
include './config/app.php';
// Check if user is logged in
IsLoggedIn();
RoleAllowed() ? null : returnError();

include './headers/siswa.php';
?>

<h2 class="my-4">Informasi Siswa</h2>

<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Ganti Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm" class="col-12" method="POST" action="./change-password.php">
                    <div class="form-group w-100">
                        <label for="exampleInputEmail1">Password Lama</label>
                        <input name="old_password" type="password" class="form-control w-100" id="oldPassword" aria-describedby="oldPasswordDesc" placeholder="Masukan Password Lama">
                    </div>
                    <div class="form-group w-100">
                        <label for="exampleInputEmail1">Password Baru</label>
                        <input name="new_password" type="password" class="form-control w-100" id="newPassword" aria-describedby="newPasswordDesc" placeholder="Masukan Password Baru">
                    </div>
                    <div class="form-group w-100">
                        <label for="exampleInputEmail1">Konfirmasi</label>
                        <input name="confirm_password" type="password" class="form-control w-100" id="confirmPassword" aria-describedby="confirmPasswordDesc" placeholder="Konfirmasi Password Baru">
                    </div>

                    <div class="w-100 d-flex mb-5">
                        <button type="submit" class="btn btn-outline-primary">Ganti Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<table class="table table-bordered my-4">
    <tbody>
        <tr><td>NIS</td><td id="data-nis"></td></tr>
        <tr><td>Nama</td><td id="data-name"></td></tr>
        <tr><td>Nomor Orang Tua</td><td id="data-parent_phone"></td></tr>
        <tr><td>Virtual Account</td><td id="data-virtual_account"></td></tr>
        <tr><td>Jenjang</td><td id="data-level"></td></tr>
        <tr><td>Tahun</td><td id="data-period"></td></tr>
        <tr><td>Semester</td><td id="data-semester"></td></tr>
        <tr><td>SPP</td><td id="data-monthly_bills"></td></tr>
        <tr><td>Pembayaran Terakhir</td><td id="data-last_payment"></td></tr>
        <tr><td>Total Tagihan</td><td id="data-total_bills"></td></tr>
    </tbody>
</table>

<div class="d-flex justify-content-center">
    <button class="btn btn-outline-primary" onclick="toggleModal()">Change Password</button>
</div>

<script>

    const toggleModal = () => {
        $('#changePasswordModal').modal('toggle');
    }
    
    const getData = () => {
        var url = 'api/students/rekap-siswa.php';

        var params = new URLSearchParams({
            user: "<?php echo $_SESSION['username'] ?>",
        });

        url += '?' + params.toString();

        var rows = '';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                $('#data-nis').text(data.data.nis);
                $('#data-name').text(data.data.name);
                $('#data-parent_phone').text(data.data.parent_phone);
                $('#data-virtual_account').text(data.data.virtual_account);
                $('#data-level').text(data.data.level);
                $('#data-period').text(data.data.period);
                $('#data-semester').text(data.data.semester);
                $('#data-last_payment').text(data.data.last_payment);
                $('#data-monthly_bills').text(formatToIDR(data.data.monthly_bills));
                $('#data-total_bills').text(formatToIDR(data.data.total_bills));
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
        $(document).on('click', '#filter-btn', getData);

        <?php
            if(isset($_SESSION['success_message'])){
                echo 'showToast(true, "'.$_SESSION['success_message'].'")';
                unset($_SESSION['success_message']);
            }

            if(isset($_SESSION['error_message'])){
                echo 'showToast(false, "'.$_SESSION['error_message'].'")';
                unset($_SESSION['error_message']);
            }
        ?>
    });

</script>