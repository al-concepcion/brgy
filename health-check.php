<?php
/**
 * Database Health Check and Maintenance Script
 * Run this periodically to ensure database integrity
 */

require_once 'includes/config.php';

echo "<h2>Database Health Check</h2>";
echo "<pre>";

// Check connection
echo "✓ Database connection established\n\n";

// Check if all required tables exist
$required_tables = [
    'users',
    'id_applications',
    'certification_requests',
    'status_history',
    'contact_messages',
    'admin_users',
    'announcements'
];

echo "Checking required tables:\n";
foreach ($required_tables as $table) {
    try {
        $stmt = $conn->query("SELECT 1 FROM $table LIMIT 1");
        echo "  ✓ $table exists\n";
    } catch (PDOException $e) {
        echo "  ✗ $table missing or error: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Check for missing columns in id_applications
echo "Checking id_applications columns:\n";
$required_columns = [
    'user_id', 'reference_number', 'first_name', 'middle_name', 'last_name',
    'birth_date', 'gender', 'civil_status', 'contact_number', 'email',
    'complete_address', 'preferred_pickup_date', 'claim_method', 'price',
    'proof_of_residency', 'valid_id', 'id_photo', 'status', 'remarks'
];

try {
    $stmt = $conn->query("DESCRIBE id_applications");
    $existing_columns = [];
    while ($row = $stmt->fetch()) {
        $existing_columns[] = $row['Field'];
    }
    
    foreach ($required_columns as $col) {
        if (in_array($col, $existing_columns)) {
            echo "  ✓ $col\n";
        } else {
            echo "  ✗ $col MISSING\n";
        }
    }
} catch (PDOException $e) {
    echo "  Error checking columns: " . $e->getMessage() . "\n";
}

echo "\n";

// Check for admin user
echo "Checking admin users:\n";
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM admin_users WHERE role = 'Admin'");
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        echo "  ✓ Admin users exist ($count)\n";
    } else {
        echo "  ✗ No admin users found\n";
        echo "  Creating default admin user...\n";
        $stmt = $conn->prepare("INSERT INTO admin_users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            'admin',
            password_hash('Admin@123', PASSWORD_DEFAULT),
            'System Administrator',
            'admin@barangay.gov.ph',
            'Admin'
        ]);
        echo "  ✓ Admin user created (username: admin, password: Admin@123)\n";
    }
} catch (PDOException $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Check database statistics
echo "Database Statistics:\n";
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    echo "  Users: " . $stmt->fetchColumn() . "\n";
    
    $stmt = $conn->query("SELECT COUNT(*) FROM id_applications");
    echo "  ID Applications: " . $stmt->fetchColumn() . "\n";
    
    $stmt = $conn->query("SELECT COUNT(*) FROM certification_requests");
    echo "  Certification Requests: " . $stmt->fetchColumn() . "\n";
    
    $stmt = $conn->query("SELECT COUNT(*) FROM contact_messages");
    echo "  Contact Messages: " . $stmt->fetchColumn() . "\n";
} catch (PDOException $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Check upload directories
echo "Checking upload directories:\n";
$upload_dirs = [
    'uploads/',
    'uploads/id_applications/',
    'uploads/certifications/'
];

foreach ($upload_dirs as $dir) {
    $full_path = __DIR__ . '/' . $dir;
    if (is_dir($full_path) && is_writable($full_path)) {
        echo "  ✓ $dir (writable)\n";
    } elseif (is_dir($full_path)) {
        echo "  ⚠ $dir (not writable)\n";
    } else {
        echo "  ✗ $dir (missing)\n";
        mkdir($full_path, 0755, true);
        echo "    Created $dir\n";
    }
}

echo "\n";

// Check logs directory
echo "Checking logs directory:\n";
$log_dir = __DIR__ . '/logs';
if (is_dir($log_dir) && is_writable($log_dir)) {
    echo "  ✓ logs/ (writable)\n";
} elseif (is_dir($log_dir)) {
    echo "  ⚠ logs/ (not writable)\n";
} else {
    echo "  ✗ logs/ (missing)\n";
    mkdir($log_dir, 0755, true);
    echo "    Created logs/\n";
}

echo "\n✓ Health check complete!\n";
echo "</pre>";
?>
