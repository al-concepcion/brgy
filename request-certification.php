<?php
require_once 'includes/config.php';
$current_page = 'request-certification';
$page_title = 'Request Certification';

require_user_login('request-certification.php');

$errors = [];
$success = false;
$reference_number = '';
$selected_certificate = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $certificate_type = sanitize_input($_POST['certificateType'] ?? '');
    $first_name = sanitize_input($_POST['firstName'] ?? '');
    $middle_name = sanitize_input($_POST['middleName'] ?? '');
    $last_name = sanitize_input($_POST['lastName'] ?? '');
    $complete_address = sanitize_input($_POST['completeAddress'] ?? '');
    $contact_number = sanitize_input($_POST['contactNumber'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $purpose = sanitize_input($_POST['purpose'] ?? '');
    $claim_method = sanitize_input($_POST['claimMethod'] ?? 'pickup');
    $preferred_date = sanitize_input($_POST['preferredDate'] ?? '');
    
    // Validation
    if (empty($certificate_type)) {
        $errors[] = 'Please select a certification type';
    }
    if (empty($first_name) || empty($last_name)) {
        $errors[] = 'First name and last name are required';
    }
    if (empty($complete_address)) {
        $errors[] = 'Complete address is required';
    }
    if (empty($contact_number)) {
        $errors[] = 'Contact number is required';
    }
    if (empty($purpose)) {
        $errors[] = 'Purpose is required';
    }
    if (empty($preferred_date)) {
        $errors[] = 'Preferred pickup/delivery date is required';
    } else {
        // Validate that the date is in the future
        $selected_date = new DateTime($preferred_date);
        $today = new DateTime('today');
        if ($selected_date < $today) {
            $errors[] = 'Preferred date must be today or a future date';
        }
    }
    
    // Upload supporting documents if provided
    $document_filename = null;
    if (isset($_FILES['supportingDocuments']) && $_FILES['supportingDocuments']['error'] == UPLOAD_ERR_OK) {
        $result = upload_file($_FILES['supportingDocuments'], 'certifications');
        if (is_array($result) && isset($result['error'])) {
            $errors[] = $result['error'];
        } else {
            $document_filename = $result;
        }
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            $reference_number = generate_application_number('CERT');
            $price = get_certificate_price($certificate_type);
            
            $stmt = $conn->prepare("INSERT INTO certification_requests (
                user_id, reference_number, certificate_type, first_name, middle_name, last_name,
                complete_address, contact_number, email, purpose, supporting_documents,
                claim_method, preferred_date, price, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $reference_number,
                $certificate_type,
                $first_name,
                $middle_name,
                $last_name,
                $complete_address,
                $contact_number,
                $email,
                $purpose,
                $document_filename,
                $claim_method,
                $preferred_date,
                $price
            ]);
            
            // Log status
            log_status_change($conn, $reference_number, 'CERT', null, 'Pending', 'Certification request submitted');
            
            // Send confirmation email
            if (function_exists('send_application_confirmation')) {
                send_application_confirmation(
                    $email,
                    $first_name . ' ' . $last_name,
                    $reference_number,
                    'CERT'
                );
            }
            
            $success = true;
            
        } catch(PDOException $e) {
            $errors[] = 'Database error: Unable to submit request';
        }
    }
}

