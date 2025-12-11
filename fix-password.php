<?php
require_once 'includes/config.php';

// Create correct password hash
$correct_hash = password_hash('admin123', PASSWORD_DEFAULT);

// Update database
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$result = $stmt->execute([$correct_hash, 'admin@test.com']);

if ($result) {
    echo "Password updated successfully!<br>";
    echo "New hash: " . $correct_hash . "<br><br>";
    
    // Verify it works
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@test.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Testing login...<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Password verification: " . (password_verify('admin123', $user['password']) ? '<span style="color:green">SUCCESS!</span>' : '<span style="color:red">FAILED</span>') . "<br><br>";
    
    echo '<a href="login.php">Go to Login Page</a>';
} else {
    echo "Failed to update password!";
}
?>
