<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Set headers to download the file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="csv_template.csv"');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Add column headers for the CSV template
    fputcsv($output, ['nis', 'name', 'level', 'phone_number', 'email_address', 'parent_phone', 'virtual_account']);

    // Close the output stream
    fclose($output);
    exit;
}
