<?php

require_once 'session.php';
require_once 'parse-env.php';

session_start();

$current_page = basename($_SERVER['PHP_SELF']);

$current_date = date('Y-m-d');
list($year, $month, $day) = explode('-', $current_date);

if ($month > 6){
    $tahun_ajaran = $year."/".($year+1);
    $semester = "Gasal";
} else {
    $tahun_ajaran = ($year-1)."/".$year;
    $semester = "Genap";
}

$months = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember',
];

/*
 * Database Setup
 */

$hostname = getenv('DB_HOSTNAME') ?: 'localhost';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$db_name  = getenv('DB_NAME') ?: 'tagihan_nusput';

$conn = new mysqli($hostname, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/**
 * This function executes a SELECT query on the database and returns the result as an associative array.
 *
 * @param string $query The SQL SELECT query to be executed.
 *
 * @return array An associative array containing the result of the query. Each row is represented as an associative array.
 *               If the query returns no rows, an empty array is returned.
 */
function read($query) {
    global $conn;
    $result = $conn->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

/**
 * Executes a CRUD (Create, Read, Update, Delete) query on the database.
 *
 * This function is used to perform any type of SQL query on the database.
 * It is designed to handle both SELECT and non-SELECT queries.
 *
 * @param string $query The SQL query to be executed.
 *
 * @return mixed The result of the query.
 *               - For SELECT queries, an associative array containing the result set is returned.
 *               - For non-SELECT queries (INSERT, UPDATE, DELETE), the function returns TRUE if the query was successful,
 *                 or FALSE if there was an error.
 *               - If the database connection fails, the function will return FALSE.
 */
function crud($query) {
    global $conn;
    return $conn->query($query);
}

function returnError() {
    header("HTTP/1.1 404 Not Found");
    exit(404);
}