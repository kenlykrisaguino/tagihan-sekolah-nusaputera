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
    bills
WHERE
    nis = '$id' AND
    semester = '$semester' AND
    period = '$tahunAjaran' AND
    MONTH(payment_due) = '$indonesianMonths[$month]'";

$checkResult = read($checkQuery);

if (isset($checkResult) && count($checkResult) != 0) {
    // Kalau sudah ada transaktinya
    $sql = "UPDATE
        bills
    SET
        $column = '$value'
    WHERE
        nis = '$id' AND
        semester = '$semester' AND
        period = '$tahunAjaran' AND
        MONTH(payment_due) = '$indonesianMonths[$month]'";
} else {
    $customer = "SELECT
    id, nis, name,
    level, virtual_account, parent_phone
    FROM users
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
        bills(
            nis, trx_id, virtual_account,
            student_name, level, parent_phone,
            student_email, student_phone, trx_amount,
            payment_due, description, semester, period
        )
        VALUES (
            '$id', '" .
        $data['level'] .
        '/11/5/1/' .
        substr($data['nis'], -4) .
        "', '" .
        $data['virtual_account'] .
        "',
            '" .
        $data['name'] .
        "', '" .
        $data['level'] .
        "', '" .
        $data['parent_phone'] .
        "',
            '', '', '$value',
            '" .
        $dayMonth->format('Y-m-d') .
        "', '23:59:59', '',
            '$semester', '$tahunAjaran'
        )";
}

$result = crud($sql);

$data = [
    'success' => $result
];

echo json_encode($data);