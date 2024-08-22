<?php

require_once '../config/app.php';

$jsonData = file_get_contents('php://input');

$apiResponse = json_decode($jsonData, true);

if ($apiResponse !== null) {
    if ($apiResponse['status_code'] == 200){
        
    }
} else {
    echo "Failed to decode JSON or no data received.";
}
?>