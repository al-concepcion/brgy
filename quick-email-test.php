<?php
require_once 'includes/config.php';

echo "=== EMAIL SYSTEM TEST ===\n\n";

// Test 1: Check if email function exists
if (function_exists('send_email')) {
    echo "✓ send_email function exists\n";
} else {
    echo "✗ send_email function NOT FOUND\n";
    exit(1);
}

// Test 2: Check configuration
echo "✓ SMTP Host: " . (defined('SMTP_HOST') ? SMTP_HOST : 'NOT CONFIGURED') . "\n";
echo "✓ SMTP Port: " . (defined('SMTP_PORT') ? SMTP_PORT : 'NOT CONFIGURED') . "\n";
echo "✓ From Email: " . (defined('FROM_EMAIL') ? FROM_EMAIL : 'NOT CONFIGURED') . "\n";
echo "✓ Username: " . (defined('SMTP_USERNAME') ? SMTP_USERNAME : 'NOT CONFIGURED') . "\n";
echo "✓ Password configured: " . (defined('SMTP_PASSWORD') && !empty(SMTP_PASSWORD) ? 'YES' : 'NO') . "\n\n";

// Test 3: Send test email
echo "Sending test email to: " . SMTP_USERNAME . "...\n";

$subject = "Test Email - Barangay System";
$body = "<h1>Test Email</h1><p>This is a test email from your Barangay E-Services Portal.</p><p>Sent at: " . date('Y-m-d H:i:s') . "</p>";

$result = send_email(SMTP_USERNAME, 'Test Recipient', $subject, $body);

if ($result) {
    echo "\n✓✓✓ EMAIL SENT SUCCESSFULLY! ✓✓✓\n";
    echo "Check your inbox: " . SMTP_USERNAME . "\n";
    echo "(Also check spam/junk folder)\n";
} else {
    echo "\n✗✗✗ EMAIL SENDING FAILED! ✗✗✗\n";
    echo "Check the error log at: d:\\xampp\\apache\\logs\\error.log\n";
}

echo "\n=========================\n";
?>
