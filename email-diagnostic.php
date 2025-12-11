<?php
/**
 * Email System Diagnostic Tool
 * This will help identify email configuration issues
 */
require_once 'includes/config.php';

$diagnostics = [
    'phpmailer_installed' => false,
    'autoload_exists' => false,
    'email_config_loaded' => false,
    'smtp_details' => [],
    'send_function_exists' => false,
    'recent_logs' => []
];

// Check 1: Vendor autoload exists
$diagnostics['autoload_exists'] = file_exists(__DIR__ . '/vendor/autoload.php');

// Check 2: PHPMailer installed
$diagnostics['phpmailer_installed'] = class_exists('PHPMailer\PHPMailer\PHPMailer');

// Check 3: Email configuration loaded
$diagnostics['email_config_loaded'] = defined('SMTP_HOST') && defined('FROM_EMAIL');

// Check 4: SMTP Details
if ($diagnostics['email_config_loaded']) {
    $diagnostics['smtp_details'] = [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT,
        'username' => SMTP_USERNAME,
        'from_email' => FROM_EMAIL,
        'from_name' => FROM_NAME,
        'password_set' => !empty(SMTP_PASSWORD),
        'password_length' => strlen(SMTP_PASSWORD ?? '')
    ];
}

// Check 5: send_email function exists
$diagnostics['send_function_exists'] = function_exists('send_email');

// Check 6: Recent error logs
$log_file = 'D:/xampp/apache/logs/error.log';
if (file_exists($log_file)) {
    $lines = file($log_file);
    $email_related = array_filter($lines, function($line) {
        return stripos($line, 'email') !== false || 
               stripos($line, 'smtp') !== false || 
               stripos($line, 'phpmailer') !== false;
    });
    $diagnostics['recent_logs'] = array_slice(array_reverse($email_related), 0, 10);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Diagnostic - Barangay E-Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding: 30px 0; background: #f5f5f5; }
        .diagnostic-card { max-width: 900px; margin: 0 auto; }
        .check-item { padding: 15px; margin: 10px 0; border-radius: 8px; }
        .check-pass { background: #d4edda; border-left: 4px solid #28a745; }
        .check-fail { background: #f8d7da; border-left: 4px solid #dc3545; }
        .check-icon { font-size: 24px; margin-right: 10px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card diagnostic-card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><i class="fas fa-stethoscope"></i> Email System Diagnostic</h3>
            </div>
            <div class="card-body">
                <h5>System Checks:</h5>
                
                <!-- Check 1 -->
                <div class="check-item <?php echo $diagnostics['autoload_exists'] ? 'check-pass' : 'check-fail'; ?>">
                    <i class="fas <?php echo $diagnostics['autoload_exists'] ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'; ?> check-icon"></i>
                    <strong>Composer Autoload:</strong>
                    <?php if ($diagnostics['autoload_exists']): ?>
                        <span class="text-success">✓ vendor/autoload.php exists</span>
                    <?php else: ?>
                        <span class="text-danger">✗ vendor/autoload.php NOT FOUND</span>
                        <div class="mt-2 alert alert-warning">
                            Run: <code>composer install</code> or use <code>install-phpmailer.bat</code>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Check 2 -->
                <div class="check-item <?php echo $diagnostics['phpmailer_installed'] ? 'check-pass' : 'check-fail'; ?>">
                    <i class="fas <?php echo $diagnostics['phpmailer_installed'] ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'; ?> check-icon"></i>
                    <strong>PHPMailer Class:</strong>
                    <?php if ($diagnostics['phpmailer_installed']): ?>
                        <span class="text-success">✓ PHPMailer loaded successfully</span>
                    <?php else: ?>
                        <span class="text-danger">✗ PHPMailer class not found</span>
                    <?php endif; ?>
                </div>

                <!-- Check 3 -->
                <div class="check-item <?php echo $diagnostics['email_config_loaded'] ? 'check-pass' : 'check-fail'; ?>">
                    <i class="fas <?php echo $diagnostics['email_config_loaded'] ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'; ?> check-icon"></i>
                    <strong>Email Configuration:</strong>
                    <?php if ($diagnostics['email_config_loaded']): ?>
                        <span class="text-success">✓ Configuration loaded</span>
                    <?php else: ?>
                        <span class="text-danger">✗ Configuration NOT loaded</span>
                    <?php endif; ?>
                </div>

                <!-- Check 4 -->
                <div class="check-item <?php echo $diagnostics['send_function_exists'] ? 'check-pass' : 'check-fail'; ?>">
                    <i class="fas <?php echo $diagnostics['send_function_exists'] ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'; ?> check-icon"></i>
                    <strong>send_email() Function:</strong>
                    <?php if ($diagnostics['send_function_exists']): ?>
                        <span class="text-success">✓ Function available</span>
                    <?php else: ?>
                        <span class="text-danger">✗ Function NOT found</span>
                    <?php endif; ?>
                </div>

                <!-- SMTP Details -->
                <?php if (!empty($diagnostics['smtp_details'])): ?>
                <hr>
                <h5>SMTP Configuration:</h5>
                <table class="table table-sm table-bordered">
                    <tr>
                        <td><strong>SMTP Host:</strong></td>
                        <td><?php echo htmlspecialchars($diagnostics['smtp_details']['host']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>SMTP Port:</strong></td>
                        <td><?php echo htmlspecialchars($diagnostics['smtp_details']['port']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Username:</strong></td>
                        <td><?php echo htmlspecialchars($diagnostics['smtp_details']['username']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Password Set:</strong></td>
                        <td>
                            <?php if ($diagnostics['smtp_details']['password_set']): ?>
                                <span class="text-success">✓ Yes (<?php echo $diagnostics['smtp_details']['password_length']; ?> characters)</span>
                            <?php else: ?>
                                <span class="text-danger">✗ No password configured</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>From Email:</strong></td>
                        <td><?php echo htmlspecialchars($diagnostics['smtp_details']['from_email']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>From Name:</strong></td>
                        <td><?php echo htmlspecialchars($diagnostics['smtp_details']['from_name']); ?></td>
                    </tr>
                </table>
                <?php endif; ?>

                <!-- Recent Logs -->
                <?php if (!empty($diagnostics['recent_logs'])): ?>
                <hr>
                <h5>Recent Email-Related Logs:</h5>
                <pre><?php echo htmlspecialchars(implode('', $diagnostics['recent_logs'])); ?></pre>
                <?php endif; ?>

                <!-- Recommendations -->
                <hr>
                <h5>Recommendations:</h5>
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb"></i> Common Issues & Solutions:</h6>
                    <ol>
                        <li><strong>Gmail Users:</strong> Must use App Password (not regular password)
                            <ul>
                                <li>Enable 2-Step Verification first</li>
                                <li>Go to: <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App Passwords</a></li>
                                <li>Create password for "Mail" app</li>
                                <li>Update SMTP_PASSWORD in includes/email.php</li>
                            </ul>
                        </li>
                        <li><strong>Port Issues:</strong> Ensure port 587 is not blocked by firewall</li>
                        <li><strong>Test Configuration:</strong> Use <a href="test-email.php">Email Test Tool</a></li>
                    </ol>
                </div>

                <div class="text-center mt-4">
                    <a href="test-email.php" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Test Email Now
                    </a>
                    <a href="phpmailer-setup.php" class="btn btn-info">
                        <i class="fas fa-book"></i> Setup Guide
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
