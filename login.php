<?php
require_once 'includes/config.php';
$current_page = 'login';
$page_title = 'Login';

$errors = [];
$success = '';
$email = '';
$redirectTarget = resolve_redirect_target($_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php');

// Redirect if already logged in
// (Removed: allow access to login page even if logged in)

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    if (empty($errors)) {
        try {
            // First, check if it's an admin login
            $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Admin login successful
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];
                
                // Update last login
                $stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$admin['id']]);
                
                // Redirect to admin dashboard
                header('Location: admin/index.php');
                exit();
            }
            
            // If not admin, check regular user login
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Redirect to homepage or requested page
                header('Location: ' . $redirectTarget);
                exit();
            } else {
                $errors[] = 'Invalid email or password';
            }
        } catch(PDOException $e) {
            $errors[] = 'Login error. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="auth-card">
                    <div class="auth-header text-center mb-4">
                        <div class="auth-icon mb-3">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <h2>Welcome Back!</h2>
                        <p class="text-muted">Login to your account to continue</p>
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
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectTarget); ?>">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address / Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="email" name="email" 
                                       placeholder="Enter your email or username" required
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
                            </div>
                            <small class="text-muted">Admin users can login with username</small>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter your password" required>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        <button type="submit" class="btn btn-gradient w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i> Login
                        </button>

                        <div class="text-center">
                            <p class="text-muted mb-2">Don't have an account? <a href="register.php" class="auth-link">Register here</a></p>
                            <a href="forgot-password.php" class="auth-link small">Forgot your password?</a>
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
