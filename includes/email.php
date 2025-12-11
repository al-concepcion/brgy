<?php
/**
 * Email Configuration and PHPMailer Setup
 * Configure your SMTP settings here
 */

// Only load PHPMailer if vendor/autoload.php exists
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    // PHPMailer not installed - skip email functionality
    return;
}

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'jyalapag@kld.edu.ph'); // Change this
define('SMTP_PASSWORD', 'pvgefvmzxrwdzjij'); // Change this (use App Password for Gmail)
define('FROM_EMAIL', 'jyalapag@kld.edu.ph'); // Change this
define('FROM_NAME', 'Barangay Santo Niño 1 E-Services');

/**
 * Send Email Function
 */
function send_email($to_email, $to_name, $subject, $body, $is_html = true) {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log('PHPMailer class not found - email not sent to: ' . $to_email);
        return false; // PHPMailer not available
    }
    
    // Validate email address
    if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
        error_log('Invalid email address: ' . $to_email);
        return false;
    }
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = 0; // Set to 2 for debugging
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 30;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to_email, $to_name);
        $mail->addReplyTo(FROM_EMAIL, FROM_NAME);
        
        // Content
        $mail->isHTML($is_html);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        if (!$is_html) {
            $mail->AltBody = $body;
        }
        
        $mail->send();
        error_log('Email sent successfully to: ' . $to_email . ' | Subject: ' . $subject);
        return true;
    } catch (Exception $e) {
        error_log('Email sending failed to: ' . $to_email . ' | Error: ' . $mail->ErrorInfo . ' | Exception: ' . $e->getMessage());
        return false;
    }
}

/**
 * Send Application Status Update Email
 */
