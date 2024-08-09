<?php

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

/*
 * Database Setup
 */

$hostname = "localhost";
$username = "root";
$password = "";
$db_name  = "tagihan_nusput";

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