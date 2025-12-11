<?php
require_once 'includes/config.php';
$current_page = 'track';
$page_title = 'Track Application';

$result = null;
$error = '';
$status_history = [];

// Handle tracking request
if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['ref'])) {
    $reference = sanitize_input($_POST['referenceNumber'] ?? $_GET['ref'] ?? '');
    
    if (!empty($reference)) {
        // Determine type from reference prefix
        $type = (strpos($reference, 'BID-') === 0) ? 'ID' : 'CERT';
        
        try {
            if ($type == 'ID') {
                $stmt = $conn->prepare("SELECT * FROM id_applications WHERE reference_number = ?");
                $stmt->execute([$reference]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $conn->prepare("SELECT * FROM certification_requests WHERE reference_number = ?");
                $stmt->execute([$reference]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if ($result) {
                // Get status history
                $stmt = $conn->prepare("SELECT * FROM status_history WHERE reference_number = ? ORDER BY created_at ASC");
                $stmt->execute([$reference]);
                $status_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $error = 'No application found with this reference number';
            }
            
        } catch(PDOException $e) {
            $error = 'Unable to retrieve application information';
        }
    }
}

include 'includes/header.php';
?>

    <!-- Tracking Section -->
    <section class="tracking-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="tracking-icon mb-4">
                        <i class="fas fa-search fa-3x text-warning"></i>
                    </div>
                    <h1>Track Your Application</h1>
                    <p class="text-muted mb-5">Enter your reference number to check the status of your application</p>

                    <!-- Tracking Form -->
                    <div class="tracking-form bg-white p-4 rounded shadow-sm">
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label class="form-label">Enter Reference Number</label>
                                <p class="text-muted small">You received this reference number when you submitted your application</p>
                                <div class="input-group input-group-lg">
                                    <input type="text" class="form-control" name="referenceNumber"
                                           placeholder="e.g., BID-12345678 or CERT-87654321" 
                                           value="<?php echo isset($_POST['referenceNumber']) ? htmlspecialchars($_POST['referenceNumber']) : ''; ?>"
                                           required>
                                    <button class="btn btn-dark" type="submit">
                                        <i class="fas fa-search me-2"></i> Track
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-warning mt-4">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Tracking Result -->
                    <?php if ($result): ?>
                    <div class="tracking-result mt-4">
                        <div class="result-card bg-white p-4 rounded shadow-sm">
                            <div class="row mb-4">
                                <div class="col-md-6 text-start">
                                    <h5>Application Details</h5>
                                    <p class="mb-1"><strong>Reference Number:</strong> <?php echo $result['reference_number']; ?></p>
                                    <p class="mb-1"><strong>Name:</strong> <?php echo $result['first_name'] . ' ' . ($result['middle_name'] ?? '') . ' ' . $result['last_name']; ?></p>
                                    <p class="mb-1"><strong>Type:</strong> <?php echo isset($result['certificate_type']) ? get_certificate_name($result['certificate_type']) : 'Barangay ID Application'; ?></p>
                                </div>
                                <div class="col-md-6 text-start">
                                    <h5>Current Status</h5>
                                    <?php
                                    $status_class = 'bg-warning';
                                    if (in_array($result['status'], ['Completed'])) $status_class = 'bg-success';
                                    elseif (in_array($result['status'], ['Rejected'])) $status_class = 'bg-danger';
                                    ?>
                                    <p class="mb-1">
                                        <span class="badge <?php echo $status_class; ?> fs-6">
                                            <?php echo $result['status']; ?>
                                        </span>
                                    </p>
                                    <p class="mb-1"><strong>Submitted:</strong> <?php echo format_date($result['created_at']); ?></p>
                                    <p class="mb-1"><strong>Last Updated:</strong> <?php echo format_datetime($result['updated_at']); ?></p>
                                    <?php if (!empty($result['preferred_pickup_date']) || !empty($result['preferred_date'])): ?>
                                    <p class="mb-1"><strong>Preferred Date:</strong> 
                                        <?php echo format_date($result['preferred_pickup_date'] ?? $result['preferred_date']); ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <h3 class="mb-4">Status Timeline</h3>
                            <div class="status-timeline">
                                <?php
                                // Define all possible statuses for display
                                $all_statuses = isset($result['certificate_type']) 
                                    ? ['Pending', 'Verification', 'Processing', 'Ready for Pickup', 'Completed']
                                    : ['Pending', 'Document Verification', 'Processing', 'Ready for Pickup', 'Completed'];
                                
                                $current_status = $result['status'];
                                $current_index = array_search($current_status, $all_statuses);
                                
                                foreach ($all_statuses as $index => $status):
                                    $is_completed = $index < $current_index || $status == $current_status;
                                    $is_active = $status == $current_status;
                                    $class = $is_completed ? 'completed' : ($is_active ? 'active' : '');
                                    
                                    // Find matching history entry
                                    $history_entry = null;
                                    foreach ($status_history as $history) {
                                        if ($history['new_status'] == $status) {
                                            $history_entry = $history;
                                            break;
                                        }
                                    }
                                ?>
                                <div class="timeline-item <?php echo $class; ?>">
                                    <div class="timeline-icon">
                                        <?php if ($is_completed): ?>
                                            <i class="fas fa-check-circle"></i>
                                        <?php elseif ($is_active): ?>
                                            <i class="fas fa-clock"></i>
                                        <?php else: ?>
                                            <i class="fas fa-hourglass-half"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="timeline-content">
                                        <h5><?php echo $status; ?></h5>
                                        <p class="text-muted">
                                            <?php 
                                            if ($history_entry) {
                                                echo format_datetime($history_entry['created_at']);
                                            } elseif ($is_active) {
                                                echo 'In progress';
                                            } else {
                                                echo 'Pending';
                                            }
                                            ?>
                                        </p>
                                        <?php if ($history_entry && !empty($history_entry['remarks'])): ?>
                                            <p class="small text-muted mb-0"><?php echo htmlspecialchars($history_entry['remarks']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($result['status'] == 'Ready for Pickup' || $result['status'] == 'Ready for Delivery'): ?>
                                <div class="alert alert-success mt-4">
                                    <h5 class="alert-heading"><i class="fas fa-check-circle"></i> Your application is ready!</h5>
                                    <p class="mb-0">Please visit the Barangay Hall during office hours to claim your document. 
                                    Bring a valid ID and your reference number.</p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($result['remarks'])): ?>
                                <div class="alert alert-info mt-4">
                                    <strong>Remarks:</strong> <?php echo htmlspecialchars($result['remarks']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