include 'includes/header.php';
?>

    <!-- Certification Request Section -->
    <section class="certification-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <div class="certification-icon mb-3">
                    <i class="fas fa-file-alt fa-3x text-danger"></i>
                </div>
                <h1>Request Barangay Certification</h1>
                <p class="text-muted">Apply for various barangay certifications and clearances online</p>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">

                    <?php if ($success): ?>
                        <!-- Success Message -->
                        <div class="alert alert-success">
                            <h4 class="alert-heading"><i class="fas fa-check-circle"></i> Request Submitted Successfully!</h4>
                            <p>Your certification request has been received. Please save your reference number:</p>
                            <h3 class="mb-3"><strong><?php echo $reference_number; ?></strong></h3>
                            <p>You can use this reference number to track your request status.</p>
                            <hr>
                            <a href="track-application.php" class="btn btn-primary">Track Your Request</a>
                            <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
                        </div>
                    <?php else: ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Certificate Selection -->
                    <div class="certification-options mb-5">
                        <h3>Select Certification Type</h3>
                        <p class="text-muted mb-4">Choose the type of certificate you need</p>
                        
                        <div class="row g-4">
                            <?php
                            $certificates = [
                                'residency' => ['name' => 'Certificate of Residency', 'price' => '₱50.00', 'desc' => 'Certifies that you are a resident of the barangay'],
                                'indigency' => ['name' => 'Certificate of Indigency', 'price' => 'Free', 'desc' => 'For financially disadvantaged residents'],
                                'clearance' => ['name' => 'Barangay Clearance', 'price' => '₱100.00', 'desc' => 'Certifies no derogatory records in the barangay'],
                                'business' => ['name' => 'Barangay Business Clearance', 'price' => '₱200.00', 'desc' => 'Required for business permit application'],
                                'good_moral' => ['name' => 'Certificate of Good Moral Character', 'price' => '₱50.00', 'desc' => 'Attests to your good moral standing']
                            ];
                            
                            foreach ($certificates as $type => $cert):
                            ?>
                            <div class="col-md-6">
                                <div class="certificate-card" data-certificate="<?php echo $type; ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h4><?php echo $cert['name']; ?></h4>
                                        <span class="price"><?php echo $cert['price']; ?></span>
                                    </div>
                                    <p class="text-muted"><?php echo $cert['desc']; ?></p>
                                    <div class="form-check mt-3">
                                        <input class="form-check-input certificate-radio" type="radio" name="certificateType" 
                                               value="<?php echo $type; ?>" id="<?php echo $type; ?>" form="certificationForm">
                                        <label class="form-check-label" for="<?php echo $type; ?>">Select</label>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Personal Information Form -->
                    <form id="certificationForm" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="certification-form bg-white p-4 rounded shadow-sm">
                            <h3>Personal Information</h3>
                            <p class="text-muted mb-4">Provide your details as they will appear on the certificate</p>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" class="form-control" name="firstName" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" name="middleName">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" name="lastName" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Complete Address *</label>
                                     <input type="text" class="form-control" name="completeAddress" 
                                         placeholder="House No., Street, Purok, Barangay Santo Niño 1" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Number *</label>
                                    <input type="tel" class="form-control" name="contactNumber" 
                                           placeholder="09XX-XXX-XXXX" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Purpose *</label>
                                    <textarea class="form-control" name="purpose" rows="3" 
                                              placeholder="Please state the purpose of this certification request" required></textarea>
                                    <div class="form-text">Example: For employment requirements, school enrollment, loan application, etc.</div>
                                </div>
                            </div>

                            <!-- Supporting Documents -->
                            <div class="mt-4">
                                <h4>Supporting Documents</h4>
                                <p class="text-muted">Upload required documents for verification (Optional but recommended)</p>
                                <div class="mb-3">
                                    <input type="file" class="form-control" name="supportingDocuments" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text">Upload valid ID and other required documents as a single PDF or image (Max 10MB)</div>
                                </div>
                            </div>

                            <!-- Claim Method -->
                            <div class="mt-4">
                                <h4>Claim Method</h4>
                                <p class="text-muted">How would you like to receive your certificate?</p>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="claimMethod" id="pickup" value="pickup" checked>
                                    <label class="form-check-label" for="pickup">
                                        <strong>Pick up at Barangay Hall</strong>
                                        <small class="d-block text-muted">Available for pickup within 1-2 business days</small>
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="claimMethod" id="delivery" value="delivery">
                                    <label class="form-check-label" for="delivery">
                                        <strong>Home Delivery</strong>
                                        <small class="d-block text-muted">Additional fee may apply (within Barangay only)</small>
                                    </label>
                                </div>
                                
                                <div class="mt-3">
                                    <label class="form-label">Preferred Pickup/Delivery Date *</label>
                                    <input type="date" class="form-control" name="preferredDate" id="preferredDate" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                    <div class="form-text" id="dateHelpText">Select when you're available to pick up the certificate</div>
                                </div>
                            </div>

                            <div class="alert alert-info mt-4">
                                <strong><i class="fas fa-info-circle"></i> Note:</strong> Please select a certification type above before submitting.
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mt-4">
                                <i class="fas fa-check me-2"></i> Submit Request
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <script>
    // Certificate card click handler
    document.addEventListener('DOMContentLoaded', function() {
        const certCards = document.querySelectorAll('.certificate-card');
        const claimMethodRadios = document.querySelectorAll('input[name="claimMethod"]');
        const dateHelpText = document.getElementById('dateHelpText');
        
        certCards.forEach(card => {
            card.addEventListener('click', function() {
                const radio = this.querySelector('.certificate-radio');
                radio.checked = true;
                
                // Remove active class from all cards
                certCards.forEach(c => c.classList.remove('active'));
                
                // Add active class to selected card
                this.classList.add('active');
            });
        });
        
        // Update date help text based on claim method
        claimMethodRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'pickup') {
                    dateHelpText.textContent = 'Select when you\'re available to pick up the certificate';
                } else {
                    dateHelpText.textContent = 'Select your preferred delivery date';
                }
            });
        });
    });
    </script>

<?php include 'includes/footer.php'; ?>
