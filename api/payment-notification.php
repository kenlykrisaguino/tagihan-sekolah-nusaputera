<?php

include_once '../config/app.php';
include '../config/fonnte.php';

header('Content-Type: application/json');

$message = array( 
    'data' => '[ 
        { 
            "target" : "087731335955", 
            "message": "Testing", 
            "delay": "1" 
        },{ 
            "target" : "087731335955", 
            "message": "Testing 2", 
            "delay": "1" 
        }
    ]'
);

echo sendMessage($message);