<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Set headers to download the file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="csv_template.csv"');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Add column headers for the CSV template
    fputcsv($output, ['nis', 'name', 'jenjang', 'tingkat', 'kelas', 'alamat', 'birthdate', 'phone_number', 'email_address', 'parent_phone']);

    // Close the output stream
    fclose($output);
    exit;
}
