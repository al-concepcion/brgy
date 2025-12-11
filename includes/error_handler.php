<?php
// Error logging and handling

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Don't display errors in production (comment out for development)
// ini_set('display_errors', 0);

// For development, show errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Create logs directory if it doesn't exist
$log_dir = __DIR__ . '/../logs';
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}

// Custom error handler
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    $error_message = date('Y-m-d H:i:s') . " - Error [$errno]: $errstr in $errfile on line $errline\n";
    error_log($error_message, 3, __DIR__ . '/../logs/php_errors.log');
    
    // For critical errors, show user-friendly message
    if ($errno === E_ERROR || $errno === E_PARSE || $errno === E_CORE_ERROR) {
        echo "An unexpected error occurred. Please try again later.";
        exit();
    }
}

// Custom exception handler
function custom_exception_handler($exception) {
    $error_message = date('Y-m-d H:i:s') . " - Exception: " . $exception->getMessage() . 
                    " in " . $exception->getFile() . " on line " . $exception->getLine() . "\n";
    error_log($error_message, 3, __DIR__ . '/../logs/php_errors.log');
    
    echo "An unexpected error occurred. Please try again later.";
}

// Set handlers
set_error_handler('custom_error_handler');
set_exception_handler('custom_exception_handler');

// Log database queries for debugging (optional)
function log_query($query, $params = []) {
    $log_message = date('Y-m-d H:i:s') . " - Query: $query\n";
    if (!empty($params)) {
        $log_message .= "Parameters: " . json_encode($params) . "\n";
    }
    error_log($log_message, 3, __DIR__ . '/../logs/queries.log');
}
?>
