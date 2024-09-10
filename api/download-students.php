<?php

include_once '../config/app.php'; 

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=students.csv');

$sql = "
    SELECT 
        u.nis, 
        u.name, 
        c.level AS jenjang, 
        c.name AS tingkat, 
        c.major AS kelas, 
        u.address AS alamat, 
        u.birthdate, 
        u.phone_number, 
        u.email_address, 
        u.parent_phone
    FROM users u
    JOIN classes c ON u.class = c.id
    WHERE u.status = 'Active'
";

$result = read($sql);

$output = fopen('php://output', 'w');

fputcsv($output, [
    'nis', 'name', 'jenjang', 'tingkat', 'kelas', 'alamat', 
    'birthdate', 'phone_number', 'email_address', 'parent_phone'
]);

foreach ($result as $row){
    fputcsv($output, $row);
}

fclose($output);
?>
