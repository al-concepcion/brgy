<?php
require_once 'includes/config.php';
$current_page = 'register';
$page_title = 'Register';

$errors = [];
$success = '';

// Redirect if already logged in
// (Removed: allow access to register page even if logged in)

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitize_input($_POST['firstName'] ?? '');
    $last_name = sanitize_input($_POST['lastName'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $contact_number = sanitize_input($_POST['contactNumber'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirmPassword'] ?? '';
    
    // Validation
    if (empty($first_name)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($last_name)) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($contact_number)) {
        $errors[] = 'Contact number is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    // Check if email already exists
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered';
            }
        } catch(PDOException $e) {
            $errors[] = 'Registration error. Please try again.';
        }
    }
    
    // Register user
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (email, password, first_name, last_name, contact_number, address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$email, $hashed_password, $first_name, $last_name, $contact_number, $address]);
            
            $user_id = $conn->lastInsertId();
            $full_name = $first_name . ' ' . $last_name;
            
            $success = 'Registration successful! Please check your email for confirmation.';
            
            // Auto login
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_email'] = $email;
            
            // Send welcome email
            if (function_exists('send_welcome_email')) {
                $email_sent = send_welcome_email($email, $full_name);
                if (!$email_sent) {
                    // Log email sending failure but don't block registration
                    error_log("Failed to send welcome email to: $email");
                }
            }
            
            // Redirect after 2 seconds
            header('refresh:2;url=index.php');
            
        } catch(PDOException $e) {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="auth-card">
                    <div class="auth-header text-center mb-4">
                        <div class="auth-icon mb-3">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h2>Create Account</h2>
                        <p class="text-muted">Register to access all services</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            <p class="mb-0 mt-2"><small>Redirecting to homepage...</small></p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" 
                                       placeholder="Enter first name" required
                                       value="<?php echo htmlspecialchars($first_name ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" 
                                       placeholder="Enter last name" required
                                       value="<?php echo htmlspecialchars($last_name ?? ''); ?>">
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label">Email Address *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="Enter your email" required
                                           value="<?php echo htmlspecialchars($email ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="contactNumber" class="form-label">Contact Number *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control" id="contactNumber" name="contactNumber" 
                                           placeholder="09XX-XXX-XXXX" required
                                           value="<?php echo htmlspecialchars($contact_number ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2" 
                                          placeholder="Your complete address"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="At least 6 characters" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirmPassword" class="form-label">Confirm Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" 
                                           placeholder="Re-enter password" required>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="auth-link">Terms and Conditions</a> and <a href="#" class="auth-link">Privacy Policy</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-gradient w-100 mt-4 mb-3">
                            <i class="fas fa-user-plus me-2"></i> Create Account
                        </button>

                        <div class="text-center">
                            <p class="text-muted mb-0">Already have an account? <a href="login.php" class="auth-link">Login here</a></p>
                        </div>
                    </form>

                    <div class="auth-divider">
                        <span>or</span>
                    </div>

                    <div class="text-center">
                        <a href="index.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-home me-2"></i> Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
