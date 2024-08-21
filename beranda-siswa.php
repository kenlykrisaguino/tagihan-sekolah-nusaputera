<?php
include './config/app.php';
// Check if user is logged in
IsLoggedIn();
RoleAllowed() ? null : returnError();

include './headers/siswa.php';
?>

<h2 class="my-4">Informasi Siswa</h2>

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

<script>
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

    $(document).ready(() => {
        getData();
        $(document).on('click', '#filter-btn', getData);

    });
</script>