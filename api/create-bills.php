<?php

include_once '../config/app.php';
header('Content-Type: application/json');

$admin_code = $tahun_ajaran."-".$semester."-create";

$read = "SELECT admin_code FROM administrations WHERE admin_code='$admin_code'";

$readResult = read($read);

if ($readResult) {
    echo json_encode(['message' => 'Tagihan sudah ada']);
    exit();
}

$semester_month = $semester == 'Genap' ? [1, 2, 3, 4, 5, 6] : [7, 8, 9, 10, 11, 12];

$first_month = $semester_month[0];

$users = "SELECT
    u.nis, u.virtual_account, u.name, u.parent_phone, u.phone_number,
    u.email_address, c.monthly_bills, c.id AS class, c.level as level
    FROM users u
    INNER JOIN classes c ON u.class = c.id
    WHERE 
    u.status = 'active' AND
    u.name != 'ADMIN'";

$usersResult = read($users);
$input = "";
foreach ($usersResult as $user) {
    foreach ($semester_month as $month_num) { 
        $num_padded = str_pad($month_num, 2, '0', STR_PAD_LEFT);

        $due_date = new DateTime("$year-$num_padded-01");
        $due_date->modify('last day of this month');

        $dayOfWeek = $due_date->format('N');

        if ($dayOfWeek == 6) {
            $due_date->modify('-1 day'); 
        } elseif ($dayOfWeek == 7) {
            $due_date->modify('-2 days'); 
        }

        $query_duedate = $due_date->format('Y-m-d') . " 23:59:59";

        $trx_status = ($month_num == $first_month) ? 'waiting' : 'inactive';

        $trx_id = generateTrxID($user['level'], $user['nis'], $month_num);
        $input .= "(
            '$user[nis]', '$trx_id', '$user[virtual_account]',
            '{$user['name']}', '$user[parent_phone]', '$user[phone_number]',
            '{$user['email_address']}', '{$user['monthly_bills']}', '$trx_status',
            'Pembayaran tahun ajaran $tahun_ajaran Semester $semester bulan $months[$num_padded]', '{$user['class']}', '$tahun_ajaran',
            '$semester', '$query_duedate'
        ), ";
    }
}

$sql = "INSERT INTO bills(
    nis, trx_id, virtual_account, 
    student_name, parent_phone, student_phone,
    student_email, trx_amount, trx_status,
    description, class, period,
    semester, payment_due
) VALUES 
$input";

$sql = rtrim($sql, ", ");

$result = crud($sql);

$sql = "INSERT INTO administrations(admin_code, type) VALUES ('$admin_code', 'create')";

$result = crud($sql);

if ($result) {
    echo json_encode([
        'status' => true,
        'message' => 'Tagihan berhasil dibuat', 
        'data' => $usersResult
    ]);
} else {
    echo json_encode([
        'status' => false,
        'message' => 'Gagal membuat tagihan'
    ]);
}

function generateTrxID($level, $nis, $month){
    global $year;
    global $semester;

    $trimmed_year = substr($year, -2);
    $curr_semester = $semester == 'Gasal' ? "1" : "2";
    $semester_month = (($month-1) % 6)+1;

    $trx_id = "$level/$trimmed_year/$curr_semester/$semester_month/$nis";

    return $trx_id;
}
?>
