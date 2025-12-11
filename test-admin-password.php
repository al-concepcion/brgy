<?php
$password = 'Admin@123';
$hash = '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1pxCVNdE5S7.kHZJkSdqIhJC7v7pq8m';

echo "Testing Admin Password<br>";
echo "Password: " . $password . "<br>";
echo "Hash: " . $hash . "<br>";
echo "Verify Result: " . (password_verify($password, $hash) ? '<span style="color:green">TRUE - Password is correct!</span>' : '<span style="color:red">FALSE - Password does not match!</span>') . "<br><br>";

// Test with common variations
$variations = ['admin123', 'Admin123', 'ADMIN123', 'Admin@123', 'admin@123'];
echo "Testing variations:<br>";
foreach ($variations as $pass) {
    echo $pass . ": " . (password_verify($pass, $hash) ? '<span style="color:green">MATCH</span>' : 'no match') . "<br>";
}

echo "<br>Generating new hash for Admin@123:<br>";
echo password_hash('Admin@123', PASSWORD_DEFAULT);
?>
