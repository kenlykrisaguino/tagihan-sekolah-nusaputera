<?php

include_once '../config/app.php'; // Memasukkan konfigurasi aplikasi
include_once '../config/fonnte.php'; // Memasukkan konfigurasi fonnte untuk fungsi pengiriman pesan

header('Content-Type: application/json'); // Mengatur header untuk output JSON

// Mengambil parameter 'type' dari query string, jika ada
$force_notify = $_GET['type'] ?? '';

// Mendapatkan tanggal sekarang
$modify_month = new DateTime();
$now = $modify_month->format('Y-m-d');

// Menghitung tanggal-tanggal penting untuk notifikasi
$modify_month->modify('first day of this month');
$firstDate = $modify_month->format('Y-m-d');

$modify_month->modify('+8 days');
$dayBefore = $modify_month->format('Y-m-d');

$modify_month->modify('+2 days');
$dayAfter = $modify_month->format('Y-m-d');

$modify_month->modify('-1 day');
$lastDate = $modify_month->format('Y-m-d');

// Daftar nama bulan
$months = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
];

// Mendapatkan bulan dan hari saat ini
$currentDate = new DateTime();
$day_int = intval($currentDate->format('d'));
$month_int = intval($currentDate->format('m'));

// Menentukan bulan pembayaran berdasarkan tanggal
// $moreThanDue = $day_int > 10;
// $currentMonth = $months[$moreThanDue ? $month_int - 1 : ($month_int - 2  == -1 ? 11 : $month_int - 2)];
$currentMonth = $months[$month_int - 1];

// Menyiapkan pesan notifikasi
$message = "Pembayaran SPP bulan $currentMonth ";
$message_complete = "Pembayaran SPP bulan $currentMonth ";
$msgValid = false;

// Menentukan kondisi untuk pengiriman notifikasi berdasarkan parameter atau tanggal
if ($force_notify != '') {
    $ifFirstDate = $force_notify == 'first_day';
    $ifDayBefore = $force_notify == 'day_before';
    $ifDayAfter = $force_notify == 'day_after';
} else {
    $ifFirstDate = $now == $firstDate;
    $ifDayBefore = $now == $dayBefore;
    $ifDayAfter = $now == $dayAfter;
}

// Menentukan pesan berdasarkan kondisi yang dipenuhi
if ($ifFirstDate) {
    $message .= "telah dibuka dan akan berakhir di tanggal *$lastDate*. ";
    $msgValid = true;
} else if ($ifDayBefore) {
    $message .= "akan berakhir besok, *$lastDate*. ";
    $msgValid = true;
} else if ($ifDayAfter) {
    $message .= "belum dibayarkan. ";
    $message_complete .= "telah dibayarkan. ";
    $msgValid = true;
}
$message .= "Diharapkan dapat melakukan pembayaran sebagai berikut: \n\n";

// Jika tidak valid, mengirimkan respons dengan status false
if (!$msgValid) {
    $data = [
        "status" => false,
        "message" => "Tidak mengirimkan notifikasi pembayaran"
    ];
    echo json_encode($data);
    exit();
}

// Query untuk mengambil data pembayaran yang perlu dibayar
$query = "SELECT
b.virtual_account, CONCAT(c.va_prefix_name, b.student_name) AS student_name, 
b.parent_phone,
SUM(CASE WHEN b.trx_status = 'not paid' THEN c.late_bills ELSE 0 END) + SUM(CASE WHEN b.trx_status = 'waiting' OR b.trx_status = 'not paid' THEN b.trx_amount ELSE 0 END) as total_payment
FROM bills b
JOIN classes c ON b.class = c.id
WHERE b.payment_due <= '$lastDate 23:59:59' 
GROUP BY
b.virtual_account, CONCAT(c.va_prefix_name, b.student_name), 
b.parent_phone
";

// Menjalankan query dan mendapatkan hasil
$result = read($query);

// Menyiapkan data pesan
$msgData = [];

foreach ($result as $row) {
    $paymentInRupiah = formatToRupiah($row['total_payment']);
    $va = $row['virtual_account'];
    $va_name = $row['student_name'];
    $usermsg = $message . "Total Pembayaran: $paymentInRupiah\nVirtual Account: BNI *$va* atas nama *$va_name*";
    $msgData[] = [
        'target' => $row['parent_phone'],
        'message' => $usermsg,
        'delay' => '1'
    ];
}


if ($ifDayAfter) {
    $payment_due = "$lastDate 23:59:59";
    $query = "SELECT b.student_name, b.parent_phone, p.trx_timestamp FROM payments p JOIN bills b ON b.id = p.bill_id WHERE b.payment_due IN ('$payment_due')";

    $completed = read($query);

    foreach ($completed as $row) {
        $student_name = $row['student_name'];
        $trx_time = $row['trx_timestamp'];
        $usermsg = $message_complete . "Terima kasih kepada orang tua $student_name yang telah melakukan pembayaran pada tanggal *$trx_time*";
        $msgData[] = [
            'target' => $row['parent_phone'],
            'message' => $usermsg,
            'delay' => '1'
        ];
    }
}

// Jika tidak ada data untuk dikirim, mengirimkan respons dengan status false
if (empty($msgData)) {
    $data = [
        "status" => false,
        "message" => "Tidak ada pembayaran yang harus dibayar",
    ];
    echo json_encode($data);
    exit();
}

// Mengirimkan pesan
$messages = json_encode($msgData);
$a = sendMessage(['data' => $messages]);

// Mengirimkan respons dengan status true
$data = [
    "status" => true,
    "message" => "Notifikasi pembayaran spp bulan $currentMonth berhasil dikirim",
    "data" => $a,
    "s" => $msgData
];

echo json_encode($data);
