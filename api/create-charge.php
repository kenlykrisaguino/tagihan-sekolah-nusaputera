<?php

// Memasukkan konfigurasi Midtrans dan aplikasi
require_once '../config/midtrans/Midtrans.php';
require_once '../config/app.php';

// Mengatur header agar output berformat JSON
header('Content-Type: application/json');

// Mengimpor kelas Midtrans
use Midtrans\Config;
use Midtrans\CoreApi;

// Query untuk mendapatkan bulan terkecil dari tagihan yang statusnya 'waiting'
$monthQuery = "SELECT MIN(MONTH(payment_due)) AS month FROM bills WHERE trx_status = 'waiting'";

// Menjalankan query untuk mendapatkan bulan administrasi saat ini
$curr_month_admin = read($monthQuery)[0]['month'];
$last_month_int = $curr_month_admin == 0 ? 12 : $curr_month_admin;

// Menambahkan angka nol di depan bulan jika kurang dari 10
if ($curr_month_admin < 10) {
    $curr_month_admin = '0'. $curr_month_admin;
}

// Membuat kode administrasi berdasarkan tahun ajaran, semester, dan bulan
$admin_code = $tahun_ajaran.'-'.$semester.'-'.$curr_month_admin."-create-bills";

// Query untuk memeriksa apakah kode administrasi sudah ada di tabel administrations
$read = "SELECT admin_code FROM administrations WHERE admin_code='$admin_code'";
$readResult = read($read);

// Jika kode administrasi sudah ada, mengembalikan pesan bahwa tagihan sudah dibuat
if ($readResult) {
    echo json_encode([
        'status' => false,
        'message' => 'Tagihan sudah ada'
    ]);
    exit(); // Menghentikan eksekusi lebih lanjut
}

// Mengatur konfigurasi Midtrans dari variabel lingkungan
$isProduction = getenv('MIDTRANS_IS_PRODUCTION') == 0 ?  false : true;
$isSanitized = getenv('MIDTRANS_IS_SANITIZED') == 0 ?  false : true;
$is3ds = getenv('MIDTRANS_IS_3DS') == 0 ?  false : true;

Config::$serverKey = getenv('MIDTRANS_SERVER_KEY');
Config::$isProduction = $isProduction;
Config::$isSanitized = $isSanitized;
Config::$is3ds = $is3ds;

// Query untuk mendapatkan tagihan yang sudah kedaluwarsa atau menunggu
$expiredPayment = "SELECT midtrans_trx_id, trx_id FROM bills WHERE LOWER(trx_status) IN ('not paid') AND MONTH(payment_due) = '$last_month_int'";

$expiredPaymentResult = read($expiredPayment);

// Jika tidak ada tagihan kedaluwarsa, ambil tagihan dengan status 'waiting'
if(empty($expiredPaymentResult)){
    $expiredPayment = "SELECT midtrans_trx_id, trx_id FROM bills WHERE LOWER(trx_status) IN ('waiting') AND MONTH(payment_due) = '$last_month_int'";
    $expiredPaymentResult = read($expiredPayment);
}

// Menyimpan data untuk bulan berikutnya
$curr_smt = "";
$curr_month = "";
$expired = [];

// Memproses setiap tagihan yang kedaluwarsa
foreach ($expiredPaymentResult as $trx){
    [$level, $year, $curr_smt, $curr_month, $nis] = explode('/', $trx['trx_id']);
    try{
        // Membatalkan transaksi jika ID transaksi Midtrans tidak null
        if($trx['midtrans_trx_id'] != null){
            $expired[] = CoreApi::expireTrx($trx['midtrans_trx_id']);
        }
    } catch (Exception $e){
        // Mengembalikan pesan error jika gagal membatalkan transaksi
        $result = array(
            'status' => false,
            'message' => 'Gagal melakukan pembatalan transaksi: '. $e->getMessage()
        );
        echo json_encode($result);
        exit(); // Menghentikan eksekusi lebih lanjut
    }
}

