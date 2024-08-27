<?php

require_once dirname(dirname(__DIR__)).'/config/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$semester = $_POST['semester'] ?? '-';
$tahunAjaran = $_POST['tahun_ajaran'] ?? '-';
$kelas = $_POST['kelas'] ?? '-';
$nama = $_POST['nama'] ?? '-';
$bank = $_POST['bank'] ?? '0';
$tunggakan = $_POST['tunggakan'] ?? '0';
$total = $_POST['total'] ?? '0';


$dompdf = new Dompdf();

$options = $dompdf->getOptions();
$options->setDefaultFont('Courier');
$dompdf->setOptions($options);

// Load the HTML content as a string from the file
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

        .data tr,
        .data th,
        .data td {
            text-align: start;
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
                    <td>$nama</td>
                </tr>
            </tbody>
        </table>
    </section>
    <section class='data'>
        <table>
            <tr>
                <td>Bank</td>
                <td>$bank</td>
            </tr>
            <tr>
                <td>Tunggakan</td>
                <td>$tunggakan</td>
            </tr>
            <tr class='bold'>
                <td>Total</td>
                <td>$total</td>
            </tr>
        </table>
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
