<?php

include_once '../config/app.php';

header('Content-Type: application/json');

$search = isset($_GET['search']) ? $_GET['search'] : '';
$tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '';
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';

if($semester == 'Genap') {
    $sql_semester = "
    SUM(CASE WHEN MONTH(expired_date) = 1 THEN trx_amount ELSE 0 END) AS Januari,
    SUM(CASE WHEN MONTH(expired_date) = 2 THEN trx_amount ELSE 0 END) AS Februari,
    SUM(CASE WHEN MONTH(expired_date) = 3 THEN trx_amount ELSE 0 END) AS Maret,
    SUM(CASE WHEN MONTH(expired_date) = 4 THEN trx_amount ELSE 0 END) AS April,
    SUM(CASE WHEN MONTH(expired_date) = 5 THEN trx_amount ELSE 0 END) AS Mei,
    SUM(CASE WHEN MONTH(expired_date) = 6 THEN trx_amount ELSE 0 END) AS Juni";
} else {
    $sql_semester = "
    SUM(CASE WHEN MONTH(expired_date) = 7 THEN trx_amount ELSE 0 END) AS Juli,
    SUM(CASE WHEN MONTH(expired_date) = 8 THEN trx_amount ELSE 0 END) AS Agustus,
    SUM(CASE WHEN MONTH(expired_date) = 9 THEN trx_amount ELSE 0 END) AS September,
    SUM(CASE WHEN MONTH(expired_date) = 10 THEN trx_amount ELSE 0 END) AS Oktober,
    SUM(CASE WHEN MONTH(expired_date) = 11 THEN trx_amount ELSE 0 END) AS November,
    SUM(CASE WHEN MONTH(expired_date) = 12 THEN trx_amount ELSE 0 END) AS Desember";
}

$sql = "SELECT
    virtual_account,
    customer_name,jenjang,no_ortu,tahun_ajaran,
    SUM(trx_amount) AS penerimaan, $sql_semester
    FROM 
        tagihan 
    WHERE 
        tahun_ajaran = '$tahun_ajaran' AND 
        semester ='$semester' AND
        customer_name LIKE '%$search%'
    GROUP BY 
        virtual_account, customer_name,jenjang,no_ortu,tahun_ajaran;
    ";

$result = read($sql);

$data = [
    'status' => 'OK',
    'message' => 'Get Input Data',
    'query' => $sql,
    'data' => $result
];

echo json_encode($data);