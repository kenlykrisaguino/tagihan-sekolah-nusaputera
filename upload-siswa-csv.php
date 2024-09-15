<?php

include_once './config/app.php';
include_once './config/midtrans/Midtrans.php';

use Midtrans\Config;
use Midtrans\CoreApi;

$admin_code = $tahun_ajaran . '-' . $semester . '-create';

$total_count = 0;
$edit_count = 0;
$add_count = 0;

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
                $total_count++;
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
            u.nis IN ($nis_all)";
            $checkUsers = read($checkQuery);

            $histories = [];

            // if checkUsers is not empty
            if (!empty($checkUsers)) {
                // Preload class data into an associative array
                $classQuery = 'SELECT id, level, name, major FROM classes';
                $classData = read($classQuery);
                $classes = [];
                foreach ($classData as $class) {
                    $classes[$class['level']][$class['name']][$class['major']] = $class['id'];
                }

                $updateQuery = "UPDATE users SET 
                    name = CASE id ";
                $addressCase = 'address = CASE id ';
                $birthdateCase = 'birthdate = CASE id ';
                $phoneNumberCase = 'phone_number = CASE id ';
                $emailAddressCase = 'email_address = CASE id ';
                $parentPhoneCase = 'parent_phone = CASE id ';
                $classCase = 'class = CASE id ';

                $userIds = [];
                $histories = [];

                foreach ($checkUsers as $user) {
                    foreach ($csvData as $new) {
                        if ($user['nis'] == $new['nis']) {
                            $edit_count++;
                            $user_id = $user['id'];
                            $registered_nis[] = $user['nis'];
                            $userIds[] = $user_id;

                            $histories[] = "('$user[nis]', '$user[name]','$user[class]', 
                            '$user[phone_number]', '$user[email_address]', '$user[parent_phone]',
                            '$user[virtual_account]','$user[period]', '$user[semester]', now())";

                            // Get the class id from the preloaded class data
                            $class_id = $classes[$new['jenjang']][$new['tingkat']][$new['kelas']] ?? 1;

                            // Build the CASE statements for batch update
                            $updateQuery .= "WHEN $user_id THEN '$new[name]' ";
                            $addressCase .= "WHEN $user_id THEN '$new[alamat]' ";
                            $birthdateCase .= "WHEN $user_id THEN '$new[birthdate]' ";
                            $phoneNumberCase .= "WHEN $user_id THEN '$new[phone_number]' ";
                            $emailAddressCase .= "WHEN $user_id THEN '$new[email_address]' ";
                            $parentPhoneCase .= "WHEN $user_id THEN '$new[parent_phone]' ";
                            $classCase .= "WHEN $user_id THEN '$class_id' ";
                        }
                    }
                }

                $updateQuery .= 'END, ';
                $updateQuery .= "$addressCase END, ";
                $updateQuery .= "$birthdateCase END, ";
                $updateQuery .= "$phoneNumberCase END, ";
                $updateQuery .= "$emailAddressCase END, ";
                $updateQuery .= "$parentPhoneCase END, ";
                $updateQuery .= "$classCase END ";
                $updateQuery .= 'WHERE id IN (' . implode(', ', $userIds) . ')';

                $_SESSION['role'] == 'ADMIN' ? crud($updateQuery) : null;

                // Insert the student history
                $historyQuery = "INSERT INTO student_history(
                    nis, name, class, 
                    phone_number, email_address, parent_phone,
                    virtual_account, period, semester, updated_at
                ) VALUES";
                $historyQuery .= implode(', ', $histories);
                $_SESSION['role'] == 'ADMIN' ? crud($historyQuery) : null;
            }

            $query_runnable = false;

            foreach ($csvData as $data) {
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

                $add_count++;

                $query_runnable = true;


                $classQuery = 'SELECT id, va_identifier from classes WHERE TRUE ';
                $classQuery .= $level ? " AND level = '$level'" : '';
                $classQuery .= $class ? " AND name = '$class'" : '';
                $classQuery .= $major ? " AND major = '$major'" : '';
                $class_detail = read($classQuery)[0] ?? null;
                $va_identifier = $class_detail['va_identifier']?? 2220;
                $class_id = $class_detail['id'] ?? 1;
                
                $va = getenv('MIDTRANS_PREFIX_VA_BNI') . $va_identifier . $nis;

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

            $read = "SELECT admin_code FROM administrations WHERE admin_code='$admin_code'";

            $readResult = read($read)[0] ?? null;

            if (isset($readResult)) {
                
                foreach ($usersResult as $user) {
                    $BillQuery = "UPDATE bills SET trx_status = 'disabled' WHERE nis = $user[nis] AND trx_status = 'waiting'";
                    crud($BillQuery);
                    $BillQuery = "UPDATE bills SET trx_status = 'disabled' WHERE nis = $user[nis] AND trx_status = 'inactive'";
                    crud($BillQuery);
                    $BillQuery = "UPDATE bills SET bill_disabled = NOW() WHERE nis = $user[nis]";
                    crud($BillQuery);
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
                            $notPaidQuery = "SELECT 
                            trx_status FROM bills 
                            WHERE 
                                nis = $user[nis] AND 
                                payment_due = '$query_duedate'";
                            $old_status = read($notPaidQuery)[0]['trx_status'];
                            $trx_status = $old_status == 'paid' || $old_status ==  'not paid' ? 'disabled' : 'not paid'; 
                            $lateBills = $old_status == 'paid' || $old_status ==  'not paid' ? 0 : $user['late_bills']; 
                            
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
                if (!crud($sql)) {
                    $_SESSION['error'] = 'Data gagal disimpan.';
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit();
                }

                $totalData = count($csvData);
                $_SESSION['success'] = "Berhasil mengolah $total_count data siswa (tambah $add_count, edit $edit_count).";
                header('Location: ./rekap-siswa.php');
            } else {
                $totalData = count($csvData);
                $_SESSION['success'] = "Berhasil mengolah $total_count data siswa (tambah $add_count, edit $edit_count).";
                header('Location: ./rekap-siswa.php');
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
