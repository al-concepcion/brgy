<?php
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=my-applications.php');
    exit();
}

$current_page = 'my-applications';
$page_title = 'My Applications';

// Get user's applications
$user_id = $_SESSION['user_id'];

try {
    // Get ID applications
    $stmt = $conn->prepare("SELECT 'ID' as type, reference_number, CONCAT(first_name, ' ', last_name) as name, status, created_at FROM id_applications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $id_applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get certification requests
    $stmt = $conn->prepare("SELECT 'CERT' as type, reference_number, CONCAT(first_name, ' ', last_name) as name, certificate_type, status, created_at FROM certification_requests WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $cert_applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Merge all applications
    $all_applications = array_merge($id_applications, $cert_applications);
    
    // Sort by date
    usort($all_applications, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
} catch(PDOException $e) {
    $all_applications = [];
}

include 'includes/header.php';
?>

<section class="profile-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="profile-card mb-4">
                    <h2><i class="fas fa-folder-open me-2"></i> My Applications</h2>
                    <p class="text-muted">View and track all your submitted applications</p>
                </div>

                <?php if (empty($all_applications)): ?>
                    <div class="profile-card text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h4>No Applications Yet</h4>
                        <p class="text-muted mb-4">You haven't submitted any applications. Start by applying for services below.</p>
                        <a href="apply-id.php" class="btn btn-gradient me-2">
                            <i class="fas fa-id-card me-2"></i> Apply for Barangay ID
                        </a>
                        <a href="request-certification.php" class="btn btn-outline-primary">
                            <i class="fas fa-file-alt me-2"></i> Request Certification
                        </a>
                    </div>
                <?php else: ?>
                    <div class="applications-table">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Reference Number</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Date Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_applications as $app): ?>
                                <tr>
                                    <td><strong><?php echo $app['reference_number']; ?></strong></td>
                                    <td>
                                        <?php if ($app['type'] == 'ID'): ?>
                                            <span class="badge bg-primary">Barangay ID</span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?php echo get_certificate_name($app['certificate_type']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badge_class = 'bg-warning';
                                        if (in_array($app['status'], ['Completed'])) $badge_class = 'bg-success';
                                        elseif (in_array($app['status'], ['Rejected'])) $badge_class = 'bg-danger';
                                        elseif (in_array($app['status'], ['Ready for Pickup', 'Ready for Delivery'])) $badge_class = 'bg-info';
                                        ?>
                                        <span class="badge badge-status <?php echo $badge_class; ?>">
                                            <?php echo $app['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo format_date($app['created_at']); ?></td>
                                    <td>
                                        <a href="track-application.php?ref=<?php echo $app['reference_number']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                        <?php if ($app['type'] == 'ID' && in_array($app['status'], ['Ready for Pickup', 'Ready for Delivery', 'Completed'])): ?>
                                            <?php
                                            // Get ID application ID from database
                                            $stmt_id = $conn->prepare("SELECT id FROM id_applications WHERE reference_number = ?");
                                            $stmt_id->execute([$app['reference_number']]);
                                            $id_data = $stmt_id->fetch(PDO::FETCH_ASSOC);
                                            ?>
                                            <a href="print-id.php?id=<?php echo $id_data['id']; ?>" 
                                               class="btn btn-sm btn-success" target="_blank">
                                                <i class="fas fa-print me-1"></i> Print ID
                                            </a>
                                        <?php elseif ($app['type'] == 'CERT' && in_array($app['status'], ['Ready for Pickup', 'Ready for Delivery', 'Completed'])): ?>
                                            <?php
                                            // Get certificate ID from database
                                            $stmt_cert = $conn->prepare("SELECT id FROM certification_requests WHERE reference_number = ?");
                                            $stmt_cert->execute([$app['reference_number']]);
                                            $cert_data = $stmt_cert->fetch(PDO::FETCH_ASSOC);
                                            ?>
                                            <a href="print-certificate.php?id=<?php echo $cert_data['id']; ?>" 
                                               class="btn btn-sm btn-success" target="_blank">
                                                <i class="fas fa-print me-1"></i> Print Certificate
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="profile-card mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Need to apply for more services?</h5>
                                <p class="text-muted mb-0">Quick access to application forms</p>
                            </div>
                            <div>
                                <a href="apply-id.php" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-id-card me-1"></i> New ID Application
                                </a>
                                <a href="request-certification.php" class="btn btn-outline-success">
                                    <i class="fas fa-file-alt me-1"></i> New Certification
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
