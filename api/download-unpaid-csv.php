<?php

// Memasukkan konfigurasi aplikasi
include_once '../config/app.php'; 

// Mengatur header untuk file CSV dan mendefinisikan nama file unduhan
// header('Content-Type: text/csv; charset=utf-8');
// header('Content-Disposition: attachment; filename=unpaid_bills.csv');

// Menjalankan query
$query = "
SELECT 
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
    SUM(CASE WHEN b.trx_status = 'not paid' THEN b.late_bills ELSE 0 END) AS denda,
    SUM(CASE WHEN b.trx_status IN ('not paid', 'waiting') THEN b.trx_amount ELSE 0 END) AS piutang
FROM 
    bills b 
JOIN 
    users u ON b.nis = u.nis 
JOIN 
    (
        SELECT b.nis, MAX(c.id) AS max_class_id
        FROM bills b
        JOIN users u ON b.nis = u.nis
        JOIN classes c ON u.class = c.id
        GROUP BY b.nis
    ) mc ON u.nis = mc.nis
JOIN 
    classes c ON mc.max_class_id = c.id
WHERE 
    b.trx_status IN ('not paid', 'waiting')
GROUP BY
    u.nis, u.name, u.virtual_account, c.id, 
    COALESCE(c.level, '-'), COALESCE(c.name, '-'), COALESCE(c.major, '-'),
    c.monthly_bills, u.additional_fee_details
ORDER BY
    c.id, u.nis;
";

$result = read($query);

// Membuka aliran output untuk menulis file CSV
$output = fopen('php://output', 'w');

// Get all possible additional payment categories (for example purposes, you could also query this dynamically)
$additional_payment_query = "SELECT category_name AS name FROM additional_payment_category";

$additional_payment_categories = read($additional_payment_query);

// Creating the dynamic headers for the CSV file
$headers = [
    'No', 'VA', 'NIS', 'Nama', 'Jenjang', 'Kelas', 'Jurusan', 'SPP',
];
foreach ($additional_payment_categories as $category) {
    $name = $category['name'];
    $headers[] = $name;
}
$headers[] = 'Jumlah Uang Sekolah';
$headers[] = 'Periode Pembayaran';
$headers[] = 'Jumlah Keterlambatan (bulan)';
$headers[] = 'Total Denda';
$headers[] = 'Total Piutang';
$headers[] = 'Jumlah Total';

// Writing headers to CSV
fputcsv($output, $headers);

// Menulis data ke file CSV
foreach ($result as $row) {
    $additional_fee_details = !empty($row['additional_fee_details']) ? json_decode($row['additional_fee_details'], true) : [];
    
    // Initialize total amount and additional payments array
    $jumlah_uang_sekolah = intval($row['SPP']);
    $additional_payments = [];

    // Initialize amounts for each additional payment category
    foreach ($additional_payment_categories as $category) {
        $amount = 0;
        
        // Loop through the JSON data and find the matching category
        if ($additional_fee_details) {
            foreach ($additional_fee_details as $payment) {
                if ($payment['name'] === $category['name']) {
                    $amount = intval($payment['amount']); // Extract the amount
                    break;
                }
            }
        }

        // Add the amount to the additional payments array and the total
        $additional_payments[] = $amount;
        $jumlah_uang_sekolah += $amount;
    }
    
    // Create a row with dynamic additional payment columns
    $csv_row = [
        $row['No'],
        $row['VA'],
        $row['NIS'],
        $row['Nama'],
        $row['Jenjang'],
        $row['Kelas'],
        $row['Jurusan'],
        $row['SPP'],
    ];

    // Append additional payment columns
    foreach ($additional_payments as $amount) {
        $csv_row[] = $amount;
    }
    
    // Append other calculated columns
    $csv_row[] = $jumlah_uang_sekolah;
    $csv_row[] = $row['Periode Pembayaran'];
    $csv_row[] = $row['jumlah_keterlambatan'];
    $csv_row[] = $row['denda'];
    $csv_row[] = $row['piutang'];
    $csv_row[] = $row['piutang'] + $row['denda'];

    // Write the row to CSV
    fputcsv($output, $csv_row);
}

fclose($output);

?>
