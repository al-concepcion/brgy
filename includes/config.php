<?php
ob_start();

// Include error handler
require_once __DIR__ . '/error_handler.php';

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'barangay_db');

// Site configuration
define('SITE_NAME', 'Barangay Santo Niño 1 E-Services Portal');
define('SITE_URL', 'http://localhost/webs/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10485760); // 10MB in bytes

// Include email functions (if PHPMailer is installed)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/email.php';
}

// Create database connection with retry logic
$max_retries = 3;
$retry_count = 0;
$conn = null;

while ($retry_count < $max_retries && $conn === null) {
    try {
        // First, try to connect to MySQL without specifying a database
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]
        );
        
        // Check if database exists
        $dbExists = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
        
        if (!$dbExists->fetch()) {
            // Database doesn't exist, create it and import the schema
            $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            
            // Import database schema if file exists
            $schema_file = __DIR__ . '/../database.sql';
            if (file_exists($schema_file)) {
                $sql = file_get_contents($schema_file);
                $conn->exec($sql);
            }
        } else {
            // Database exists, connect to it
            $conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        }
        
        // Verify connection is working
        $conn->query("SELECT 1");
        
    } catch(PDOException $e) {
        $retry_count++;
        $conn = null;
        
        error_log("Database connection attempt $retry_count failed: " . $e->getMessage());
        
        if ($retry_count >= $max_retries) {
            // Show user-friendly error message
            $error_msg = "Unable to connect to database. Please try again later.";
            if (ini_get('display_errors')) {
                $error_msg .= "<br><small>Technical details: " . htmlspecialchars($e->getMessage()) . "</small>";
            }
            die($error_msg);
        }
        
        // Wait before retrying
        sleep(1);
    }
}

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
    mkdir(UPLOAD_DIR . 'id_applications/', 0777, true);
    mkdir(UPLOAD_DIR . 'certifications/', 0777, true);
}

// Common functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function resolve_redirect_target($target, $default = 'index.php') {
    if (empty($target)) {
        return $default;
    }

    $parsed = parse_url($target, PHP_URL_PATH);
    if ($parsed === false || $parsed === null) {
        return $default;
    }

    $normalized = ltrim($parsed, '/');
    if (str_contains($normalized, '/')) {
        $normalized = basename($normalized);
    }
    if ($normalized === '' || str_contains($normalized, '..')) {
        return $default;
    }

    return $normalized;
}

function require_user_login($redirect = null) {
    if (!isset($_SESSION['user_id'])) {
        $target = $redirect ?? resolve_redirect_target($_SERVER['REQUEST_URI'] ?? '', 'index.php');
        header('Location: login.php?redirect=' . urlencode($target));
        exit();
    }
}

// Generate unique application number
function generate_application_number($type = 'BID') {
    $prefix = $type; // BID for Barangay ID, CERT for Certification
    $timestamp = time();
    $random = rand(1000, 9999);
    return $prefix . '-' . $timestamp . $random;
}

// Format date to readable format
function format_date($date) {
    return date('F j, Y', strtotime($date));
}

// Format datetime to readable format
function format_datetime($datetime) {
    return date('F j, Y g:i A', strtotime($datetime));
}

// Upload file function
function upload_file($file, $folder = 'id_applications') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['error' => 'File size exceeds maximum limit of 10MB'];
    }
    
    // Allowed file types
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    if (!in_array($file['type'], $allowed_types)) {
        return ['error' => 'Invalid file type. Only JPG, PNG, and PDF are allowed'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $upload_path = UPLOAD_DIR . $folder . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $filename;
    }
    
    return false;
}

// Get certificate price
function get_certificate_price($type) {
    $prices = [
        'residency' => 50.00,
        'indigency' => 0.00,
        'clearance' => 100.00,
        'business' => 200.00,
        'good_moral' => 50.00
    ];
    return isset($prices[$type]) ? $prices[$type] : 0.00;
}

// Get certificate name
function get_certificate_name($type) {
    $names = [
        'residency' => 'Certificate of Residency',
        'indigency' => 'Certificate of Indigency',
        'clearance' => 'Barangay Clearance',
        'business' => 'Barangay Business Clearance',
        'good_moral' => 'Certificate of Good Moral Character'
    ];
    return isset($names[$type]) ? $names[$type] : 'Certificate';
}

// Log status change
function log_status_change($conn, $reference_number, $type, $old_status, $new_status, $remarks = '', $updated_by = 'System') {
    try {
        $stmt = $conn->prepare("INSERT INTO status_history (reference_number, application_type, old_status, new_status, remarks, updated_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$reference_number, $type, $old_status, $new_status, $remarks, $updated_by]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

// Get application statistics
function get_statistics($conn) {
    $stats = [
        'residents' => 0,
        'applications' => 0,
        'processing_time_hours' => null,
        'processing_time_label' => 'N/A'
    ];

    try {
        // Total registered residents (users table)
        $stmt = $conn->query("SELECT COUNT(*) AS total FROM users");
        $stats['residents'] = (int) $stmt->fetchColumn();

        // Applications processed (total submissions across both services)
        $stmt = $conn->query("SELECT COUNT(*) AS total FROM id_applications");
        $idTotal = (int) $stmt->fetchColumn();

        $stmt = $conn->query("SELECT COUNT(*) AS total FROM certification_requests");
        $certTotal = (int) $stmt->fetchColumn();

        $stats['applications'] = $idTotal + $certTotal;

        // Average processing time (in hours) for completed or ready applications
        $stmt = $conn->query("
            SELECT AVG(processing_hours) AS avg_hours FROM (
                SELECT TIMESTAMPDIFF(HOUR, created_at, updated_at) AS processing_hours
                FROM id_applications
                WHERE status IN ('Ready for Pickup', 'Completed')
                  AND updated_at IS NOT NULL
                UNION ALL
                SELECT TIMESTAMPDIFF(HOUR, created_at, updated_at) AS processing_hours
                FROM certification_requests
                WHERE status IN ('Ready for Pickup', 'Ready for Delivery', 'Completed')
                  AND updated_at IS NOT NULL
            ) AS durations
        ");

        $avgHours = $stmt->fetchColumn();

        if ($avgHours !== false && $avgHours !== null) {
            $stats['processing_time_hours'] = (float) $avgHours;
            $stats['processing_time_label'] = format_processing_time_label($avgHours);
        }
    } catch(PDOException $e) {
        // Fail silently and return defaults
    }

    return $stats;
}

function format_processing_time_label($hours) {
    if ($hours === null) {
        return 'N/A';
    }

    if ($hours < 1) {
        return 'Under 1 hour';
    }

    if ($hours < 24) {
        return round($hours) . ' hour' . (round($hours) === 1 ? '' : 's');
    }

    $days = $hours / 24;
    if ($days < 7) {
        $rounded = round($days, 1);
        return $rounded . ' day' . ($rounded == 1.0 ? '' : 's');
    }

    $weeks = $days / 7;
    $roundedWeeks = round($weeks, 1);
    return $roundedWeeks . ' week' . ($roundedWeeks == 1.0 ? '' : 's');
}

// Get recent announcements
function get_announcements($conn, $limit = 3) {
    try {
        $stmt = $conn->prepare("SELECT * FROM announcements WHERE is_active = 1 ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Check if user is logged in (for admin)
function is_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        header('Location: admin/login.php');
        exit();
    }
}

// Start session with security settings if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Session security settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    // Start session
    session_start();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    // Session timeout (2 hours of inactivity)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}
?>