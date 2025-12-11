<!DOCTYPE html>
<html>
<head>
    <title>Email Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .result { padding: 20px; margin: 20px 0; border-radius: 5px; }
        .success { background: #d4edda; border: 2px solid #28a745; color: #155724; }
        .error { background: #f8d7da; border: 2px solid #dc3545; color: #721c24; }
        pre { background: #fff; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Email System Test</h1>
    <?php
    require_once 'includes/email.php';
    
    echo "<h3>Configuration Check:</h3>";
    echo "<ul>";
    echo "<li>PHPMailer Class: " . (class_exists('PHPMailer\PHPMailer\PHPMailer') ? '✓ Loaded' : '✗ Not Found') . "</li>";
    echo "<li>OpenSSL Extension: " . (extension_loaded('openssl') ? '✓ Enabled' : '✗ DISABLED - THIS IS THE PROBLEM!') . "</li>";
    echo "<li>send_email Function: " . (function_exists('send_email') ? '✓ Available' : '✗ Not Found') . "</li>";
    echo "<li>SMTP Host: " . (defined('SMTP_HOST') ? SMTP_HOST : 'Not configured') . "</li>";
    echo "<li>SMTP Port: " . (defined('SMTP_PORT') ? SMTP_PORT : 'Not configured') . "</li>";
    echo "</ul>";
    
    if (isset($_POST['send_test'])) {
        echo "<h3>Sending Test Email...</h3>";
        
        if (!extension_loaded('openssl')) {
            echo "<div class='result error'>";
            echo "<h4>✗ Cannot Send Email - OpenSSL Not Enabled</h4>";
            echo "<p><strong>Solution:</strong></p>";
            echo "<ol>";
            echo "<li>Open XAMPP Control Panel</li>";
            echo "<li>Click 'Config' for Apache → PHP (php.ini)</li>";
            echo "<li>Find the line: <code>;extension=openssl</code></li>";
            echo "<li>Remove the semicolon to make it: <code>extension=openssl</code></li>";
            echo "<li>Save the file</li>";
            echo "<li>Stop and Start Apache in XAMPP Control Panel</li>";
            echo "<li>Refresh this page</li>";
            echo "</ol>";
            echo "</div>";
        } else {
            $to_email = $_POST['email'] ?? SMTP_USERNAME;
            $result = send_email(
                $to_email,
                'Test User',
                'Test Email - Barangay System',
                '<h1>✓ Email Test Successful!</h1><p>Your email system is working correctly.</p><p>Sent at: ' . date('Y-m-d H:i:s') . '</p>'
            );
            
            if ($result) {
                echo "<div class='result success'>";
                echo "<h4>✓✓✓ EMAIL SENT SUCCESSFULLY!</h4>";
                echo "<p>Test email sent to: <strong>" . htmlspecialchars($to_email) . "</strong></p>";
                echo "<p>Check your inbox (and spam folder).</p>";
                echo "</div>";
            } else {
                echo "<div class='result error'>";
                echo "<h4>✗ Email Sending Failed</h4>";
                echo "<p>Check Apache error log: <code>d:\\xampp\\apache\\logs\\error.log</code></p>";
                echo "</div>";
            }
        }
    }
    ?>
    
    <form method="POST" style="background: white; padding: 20px; border-radius: 5px; max-width: 500px;">
        <h3>Send Test Email</h3>
        <label>Email Address:</label><br>
        <input type="email" name="email" value="<?php echo SMTP_USERNAME; ?>" required style="width: 100%; padding: 8px; margin: 10px 0;"><br>
        <button type="submit" name="send_test" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Send Test Email
        </button>
    </form>
    
    <div style="margin-top: 20px;">
        <a href="email-diagnostic.php">← Back to Diagnostics</a> |
        <a href="index.php">← Back to Home</a>
    </div>
</body>
</html>