function send_status_update_email($email, $name, $reference_number, $status, $type) {
    // Determine the subject and special message based on status
    $is_ready = in_array($status, ['Ready for Pickup', 'Ready for Delivery']);
    $is_rejected = ($status == 'Rejected');
    $is_processing = ($status == 'Processing');
    
    if ($is_ready) {
        $subject = "✅ Your " . ($type == 'ID' ? 'Barangay ID' : 'Certificate') . " is Ready! - $reference_number";
        $special_message = "
        <div style='background: #d4edda; border: 2px solid #28a745; border-radius: 10px; padding: 20px; margin: 20px 0; text-align: center;'>
            <h3 style='color: #155724; margin: 0;'>🎉 Great News!</h3>
            <p style='color: #155724; font-size: 16px; margin: 10px 0;'>
                Your " . ($type == 'ID' ? 'Barangay ID' : 'certificate') . " is now ready for " . 
                ($status == 'Ready for Delivery' ? 'delivery' : 'pickup') . "!
            </p>
        </div>
        ";
        
        if ($status == 'Ready for Pickup') {
            $action_required = "
            <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;'>
                <h4 style='margin-top: 0; color: #856404;'>📍 What to do next:</h4>
                <ol style='color: #856404; margin: 10px 0;'>
                    <li>Visit the Barangay Hall during office hours</li>
                    <li>Bring a valid ID for verification</li>
                    <li>Present your reference number: <strong>$reference_number</strong></li>
                    <li>Claim your " . ($type == 'ID' ? 'Barangay ID' : 'certificate') . "</li>
                </ol>
                <p style='margin: 10px 0; color: #856404;'><strong>Office Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM</p>
            </div>
            ";
        } else {
            $action_required = "
            <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;'>
                <h4 style='margin-top: 0; color: #856404;'>📦 Delivery Information:</h4>
                <p style='color: #856404;'>Your certificate will be delivered to your registered address within 1-2 business days.</p>
                <p style='color: #856404;'>Please ensure someone is available to receive the document.</p>
            </div>
            ";
        }
    } elseif ($is_rejected) {
        $subject = "Application Update - $reference_number";
        $special_message = "
        <div style='background: #f8d7da; border: 2px solid #dc3545; border-radius: 10px; padding: 20px; margin: 20px 0;'>
            <h3 style='color: #721c24; margin: 0;'>Application Status Update</h3>
            <p style='color: #721c24; margin: 10px 0;'>
                We regret to inform you that your application has been rejected.
            </p>
        </div>
        ";
        $action_required = "
        <div style='background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 20px 0;'>
            <h4 style='margin-top: 0; color: #0c5460;'>ℹ️ Need Help?</h4>
            <p style='color: #0c5460;'>Please contact our office for more information about your application.</p>
            <p style='color: #0c5460;'><strong>Contact:</strong> Visit the Barangay Hall during office hours or check the remarks in the tracking page.</p>
        </div>
        ";
    } elseif ($is_processing) {
        $subject = "Application Being Processed - $reference_number";
        $special_message = "
        <div style='background: #d1ecf1; border: 2px solid #0d6efd; border-radius: 10px; padding: 20px; margin: 20px 0; text-align: center;'>
            <h3 style='color: #004085; margin: 0;'>⚙️ Processing Your Application</h3>
            <p style='color: #004085; font-size: 16px; margin: 10px 0;'>
                Your application has been accepted and is now being processed.
            </p>
        </div>
        ";
        $action_required = "
        <div style='background: #e7f3ff; border-left: 4px solid #0d6efd; padding: 15px; margin: 20px 0;'>
            <h4 style='margin-top: 0; color: #004085;'>⏱️ What's Happening:</h4>
            <p style='color: #004085;'>Your application is currently being reviewed and processed by our staff.</p>
            <p style='color: #004085;'>Estimated processing time: <strong>2-3 business days</strong></p>
            <p style='color: #004085;'>You will receive another email when your " . ($type == 'ID' ? 'ID' : 'certificate') . " is ready.</p>
        </div>
        ";
    } else {
        $subject = "Application Status Update - $reference_number";
        $special_message = "";
        $action_required = "";
    }
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .status-badge { display: inline-block; padding: 10px 20px; border-radius: 5px; font-weight: bold; margin: 20px 0; }
            .status-pending { background: #ffc107; color: #000; }
            .status-processing { background: #0d6efd; color: #fff; }
            .status-ready { background: #28a745; color: #fff; }
            .status-completed { background: #6c757d; color: #fff; }
            .status-rejected { background: #dc3545; color: #fff; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🏛️ Barangay Santo Niño 1</h1>
                <p>E-Services Portal</p>
            </div>
            <div class='content'>
                <h2>Application Status Update</h2>
                <p>Dear <strong>$name</strong>,</p>
                
                $special_message
                
                <table style='width: 100%; margin: 20px 0; border-collapse: collapse;'>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Reference Number:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'>$reference_number</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; background: #f5f5f5; border: 1px solid #ddd;'><strong>Application Type:</strong></td>
                        <td style='padding: 10px; background: #f5f5f5; border: 1px solid #ddd;'>" . ($type == 'ID' ? 'Barangay ID' : 'Certification Request') . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong>Current Status:</strong></td>
                        <td style='padding: 10px; background: #fff; border: 1px solid #ddd;'><strong style='color: #667eea;'>$status</strong></td>
                    </tr>
                </table>
                
                $action_required
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://localhost/webs/track-application.php?ref=$reference_number' 
                       style='display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                              color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                        Track Your Application
                    </a>
                </div>
                
                <p>If you have any questions, please contact our office during business hours.</p>
                
                <p>Thank you,<br><strong>Barangay Santo Niño 1</strong></p>
            </div>
            <div class='footer'>
                <p>This is an automated message. Please do not reply to this email.</p>
                <p>&copy; " . date('Y') . " Barangay Santo Niño 1 - E-Services Portal</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return send_email($email, $name, $subject, $body);
}

/**
 * Send Welcome Email to New Users
 */
function send_welcome_email($email, $name) {
    $subject = "Welcome to Barangay Santo Niño 1 E-Services Portal! 🎉";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 10px 5px; }
            .feature-box { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #667eea; border-radius: 5px; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 20px 0; }
            .info-item { background: white; padding: 15px; border-radius: 5px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Welcome to Barangay Santo Niño 1!</h1>
                <p style='font-size: 18px; margin: 10px 0;'>Your account has been successfully created</p>
            </div>
            <div class='content'>
                <h2>Hello $name!</h2>
                <p>Thank you for registering with <strong>Barangay Santo Niño 1 E-Services Portal</strong>. We're excited to have you as part of our community!</p>
                
                <div class='feature-box'>
                    <h3 style='margin-top: 0; color: #667eea;'>✨ What You Can Do Now:</h3>
                    <ul style='margin: 10px 0; padding-left: 20px;'>
                        <li><strong>Apply for Barangay ID</strong> - Get your official barangay identification card online</li>
                        <li><strong>Request Certifications</strong> - Apply for various certificates (Residency, Indigency, Clearance, Business, Good Moral)</li>
                        <li><strong>Track Applications</strong> - Monitor your requests in real-time with reference numbers</li>
                        <li><strong>Manage Profile</strong> - Update your personal information anytime</li>
                        <li><strong>View History</strong> - Access all your previous applications</li>
                    </ul>
                </div>
                
                <h3>📋 Available Services:</h3>
                <div class='info-grid'>
                    <div class='info-item'>
                        <strong style='color: #667eea; font-size: 18px;'>💳</strong>
                        <p style='margin: 5px 0; font-weight: bold;'>Barangay ID</p>
                        <small style='color: #666;'>Official ID Card</small>
                    </div>
                    <div class='info-item'>
                        <strong style='color: #667eea; font-size: 18px;'>📜</strong>
                        <p style='margin: 5px 0; font-weight: bold;'>Certifications</p>
                        <small style='color: #666;'>Various Documents</small>
                    </div>
                    <div class='info-item'>
                        <strong style='color: #667eea; font-size: 18px;'>🔍</strong>
                        <p style='margin: 5px 0; font-weight: bold;'>Track Status</p>
                        <small style='color: #666;'>Real-time Updates</small>
                    </div>
                    <div class='info-item'>
                        <strong style='color: #667eea; font-size: 18px;'>📧</strong>
                        <p style='margin: 5px 0; font-weight: bold;'>Notifications</p>
                        <small style='color: #666;'>Email Alerts</small>
                    </div>
                </div>
                
                <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                    <h4 style='margin-top: 0; color: #856404;'>🕐 Office Hours:</h4>
                    <p style='margin: 5px 0; color: #856404;'><strong>Monday - Friday:</strong> 8:00 AM - 5:00 PM</p>
                    <p style='margin: 5px 0; color: #856404;'><strong>Saturday:</strong> 8:00 AM - 12:00 PM</p>
                    <p style='margin: 5px 0; color: #856404;'><strong>Sunday & Holidays:</strong> Closed</p>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://localhost/webs/login.php' class='button'>
                        🔐 Login to Your Account
                    </a>
                    <a href='http://localhost/webs/apply-id.php' class='button' style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%);'>
                        📝 Apply for Barangay ID
                    </a>
                </div>
                
                <div class='feature-box' style='border-left-color: #28a745;'>
                    <h4 style='margin-top: 0; color: #28a745;'>💡 Quick Tips:</h4>
                    <ul style='margin: 10px 0; padding-left: 20px;'>
                        <li>Keep your reference numbers safe for tracking applications</li>
                        <li>Ensure your email is correct to receive status updates</li>
                        <li>Prepare required documents before applying (Valid ID, Proof of Residency)</li>
                        <li>Check your spam folder if you don't receive our emails</li>
                    </ul>
                </div>
                
                <p style='margin-top: 20px;'>If you have any questions or need assistance, feel free to contact us through the portal or visit our office during business hours.</p>
                
                <p style='margin-top: 30px;'>Welcome aboard!<br><strong>Barangay Santo Niño 1 Team</strong></p>
            </div>
            <div class='footer'>
                <p>This email was sent because you registered an account at Barangay Santo Niño 1 E-Services Portal.</p>
                <p>&copy; " . date('Y') . " Barangay Santo Niño 1 - E-Services Portal. All rights reserved.</p>
                <p style='margin-top: 10px;'><a href='http://localhost/webs' style='color: #667eea; text-decoration: none;'>Visit Portal</a> | <a href='http://localhost/webs/about-contact.php' style='color: #667eea; text-decoration: none;'>Contact Us</a></p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return send_email($email, $name, $subject, $body);
}

/**
 * Send Application Received Confirmation
 */
function send_application_confirmation($email, $name, $reference_number, $type) {
    $subject = "Application Received - $reference_number";
    
    $app_type = ($type == 'ID') ? 'Barangay ID Application' : 'Certification Request';
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .ref-box { background: #fff; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>✅ Application Received</h1>
            </div>
            <div class='content'>
                <h2>Dear $name,</h2>
                <p>Thank you for submitting your <strong>$app_type</strong>.</p>
                
                <div class='ref-box'>
                    <p><strong>Your Reference Number:</strong></p>
                    <h2 style='color: #667eea; margin: 10px 0;'>$reference_number</h2>
                    <p style='font-size: 14px; color: #666;'>Please save this reference number for tracking your application.</p>
                </div>
                
                <p><strong>What's Next?</strong></p>
                <ol>
                    <li>Your application is being reviewed</li>
                    <li>You will receive email updates on status changes</li>
                    <li>You can track your application status anytime</li>
                </ol>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://localhost/webs/track-application.php?ref=$reference_number' 
                       style='display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                              color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                        Track Your Application
                    </a>
                </div>
                
                <p><strong>Processing Time:</strong> 2-3 business days</p>
                
                <p>Thank you,<br><strong>Barangay Santo Niño 1</strong></p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Barangay Santo Niño 1 - E-Services Portal</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return send_email($email, $name, $subject, $body);
}

/**
 * Send Contact Form Reply
 */
function send_contact_reply($email, $name, $message) {
    $subject = "Re: Your Message to Barangay Santo Niño 1";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>📧 Barangay Santo Niño 1</h1>
            </div>
            <div class='content'>
                <h2>Dear $name,</h2>
                <p>Thank you for contacting Barangay Santo Niño 1.</p>
                
                <div style='background: #fff; padding: 20px; margin: 20px 0; border-left: 4px solid #667eea;'>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
                
                <p>If you have additional questions, feel free to visit our office or send us another message.</p>
                
                <p>Best regards,<br><strong>Barangay Santo Niño 1 Team</strong></p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Barangay Santo Niño 1 - E-Services Portal</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return send_email($email, $name, $subject, $body);
}
?>
