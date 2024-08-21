<?php

function parseEnvFile($filePath) {
    $variables = [];
    if (file_exists($filePath)) {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && $line[0] !== '#') {
                list($key, $value) = explode('=', $line, 2);
                $variables[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
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
        putenv("$key=$value");
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    exit("Error loading environment variables.");
}
