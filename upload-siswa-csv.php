<?php

include_once './config/app.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['data']) && $_FILES['data']['error'] === UPLOAD_ERR_OK) { // Check if file upload was successful
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
                // Redirect back to the previous page if CSV has no data
                $_SESSION['error'] = 'No data found in the CSV file.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            // Simulating adding users to the database
            $users = [];

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

                $va = "988110562223" . $nis;

                $classQuery = "SELECT id from classes WHERE level = '$level' AND name = '$class' AND major = '$major';";
                $class_id = read($classQuery)[0]['id'] ?? 1;

                $password = password_hash($va, PASSWORD_DEFAULT);

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

            $query .= implode(", ", $users);

            if (crud($query)) {
                // Redirect to success page on success
                // Count total data 
                $totalData = count($csvData);
                $_SESSION['success'] = "Berhasil menambahkan $totalData data siswa.";
                header('Location: ./rekap-siswa.php');
                exit;
            } else {
                // Redirect back to the previous page on failure
                $_SESSION['error'] = 'Data gagal disimpan.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

        } else {
            // Redirect back if file processing fails
            $_SESSION['error'] = 'Failed to process the uploaded file.';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

    } else {
        // Redirect back if no file or file upload error occurs
        $_SESSION['error'] = 'Tidak ada file atau terjadi kesalahan saat mengunggah.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // Redirect back if the request method is invalid
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
