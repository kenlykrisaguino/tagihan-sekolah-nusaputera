<?php

include_once '../config/app.php';

header('Content-Type: application/json');

$check_query = "SELECT MIN(payment_due) AS payment_due FROM bills WHERE trx_status = 'waiting' AND payment_due <= NOW()";

$check_result = read($check_query)[0]['payment_due'] ?? null;

if(!$check_result){
    echo json_encode([
        'status' => false,
        'message' => 'Tidak ada tagihan yang dapat dicek.', 
        'data' => null
    ]);
    exit();
}

$payment_due = str_replace(' ', '/', $check_result);

$admin_code = "$payment_due/CHECK-BILLS";
$read_admin = "SELECT admin_code FROM administrations WHERE admin_code = '$admin_code'";

$read_admin_result = read($read_admin)[0]['admin_code']?? null;

if ($read_admin_result != null){
    echo json_encode([
        'status' => false,
        'message' => 'Telah mengecek tagihan bills',
        'data' => null
    ]);
    exit();
}

// Query untuk mengambil data kelas beserta jumlah tagihan terlambat, 
// serta menggabungkan level, nama, dan jurusan jika tersedia
$level_query = "SELECT TRIM(CONCAT(
    COALESCE(level, ''), 
    CASE WHEN COALESCE(level, '') = '' THEN '' ELSE '-' END,
    COALESCE(name, ''), 
    CASE WHEN COALESCE(name, '') = '' THEN '' ELSE '-' END,
    COALESCE(major, '')
)) AS name, late_bills FROM classes";

// Mengambil hasil query dalam array $late_bill_list
$late_bill_list = read($level_query);

// Menginisialisasi array untuk menyimpan jumlah tagihan terlambat per kelas
foreach ($late_bill_list as $late_bill){
    $late_bills[$late_bill['name']] = $late_bill['late_bills'];
}

// Membuat tabel sementara (temporary) untuk menyimpan data tagihan saat ini dan tagihan selanjutnya
$sql_create_temp_table = "
    CREATE TEMPORARY TABLE temp_bills AS
    SELECT b.id, next_b.id AS next_b_id, b.class, b.payment_due
    FROM bills b
    LEFT JOIN bills next_b ON next_b.nis = b.nis 
        AND next_b.payment_due = (
            SELECT MIN(nb.payment_due)
            FROM bills nb
            WHERE nb.nis = b.nis 
            AND nb.payment_due > b.payment_due
            AND nb.trx_status != 'waiting'
        )
    WHERE 
        b.trx_status IN ('waiting', 'paid', 'not paid') 
        AND b.payment_due < DATE_SUB(NOW(), INTERVAL 24 HOUR)
";

// Eksekusi perintah SQL untuk membuat tabel sementara
crud($sql_create_temp_table);

// SQL untuk mengupdate status transaksi dan mengatur tagihan terlambat
$sql_update = "
    UPDATE bills b
    LEFT JOIN temp_bills t ON b.id = t.id
    LEFT JOIN bills next_b ON next_b.id = t.next_b_id
    LEFT JOIN classes c ON c.id = t.class
    SET 
        b.trx_status = CASE
            WHEN b.trx_status = 'waiting' THEN 'not paid'
            WHEN b.trx_status = 'disabled' THEN 'disabled'
            ELSE b.trx_status
        END,
        b.late_bills = CASE
            WHEN b.trx_status = 'waiting' THEN COALESCE(c.late_bills, 0)
            WHEN b.trx_status = 'not paid' THEN b.late_bills + COALESCE(c.late_bills, 0)
            WHEN b.trx_status = 'disabled' THEN 0
            ELSE b.late_bills
        END,
        next_b.trx_status = CASE
            WHEN b.trx_status IN ('waiting', 'paid') THEN 'waiting'
            WHEN b.trx_status = 'disabled' THEN next_b.trx_status
            ELSE next_b.trx_status
        END,
        b.payment_due = CASE
            WHEN b.trx_status = 'waiting' THEN t.payment_due
            WHEN b.trx_status = 'disabled' THEN b.payment_due
            ELSE b.payment_due
        END
    WHERE 
        b.trx_status IN ('waiting', 'paid', 'not paid') 
        AND b.payment_due < DATE_SUB(NOW(), INTERVAL 24 HOUR)
";


// Eksekusi query update untuk memperbarui tagihan
$result = crud($sql_update);

// Menghapus tabel sementara setelah update selesai
$sql_drop_temp_table = "DROP TEMPORARY TABLE temp_bills";
crud($sql_drop_temp_table);

// Menambahkan log ke administrations

$sql_add_log = "INSERT INTO administrations(admin_code, type) VALUES ('$admin_code', 'check')";
crud($sql_add_log);

// Mengembalikan hasil dalam format JSON
echo json_encode([
    'status' => true,
    'message' => 'Tagihan berhasil dicek', 
    'data' => $late_bills
]);
exit();

?>
