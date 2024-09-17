<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Set headers untuk mendownload file CSV
    header('Content-Type: text/csv'); // Menyatakan bahwa konten yang dikirim adalah file CSV
    header('Content-Disposition: attachment;filename="csv_template.csv"'); // Mengatur nama file CSV yang akan diunduh sebagai 'csv_template.csv'

    // Buka stream output untuk menulis file CSV
    $output = fopen('php://output', 'w'); // 'php://output' adalah stream khusus yang memungkinkan menulis data langsung ke output buffer

    // Tambahkan header kolom untuk template CSV
    fputcsv($output, ['nis', 'name', 'jenjang', 'tingkat', 'kelas', 'alamat', 'birthdate', 'phone_number', 'email_address', 'parent_phone']);
    // fputcsv() digunakan untuk menulis baris data CSV ke stream output. Di sini, header kolom yang ditambahkan termasuk:
    // - nis: Nomor Induk Siswa
    // - name: Nama Siswa
    // - jenjang: Jenjang Pendidikan
    // - tingkat: Tingkat Pendidikan
    // - kelas: Kelas
    // - alamat: Alamat
    // - birthdate: Tanggal Lahir
    // - phone_number: Nomor Telepon Siswa
    // - email_address: Alamat Email
    // - parent_phone: Nomor Telepon Orang Tua

    // Tutup stream output
    fclose($output); // Menutup stream output setelah selesai menulis data
    exit; // Menghentikan eksekusi script setelah file CSV dihasilkan
}
?>
