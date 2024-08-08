<?php

include_once '../config/app.php';

header('Content-Type: application/json');

$id = $_POST['id'];
$column = $_POST['column'];
$value = $_POST['value'];
$tahunAjaran = $_POST['tahunAjaran'];
$semester = $_POST['semester'];
$month = $_POST['month'];

$indonesianMonths = [
    'Januari' => 1,
    'Februari' => 2,
    'Maret' => 3,
    'April' => 4,
    'Mei' => 5,
    'Juni' => 6,
    'Juli' => 7,
    'Agustus' => 8,
    'September' => 9,
    'Oktober' => 10,
    'November' => 11,
    'Desember' => 12,
];

$checkQuery = "SELECT
    trx_amount
FROM
    tagihan
WHERE
    nis = '$id' AND
    semester = '$semester' AND
    tahun_ajaran = '$tahunAjaran' AND
    MONTH(expired_date) = '$indonesianMonths[$month]'";

$checkResult = read($checkQuery);

if (isset($checkResult) && count($checkResult) != 0) {
    // Kalau sudah ada transaktinya
    $sql = "UPDATE
        tagihan
    SET
        $column = '$value'
    WHERE
        nis = '$id' AND
        semester = '$semester' AND
        tahun_ajaran = '$tahunAjaran' AND
        MONTH(expired_date) = '$indonesianMonths[$month]'";
} else {
    $customer = "SELECT
    id, nis, nama,
    jenjang, mva, telp_ortu
    FROM siswa
    WHERE nis = '$id'";

    $customer_data = read($customer);

    $data = $customer_data[0];

    if ($semester == 'Gasal') {
        $tahun = substr($tahunAjaran, 0, 4);
    } elseif ($semester == 'Genap') {
        $tahun = substr($tahunAjaran, -4);
    }

    $dayMonth = new DateTime("$tahun-" . str_pad($indonesianMonths[$month], 2, '0', STR_PAD_LEFT) . '-01');

    $dayMonth->modify('last day of this month');

    $dayOfWeek = $dayMonth->format('N');

    if ($dayOfWeek == 6) {
        $dayMonth->modify('-1 day'); // Move to Friday
    } elseif ($dayOfWeek == 7) {
        $dayMonth->modify('-2 days'); // Move to Friday
    }

    $sql =
        "INSERT INTO
        tagihan(
            nis, trx_id, virtual_account,
            customer_name, jenjang, no_ortu,
            customer_email, customer_phone, trx_amount,
            expired_date, expired_time, description,
            semester, tahun_ajaran
        )
        VALUES (
            '$id', '" .
        $data['jenjang'] .
        '/11/5/1/' .
        substr($data['nis'], -4) .
        "', '" .
        $data['mva'] .
        "',
            '" .
        $data['nama'] .
        "', '" .
        $data['jenjang'] .
        "', '" .
        $data['telp_ortu'] .
        "',
            '', '', '$value',
            '" .
        $dayMonth->format('Y-m-d') .
        "', '23:59:59', 'Tagihan sd MEI 2024',
            '$semester', '$tahunAjaran'
        )";
}

$result = crud($sql);

$data = [
    'success' => $result
];

echo json_encode($data);