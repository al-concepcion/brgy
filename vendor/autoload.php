<?php
/**
 * Autoloader for PHPMailer (Manual Installation)
 */

// Check if Composer autoload exists
if (file_exists(__DIR__ . '/autoload_real.php')) {
    require_once __DIR__ . '/autoload_real.php';
    return;
}

// Manual autoload for PHPMailer
$phpmailer_base = __DIR__ . '/phpmailer/src/';

if (file_exists($phpmailer_base . 'PHPMailer.php')) {
    require_once $phpmailer_base . 'PHPMailer.php';
    require_once $phpmailer_base . 'SMTP.php';
    require_once $phpmailer_base . 'Exception.php';
} else {
    // PHPMailer not installed - this is OK, email functions will be skipped
    return;
}
?>
