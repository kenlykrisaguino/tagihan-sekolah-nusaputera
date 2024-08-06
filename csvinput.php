<?php
include_once "./config/app.php";

if (isset($_POST["submit"])) {
    if ($_FILES['file']['name']) {
        $filename = explode(".", $_FILES['file']['name']);
        if (end($filename) == "csv") {
            $handle = fopen($_FILES['file']['tmp_name'], "r");
            // Skip the first row (header)
            fgetcsv($handle, 1000, ",");
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $trx_id = $data[0];
                $virtual_account = $data[1];
                $customer_name = $data[2];
                $customer_email = $data[3];
                $customer_phone = $data[4];
                $trx_amount = $data[5];
                $expired_date = $data[6];
                $expired_time = $data[7];
                $description = $data[8];

                $parts = explode('/', $trx_id);
                list($tahun, $bulan, $tanggal) = explode('-', $expired_date);

                $tahunsebelum=$tahun-1;
                $tahunsetelah=$tahun+1;
                if ($bulan>=7){
                    $ta = $tahun."/".$tahunsetelah;
                    $semester = "Gasal";
                } else {
                    $ta = $tahunsebelum."/".$tahun;
                    $semester = "Genap";
                }
                // Insert into transaksi table
                $sql_insert = "INSERT INTO tagihan (trx_id, virtual_account, customer_name, jenjang, customer_email, customer_phone, trx_amount, expired_date, expired_time, description, semester, tahun_ajaran) 
                               VALUES ('$trx_id', '$virtual_account', '$customer_name','$parts[0]', '$customer_email', '$customer_phone', '$trx_amount', '$expired_date', '$expired_time', '$description','$semester','$ta')";
                crud($sql_insert);
            }
            fclose($handle);
            echo "Import and update done";
            ob_start();
            echo "<script type='text/javascript'>alert('Upload Berhasil'); window.location.href = 'index.php';</script>";
            ob_end_flush();
        } else {
            echo "Please upload a CSV file.";
        }
    } else {
        echo "Please select a file.";
    }
}

$conn->close();
?>
