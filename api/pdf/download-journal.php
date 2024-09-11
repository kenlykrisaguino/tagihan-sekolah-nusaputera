<?php

require_once dirname(dirname(__DIR__)) . '/config/dompdf/autoload.inc.php';
require_once dirname(dirname(__DIR__)) . '/config/app.php';

use Dompdf\Dompdf;

$semester = $_POST['semester'] ?? '-';
$tahunAjaran = $_POST['tahun_ajaran'] ?? '-';
$kelas = $_POST['kelas'] ?? '-';
$nis = $_POST['nis'] ?? '-';
$bank = $_POST['bank'] ?? '0';
$tunggakan = $_POST['tunggakan'] ?? '0';
$total = $_POST['total'] ?? '0';

if($_POST['nis']){
    $query = "SELECT name FROM users WHERE nis = '$nis'";
    $nama = read($query)[0]['name'];
    $nis = "(".$nis.") - ".$nama;
} else {
    $nis = '-';
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
            padding: 16px;
            text-align: center;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 16px;
            margin-bottom: 16px;
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
            <h1>Data Penjurnalan</h1>
            <p>Sekolah Nusaputera Semarang</p>
        </div>
    </header>
    <hr>
    <section class='filter'>
        <table>
            <thead>
                <th>Semester</th>
                <th>Tahun Ajaran</th>
                <th>Kelas</th>
            </thead>
            <tbody>
                <tr>
                    <td>$semester</td>
                    <td>$tahunAjaran</td>
                    <td>$kelas</td>
                </tr>
            </tbody>
        </table>
        <table>
            <thead>
                <th>Nama</th>
            </thead>
            <tbody>
                <tr>
                    <td>$nis</td>
                </tr>
            </tbody>
        </table>
    </section>
    <section class='data'>
        <table>
            <tr>
                <td class='text-left'>Bank</td>
                <td class='text-left'>$bank</td>
            </tr>
            <tr class='bold'>
                <td class='text-right'>Total</td>
                <td class='text-right'>$total</td>
            </tr>
        </table>
        <p>Total Denda : $tunggakan</p>
    </section>
</body>

</html>
";

$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser with forced download
$dompdf->stream('journal.pdf', ['Attachment' => true]);
