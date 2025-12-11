# PHPMailer Email Configuration Guide

## Installation Complete ✓

PHPMailer has been integrated into your Barangay E-Services Portal.

## Email Features Added

### 1. **Welcome Email** (register.php)
- Sent when a new user registers
- Contains welcome message and quick start guide

### 2. **Application Confirmation** (apply-id.php, request-certification.php)
- Sent immediately after submitting an application
- Includes reference number and tracking link
- Confirms receipt of ID applications and certification requests

### 3. **Status Update Notifications** (admin/id-applications.php, admin/certifications.php)
- Sent when admin updates application status
- Shows new status (Pending → Processing → Ready → Completed)
- Includes reference number and tracking information

### 4. **Contact Form Auto-Reply** (Future enhancement)
- Can be added to contact form submissions
- Professional acknowledgment of received messages

---

## Configuration Required

### Step 1: Install PHPMailer

Run the installation batch file:
```batch
cd d:\xampp\htdocs\webs
install-phpmailer.bat
```

Or manually install via Composer:
```batch
composer install
```

### Step 2: Configure Email Settings

Edit `includes/email.php` and update these constants:

```php
// For Gmail (Recommended)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');     // Your Gmail address
define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx');      // Your App Password
define('FROM_EMAIL', 'your-email@gmail.com');
define('FROM_NAME', 'Barangay Santo Niño E-Services');
```

### Step 3: Get Gmail App Password

1. Go to https://myaccount.google.com/security
2. Enable **2-Step Verification** (if not already enabled)
3. Go to **App Passwords** (search for "App passwords")
4. Select **Mail** and **Windows Computer**
5. Click **Generate**
6. Copy the 16-character password (format: xxxx xxxx xxxx xxxx)
7. Paste it in `SMTP_PASSWORD` in `includes/email.php`

---

## Testing Email Functionality

### Test 1: Registration Email
1. Register a new user account
2. Check the email inbox for welcome message
3. Verify all links work correctly

### Test 2: Application Confirmation
1. Login and submit an ID application
2. Check email for confirmation with reference number
3. Click tracking link to verify it works

### Test 3: Status Update Email
1. Login to admin panel
2. Update any application status
3. Check applicant's email for status notification

---

## Email Templates

All emails use professional HTML templates with:
- Purple gradient header matching your site theme
- Responsive design for mobile devices
- Clear call-to-action buttons
- Barangay branding

### Customization

Edit email templates in `includes/email.php`:
- `send_welcome_email()` - New user registration
- `send_application_confirmation()` - Application received
- `send_status_update_email()` - Status changes
- `send_contact_reply()` - Contact form responses

---

## Alternative SMTP Providers

### For Other Email Services:

**Outlook/Hotmail:**
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
```

**Yahoo:**
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
```

**Custom SMTP:**
```php
define('SMTP_HOST', 'mail.yourdomain.com');
define('SMTP_PORT', 587);
```

---

## Troubleshooting

### Emails Not Sending?

1. **Check PHP error log:** `d:\xampp\apache\logs\error.log`
2. **Verify credentials:** Make sure App Password is correct
3. **Check firewall:** Allow outbound port 587
4. **Enable debugging:** Edit `includes/email.php` and add:
   ```php
   $mail->SMTPDebug = 2; // Add this line in send_email()
   ```

### Test Email Function

Create `test-email.php`:
```php
<?php
require_once 'includes/config.php';

if (function_exists('send_email')) {
    $result = send_email(
        'test@example.com',
        'Test User',
        'Test Email',
        '<h1>Test email from Barangay Portal</h1>'
    );
    echo $result ? 'Email sent!' : 'Email failed!';
} else {
    echo 'PHPMailer not installed. Run install-phpmailer.bat';
}
?>
```

---

## Files Modified

### New Files:
- ✅ `includes/email.php` - Email configuration and functions
- ✅ `composer.json` - PHPMailer dependency
- ✅ `install-phpmailer.bat` - Installation script
- ✅ `EMAIL-SETUP.md` - This guide

### Modified Files:
- ✅ `includes/config.php` - Loads email functions
- ✅ `register.php` - Sends welcome email
- ✅ `apply-id.php` - Sends confirmation email
- ✅ `request-certification.php` - Sends confirmation email
- ✅ `admin/id-applications.php` - Sends status updates
- ✅ `admin/certifications.php` - Sends status updates

---

## Security Notes

⚠️ **Important Security Practices:**

1. **Never commit credentials** to version control
2. **Use App Passwords**, not your actual Gmail password
3. **Enable 2FA** on your email account
4. **Rotate passwords** regularly
5. **Monitor email logs** for suspicious activity

---

## Production Deployment

When deploying to production:

1. Update `SITE_URL` in `includes/config.php`
2. Update email links to use production domain
3. Use a dedicated email account (e.g., noreply@barangay.gov.ph)
4. Consider using a transactional email service (SendGrid, Mailgun)
5. Implement rate limiting to prevent spam

---

## Support

For issues or questions:
- Check error logs in `d:\xampp\apache\logs\error.log`
- Review PHPMailer documentation: https://github.com/PHPMailer/PHPMailer
- Test with Gmail App Password first before trying other providers

---

**Status:** ✅ PHPMailer integration complete and ready for configuration!
