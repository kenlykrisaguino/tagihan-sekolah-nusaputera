<?php

include_once './config/app.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['data']) && $_FILES['data']['error'] === UPLOAD_ERR_OK) {
        // Check if file upload was successful
        $fileTmpPath = $_FILES['data']['tmp_name'];
        $fileName = $_FILES['data']['name'];

        if (is_uploaded_file($fileTmpPath)) {
            $csvData = [];

            if (($handle = fopen($fileTmpPath, 'r')) !== false) {
                $headers = fgetcsv($handle);
                while (($row = fgetcsv($handle)) !== false) {
                    $csvData[] = array_combine($headers, $row);
                }
                fclose($handle);
            }

            if (empty($csvData)) {
                $_SESSION['error'] = 'No data found in the CSV file.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }

            $users = [];
            $nis_list = [];
            $registered_nis = [];

            // get NIS data from CSV file
            foreach ($csvData as $data) {
                $nis = $data['nis'];
                $nis_list[] = $nis;
            }

            // check if user is already registered, 
            // then move to student_history table            

            $nis_all = implode(', ', $nis_list);

            $checkQuery = "SELECT
            u.id, u.nis, u.virtual_account, u.name, u.parent_phone, u.phone_number,
            u.email_address, c.monthly_bills, c.id AS class, c.level as level, c.late_bills AS late_bills,
            u.semester, u.period
            FROM users u
            INNER JOIN classes c ON u.class = c.id
            WHERE
            u.status = 'Active' AND
            u.nis IN ($nis_all)";
            $checkUsers = read($checkQuery);

            $histories = [];
            
            // if checkUsers is not empty
            if (!empty($checkUsers)) {
                foreach ($checkUsers as $user) {
                    $user_id = $user['id'];
                    $registered_nis[] = $user['nis'];

                    

                    $histories[] = "('$user[nis]', '$user[name]','$user[class]', 
                    '$user[phone_number]', '$user[email_address]', '$user[parent_phone]',
                    '$user[virtual_account]','$user[period]', '$user[semester]', now())";
                }

                $historyQuery = "INSERT INTO student_history(
                    nis, name, class, 
                    phone_number, email_address, parent_phone,
                    virtual_account, period, semester, updated_at
                ) VALUES";
                $historyQuery.= implode(', ', $histories);
                crud($historyQuery);
            }

            $query_runnable = false;

            foreach ($csvData as $data){
                $nis = $data['nis'];
                $name = $data['name'];
                $level = $data['jenjang'];
                $class = $data['tingkat'];
                $major = $data['kelas'];
                $address = $data['alamat'];
                $birthdate = $data['birthdate'];
                $phone_number = $data['phone_number'];
                $email_address = $data['email_address'];
                $parent_phone = $data['parent_phone'];

                if (in_array($nis, $registered_nis)) {
                    continue;
                }

                $query_runnable = true;

                $va = getenv('MIDTRANS_PREFIX_VA_BNI') . '2223' . $nis;

                $classQuery = 'SELECT id from classes WHERE TRUE ';
                $classQuery .= $level ? " AND level = '$level'" : '';
                $classQuery .= $class ? " AND name = '$class'" : '';
                $classQuery .= $major ? " AND major = '$major'" : '';
                $class_id = read($classQuery)[0]['id'] ?? 1;

                $password = md5($va);

                $users[] = "('$nis', '$name', '$address', 
                '$birthdate', 'Active', '$class_id', 
                '$phone_number', '$email_address', '$parent_phone',
                '$va', '$tahun_ajaran', '$semester', '$password')";
            }
            

            $query = "INSERT INTO users(
                nis, name, address,
                birthdate, status, class,
                phone_number, email_address, parent_phone,
                virtual_account, period, semester, password
            ) VALUES";

            $query .= implode(', ', $users);

            if ($query_runnable && !crud($query)) {
                $_SESSION['error'] = 'Data gagal disimpan.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }

            $userData = "SELECT
            u.nis, u.virtual_account, u.name, u.parent_phone, u.phone_number,
            u.email_address, c.monthly_bills, c.id AS class, c.level as level, c.late_bills AS late_bills
            FROM users u
            INNER JOIN classes c ON u.class = c.id
            WHERE
            u.status = 'active' AND
            u.nis IN ($nis_all)";
            $usersResult = read($userData);

            $semester_month = $semester == 'Genap' ? [1, 2, 3, 4, 5, 6] : [7, 8, 9, 10, 11, 12];
            $first_month = $semester_month[0];
            $curr_month = $month;


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
                    if ($month_num < $curr_month) {
                        $trx_status = 'not paid';
                        $lateBills = $user['late_bills'];
                    } elseif ($month_num > $curr_month) {
                        $trx_status = 'inactive';
                    } else {
                        $trx_status = 'waiting';
                    }
        
                    $trx_id = generateTrxID($user['level'], $user['nis'], $month_num);
                    $input .= "(
                    '$user[nis]', '$trx_id', '$user[virtual_account]',
                    '{$user['name']}', '$user[parent_phone]', '$user[phone_number]',
                    '{$user['email_address']}', '{$user['monthly_bills']}', '$trx_status',
                    'Pembayaran tahun ajaran $tahun_ajaran Semester $semester bulan $months[$num_padded]', '{$user['class']}', '$tahun_ajaran',
                    '$semester', '$query_duedate', '$lateBills'), ";
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
            if(crud($sql)){
                $totalData = count($csvData);
                $_SESSION['success'] = "Berhasil menambahkan $totalData data siswa.";
                header('Location: ./rekap-siswa.php');
            } else {
                $_SESSION['error'] = 'Data gagal disimpan.';
                header('Location: '. $_SERVER['HTTP_REFERER']);
                exit();
            }

        } else {
            // Redirect back if file processing fails
            $_SESSION['error'] = 'Failed to process the uploaded file.';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
    } else {
        // Redirect back if no file or file upload error occurs
        $_SESSION['error'] = 'Tidak ada file atau terjadi kesalahan saat mengunggah.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    // Redirect back if the request method is invalid
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
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

?>
