<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Complete Email Test</title>
    <meta http-equiv="refresh" content="0">
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { background: #d4edda; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; }
        h1 { color: #333; }
        .btn { padding: 12px 24px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #0056b3; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        table td { padding: 8px; border-bottom: 1px solid #ddd; }
        table td:first-child { font-weight: bold; width: 200px; }
    </style>
</head>
<body>
    <h1>🔍 Complete Email System Test</h1>
    
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Test 1: Load email configuration
    echo "<div class='box'>";
    echo "<h2>Step 1: Loading Email Configuration</h2>";
    
    $config_loaded = false;
    if (file_exists('includes/email.php')) {
        require_once 'includes/email.php';
        $config_loaded = true;
        echo "<p class='success'>✓ Email configuration loaded</p>";
    } else {
        echo "<p class='error'>✗ includes/email.php not found</p>";
    }
    echo "</div>";
    
    // Test 2: Check PHP Extensions
    echo "<div class='box'>";
    echo "<h2>Step 2: PHP Extension Check</h2>";
    echo "<table>";
    
    $openssl_loaded = extension_loaded('openssl');
    echo "<tr><td>OpenSSL Extension</td><td>" . ($openssl_loaded ? "<span style='color: green;'>✓ Enabled</span>" : "<span style='color: red;'>✗ DISABLED (CRITICAL!)</span>") . "</td></tr>";
    
    $sockets_loaded = extension_loaded('sockets');
    echo "<tr><td>Sockets Extension</td><td>" . ($sockets_loaded ? "<span style='color: green;'>✓ Enabled</span>" : "<span style='color: orange;'>⚠ Not loaded</span>") . "</td></tr>";
    
    echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
    echo "</table>";
    
    if (!$openssl_loaded) {
        echo "<div class='error' style='margin-top: 15px;'>";
        echo "<h3>⚠ CRITICAL: OpenSSL is NOT enabled!</h3>";
        echo "<p><strong>This is preventing emails from being sent.</strong></p>";
        echo "<p><strong>Fix:</strong> I've already enabled it in php.ini, but Apache needs to be restarted.</p>";
        echo "<p><strong>Manual restart:</strong> Open XAMPP Control Panel → Stop Apache → Start Apache</p>";
        echo "</div>";
    }
    echo "</div>";
    
    // Test 3: PHPMailer Check
    echo "<div class='box'>";
    echo "<h2>Step 3: PHPMailer Check</h2>";
    echo "<table>";
    
    $phpmailer_exists = class_exists('PHPMailer\\PHPMailer\\PHPMailer');
    echo "<tr><td>PHPMailer Class</td><td>" . ($phpmailer_exists ? "<span style='color: green;'>✓ Available</span>" : "<span style='color: red;'>✗ Not Found</span>") . "</td></tr>";
    
    $send_function = function_exists('send_email');
    echo "<tr><td>send_email() Function</td><td>" . ($send_function ? "<span style='color: green;'>✓ Available</span>" : "<span style='color: red;'>✗ Not Found</span>") . "</td></tr>";
    
    echo "</table>";
    echo "</div>";
    
    // Test 4: SMTP Configuration
    if ($config_loaded) {
        echo "<div class='box'>";
        echo "<h2>Step 4: SMTP Configuration</h2>";
        echo "<table>";
        echo "<tr><td>SMTP Host</td><td>" . (defined('SMTP_HOST') ? SMTP_HOST : 'NOT SET') . "</td></tr>";
        echo "<tr><td>SMTP Port</td><td>" . (defined('SMTP_PORT') ? SMTP_PORT : 'NOT SET') . "</td></tr>";
        echo "<tr><td>Username</td><td>" . (defined('SMTP_USERNAME') ? SMTP_USERNAME : 'NOT SET') . "</td></tr>";
        echo "<tr><td>Password</td><td>" . (defined('SMTP_PASSWORD') && !empty(SMTP_PASSWORD) ? 'Set (' . strlen(SMTP_PASSWORD) . ' characters)' : 'NOT SET') . "</td></tr>";
        echo "<tr><td>From Email</td><td>" . (defined('FROM_EMAIL') ? FROM_EMAIL : 'NOT SET') . "</td></tr>";
        echo "<tr><td>From Name</td><td>" . (defined('FROM_NAME') ? FROM_NAME : 'NOT SET') . "</td></tr>";
        echo "</table>";
        echo "</div>";
    }
    
    // Test 5: Send Test Email
    if (isset($_POST['send_email'])) {
        echo "<div class='box'>";
        echo "<h2>Step 5: Sending Test Email...</h2>";
        
        if (!$openssl_loaded) {
            echo "<div class='error'>";
            echo "<h3>✗ Cannot send email - OpenSSL not enabled</h3>";
            echo "<p>Please restart Apache in XAMPP Control Panel first.</p>";
            echo "</div>";
        } elseif (!$phpmailer_exists) {
            echo "<div class='error'>";
            echo "<h3>✗ Cannot send email - PHPMailer not loaded</h3>";
            echo "</div>";
        } elseif (!$send_function) {
            echo "<div class='error'>";
            echo "<h3>✗ Cannot send email - send_email() function not available</h3>";
            echo "</div>";
        } else {
            $test_email = $_POST['test_email'] ?? SMTP_USERNAME;
            
            echo "<p>Attempting to send email to: <strong>" . htmlspecialchars($test_email) . "</strong></p>";
            
            // Capture output
            ob_start();
            $result = send_email(
                $test_email,
                'Test User',
                '✅ Test Email from Barangay System - ' . date('H:i:s'),
                '<html><body style="font-family: Arial;"><h1 style="color: #28a745;">✓ Email Test Successful!</h1><p>Your Barangay E-Services email system is working correctly!</p><p><strong>Sent at:</strong> ' . date('Y-m-d H:i:s') . '</p><p>All email notifications will now work:</p><ul><li>Registration welcome emails</li><li>Application confirmations</li><li>Status updates</li><li>Release notifications</li></ul></body></html>'
            );
            $output = ob_get_clean();
            
            if ($result) {
                echo "<div class='success'>";
                echo "<h3>✓✓✓ EMAIL SENT SUCCESSFULLY! ✓✓✓</h3>";
                echo "<p><strong>Sent to:</strong> " . htmlspecialchars($test_email) . "</p>";
                echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
                echo "<p>✓ Check your inbox (and spam/junk folder)</p>";
                echo "<p>✓ All email notifications are now working!</p>";
                echo "</div>";
                
                if (!empty($output)) {
                    echo "<details><summary>Debug Output</summary><pre>" . htmlspecialchars($output) . "</pre></details>";
                }
            } else {
                echo "<div class='error'>";
                echo "<h3>✗ Email sending failed</h3>";
                
                if (!empty($output)) {
                    echo "<pre>" . htmlspecialchars($output) . "</pre>";
                }
                
                echo "<p><strong>Common issues:</strong></p>";
                echo "<ol>";
                echo "<li>Gmail App Password incorrect - must be 16 characters without spaces</li>";
                echo "<li>2-Step Verification not enabled on Gmail account</li>";
                echo "<li>Firewall blocking port 587</li>";
                echo "</ol>";
                echo "</div>";
            }
        }
        echo "</div>";
    }
    ?>
    
    <div class="box">
        <h2>Send Test Email</h2>
        <form method="POST">
            <label><strong>Email Address:</strong></label><br>
            <input type="email" name="test_email" value="<?php echo defined('SMTP_USERNAME') ? SMTP_USERNAME : ''; ?>" required style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px;"><br>
            <button type="submit" name="send_email" class="btn">📧 Send Test Email Now</button>
        </form>
    </div>
    
    <div class="box info">
        <h3>📋 Summary</h3>
        <p><strong>Status:</strong> 
        <?php
        if (!$openssl_loaded) {
            echo "<span style='color: red;'>⚠ OpenSSL needs to be enabled - Restart Apache!</span>";
        } elseif (!$phpmailer_exists) {
            echo "<span style='color: red;'>✗ PHPMailer not installed</span>";
        } else {
            echo "<span style='color: green;'>✓ System ready to send emails</span>";
        }
        ?>
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 20px;">
        <a href="email-diagnostic.php" style="color: #007bff;">Full Diagnostic</a> | 
        <a href="index.php" style="color: #007bff;">Back to Home</a>
    </div>
    
    <script>
    // Auto-refresh if OpenSSL was just enabled
    <?php if (isset($_GET['refreshed'])): ?>
        console.log('Page refreshed after configuration change');
    <?php elseif (!$openssl_loaded && !isset($_POST['send_email'])): ?>
        // Only auto-refresh once
        if (!window.location.search.includes('refreshed')) {
            setTimeout(function() {
                window.location.href = window.location.href + '?refreshed=1';
            }, 2000);
        }
    <?php endif; ?>
    </script>
</body>
</html>
