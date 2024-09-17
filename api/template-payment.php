<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Set headers untuk mendownload file CSV
    header('Content-Type: text/csv'); // Menyatakan bahwa konten yang dikirim adalah file CSV
    header('Content-Disposition: attachment;filename="csv_template.csv"'); // Mengatur nama file CSV yang akan diunduh

    // Buka stream output untuk menulis file CSV
    $output = fopen('php://output', 'w'); // 'php://output' adalah stream khusus yang memungkinkan menulis data langsung ke output buffer

    // Tambahkan header kolom untuk template CSV
    fputcsv($output, ['nis', 'virtual_account', 'trx_amount', 'notes', 'trx_timestamp']); 
    // fputcsv() digunakan untuk menulis baris data CSV ke stream output. Header kolom di sini adalah nama-nama kolom yang diinginkan dalam template CSV

    // Tutup stream output
    fclose($output); // Menutup stream output setelah selesai menulis
    exit; // Menghentikan eksekusi script setelah file CSV dihasilkan
}
?>
