<?php
require_once 'includes/config.php';

// Create correct password hash for Admin@123
$correct_hash = password_hash('Admin@123', PASSWORD_DEFAULT);

// Update database
$stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE username = ?");
$result = $stmt->execute([$correct_hash, 'admin']);

if ($result) {
    echo "Admin password updated successfully!<br>";
    echo "New hash: " . $correct_hash . "<br><br>";
    
    // Verify it works
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Testing admin login...<br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Password verification for 'Admin@123': " . (password_verify('Admin@123', $admin['password']) ? '<span style="color:green">SUCCESS!</span>' : '<span style="color:red">FAILED</span>') . "<br><br>";
    
    echo '<strong style="color:green;">You can now login with:</strong><br>';
    echo 'Username: admin<br>';
    echo 'Password: Admin@123<br><br>';
    echo '<a href="login.php">Go to Login Page</a>';
} else {
    echo "Failed to update password!";
}
?>
