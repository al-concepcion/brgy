<?php
// Test password verification
$password = 'admin123';
$hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

echo "Password: " . $password . "<br>";
echo "Hash: " . $hash . "<br>";
echo "Verify Result: " . (password_verify($password, $hash) ? 'TRUE' : 'FALSE') . "<br><br>";

// Create new hash for admin123
$new_hash = password_hash('admin123', PASSWORD_DEFAULT);
echo "New hash for 'admin123': " . $new_hash . "<br><br>";

// Test database connection
require_once 'includes/config.php';

$email = 'admin@test.com';
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "User found in database!<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Name: " . $user['first_name'] . " " . $user['last_name'] . "<br>";
    echo "Password Hash from DB: " . $user['password'] . "<br>";
    echo "Password verify with DB hash: " . (password_verify($password, $user['password']) ? 'TRUE' : 'FALSE') . "<br>";
} else {
    echo "User NOT found in database!";
}
?>
