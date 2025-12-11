<?php
// Direct email test without database
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'jyalapag@kld.edu.ph');
define('SMTP_PASSWORD', 'pvgefvmzxrwdzjij');
define('FROM_EMAIL', 'jyalapag@kld.edu.ph');
define('FROM_NAME', 'Barangay Santo Niño 1 E-Services');

echo "=== DIRECT EMAIL TEST ===\n\n";

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';
    
    // Recipients
    $mail->setFrom(FROM_EMAIL, FROM_NAME);
    $mail->addAddress(SMTP_USERNAME, 'Test User');
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email - Barangay System';
    $mail->Body    = '<h1>Test Email</h1><p>This is a test email.</p><p>Sent at: ' . date('Y-m-d H:i:s') . '</p>';
    
    $mail->send();
    echo "\n\n✓✓✓ EMAIL SENT SUCCESSFULLY! ✓✓✓\n";
    echo "Check your inbox: " . SMTP_USERNAME . "\n";
    
} catch (Exception $e) {
    echo "\n\n✗✗✗ EMAIL FAILED! ✗✗✗\n";
    echo "Error: {$mail->ErrorInfo}\n";
    echo "Exception: {$e->getMessage()}\n";
}
?>
