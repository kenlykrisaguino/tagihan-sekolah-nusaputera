<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once './config/app.php';

    $nis = $_POST['nis'] ?? null;
    $name = $_POST['nama'] ?? null;
    $level = $_POST['jenjang'] ?? null;
    $class = $_POST['tingkat'] ?? null;
    $major = $_POST['kelas'] ?? null;
    $birthdate = $_POST['birth_date'] ?? null;
    $phone_number = $_POST['phone_number'] ?? '';
    $email_address = $_POST['email_address'] ?? '';
    $parent_phone = $_POST['parent_phone'] ?? null;
    $address = $_POST['address'] ?? null;

    $va = getenv('MIDTRANS_PREFIX_VA_BNI') . '2223' . $nis;
    $password = password_hash($nis, PASSWORD_DEFAULT);

    $classQuery = 'SELECT id from classes WHERE TRUE';
    $classQuery .= $level ? " AND level = '$level'" : '';
    $classQuery .= $class ? " AND name = '$class'" : '';
    $classQuery .= $major ? " AND major = '$major'" : '';
    $class_id = read($classQuery)[0]['id'] ?? 1;

    $userQuery = "INSERT INTO users(
        nis, name, address,
        birthdate, status, class,
        phone_number, email_address, parent_phone,
        virtual_account, period, semester, password
    ) VALUES
    ('$nis', '$name', '$address',
    '$birthdate', 'Active', '$class_id',
    '$phone_number', '$email_address', '$parent_phone',
    '$va', '$tahun_ajaran', '$semester', '$password')";

    if (!crud($userQuery)) {
        $_SESSION['error'] = 'Data gagal disimpan.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Generate Bills
    $semester_month = $semester == 'Genap' ? [1, 2, 3, 4, 5, 6] : [7, 8, 9, 10, 11, 12];
    $first_month = $semester_month[0];

    $users = "SELECT
    u.nis, u.virtual_account, u.name, u.parent_phone, u.phone_number,
    u.email_address, c.monthly_bills, c.id AS class, c.level as level, c.late_bills AS late_bills
    FROM users u
    INNER JOIN classes c ON u.class = c.id
    WHERE
    u.status = 'active' AND
    u.nis = '$nis'";
    $usersResult = read($users);
    $input = '';

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

            $query_duedate = $due_date->format('Y-m-d') . ' 23:59:59';

            $trx_status = '';
            $lateBills = 0;
            if ($month_num < $first_month) {
                $trx_status = 'not paid';
                $lateBills = $user['late_bills'];
            } elseif ($month_num > $first_month) {
                $trx_status = 'inactive';
            } else {
                $trx_status = 'waiting';
            }

            var_dump($month_num);
            exit();

            $trx_id = generateTrxID($user['level'], $user['nis'], $month_num);
            $input .= "(
            '$user[nis]', '$trx_id', '$user[virtual_account]',
            '{$user['name']}', '$user[parent_phone]', '$user[phone_number]',
            '{$user['email_address']}', '{$user['monthly_bills']}', '$trx_status',
            'Pembayaran tahun ajaran $tahun_ajaran Semester $semester bulan $months[$num_padded]', '{$user['class']}', '$tahun_ajaran',
            '$semester', '$query_duedate', '$lateBills'
        ), ";
        }
    }

    $sql = "INSERT INTO bills(
        nis, trx_id, virtual_account,
        student_name, parent_phone, student_phone,
        student_email, trx_amount, trx_status,
        description, class, period,
        semester, payment_due, late_bills
    ) VALUES
    $input";

    $sql = rtrim($sql, ', ');

    $result = crud($sql);

    $_SESSION['success'] = "Berhasil menambahkan $name ke data siswa.";
    header('Location: ./rekap-siswa.php');
    exit();
}

function generateTrxID($level, $nis, $month)
{
    global $year;
    global $semester;

    $trimmed_year = substr($year, -2);
    $curr_semester = $semester == 'Gasal' ? '1' : '2';
    $semester_month = (($month - 1) % 6) + 1;

    $trx_id = "$level/$trimmed_year/$curr_semester/$semester_month/$nis";

    return $trx_id;
}
