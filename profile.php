<?php
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=profile.php');
    exit();
}

$current_page = 'profile';
$page_title = 'My Profile';

$errors = [];
$success = '';

// Get user data
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit();
    }
} catch(PDOException $e) {
    die('Error loading profile');
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitize_input($_POST['firstName'] ?? '');
    $last_name = sanitize_input($_POST['lastName'] ?? '');
    $contact_number = sanitize_input($_POST['contactNumber'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    
    if (empty($first_name) || empty($last_name)) {
        $errors[] = 'First name and last name are required';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, contact_number = ?, address = ? WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $contact_number, $address, $_SESSION['user_id']]);
            
            $_SESSION['user_name'] = $first_name . ' ' . $last_name;
            $success = 'Profile updated successfully!';
            
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            $errors[] = 'Failed to update profile';
        }
    }
}

include 'includes/header.php';
?>

<section class="profile-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="profile-info">
                            <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                            <p><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?></p>
                            <p class="text-muted mb-0"><small>Member since <?php echo format_date($user['created_at']); ?></small></p>
                        </div>
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
                        </div>
                    <?php endif; ?>

                    <h4 class="mb-4">Edit Profile</h4>

                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            <div class="col-md-6">
                                <label for="contactNumber" class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" id="contactNumber" name="contactNumber" 
                                       value="<?php echo htmlspecialchars($user['contact_number']); ?>">
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-gradient">
                                <i class="fas fa-save me-2"></i> Save Changes
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-times me-2"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>

                <div class="profile-card">
                    <h4 class="mb-3">Quick Actions</h4>
                    <div class="d-grid gap-2">
                        <a href="my-applications.php" class="btn btn-outline-primary">
                            <i class="fas fa-folder me-2"></i> View My Applications
                        </a>
                        <a href="apply-id.php" class="btn btn-outline-success">
                            <i class="fas fa-id-card me-2"></i> Apply for Barangay ID
                        </a>
                        <a href="request-certification.php" class="btn btn-outline-info">
                            <i class="fas fa-file-alt me-2"></i> Request Certification
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
