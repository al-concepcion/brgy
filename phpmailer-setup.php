<?php
/**
 * Manual PHPMailer Setup (Standalone - No Composer Required)
 * 
 * Since Composer installation failed, follow these steps:
 * 
 * OPTION 1: Download PHPMailer Manually
 * =====================================
 * 1. Go to: https://github.com/PHPMailer/PHPMailer/releases/latest
 * 2. Download the .zip file
 * 3. Extract it to: d:\xampp\htdocs\webs\vendor\phpmailer\phpmailer\
 * 4. The folder structure should be:
 *    vendor/
 *      phpmailer/
 *        phpmailer/
 *          src/
 *            PHPMailer.php
 *            SMTP.php
 *            Exception.php
 * 
 * OPTION 2: Use Direct Download Link
 * ===================================
 * Download this file on a computer with internet:
 * https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.9.1.zip
 * 
 * Then transfer it to your XAMPP and extract to vendor/ folder
 * 
 * OPTION 3: Test Without Email (Temporary)
 * =========================================
 * The system will still work! Email functions are optional.
 * If PHPMailer is not installed, emails simply won't be sent.
 * All other features work normally.
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>PHPMailer Setup Guide</title>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
    h1 { color: #667eea; }
    h2 { color: #764ba2; margin-top: 30px; }
    .step { background: #f5f5f5; padding: 15px; margin: 10px 0; border-left: 4px solid #667eea; }
    code { background: #fff; padding: 2px 6px; border: 1px solid #ddd; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .folder-structure { background: #2d2d2d; color: #fff; padding: 15px; font-family: monospace; }
</style></head><body>";

echo "<h1>📧 PHPMailer Setup Guide</h1>";

// Check if PHPMailer is installed
$phpmailer_installed = file_exists(__DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php');
$autoload_exists = file_exists(__DIR__ . '/vendor/autoload.php');

if ($phpmailer_installed || $autoload_exists) {
    echo "<p class='success'>✅ PHPMailer is installed!</p>";
    echo "<p>Email functionality is ready. Configure your SMTP settings in <code>includes/email.php</code></p>";
} else {
    echo "<p class='warning'>⚠️ PHPMailer is not yet installed</p>";
    echo "<p>Don't worry! The website still works. Email notifications just won't be sent until you install PHPMailer.</p>";
    
    echo "<h2>Installation Options</h2>";
    
    echo "<div class='step'>";
    echo "<h3>Option 1: Manual Download (Recommended)</h3>";
    echo "<ol>";
    echo "<li>Download from: <a href='https://github.com/PHPMailer/PHPMailer/releases/latest' target='_blank'>GitHub Releases</a></li>";
    echo "<li>Extract the ZIP file</li>";
    echo "<li>Copy the extracted folder to: <code>d:\\xampp\\htdocs\\webs\\vendor\\phpmailer\\phpmailer\\</code></li>";
    echo "<li>Refresh this page to verify installation</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Required Folder Structure:</h3>";
    echo "<div class='folder-structure'>";
    echo "d:\\xampp\\htdocs\\webs\\<br>";
    echo "├── vendor/<br>";
    echo "│   └── phpmailer/<br>";
    echo "│       └── phpmailer/<br>";
    echo "│           ├── src/<br>";
    echo "│           │   ├── PHPMailer.php<br>";
    echo "│           │   ├── SMTP.php<br>";
    echo "│           │   └── Exception.php<br>";
    echo "│           └── ...<br>";
    echo "└── ...<br>";
    echo "</div>";
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Option 2: System Works Without Email</h3>";
    echo "<p>You can skip PHPMailer installation for now:</p>";
    echo "<ul>";
    echo "<li>✅ All features work normally</li>";
    echo "<li>✅ Applications are submitted successfully</li>";
    echo "<li>✅ Admin can manage applications</li>";
    echo "<li>⚠️ Email notifications won't be sent</li>";
    echo "<li>📝 Users can still track applications manually</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Install PHPMailer (see options above)</li>";
echo "<li>Edit <code>includes/email.php</code> with your email credentials</li>";
echo "<li>For Gmail: Generate an App Password from Google Account settings</li>";
echo "<li>Test email by registering a new user</li>";
echo "</ol>";

echo "<h2>Test Email Configuration</h2>";
echo "<p>After installing PHPMailer and configuring SMTP settings, test with:</p>";
echo "<code>http://localhost/webs/test-email.php</code>";

echo "<hr>";
echo "<p style='text-align: center; color: #666;'>";
echo "For detailed instructions, see <code>EMAIL-SETUP.md</code><br>";
echo "<a href='index.php' style='color: #667eea;'>← Back to Website</a>";
echo "</p>";

echo "</body></html>";
?>
