<?php
/**
 * Email Test Page
 * Use this to test if PHPMailer is working correctly
 */
require_once 'includes/config.php';

$test_result = null;
$test_email = '';
$error_details = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_email'])) {
    $test_email = $_POST['test_email'];
    
    if (function_exists('send_email')) {
        $subject = "Test Email from Barangay E-Services";
        $body = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ Email Test Successful!</h1>
                </div>
                <div style='padding: 30px; background: #f9f9f9;'>
                    <p>This is a test email from your Barangay E-Services Portal.</p>
                    <p>If you're reading this, PHPMailer is configured correctly!</p>
                    <p><strong>Test Time:</strong> " . date('Y-m-d H:i:s') . "</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Capture any errors
        ob_start();
        $test_result = send_email($test_email, 'Test User', $subject, $body);
        $error_details = ob_get_clean();
    } else {
        $test_result = false;
        $error_details = 'send_email function not available';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Test - Barangay E-Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding: 50px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .test-card { max-width: 600px; margin: 0 auto; }
        .status-box { padding: 20px; border-radius: 10px; margin: 20px 0; }
        .status-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .status-error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .status-warning { background: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card test-card shadow-lg">
            <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h3 class="mb-0"><i class="fas fa-envelope"></i> Email Test Tool</h3>
            </div>
            <div class="card-body">
                <?php if (!function_exists('send_email')): ?>
                    <div class="status-box status-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> PHPMailer Not Installed</h5>
                        <p class="mb-0">PHPMailer is not yet installed or configured.</p>
                        <hr>
                        <a href="phpmailer-setup.php" class="btn btn-primary">View Setup Instructions</a>
                    </div>
                <?php else: ?>
                    <?php if ($test_result === true): ?>
                        <div class="status-box status-success">
                            <h5><i class="fas fa-check-circle"></i> Email Sent Successfully!</h5>
                            <p>Test email was sent to: <strong><?php echo htmlspecialchars($test_email); ?></strong></p>
                            <p class="mb-0">Check your inbox (and spam folder) for the test email.</p>
                        </div>
                    <?php elseif ($test_result === false): ?>
                        <div class="status-box status-error">
                            <h5><i class="fas fa-times-circle"></i> Email Failed to Send</h5>
                            <p><strong>Troubleshooting Steps:</strong></p>
                            <ol>
                                <li><strong>Check SMTP Credentials:</strong> Verify username and password in <code>includes/email.php</code></li>
                                <li><strong>Gmail Users:</strong> Use App Password instead of regular password
                                    <ul>
                                        <li>Go to Google Account → Security → 2-Step Verification → App Passwords</li>
                                        <li>Generate new app password for "Mail"</li>
                                        <li>Use that password in <code>SMTP_PASSWORD</code></li>
                                    </ul>
                                </li>
                                <li><strong>Check Firewall:</strong> Ensure port 587 is not blocked</li>
                                <li><strong>Check Error Log:</strong> <code>d:\\xampp\\apache\\logs\\error.log</code></li>
                                <li><strong>Test SMTP:</strong> Try using a different email provider (e.g., Mailtrap for testing)</li>
                            </ol>
                            <?php if ($error_details): ?>
                            <div class="alert alert-dark mt-2">
                                <small><strong>Error Details:</strong><br><?php echo htmlspecialchars($error_details); ?></small>
                            </div>
                            <?php endif; ?>
                            <hr>
                            <a href="https://support.google.com/accounts/answer/185833" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-external-link-alt"></i> Gmail App Password Guide
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="mt-4">
                        <div class="mb-3">
                            <label class="form-label"><strong>Enter Email Address to Test:</strong></label>
                            <input type="email" name="test_email" class="form-control" 
                                   value="<?php echo htmlspecialchars($test_email); ?>" 
                                   required placeholder="your-email@example.com">
                            <small class="text-muted">A test email will be sent to this address</small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Test Email
                        </button>
                    </form>
                    
                    <hr>
                    
                    <h5>Current Configuration:</h5>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>SMTP Host:</strong></td>
                            <td><?php echo defined('SMTP_HOST') ? SMTP_HOST : 'Not configured'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>SMTP Port:</strong></td>
                            <td><?php echo defined('SMTP_PORT') ? SMTP_PORT : 'Not configured'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>From Email:</strong></td>
                            <td><?php echo defined('FROM_EMAIL') ? FROM_EMAIL : 'Not configured'; ?></td>
                        </tr>
                    </table>
                    
                    <div class="alert alert-info mt-3">
                        <strong><i class="fas fa-info-circle"></i> Configuration:</strong><br>
                        Edit SMTP settings in <code>includes/email.php</code>
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                    <a href="phpmailer-setup.php" class="btn btn-outline-primary">
                        <i class="fas fa-book"></i> Setup Guide
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
