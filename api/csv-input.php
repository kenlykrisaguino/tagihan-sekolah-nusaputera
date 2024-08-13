<?php

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['input'])) {
        $fileTmpPath = $_FILES['input']['tmp_name'];
        $fileName = $_FILES['input']['name'];

        $csvData = file_get_contents($fileTmpPath);

        echo json_encode(['fileName' => $fileName, 'fileContent' => $csvData]);
    } else {
        echo json_encode(['error' => 'No file uploaded.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}