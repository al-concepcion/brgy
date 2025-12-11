<?php
require_once '../includes/config.php';
require_login();

$page_title = 'Certifications';

// Handle AJAX quick action requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quick_action'])) {
    header('Content-Type: application/json');
    $id = $_POST['id'];
    $action = $_POST['action'];
    $reason = isset($_POST['reason']) ? sanitize_input($_POST['reason']) : '';
    
    try {
        // Get certification details first to check claim method
        $stmt = $conn->prepare("SELECT claim_method FROM certification_requests WHERE id = ?");
        $stmt->execute([$id]);
        $claim_method = $stmt->fetchColumn();
        
        // Determine the new status based on action
        $status = match($action) {
            'accept' => 'Processing',
            'reject' => 'Rejected',
            'release' => ($claim_method == 'delivery' ? 'Ready for Delivery' : 'Ready for Pickup'),
            default => null
        };
        
        if (!$status) {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit();
        }
        
        if ($action === 'reject' && !empty($reason)) {
            $reason = function_exists('mb_substr') ? mb_substr($reason, 0, 500) : substr($reason, 0, 500);
        }

        $remarks = match($action) {
            'accept' => 'Request accepted and now being processed',
            'reject' => (!empty($reason) ? $reason : 'Request rejected'),
            'release' => ($claim_method == 'delivery' ? 'Certificate is ready for delivery' : 'Certificate is ready for pickup'),
            default => ''
        };

        if ($action === 'reject' && empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Please provide a reason for rejection.']);
            exit();
        }
        
        // Update the certification status
        $stmt = $conn->prepare("UPDATE certification_requests SET status = ?, remarks = ? WHERE id = ?");
        $stmt->execute([$status, $remarks, $id]);
        
        // Get certification details for logging and email
        $stmt = $conn->prepare("SELECT reference_number, email, CONCAT(first_name, ' ', last_name) as name FROM certification_requests WHERE id = ?");
        $stmt->execute([$id]);
        $cert = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cert) {
            // Log status change
            $stmt = $conn->prepare("INSERT INTO status_history (reference_number, application_type, new_status, remarks, updated_by) VALUES (?, 'CERT', ?, ?, ?)");
            $stmt->execute([$cert['reference_number'], $status, $remarks, $_SESSION['admin_name']]);
            
            // Send email notification
            if ($cert['email']) {
                if (function_exists('send_status_update_email')) {
                    $email_result = send_status_update_email($cert['email'], $cert['name'], $cert['reference_number'], $status, 'CERT');
                    error_log("Email sent to {$cert['email']} for {$status}: " . ($email_result ? 'SUCCESS' : 'FAILED'));
                } else {
                    error_log("ERROR: send_status_update_email function not available!");
                }
            } else {
                error_log("No email address for certification ID: $id");
            }
        }
        
        echo json_encode(['success' => true, 'message' => ucfirst($action) . ' action completed successfully']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to process action: ' . $e->getMessage()]);
    }
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $remarks = sanitize_input($_POST['remarks'] ?? '');
    
    try {
        $stmt = $conn->prepare("UPDATE certification_requests SET status = ?, remarks = ? WHERE id = ?");
        $stmt->execute([$status, $remarks, $id]);
        
        // Log status change
        $stmt = $conn->prepare("SELECT reference_number FROM certification_requests WHERE id = ?");
        $stmt->execute([$id]);
        $ref = $stmt->fetchColumn();
        
        $stmt = $conn->prepare("INSERT INTO status_history (reference_number, application_type, new_status, remarks, updated_by) VALUES (?, 'CERT', ?, ?, ?)");
        $stmt->execute([$ref, $status, $remarks, $_SESSION['admin_name']]);
        
        // Send email notification
        $stmt = $conn->prepare("SELECT email, CONCAT(first_name, ' ', last_name) as name FROM certification_requests WHERE reference_number = ?");
        $stmt->execute([$ref]);
        $cert_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cert_data && $cert_data['email']) {
            send_status_update_email($cert_data['email'], $cert_data['name'], $ref, $status, 'CERT');
        }
        
        $success = 'Status updated successfully';
    } catch(PDOException $e) {
        $error = 'Failed to update status';
    }
}

// Get all certifications
$certifications = [];
try {
    $stmt = $conn->query("SELECT * FROM certification_requests ORDER BY created_at DESC");
    $certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = 'Failed to load certifications';
}

