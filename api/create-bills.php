<?php

// Memasukkan file konfigurasi aplikasi
include_once '../config/app.php';
// Mengatur header agar output berformat JSON
header('Content-Type: application/json');

// Membuat admin code berdasarkan tahun ajaran dan semester
$admin_code = $tahun_ajaran."-".$semester."-create";

// Memeriksa apakah admin_code sudah ada dalam tabel administrations
$read = "SELECT admin_code FROM administrations WHERE admin_code='$admin_code'";
$readResult = read($read);

// Jika admin_code sudah ada, keluarkan pesan bahwa tagihan sudah dibuat
if ($readResult) {
    echo json_encode(['message' => 'Tagihan sudah ada']);
    exit(); // Menghentikan eksekusi lebih lanjut
}

// Menentukan bulan untuk semester Genap atau Gasal
$semester_month = $semester == 'Genap' ? [1, 2, 3, 4, 5, 6] : [7, 8, 9, 10, 11, 12];

// Bulan pertama pada semester yang dipilih
$first_month = $semester_month[0];

// Mendapatkan data siswa yang aktif dan bukan admin
$users = "SELECT
    u.nis, u.virtual_account, u.name, u.parent_phone, u.phone_number,
    u.email_address, c.monthly_bills, c.id AS class, c.level as level
    FROM users u
    INNER JOIN classes c ON u.class = c.id
    WHERE 
    u.status = 'active' AND
    u.name != 'ADMIN'";

// Menjalankan query untuk mendapatkan data siswa
$usersResult = read($users);

// Variabel untuk menyimpan query insert tagihan
$input = "";

// Looping melalui setiap siswa untuk setiap bulan dalam semester
foreach ($usersResult as $user) {
    foreach ($semester_month as $month_num) { 
        // Format nomor bulan menjadi dua digit (misalnya: 01, 02)
        $num_padded = str_pad($month_num, 2, '0', STR_PAD_LEFT);

        // Menghitung tanggal jatuh tempo (10 hari setelah akhir bulan)
        $due_date = new DateTime("$year-$num_padded-01");
        $due_date->modify('first day of next month');
        $due_date->modify('+9 days');

        // Mengubah tanggal jatuh tempo ke format yang diinginkan
        $query_duedate = $due_date->format('Y-m-d') . " 23:59:59";

        // Status transaksi: bulan pertama 'waiting', bulan berikutnya 'inactive'
        $trx_status = ($month_num == $first_month) ? 'waiting' : 'inactive';

        // Membuat trx_id untuk transaksi berdasarkan level, NIS, dan bulan
        $trx_id = generateTrxID($user['level'], $user['nis'], $month_num);

        // Menyusun nilai untuk query insert ke tabel bills
        $input .= "(
            '$user[nis]', '$trx_id', '$user[virtual_account]',
            '{$user['name']}', '$user[parent_phone]', '$user[phone_number]',
            '{$user['email_address']}', '{$user['monthly_bills']}', '$trx_status',
            'Pembayaran tahun ajaran $tahun_ajaran Semester $semester bulan $months[$num_padded]', '{$user['class']}', '$tahun_ajaran',
            '$semester', '$query_duedate'
        ), ";
    }
}

// Query untuk memasukkan tagihan ke tabel bills
$sql = "INSERT INTO bills(
    nis, trx_id, virtual_account, 
    student_name, parent_phone, student_phone,
    student_email, trx_amount, trx_status,
    description, class, period,
    semester, payment_due
) VALUES 
$input";

// Menghapus koma terakhir dari string query
$sql = rtrim($sql, ", ");

// Menjalankan query insert untuk memasukkan tagihan
$result = crud($sql);

// Query untuk memasukkan kode administrasi ke tabel administrations
$sql = "INSERT INTO administrations(admin_code, type) VALUES ('$admin_code', 'create')";

// Menjalankan query insert untuk tabel administrations
$result = crud($sql);

// Mengembalikan response JSON berdasarkan hasil eksekusi query
if ($result) {
    echo json_encode([
        'status' => true,
        'message' => 'Tagihan berhasil dibuat', 
        'data' => $usersResult // Mengembalikan data siswa sebagai response
    ]);
} else {
    echo json_encode([
        'status' => false,
        'message' => 'Gagal membuat tagihan'
    ]);
}

// Fungsi untuk menghasilkan trx_id berdasarkan level, NIS, dan bulan
function generateTrxID($level, $nis, $month){
    global $year;
    global $semester;

    // Mengambil dua digit terakhir dari tahun ajaran
    $trimmed_year = substr($year, -2);
    // Menentukan semester (1 untuk Gasal, 2 untuk Genap)
    $curr_semester = $semester == 'Gasal' ? "1" : "2";
    // Menghitung bulan semester (berdasarkan 6 bulan dalam semester)
    $semester_month = (($month-1) % 6)+1;

    // Menyusun trx_id berdasarkan level, tahun, semester, bulan, dan NIS
    $trx_id = "$level/$trimmed_year/$curr_semester/$semester_month/$nis";

    return $trx_id; // Mengembalikan trx_id yang dihasilkan
}
?>
