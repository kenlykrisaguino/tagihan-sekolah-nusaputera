<?php

include_once '../config/app.php';

// Set header untuk format output JSON
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);


// Mengambil parameter dari query string
$updated_by = $data['updated_by'];
$nis = $data['nis'] ?? '';
$fees = $data['additional_fee'] ?? [];

$name_query = "SELECT * FROM additional_payment_category";
$names = read($name_query);
$fee_name = [];

foreach ($names as $name){
    $fee_name[$name['id']] = $name['category_name'];
}

$fees_arr = [];
$fee_total = 0;

foreach($fees as $fee){
    $fee_total += $fee['amount'];
    $fees_arr[] = array(
        'type' => $fee['type'],
        'name' => $fee_name[$fee['type']],
        'amount' => $fee['amount'],
        'years' => $fee['years'],
        'months' => $fee['months'],
    );
}
$fees_json = json_encode($fees_arr);

$query = "UPDATE users 
SET 
additional_fee_details = '$fees_json'
WHERE nis = '$nis'";

$result = crud($query);

// Mengelompokkan data menjadi per tahun ajaran -> bulan
$data = [];
$amount = [];

foreach ($fees as $fee) {
    foreach ($fee['years'] as $year) {
        foreach ($fee['months'] as $month) {

            $data[$year][$month][] = array(
                'type' => $fee['type'],
                'name' => $fee_name[$fee['type']],
                'amount' => $fee['amount']
            );

            if (!isset($amount[$year][$month])) {
                $amount[$year][$month] = 0;
            }

            $amount[$year][$month] += $fee['amount'];
        }
    }
}

// Memasukan record tambahan ke tiap bills
$detail_query  = [];
$amount_query = [];

foreach ($data as $year => $months) {
    foreach ($months as $month => $details) {
        $detail_json = json_encode($details);
        $detail_query[] = "WHEN period = '$year' AND MONTH(payment_due) = '$month' THEN '$detail_json'";
    }
}

foreach ($amount as $year => $months) {
    foreach ($months as $month => $amount) {
        $amount_query[] = "WHEN period = '$year' AND MONTH(payment_due) = '$month' THEN $amount";
    }
}

$billreset = "UPDATE bills
SET
    additional_fee_details = NULL,
    additional_fee_amount = 0
WHERE nis = '$nis'
";

crud($billreset);

$bills_query = "UPDATE bills
SET
    additional_fee_details = CASE
        " . implode("\n        ", $detail_query) . "
        ELSE additional_fee_details
    END,
    additional_fee_amount = CASE
        " . implode("\n        ", $amount_query) . "
        ELSE additional_fee_amount
    END;
";

$result = crud($bills_query);

$output = [];
if($result) {
    $output = [
        'status' => true,
        'message' => 'Tagihan tambahan berhasil diubah',
        'data' => $result
    ];
} else {
    $output = [
        'status' => false,
        'message' => 'Tagihan tambahan gagal diubah'
    ];
}

// Membuat log activity
$fee_json = json_encode($fees);
$log = "INSERT INTO activity_log(activity_by, activity) VALUES
('$updated_by', 'Mengupdate biaya tambahan untuk NIS $nis menjadi $fee_json')";

// Menjalankan query SQL untuk menulis log
crud($log);

echo json_encode($output);
exit();
?>
