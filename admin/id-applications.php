<?php
require_once '../includes/config.php';
require_login();

$page_title = 'ID Applications';

// Handle AJAX quick action requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quick_action'])) {
    header('Content-Type: application/json');
    $id = $_POST['id'];
    $action = $_POST['action'];
    $reason = isset($_POST['reason']) ? sanitize_input($_POST['reason']) : '';
    
    try {
        // Determine the new status based on action
        $status = match($action) {
            'accept' => 'Processing',
            'reject' => 'Rejected',
            'release' => 'Ready for Pickup',
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
            'accept' => 'Application accepted and now being processed',
            'reject' => (!empty($reason) ? $reason : 'Application rejected'),
            'release' => 'Barangay ID is ready for pickup',
            default => ''
        };

        if ($action === 'reject' && empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Please provide a reason for rejection.']);
            exit();
        }
        
        // Update the application status
        $stmt = $conn->prepare("UPDATE id_applications SET status = ?, remarks = ? WHERE id = ?");
        $stmt->execute([$status, $remarks, $id]);
        
        // Get application details for logging and email
        $stmt = $conn->prepare("SELECT reference_number, email, CONCAT(first_name, ' ', last_name) as name FROM id_applications WHERE id = ?");
        $stmt->execute([$id]);
        $app = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($app) {
            // Log status change
            $stmt = $conn->prepare("INSERT INTO status_history (reference_number, application_type, new_status, remarks, updated_by) VALUES (?, 'ID', ?, ?, ?)");
            $stmt->execute([$app['reference_number'], $status, $remarks, $_SESSION['admin_name']]);
            
            // Send email notification
            if ($app['email']) {
                if (function_exists('send_status_update_email')) {
                    $email_result = send_status_update_email($app['email'], $app['name'], $app['reference_number'], $status, 'ID');
                    error_log("Email sent to {$app['email']} for {$status}: " . ($email_result ? 'SUCCESS' : 'FAILED'));
                } else {
                    error_log("ERROR: send_status_update_email function not available!");
                }
            } else {
                error_log("No email address for application ID: $id");
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
        $stmt = $conn->prepare("UPDATE id_applications SET status = ?, remarks = ? WHERE id = ?");
        $stmt->execute([$status, $remarks, $id]);
        
        // Log status change
        $stmt = $conn->prepare("SELECT reference_number FROM id_applications WHERE id = ?");
        $stmt->execute([$id]);
        $ref = $stmt->fetchColumn();
        
        $stmt = $conn->prepare("INSERT INTO status_history (reference_number, application_type, new_status, remarks, updated_by) VALUES (?, 'ID', ?, ?, ?)");
        $stmt->execute([$ref, $status, $remarks, $_SESSION['admin_name']]);
        
        // Send email notification
        $stmt = $conn->prepare("SELECT email, CONCAT(first_name, ' ', last_name) as name FROM id_applications WHERE reference_number = ?");
        $stmt->execute([$ref]);
        $app_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($app_data && $app_data['email']) {
            send_status_update_email($app_data['email'], $app_data['name'], $ref, $status, 'ID');
        }
        
        $success = 'Status updated successfully';
    } catch(PDOException $e) {
        $error = 'Failed to update status';
    }
}

// Get all ID applications
$applications = [];
try {
    $stmt = $conn->query("SELECT * FROM id_applications ORDER BY created_at DESC");
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = 'Failed to load applications';
}
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
                        <a class="nav-link active" href="id-applications.php">
                            <i class="fas fa-id-card me-2"></i> ID Applications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="certifications.php">
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
                    <i class="fas fa-id-card me-2"></i> ID Applications
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
                                        <th>Name</th>
                                        <th>Contact</th>
                                        <th>Email</th>
                                        <th>Claim Method</th>
                                        <th>Fee</th>
                                        <th>Status</th>
                                        <th>Date Applied</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><strong><?php echo $app['reference_number']; ?></strong></td>
                                        <td><?php echo $app['first_name'] . ' ' . $app['middle_name'] . ' ' . $app['last_name']; ?></td>
                                        <td><?php echo $app['contact_number']; ?></td>
                                        <td><?php echo $app['email']; ?></td>
                                        <td><?php echo ucfirst($app['claim_method'] ?? 'pickup'); ?></td>
                                        <td>₱<?php echo number_format($app['price'] ?? 100, 2); ?></td>
                                        <td>
                                            <?php
                                            $badge_class = match($app['status']) {
                                                'Pending' => 'bg-warning',
                                                'Document Verification' => 'bg-info',
                                                'Processing' => 'bg-primary',
                                                'Ready for Pickup' => 'bg-success',
                                                'Completed' => 'bg-dark',
                                                'Rejected' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <?php echo $app['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo format_date($app['created_at']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $app['id']; ?>" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#updateModal<?php echo $app['id']; ?>" title="Update Status">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if (in_array($app['status'], ['Ready for Pickup', 'Completed'])): ?>
                                            <a href="print-id.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-secondary" target="_blank" title="Print ID">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if ($app['status'] == 'Pending'): ?>
                                            <button class="btn btn-sm btn-success quick-action" data-id="<?php echo $app['id']; ?>" data-action="accept" title="Accept Application">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger quick-action" data-id="<?php echo $app['id']; ?>" data-action="reject" title="Reject Application">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($app['status'] == 'Processing'): ?>
                                            <button class="btn btn-sm btn-warning quick-action" data-id="<?php echo $app['id']; ?>" data-action="release" title="Release for Pickup">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- View Modal -->
                                    <div class="modal fade" id="viewModal<?php echo $app['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Application Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p><strong>Reference Number:</strong><br><?php echo $app['reference_number']; ?></p>
                                                            <p><strong>Full Name:</strong><br><?php echo $app['first_name'] . ' ' . $app['middle_name'] . ' ' . $app['last_name'] . ' ' . $app['suffix']; ?></p>
                                                            <p><strong>Birth Date:</strong><br><?php echo format_date($app['birth_date']); ?></p>
                                                            <p><strong>Gender:</strong><br><?php echo $app['gender']; ?></p>
                                                            <p><strong>Civil Status:</strong><br><?php echo $app['civil_status']; ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Contact Number:</strong><br><?php echo $app['contact_number']; ?></p>
                                                            <p><strong>Email:</strong><br><?php echo $app['email']; ?></p>
                                                            <p><strong>Address:</strong><br><?php echo $app['complete_address']; ?></p>
                                                            <?php if ($app['preferred_pickup_date']): ?>
                                                            <p><strong>Preferred Pickup Date:</strong><br><?php echo format_date($app['preferred_pickup_date']); ?></p>
                                                            <?php endif; ?>
                                                            <p><strong>Claim Method:</strong><br><?php echo ucfirst($app['claim_method'] ?? 'pickup'); ?></p>
                                                            <p><strong>Fee:</strong><br>₱<?php echo number_format($app['price'] ?? 100, 2); ?></p>
                                                            <p><strong>Status:</strong><br><span class="badge <?php echo $badge_class; ?>"><?php echo $app['status']; ?></span></p>
                                                            <?php if ($app['remarks']): ?>
                                                            <p><strong>Remarks:</strong><br><?php echo $app['remarks']; ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-12 mt-3">
                                                            <h5 class="mb-3">Submitted Requirements</h5>
                                                            <?php
                                                            $documents = [
                                                                'Proof of Residency' => $app['proof_of_residency'],
                                                                'Valid Government ID' => $app['valid_id'],
                                                                'ID Photo' => $app['id_photo']
                                                            ];

                                                            // Verify the file exists before exposing links to the admin
                                                            foreach ($documents as $label => $filename):
                                                                $hasFile = !empty($filename);
                                                                $storagePath = $hasFile ? UPLOAD_DIR . 'id_applications/' . $filename : '';
                                                                $fileAvailable = $hasFile && file_exists($storagePath);
                                                                $viewUrl = $fileAvailable ? 'view-document.php?type=id&mode=open&file=' . urlencode($filename) : '';
                                                                $downloadUrl = $fileAvailable ? 'view-document.php?type=id&mode=download&file=' . urlencode($filename) : '';
                                                            ?>
                                                                <div class="border rounded p-3 mb-2">
                                                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                                                                        <div>
                                                                            <strong><?php echo htmlspecialchars($label); ?></strong><br>
                                                                            <?php if ($fileAvailable): ?>
                                                                                <span class="text-muted small">File: <?php echo htmlspecialchars($filename); ?></span>
                                                                            <?php elseif ($hasFile): ?>
                                                                                <span class="text-danger small">Stored filename missing from uploads directory</span>
                                                                            <?php else: ?>
                                                                                <span class="text-muted">Not submitted</span>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                        <div class="d-flex gap-2">
                                                                            <?php if ($fileAvailable): ?>
                                                                                <a class="btn btn-outline-primary btn-sm" href="<?php echo htmlspecialchars($viewUrl); ?>" target="_blank" rel="noopener">
                                                                                    <i class="fas fa-external-link-alt me-1"></i> Open
                                                                                </a>
                                                                                <a class="btn btn-outline-secondary btn-sm" href="<?php echo htmlspecialchars($downloadUrl); ?>">
                                                                                    <i class="fas fa-download me-1"></i> Download
                                                                                </a>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Update Status Modal -->
                                    <div class="modal fade" id="updateModal<?php echo $app['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Update Status</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Status</label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="Pending" <?php echo $app['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="Document Verification" <?php echo $app['status'] == 'Document Verification' ? 'selected' : ''; ?>>Document Verification</option>
                                                                <option value="Processing" <?php echo $app['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                                                <option value="Ready for Pickup" <?php echo $app['status'] == 'Ready for Pickup' ? 'selected' : ''; ?>>Ready for Pickup</option>
                                                                <option value="Completed" <?php echo $app['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                                <option value="Rejected" <?php echo $app['status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Remarks</label>
                                                            <textarea name="remarks" class="form-control" rows="3"><?php echo $app['remarks']; ?></textarea>
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
            // Initialize DataTable
            $('table').DataTable({
                pageLength: 10,
                order: [[5, 'desc']], // Sort by date
                language: {
                    search: "Search applications:",
                    lengthMenu: "Show _MENU_ applications per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ applications"
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
                text: '<?php echo $error; ?>',
                confirmButtonColor: '#d33'
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
                    'release': 'Release for Pickup'
                }[action];

                const actionMessage = {
                    'accept': 'This will move the application to Processing status.',
                    'reject': 'This will reject the application.',
                    'release': 'This will mark the ID as ready for pickup.'
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
                        url: 'id-applications.php',
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
                        title: 'Reject Application',
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
                        confirmButtonText: 'Reject Application'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            processAction(result.value.trim());
                        }
                    });
                } else {
                    Swal.fire({
                        title: actionText + ' Application?',
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

        // Confirm before updating status
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
                text: 'Are you sure you want to update this application status?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>
