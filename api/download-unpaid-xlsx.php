<?php

use Shuchkin\SimpleXLSXGen;

include_once '../config/app.php';
include_once '../config/SimpleXLSXGen.php';
header('Content-Type: application/json');

$q = "SELECT
    ROW_NUMBER() OVER (ORDER BY c.id, u.nis) AS No,
    u.virtual_account AS VA,
    u.nis AS NIS,
    u.name AS Nama,
    COALESCE(c.level, '-') AS Jenjang,
    COALESCE(c.name, '-') AS Kelas,
    COALESCE(c.major, '-') AS Jurusan,
    c.monthly_bills AS SPP,
    u.additional_fee_details,
    MAX(CONCAT(YEAR(b.payment_due), '/', LPAD(MONTH(b.payment_due), 2, '0'))) AS 'Periode Pembayaran',
    COUNT(CASE WHEN b.trx_status = 'not paid' THEN 1 ELSE NULL END) AS 'jumlah_keterlambatan',
    -- Menghitung total denda dari tagihan yang belum dibayar.
    SUM(CASE WHEN b.trx_status = 'not paid' THEN b.late_bills ELSE 0 END) AS denda,

    -- Menghitung total piutang dari tagihan yang belum dibayar dan yang statusnya masih menunggu.
    SUM(CASE WHEN b.trx_status IN ('not paid', 'waiting') THEN b.trx_amount ELSE 0 END) AS piutang,

    -- Subquery terkait untuk membuat array JSON dari tagihan yang belum dibayar dan yang statusnya masih menunggu.
    (
        SELECT JSON_ARRAYAGG(
            JSON_OBJECT(
                'trx_amount', b2.trx_amount,
                'additional_fee_details', b2.additional_fee_details,
                'additional_fee_amount', b2.additional_fee_amount,
                'late_bills', b2.late_bills,
                'payment_due', b2.payment_due
            )
        )
        FROM bills b2
        JOIN classes c2 ON b2.class = c2.id
        WHERE b2.nis = u.nis
        AND b2.trx_status IN ('not paid')
    ) AS unpaid_bills

FROM
    bills b
JOIN
    users u ON b.nis = u.nis
JOIN
    (
        -- Subquery untuk mendapatkan class id terbesar
        SELECT b.nis, MAX(c.id) AS max_class_id
        FROM bills b
        JOIN users u ON b.nis = u.nis
        JOIN classes c ON u.class = c.id
        GROUP BY b.nis
    ) mc ON u.nis = mc.nis
JOIN
    classes c ON mc.max_class_id = c.id

WHERE
    b.trx_status IN ('not paid', 'waiting') AND
    b.payment_due <= NOW()
GROUP BY
    u.nis, u.name, u.virtual_account, c.id,
    COALESCE(c.level, '-'), COALESCE(c.name, '-'), COALESCE(c.major, '-'),
    c.monthly_bills, u.additional_fee_details
ORDER BY
    c.id, u.nis;
";

$result = read($q);
$max_late = 0;
$data = [];
$additional_unpaid_bills = [];
foreach ($result as $row) {
    $max_late = $row['jumlah_keterlambatan'] > $max_late ? $row['jumlah_keterlambatan'] : $max_late;

    $fee_json = $row['additional_fee_details'] != null ? json_decode($row['additional_fee_details'], true) : [];
    $additional_fee = [];
    foreach ($fee_json as $fee_data) {
        $additional_fee[$fee_data['name']] = [
            'name' => $fee_data['name'],
            'amount' => $fee_data['amount'],
        ];
    }

    $details = [];
    $unpaid_bills = json_decode($row['unpaid_bills'], true);

    foreach ($unpaid_bills as $index => $bill) {
        $additional = $bill['additional_fee_details'] != null ? json_decode($bill['additional_fee_details'], true) : [];
        foreach ($additional as $a) {
            if (!isset($additional_unpaid_bills[$row['NIS']][$a['name']])) {
                $additional_unpaid_bills[$row['NIS']][$a['name']] = 0;
            }
            $additional_unpaid_bills[$row['NIS']][$a['name']] += $a['amount'];
        }
        $unpaid_bills[$index]['additional_fee_details'] = $additional;
    }

    $data[$row['NIS']] = [
        'no' => $row['No'],
        'va' => $row['VA'],
        'nis' => $row['NIS'],
        'name' => $row['Nama'],
        'jenjang' => $row['Jenjang'],
        'kelas' => $row['Kelas'],
        'jurusan' => $row['Jurusan'],
        'spp' => $row['SPP'],
        'additional_fee_details' => $additional_fee,
        'payment_periode' => $row['Periode Pembayaran'],
        'jumlah_keterlambatan' => $row['jumlah_keterlambatan'],
        'denda' => $row['denda'],
        'piutang' => $row['piutang'],
        'unpaid_bills' => json_decode($row['unpaid_bills'], true),
    ];
}

