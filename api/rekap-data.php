<?php

include_once '../config/app.php';

// Set header untuk format output JSON
header('Content-Type: application/json');

// Mengambil parameter dari query string
$search = isset($_GET['search']) ? $_GET['search'] : '';
$tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '';
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';
$month = isset($_GET['month']) ? $_GET['month'] : '';
$level = isset($_GET['level']) ? $_GET['level'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';
$major = isset($_GET['major']) ? $_GET['major'] : '';
$sort_by = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'virtual_account';
$sort_direction = isset($_GET['sortDir']) && strtolower($_GET['sortDir']) === 'asc' ? 'ASC' : 'DESC'; 

// Menyiapkan query tambahan berdasarkan bulan, level, kelas, dan major
$additional_query = '';

if ($month != '') {
    $additional_query = "AND MONTH(b.payment_due) = $month";
}

if ($level != '') {
    $additional_query .= " AND c.level = '$level'";
}

if ($class != '') {
    $additional_query .= " AND c.name = '$class'";
}

if ($major != '') {
    $additional_query .= " AND c.major = '$major'";
}

// Query utama untuk mengambil data
$sql = "
    SELECT
        u.virtual_account,
        u.name AS student_name, 
        CONCAT(COALESCE(c.level, ''), ' ', COALESCE(c.name, ''), ' ', COALESCE(c.major, '')) AS class, 
        u.parent_phone,
        SUM(CASE 
            WHEN b.trx_status = 'paid' OR b.trx_status = 'late' THEN b.trx_amount 
            ELSE 0 
        END) + SUM(CASE 
            WHEN b.trx_status = 'late' THEN c.late_bills 
            ELSE 0 
        END) AS penerimaan, 
        SUM(CASE 
            WHEN b.trx_status = 'not paid' THEN c.late_bills 
            ELSE 0 
        END) AS tunggakan
    FROM 
        bills b
        JOIN users u ON b.nis = u.nis
        JOIN classes c ON b.class = c.id
    WHERE 
        b.period = '$tahun_ajaran' AND 
        b.semester = '$semester' AND
        u.student_name LIKE '%$search%'
        $additional_query
    GROUP BY 
        u.virtual_account, 
        u.name,
        CONCAT(COALESCE(c.level, ''), ' ', COALESCE(c.name, ''), ' ', COALESCE(c.major, '')), 
        u.parent_phone, 
        b.period
    ORDER BY $sort_by $sort_direction;
";

// Menjalankan query dan mengambil hasilnya
$result = read($sql);

// Menyiapkan data untuk output JSON
$data = [
    'status' => true,
    'message' => 'Get Input Data',
    'data' => $result
];

// Mengoutput data dalam format JSON
echo json_encode($data);
?>
