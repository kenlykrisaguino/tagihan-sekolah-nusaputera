<div class="col-12">
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
            <?php if ($_SESSION['username'] == 'subadmin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'additional-payment.php' ? 'active' : ''; ?>" href="additional-payment.php">Biaya Tambahan</a>
            </li>
            <?php endif;?>
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
            <?php if ($_SESSION['username'] == 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'activity-logs.php' ? 'active' : ''; ?>" href="activity-logs.php">Logs</a>
                </li>
            <?php endif;?>
            <li class="nav-item">
                <a class="nav-link" href="Logout.php">Logout</a>
            </li>
        </ul>
    </div>
</div>