$header = ['No', 'VA', 'NIS', 'Nama', 'Jenjang', 'Kelas', 'Jurusan', 'SPP'];
$additional_query = 'SELECT * FROM additional_payment_category';

$additional_payment_categories = read($additional_query);

foreach ($additional_payment_categories as $category) {
    $header[] = $category['category_name'];
}
$header[] = 'Periode Pembayaran';
$header[] = 'JML TUNGGAKAN (BULAN)';

for ($i = 0; $i < $max_late; $i++) {
    $header[] = $i + 1;
    $header[] = 'Besar Tagihan ' . $i + 1;
}

$header[] = 'JUMLAH';
$header[] = 'HER (DPP/UP)';
$header[] = 'JUMLAH TOTAL';

$formatted_header = [];
foreach ($header as $value) {
    $formatted_header[] = "<wraptext><b><middle><center>$value</center></middle></b><wraptext>";
}

$xlsx_content = [];
$current_date = date('Y-m-d H:i:s');
$xlsx_content[] = ["TUNGGAKAN PER $current_date"];
$xlsx_content[] = [];

$xlsx_content[] = $formatted_header;

foreach ($data as $row) {
    $row_data = [];

    // Menambahkan data awal untuk setiap baris
    $row_data[] = "<middle><center>$row[no]</middle></center>";
    $row_data[] = "<middle><center>$row[va]</middle></center>";
    $row_data[] = "<middle><center>$row[nis]</middle></center>";
    $row_data[] = "$row[name]";
    $row_data[] = "<middle><center>$row[jenjang]</middle></center>";
    $row_data[] = "<middle><center>$row[kelas]</middle></center>";
    $row_data[] = "<middle><center>$row[jurusan]</middle></center>";
    $row_data[] = 'IDR ' . number_format($row['spp'], 0, '', ',');

    // Memproses rincian biaya tambahan
    foreach ($additional_payment_categories as $type) {
        $amount = $additional_unpaid_bills[$row['nis']][$type['category_name']] ?? '-';
        $row_data[] = $amount !== '-' ? 'IDR ' . number_format($amount, 0, '', ',') : $amount;
    }

    // Menambahkan periode pembayaran dan jumlah keterlambatan
    $row_data[] = "<middle><center>$row[payment_periode]</middle></center>";
    $row_data[] = "<middle><center>$row[jumlah_keterlambatan]</middle></center>";

    // Inisialisasi variabel untuk menghitung total denda keterlambatan
    $late_bills = 0;

    // Looping untuk tagihan yang belum dibayar
    for ($i = 0; $i < $max_late; $i++) {
        $due_date = '-';

        // Mengecek apakah tanggal jatuh tempo tersedia
        if (isset($row['unpaid_bills'][$i]['payment_due'])) {
            $due_date_format = new DateTime($row['unpaid_bills'][$i]['payment_due']);
            $due_date = $due_date_format->format('M-y');
        }
        $row_data[] = "<middle><center>$due_date</middle></center>";

        // Jika ada tanggal jatuh tempo, hitung total tagihan
        if ($due_date !== '-') {
            $trx_amount = $row['unpaid_bills'][$i]['trx_amount'] ?? 0;
            $additional_fee_amount = $row['unpaid_bills'][$i]['additional_fee_amount'] ?? 0;
            $late_bills_amount = $row['unpaid_bills'][$i]['late_bills'] ?? 0;

            // Total tagihan untuk tagihan yang belum dibayar
            $total_amount = $trx_amount + $additional_fee_amount + $late_bills_amount;
            $row_data[] = 'IDR ' . number_format($total_amount, 0, '', ',');

            // Tambahkan ke total denda keterlambatan
            $late_bills += $total_amount;
        } else {
            $row_data[] = '-';
        }
    }

    // Menambahkan total piutang termasuk denda keterlambatan
    $row_data[] = 'IDR ' . number_format($row['piutang'] + $late_bills, 0, '', ',');

    // Kolom placeholder
    $row_data[] = '-';

    // Menambahkan total piutang lagi untuk kolom terakhir (berdasarkan struktur yang diberikan)
    $row_data[] = 'IDR ' . number_format($row['piutang'] + $late_bills, 0, '', ',');

    // Menambahkan data baris ke dalam konten akhir Excel
    $xlsx_content[] = $row_data;
}
//get additional_payment_categories length

$l_additional = count($additional_payment_categories);

// print_r($xlsx_content);

$xlsx = SimpleXLSXGen::fromArray($xlsx_content)
    ->mergeCells('A1:H1')
    ->setColWidth(1, 5)
    ->setColWidth($l_additional + 9, 12)
    ->setColWidth($l_additional + 10, 12)
    ->freezePanes('I4')
    ->downloadAs('unpaid_bills_report.xlsx');