// Menghitung bulan berikutnya untuk tagihan
$month = ($curr_smt == '1' ? (int)$curr_month + 6 : (int)$curr_month) + 1;
$month = $month % 12;

$data = [];

// Query untuk mendapatkan tagihan yang harus dibuat
$sql = "SELECT
    MAX(b.trx_id) AS trx_id,
    CONCAT(c.va_prefix_name ,b.student_name) AS student_name, b.parent_phone, b.virtual_account, MAX(b.payment_due) AS payment_due,
    SUM(CASE WHEN b.trx_status = 'waiting' OR b.trx_status = 'not paid' THEN b.trx_amount ELSE 0 END) AS monthly_total,
    COUNT(CASE WHEN b.trx_status = 'waiting' OR b.trx_status = 'not paid' THEN b.trx_amount ELSE NULL END) AS monthly_count,
    SUM(CASE WHEN b.trx_status = 'not paid' THEN b.late_bills ELSE 0 END) AS late_total,
    COUNT(CASE WHEN b.trx_status = 'not paid' THEN b.late_bills ELSE NULL END) AS late_count,
    SUM(CASE WHEN b.trx_status = 'waiting' OR b.trx_status = 'not paid' THEN b.trx_amount ELSE 0 END) + SUM(CASE WHEN b.trx_status = 'not paid' THEN b.late_bills ELSE 0 END) AS total_charge
FROM
    bills b JOIN classes c on b.class = c.id
WHERE
    MONTH(b.payment_due) <= '$month' AND
    YEAR(b.payment_due) <= '20$year'
GROUP BY
    CONCAT(c.va_prefix_name ,b.student_name), b.parent_phone, b.virtual_account
";

// Menjalankan query untuk mendapatkan data tagihan
$bills = read($sql);

// Jika tidak ada data tagihan, mengembalikan pesan bahwa data tagihan kosong
if(empty($bills)){
    $response = array(
        'status' => false,
        'message' => 'Data tagihan bulan ini kosong'
    );
    echo json_encode($response);
    exit(); // Menghentikan eksekusi lebih lanjut
}

// Menyiapkan data untuk dikirim ke Midtrans
foreach ($bills as $bill){
    $date = date("c"); // Mendapatkan tanggal saat ini dalam format ISO 8601

    $dueDate = new DateTime($bill['payment_due']);
    $nowDate = new DateTime();
    $interval = $dueDate->diff($nowDate);
    $interval = $interval->format('%a');

    // Menyusun data tagihan untuk Midtrans
    $data[] = array(
        'payment_type' => 'bank_transfer',
        'transaction_details' => array(
            'order_id' => $bill['trx_id'].'/'.$date,
            'gross_amount' => $bill['total_charge'],
        ),
        'custom_expiry' => array(
            'unit' => 'days',
            'expiry_duration' => (int)$interval+1, 
        ),
        'customer_details' => array(
            'first_name' => $bill['student_name'],
            'phone' => $bill['parent_phone']
        ),
        'bank_transfer' => array(
            'bank' => 'bni',
        ),
        'bni_va' => array(
            'va_number' => $bill['virtual_account'],
        ), 
        "custom_field1" => "Pembayaran ".$bill['student_name'] 
    );
}

// Mengirimkan data tagihan ke Midtrans dan menangani responsenya
$chargeResponse = [];

foreach ($data as $charge) {
    try {
        $chargeResponse[] = CoreApi::charge($charge);
    } catch (Exception $e) {
        // Mengembalikan pesan error jika gagal mengirimkan data tagihan ke Midtrans
        $response = [
            'status' => false,
            'message' => $e->getMessage(),
        ];
        echo json_encode($response);
        exit(); // Menghentikan eksekusi lebih lanjut
    }
}

// Menyimpan log administrasi untuk pencatatan
$adminLog = "INSERT INTO administrations(admin_code, type) VALUES ('$admin_code', 'charge')";
crud($adminLog);

// Mengembalikan response JSON dengan status berhasil
$response = array(
    'status' => true,
    'message' => 'Berhasil menambahakan tagihan',
    'data' => $chargeResponse
);

echo json_encode($response);
?>
