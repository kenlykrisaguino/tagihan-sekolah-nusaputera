<?php

function parseEnvFile($filePath) {
    $variables = [];
    
    if (file_exists($filePath)) {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && $line[0] !== '#') {
                list($key, $value) = explode('=', $line, 2);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                
                if (strcasecmp($value, 'true') === 0 || $value === '1') {
                    $value = true;
                } elseif (strcasecmp($value, 'false') === 0 || $value === '0') {
                    $value = false;
                }
                
                $variables[trim($key)] = $value;
            }
        }
    } else {
        throw new Exception("Environment file not found: $filePath");
    }
    
    return $variables;
}

$envFile = dirname(__DIR__) . '/.env';
try {
    $envVars = parseEnvFile($envFile);

    foreach ($envVars as $key => $value) {
        putenv("$key=" . ($value === true ? '1' : ($value === false ? '0' : $value)));
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    exit("Error loading environment variables.");
}
