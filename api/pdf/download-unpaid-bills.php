<?php

require_once dirname(dirname(__DIR__)) . '/config/dompdf/autoload.inc.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';

use Dompdf\Dompdf;

// get current date that can be compared in mysql query format

$currentDate = date('Y-m-d'). " 23:59:59";

$query = "
SELECT 
    u.nis, 
    u.name AS student_name, 
    c.id AS class_id,
    CONCAT(COALESCE(c.level, ''), ' ', COALESCE(c.name, ''), ' ', COALESCE(c.major, '')) AS level, 
    u.virtual_account,
    SUM(CASE WHEN b.trx_status = 'not paid' THEN b.late_bills ELSE 0 END) + SUM(CASE WHEN b.trx_status in ('waiting', 'not paid', 'inactive') THEN b.trx_amount ELSE 0 END) AS tagihan,
    SUM(CASE WHEN b.trx_status = 'not paid' THEN b.late_bills ELSE 0 END) AS denda,
    SUM(CASE WHEN b.trx_status in ('waiting', 'not paid', 'inactive') THEN b.trx_amount ELSE 0 END) AS piutang
FROM 
    bills b 
JOIN 
    users u ON b.nis = u.nis 
JOIN 
    (
        -- Subquery to get the maximum class ID from the bills table for each user
        SELECT b.nis, MAX(c.id) AS max_class_id
        FROM bills b
        JOIN users u ON b.nis = u.nis
        JOIN classes c ON u.class = c.id
        GROUP BY b.nis
    ) mc ON u.nis = mc.nis  -- Join with the subquery result
JOIN 
    classes c ON mc.max_class_id = c.id  -- Join with classes to get class details
WHERE 
    b.trx_status IN ('not paid', 'waiting', 'inactive') AND
    b.payment_due <= '$currentDate'
GROUP BY
    u.nis, u.name, 
    c.id,  -- Group by class id
    u.virtual_account
ORDER BY
    c.id, u.nis;
";

$result = read($query);

$data_content = "";

$content = "";

foreach($result as $data){
    $tagihan = formatToRupiah($data['tagihan']);
    $denda = formatToRupiah($data['denda']);
    $piutang = formatToRupiah($data['piutang']);

    $content .= "
    <tr>
        <td>{$data['nis']}</td>
        <td>{$data['student_name']}</td>
        <td>{$data['level']}</td>
        <td>{$data['virtual_account']}</td>
        <td>{$piutang}</td>
        <td>{$denda}</td>
        <td>{$tagihan}</td>
    </tr>";
}


$dompdf = new Dompdf();

$options = $dompdf->getOptions();
$options->setDefaultFont('Courier');
$dompdf->setOptions($options);

$html = "<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 10px;
        }

        header {
            margin: 32px 48px 32px 48px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        header img {
            height: 5rem;
        }

        .text-header {
            display: flex;
            gap: 12px;
            flex-direction: column;
        }

        tr,
        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th{
            text-align: center;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 12px;
            margin-bottom: 12px;
        }

        .filter {
            padding-left: 48px;
            padding-right: 48px;
        }

        .bold {
            font-weight: bold;
        }

        .data {
            padding: 0 48px;
            margin-top: 64px;
        }

        .text-left{
            text-align: left;
        }

        .text-right{
            text-align: right;
        }
    </style>
</head>

<body>
    <header>
        <div class='text-header'>
            <h1>Data Tagihan</h1>
            <p>Sekolah Nusaputera Semarang</p>
        </div>
    </header>
    <hr>
    <table>
        <thead>
            <tr>
                <th>NIS</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>VA</th>
                <th>Piutang</th>
                <th>Denda</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            $content
        </tbody>
    </table>
</body>

</html>
";

$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser with forced download
$dompdf->stream('unpaid-bills.pdf', ['Attachment' => true]);
