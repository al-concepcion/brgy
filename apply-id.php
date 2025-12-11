<?php
require_once 'includes/config.php';
$current_page = 'apply-id';
$page_title = 'Apply for Barangay ID';

require_user_login('apply-id.php');

$errors = [];
$success = false;
$reference_number = '';

// Initialize form data from session
if (!isset($_SESSION['id_form_data'])) {
    $_SESSION['id_form_data'] = [
        'step' => 1,
        'personal_info' => [],
        'contact_info' => [],
        'documents' => []
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $step = isset($_POST['step']) ? intval($_POST['step']) : 1;
    
    if ($step == 1) {
        // Personal Information
        $_SESSION['id_form_data']['personal_info'] = [
            'first_name' => sanitize_input($_POST['firstName'] ?? ''),
            'middle_name' => sanitize_input($_POST['middleName'] ?? ''),
            'last_name' => sanitize_input($_POST['lastName'] ?? ''),
            'suffix' => sanitize_input($_POST['suffix'] ?? ''),
            'birth_date' => sanitize_input($_POST['birthDate'] ?? ''),
            'gender' => sanitize_input($_POST['gender'] ?? ''),
            'civil_status' => sanitize_input($_POST['civilStatus'] ?? '')
        ];
        $_SESSION['id_form_data']['step'] = 2;
        header('Location: apply-id.php');
        exit();
        
    } elseif ($step == 2) {
        // Contact Information
        $_SESSION['id_form_data']['contact_info'] = [
            'contact_number' => sanitize_input($_POST['contactNumber'] ?? ''),
            'email' => sanitize_input($_POST['email'] ?? ''),
            'complete_address' => sanitize_input($_POST['completeAddress'] ?? ''),
            'preferred_pickup_date' => sanitize_input($_POST['preferredPickupDate'] ?? ''),
            'claim_method' => sanitize_input($_POST['claimMethod'] ?? 'pickup')
        ];
        $_SESSION['id_form_data']['step'] = 3;
        header('Location: apply-id.php');
        exit();
        
    } elseif ($step == 3) {
        // Document Upload and Final Submission
        $uploaded_files = [];
        
        // Upload proof of residency
        if (isset($_FILES['proofOfResidency']) && $_FILES['proofOfResidency']['error'] == UPLOAD_ERR_OK) {
            $result = upload_file($_FILES['proofOfResidency'], 'id_applications');
            if (is_array($result) && isset($result['error'])) {
                $errors[] = 'Proof of Residency: ' . $result['error'];
            } else {
                $uploaded_files['proof_of_residency'] = $result;
            }
        }
        
        // Upload valid ID
        if (isset($_FILES['validId']) && $_FILES['validId']['error'] == UPLOAD_ERR_OK) {
            $result = upload_file($_FILES['validId'], 'id_applications');
            if (is_array($result) && isset($result['error'])) {
                $errors[] = 'Valid ID: ' . $result['error'];
            } else {
                $uploaded_files['valid_id'] = $result;
            }
        }
        
        // Upload ID photo
        if (isset($_FILES['idPhoto']) && $_FILES['idPhoto']['error'] == UPLOAD_ERR_OK) {
            $result = upload_file($_FILES['idPhoto'], 'id_applications');
            if (is_array($result) && isset($result['error'])) {
                $errors[] = 'ID Photo: ' . $result['error'];
            } else {
                $uploaded_files['id_photo'] = $result;
            }
        }
        
        // If no errors, save to database
        if (empty($errors)) {
            try {
                $reference_number = generate_application_number('BID');
                $personal = $_SESSION['id_form_data']['personal_info'];
                $contact = $_SESSION['id_form_data']['contact_info'];
                $claim_method = $contact['claim_method'] ?? 'pickup';
                $price = 100.00;
                $stmt = $conn->prepare("INSERT INTO id_applications (
                    user_id, reference_number, first_name, middle_name, last_name, suffix, 
                    birth_date, gender, civil_status, contact_number, email, 
                    complete_address, preferred_pickup_date, proof_of_residency, valid_id, id_photo, claim_method, price, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $reference_number,
                    $personal['first_name'],
                    $personal['middle_name'],
                    $personal['last_name'],
                    $personal['suffix'],
                    $personal['birth_date'],
                    $personal['gender'],
                    $personal['civil_status'],
                    $contact['contact_number'],
                    $contact['email'],
                    $contact['complete_address'],
                    $contact['preferred_pickup_date'] ?? null,
                    $uploaded_files['proof_of_residency'] ?? null,
                    $uploaded_files['valid_id'] ?? null,
                    $uploaded_files['id_photo'] ?? null,
                    $claim_method,
                    $price
                ]);
                // Log status
                log_status_change($conn, $reference_number, 'ID', null, 'Pending', 'Application submitted');
                // Send confirmation email
                if (function_exists('send_application_confirmation')) {
                    send_application_confirmation(
                        $contact['email'],
                        $personal['first_name'] . ' ' . $personal['last_name'],
                        $reference_number,
                        'ID'
                    );
                }
                $success = true;
                unset($_SESSION['id_form_data']); // Clear form data
            } catch(PDOException $e) {
                $errors[] = 'Database error: Unable to submit application';
            }
        }
    }
}