$cert_types = [
    'residency' => 'Certificate of Residency',
    'indigency' => 'Certificate of Indigency',
    'clearance' => 'Barangay Clearance',
    'business' => 'Business Clearance',
    'good_moral' => 'Good Moral Character'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-user-shield me-2"></i> Admin Panel
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i> <?php echo $_SESSION['admin_name']; ?>
                </span>
                <a href="../index.php" class="btn btn-sm btn-outline-light me-2" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Site
                </a>
                <a href="logout.php" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 bg-light vh-100 p-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="id-applications.php">
                            <i class="fas fa-id-card me-2"></i> ID Applications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="certifications.php">
                            <i class="fas fa-file-alt me-2"></i> Certifications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">
                            <i class="fas fa-envelope me-2"></i> Messages
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4">
                    <i class="fas fa-file-alt me-2"></i> Certification Requests
                </h2>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Reference #</th>
                                        <th>Certificate Type</th>
                                        <th>Name</th>
                                        <th>Contact</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certifications as $cert): ?>
                                    <tr>
                                        <td><strong><?php echo $cert['reference_number']; ?></strong></td>
                                        <td><?php echo $cert_types[$cert['certificate_type']] ?? $cert['certificate_type']; ?></td>
                                        <td><?php echo $cert['first_name'] . ' ' . $cert['last_name']; ?></td>
                                        <td><?php echo $cert['contact_number']; ?></td>
                                        <td>₱<?php echo number_format($cert['price'], 2); ?></td>
                                        <td>
                                            <?php
                                            $badge_class = match($cert['status']) {
                                                'Pending' => 'bg-warning',
                                                'Verification' => 'bg-info',
                                                'Processing' => 'bg-primary',
                                                'Ready for Pickup', 'Ready for Delivery' => 'bg-success',
                                                'Completed' => 'bg-dark',
                                                'Rejected' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <?php echo $cert['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo format_date($cert['created_at']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $cert['id']; ?>" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#updateModal<?php echo $cert['id']; ?>" title="Update Status">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if (in_array($cert['status'], ['Ready for Pickup', 'Ready for Delivery', 'Completed'])): ?>
                                            <a href="print-certificate.php?id=<?php echo $cert['id']; ?>" class="btn btn-sm btn-secondary" target="_blank" title="Print Certificate">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($cert['status'] == 'Pending'): ?>
                                            <button class="btn btn-sm btn-success quick-action" data-id="<?php echo $cert['id']; ?>" data-action="accept" title="Accept Request">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger quick-action" data-id="<?php echo $cert['id']; ?>" data-action="reject" title="Reject Request">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($cert['status'] == 'Processing'): ?>
                                            <button class="btn btn-sm btn-warning quick-action" data-id="<?php echo $cert['id']; ?>" data-action="release" title="Release Certificate">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- View Modal -->
                                    <div class="modal fade" id="viewModal<?php echo $cert['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Certification Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p><strong>Reference Number:</strong><br><?php echo $cert['reference_number']; ?></p>
                                                            <p><strong>Certificate Type:</strong><br><?php echo $cert_types[$cert['certificate_type']]; ?></p>
                                                            <p><strong>Full Name:</strong><br><?php echo $cert['first_name'] . ' ' . $cert['middle_name'] . ' ' . $cert['last_name']; ?></p>
                                                            <p><strong>Contact Number:</strong><br><?php echo $cert['contact_number']; ?></p>
                                                            <p><strong>Email:</strong><br><?php echo $cert['email']; ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Address:</strong><br><?php echo $cert['complete_address']; ?></p>
                                                            <p><strong>Purpose:</strong><br><?php echo $cert['purpose']; ?></p>
                                                            <p><strong>Claim Method:</strong><br><?php echo ucfirst($cert['claim_method']); ?></p>
                                                            <?php if ($cert['preferred_date']): ?>
                                                            <p><strong>Preferred <?php echo ucfirst($cert['claim_method']); ?> Date:</strong><br><?php echo format_date($cert['preferred_date']); ?></p>
                                                            <?php endif; ?>
                                                            <p><strong>Price:</strong><br>₱<?php echo number_format($cert['price'], 2); ?></p>
                                                            <p><strong>Status:</strong><br><span class="badge <?php echo $badge_class; ?>"><?php echo $cert['status']; ?></span></p>
                                                            <?php if ($cert['remarks']): ?>
                                                            <p><strong>Remarks:</strong><br><?php echo $cert['remarks']; ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-12 mt-3">
                                                            <h5 class="mb-3">Supporting Documents</h5>
                                                            <?php
                                                            // Verify presence of the uploaded document before showing links to the admin
                                                            $hasSupportingDoc = !empty($cert['supporting_documents']);
                                                            $storagePath = $hasSupportingDoc ? UPLOAD_DIR . 'certifications/' . $cert['supporting_documents'] : '';
                                                            $fileAvailable = $hasSupportingDoc && file_exists($storagePath);
                                                            $viewUrl = $fileAvailable ? 'view-document.php?type=cert&mode=open&file=' . urlencode($cert['supporting_documents']) : '';
                                                            $downloadUrl = $fileAvailable ? 'view-document.php?type=cert&mode=download&file=' . urlencode($cert['supporting_documents']) : '';
                                                            ?>
                                                            <div class="border rounded p-3">
                                                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                                                                    <div>
                                                                        <strong>Uploaded File</strong><br>
                                                                        <?php if ($fileAvailable): ?>
                                                                            <span class="text-muted small">File: <?php echo htmlspecialchars($cert['supporting_documents']); ?></span>
                                                                        <?php elseif ($hasSupportingDoc): ?>
                                                                            <span class="text-danger small">Stored filename missing from uploads directory</span>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">No supporting document submitted</span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <?php if ($fileAvailable): ?>
                                                                    <div class="d-flex gap-2">
                                                                        <a class="btn btn-outline-primary btn-sm" href="<?php echo htmlspecialchars($viewUrl); ?>" target="_blank" rel="noopener">
                                                                            <i class="fas fa-external-link-alt me-1"></i> Open
                                                                        </a>
                                                                        <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($downloadUrl); ?>">
                                                                            <i class="fas fa-download me-1"></i> Download
                                                                        </a>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Update Status Modal -->
                                    <div class="modal fade" id="updateModal<?php echo $cert['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Update Status</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?php echo $cert['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Status</label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="Pending" <?php echo $cert['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="Verification" <?php echo $cert['status'] == 'Verification' ? 'selected' : ''; ?>>Verification</option>
                                                                <option value="Processing" <?php echo $cert['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                                                <option value="Ready for Pickup" <?php echo $cert['status'] == 'Ready for Pickup' ? 'selected' : ''; ?>>Ready for Pickup</option>
                                                                <option value="Ready for Delivery" <?php echo $cert['status'] == 'Ready for Delivery' ? 'selected' : ''; ?>>Ready for Delivery</option>
                                                                <option value="Completed" <?php echo $cert['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                                <option value="Rejected" <?php echo $cert['status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Remarks</label>
                                                            <textarea name="remarks" class="form-control" rows="3"><?php echo $cert['remarks']; ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            $('table').DataTable({
                pageLength: 10,
                order: [[6, 'desc']],
                language: {
                    search: "Search certifications:",
                    lengthMenu: "Show _MENU_ certifications per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ certifications"
                }
            });

            <?php if (isset($success)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo $success; ?>',
                timer: 3000,
                showConfirmButton: false
            });
            <?php endif; ?>

            <?php if (isset($error)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '<?php echo $error; ?>'
            });
            <?php endif; ?>
            
            // Handle quick action buttons
            $('.quick-action').on('click', function() {
                const btn = $(this);
                const id = btn.data('id');
                const action = btn.data('action');

                const actionText = {
                    'accept': 'Accept',
                    'reject': 'Reject',
                    'release': 'Release Certificate'
                }[action];

                const actionMessage = {
                    'accept': 'This will move the request to Processing status.',
                    'reject': 'This will reject the certification request.',
                    'release': 'This will mark the certificate as ready for pickup/delivery.'
                }[action];

                const processAction = (reason = '') => {
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: 'certifications.php',
                        type: 'POST',
                        data: {
                            quick_action: true,
                            id: id,
                            action: action,
                            reason: reason
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: response.message,
                                    confirmButtonColor: '#d33'
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Failed to process request',
                                confirmButtonColor: '#d33'
                            });
                        }
                    });
                };

                if (action === 'reject') {
                    Swal.fire({
                        title: 'Reject Request',
                        text: actionMessage,
                        input: 'textarea',
                        inputLabel: 'Reason for Rejection',
                        inputPlaceholder: 'Provide a brief explanation…',
                        inputAttributes: {
                            'aria-label': 'Reason for rejection'
                        },
                        inputValidator: (value) => {
                            if (!value || !value.trim()) {
                                return 'Please provide a reason for rejection.';
                            }
                            if (value.length > 500) {
                                return 'Please keep the reason within 500 characters.';
                            }
                            return null;
                        },
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Reject Request'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            processAction(result.value.trim());
                        }
                    });
                } else {
                    Swal.fire({
                        title: actionText + ' Request?',
                        text: actionMessage,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, ' + action + ' it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            processAction();
                        }
                    });
                }
            });
        });

        $('form').on('submit', function(e) {
            const statusField = $(this).find('select[name="status"]');
            const remarksField = $(this).find('textarea[name="remarks"]');
            if (statusField.length && statusField.val() === 'Rejected') {
                const remarks = remarksField.val() || '';
                if (!remarks.trim()) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Remarks Required',
                        text: 'Please provide the reason for rejection before updating the status.',
                        confirmButtonColor: '#d33'
                    });
                    return;
                }
            }

            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Update Status?',
                text: 'Confirm status update for this certification request',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Yes, update!'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    </script>
</body>
</html>