$current_step = $_SESSION['id_form_data']['step'] ?? 1;
$form_data = $_SESSION['id_form_data'] ?? [];

include 'includes/header.php';
?>

    <!-- Application Form Section -->
    <section class="application-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="application-header text-center mb-4">
                        <div class="application-icon mb-3">
                            <i class="fas fa-id-card fa-3x text-primary"></i>
                        </div>
                        <h1>Apply for Barangay ID</h1>
                        <p class="text-muted">Complete the application form below to get your Barangay identification card</p>
                    </div>

                    <?php if ($success): ?>
                        <!-- Success Message -->
                        <div class="alert alert-success">
                            <h4 class="alert-heading"><i class="fas fa-check-circle"></i> Application Submitted Successfully!</h4>
                            <p>Your Barangay ID application has been received. Please save your reference number:</p>
                            <h3 class="mb-3"><strong><?php echo $reference_number; ?></strong></h3>
                            <p>You can use this reference number to track your application status.</p>
                            <hr>
                            <a href="track-application.php" class="btn btn-primary">Track Your Application</a>
                            <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
                        </div>
                    <?php else: ?>

                    <!-- Progress Bar -->
                    <div class="progress-wrapper mb-5">
                        <p class="small text-muted mb-2">Step <?php echo $current_step; ?> of 3 - <?php echo ($current_step * 33.33); ?>% Complete</p>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo ($current_step * 33.33); ?>%"></div>
                        </div>
                        <div class="progress-steps d-flex justify-content-between mt-2">
                            <span class="progress-step <?php echo ($current_step >= 1) ? 'active' : ''; ?>">Personal Info</span>
                            <span class="progress-step <?php echo ($current_step >= 2) ? 'active' : ''; ?>">Contact Details</span>
                            <span class="progress-step <?php echo ($current_step >= 3) ? 'active' : ''; ?>">Upload Documents</span>
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

                    <!-- Application Form -->
                    <div class="application-form">
                        
                        <?php if ($current_step == 1): ?>
                        <!-- Step 1: Personal Information -->
                        <form method="POST" action="">
                            <input type="hidden" name="step" value="1">
                            <h3 class="mb-4">Personal Information</h3>
                            <p class="text-muted mb-4">Please provide your personal information as it appears on official documents</p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" required 
                                           value="<?php echo $form_data['personal_info']['first_name'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="middleName" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="middleName" name="middleName"
                                           value="<?php echo $form_data['personal_info']['middle_name'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" required
                                           value="<?php echo $form_data['personal_info']['last_name'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="suffix" class="form-label">Suffix</label>
                                    <select class="form-select" id="suffix" name="suffix">
                                        <option value="">Select suffix</option>
                                        <option value="Jr" <?php echo (($form_data['personal_info']['suffix'] ?? '') == 'Jr') ? 'selected' : ''; ?>>Jr.</option>
                                        <option value="Sr" <?php echo (($form_data['personal_info']['suffix'] ?? '') == 'Sr') ? 'selected' : ''; ?>>Sr.</option>
                                        <option value="III" <?php echo (($form_data['personal_info']['suffix'] ?? '') == 'III') ? 'selected' : ''; ?>>III</option>
                                        <option value="IV" <?php echo (($form_data['personal_info']['suffix'] ?? '') == 'IV') ? 'selected' : ''; ?>>IV</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="birthDate" class="form-label">Date of Birth *</label>
                                    <input type="date" class="form-control" id="birthDate" name="birthDate" required
                                           value="<?php echo $form_data['personal_info']['birth_date'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="gender" class="form-label">Gender *</label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="">Select gender</option>
                                        <option value="Male" <?php echo (($form_data['personal_info']['gender'] ?? '') == 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo (($form_data['personal_info']['gender'] ?? '') == 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo (($form_data['personal_info']['gender'] ?? '') == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="civilStatus" class="form-label">Civil Status</label>
                                    <select class="form-select" id="civilStatus" name="civilStatus">
                                        <option value="">Select civil status</option>
                                        <option value="Single" <?php echo (($form_data['personal_info']['civil_status'] ?? '') == 'Single') ? 'selected' : ''; ?>>Single</option>
                                        <option value="Married" <?php echo (($form_data['personal_info']['civil_status'] ?? '') == 'Married') ? 'selected' : ''; ?>>Married</option>
                                        <option value="Widowed" <?php echo (($form_data['personal_info']['civil_status'] ?? '') == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                                        <option value="Divorced" <?php echo (($form_data['personal_info']['civil_status'] ?? '') == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                                        <option value="Separated" <?php echo (($form_data['personal_info']['civil_status'] ?? '') == 'Separated') ? 'selected' : ''; ?>>Separated</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Requirements Info Box -->
                            <div class="requirements-box mt-4 p-4 bg-light rounded">
                                <h4 class="mb-3">Requirements:</h4>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Proof of residency (at least 6 months)</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Valid government-issued ID</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Recent 2x2 ID photo (white background)</li>
                                    <li><i class="fas fa-clock text-primary me-2"></i> Processing time: 3-5 business days</li>
                                </ul>
                            </div>

                            <!-- Form Navigation -->
                            <div class="form-navigation mt-4 d-flex justify-content-between">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i> Back
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Next <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </form>

                        <?php elseif ($current_step == 2): ?>
                        <!-- Step 2: Contact Information -->
                        <form method="POST" action="">
                            <input type="hidden" name="step" value="2">
                            <h3 class="mb-4">Contact Information</h3>
                            <p class="text-muted mb-4">Provide your contact details for notifications and updates</p>
                            <div class="alert alert-info mb-4">
                                <strong>Claim Method & Fee:</strong><br>
                                Please select how you want to claim your Barangay ID. <br>
                                <span class="fw-bold">Fee: ₱100.00</span> (payable upon claiming)
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="contactNumber" class="form-label">Contact Number *</label>
                                    <input type="tel" class="form-control" id="contactNumber" name="contactNumber" 
                                           placeholder="09XX-XXX-XXXX" required
                                           value="<?php echo $form_data['contact_info']['contact_number'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?php echo $form_data['contact_info']['email'] ?? ''; ?>">
                                </div>
                                <div class="col-12">
                                    <label for="completeAddress" class="form-label">Complete Address *</label>
                                    <textarea class="form-control" id="completeAddress" name="completeAddress" rows="3" 
                                              placeholder="House No., Street, Purok, Barangay Santo Niño 1" required><?php echo $form_data['contact_info']['complete_address'] ?? ''; ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label for="preferredPickupDate" class="form-label">Preferred Pickup Date *</label>
                                    <input type="date" class="form-control" id="preferredPickupDate" name="preferredPickupDate" 
                                           min="<?php echo date('Y-m-d'); ?>" required
                                           value="<?php echo $form_data['contact_info']['preferred_pickup_date'] ?? ''; ?>">
                                    <div class="form-text">Select when you're available to pick up your Barangay ID at the office</div>
                                </div>
                                <div class="col-12">
                                    <label for="claimMethod" class="form-label">Claim Method *</label>
                                    <select class="form-select" id="claimMethod" name="claimMethod" required>
                                        <option value="pickup" <?php echo (($form_data['contact_info']['claim_method'] ?? 'pickup') == 'pickup') ? 'selected' : ''; ?>>Pickup at Barangay Hall</option>
                                        <option value="delivery" <?php echo (($form_data['contact_info']['claim_method'] ?? '') == 'delivery') ? 'selected' : ''; ?>>Delivery to Address</option>
                                    </select>
                                    <div class="form-text">Choose how you want to receive your Barangay ID</div>
                                </div>
                            </div>
                            <!-- Form Navigation -->
                            <div class="form-navigation mt-4 d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='?back=1'">
                                    <i class="fas fa-arrow-left me-2"></i> Back
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    Next <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </form>

                        <?php elseif ($current_step == 3): ?>
                        <!-- Step 3: Document Upload -->
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="step" value="3">
                            <h3 class="mb-4">Upload Documents</h3>
                            <p class="text-muted mb-4">Upload clear and legible copies of required documents (JPG, PNG, or PDF - Max 10MB)</p>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="proofOfResidency" class="form-label">Proof of Residency *</label>
                                    <input type="file" class="form-control" id="proofOfResidency" name="proofOfResidency" accept=".jpg,.jpeg,.png,.pdf" required>
                                    <div class="form-text">Accepted: Barangay Clearance, Utility Bill, Lease Agreement</div>
                                </div>
                                <div class="col-12">
                                    <label for="validId" class="form-label">Valid Government-Issued ID *</label>
                                    <input type="file" class="form-control" id="validId" name="validId" accept=".jpg,.jpeg,.png,.pdf" required>
                                    <div class="form-text">Accepted: Driver's License, Passport, National ID, etc.</div>
                                </div>
                                <div class="col-12">
                                    <label for="idPhoto" class="form-label">2x2 ID Photo (White Background) *</label>
                                    <input type="file" class="form-control" id="idPhoto" name="idPhoto" accept=".jpg,.jpeg,.png" required>
                                    <div class="form-text">Recent photograph with white background</div>
                                </div>
                            </div>

                            <!-- Form Navigation -->
                            <div class="form-navigation mt-4 d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='?back=1'">
                                    <i class="fas fa-arrow-left me-2"></i> Back
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-2"></i> Submit Application
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>

                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

<?php
// Handle back navigation
if (isset($_GET['back']) && $_GET['back'] == 1 && $current_step > 1) {
    $_SESSION['id_form_data']['step'] = $current_step - 1;
    header('Location: apply-id.php');
    exit();
}

include 'includes/footer.php';
?>
