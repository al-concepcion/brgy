<?php
require_once 'includes/config.php';
$current_page = 'home';
$page_title = 'Home';

// Get statistics
$stats = get_statistics($conn);

// Get recent announcements
$announcements = get_announcements($conn, 3);

include 'includes/header.php';
?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <div class="hero-content text-white">
                        <span class="badge bg-warning mb-3">Welcome to Our E-Services Portal</span>
                        <h1>Barangay Santo Niño 1</h1>
                        <p class="lead">Access Barangay services online. Apply for IDs, request certifications, and track your applications - all from the comfort of your home.</p>
                        <div class="mt-4">
                            <a href="apply-id.php" class="btn btn-light me-3">Get Started</a>
                            <a href="about-contact.php" class="btn btn-outline-light">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Services Section -->
    <section class="services-section py-5">
        <div class="container">
            <h2 class="section-title">Quick Services</h2>
            <p class="section-subtitle">Access the services you need quickly and easily</p>
            
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon bg-primary">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h3>Apply for Barangay ID</h3>
                        <p>Get your official Barangay identification card</p>
                        <a href="apply-id.php" class="service-link">Access Service <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon bg-danger">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3>Request Certification</h3>
                        <p>Apply for residency, indigency, clearance, and more</p>
                        <a href="request-certification.php" class="service-link">Access Service <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <div class="service-icon bg-warning">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Track Application</h3>
                        <p>Check the status of your submitted applications</p>
                        <a href="track-application.php" class="service-link">Access Service <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Track Application Section -->
    <section class="tracking-section py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="tracking-icon mb-4">
                        <i class="fas fa-map-marker-alt fa-3x text-success"></i>
                    </div>
                    <h2 class="section-title">Track Your Application</h2>
                    <p class="text-muted mb-4">Enter your reference number to check the status of your application</p>

                    <!-- Tracking Form -->
                    <div class="tracking-form bg-white p-4 rounded shadow-sm">
                        <form method="GET" action="track-application.php">
                            <div class="mb-3">
                                <label class="form-label">Enter Reference Number</label>
                                <p class="text-muted small">You received this reference number when you submitted your application</p>
                                <div class="input-group input-group-lg">
                                    <input type="text" class="form-control" name="ref"
                                           placeholder="e.g., BID-12345678 or CERT-87654321" 
                                           required>
                                    <button class="btn btn-success" type="submit">
                                        <i class="fas fa-search me-2"></i> Track Now
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="stat-item">
                        <i class="fas fa-users mb-3 text-primary"></i>
                        <h3><?php echo number_format($stats['residents'] ?? 0); ?></h3>
                        <p>Registered Residents</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <i class="fas fa-file-check mb-3 text-primary"></i>
                        <h3><?php echo number_format($stats['applications'] ?? 0); ?></h3>
                        <p>Applications Processed</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <i class="fas fa-clock mb-3 text-primary"></i>
                        <h3><?php echo htmlspecialchars($stats['processing_time_label'] ?? 'N/A'); ?></h3>
                        <p>Average Processing Time</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Announcements Section -->
    <section class="announcements-section py-5">
        <div class="container">
            <h2 class="section-title">Latest Announcements</h2>
            <div class="row mt-4">
                <?php if (!empty($announcements)): ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="col-md-4">
                            <div class="announcement-card">
                                <?php
                                $badge_class = 'bg-dark';
                                if ($announcement['badge_type'] == 'Important') $badge_class = 'bg-danger';
                                elseif ($announcement['badge_type'] == 'New') $badge_class = 'bg-dark';
                                elseif ($announcement['badge_type'] == 'Event') $badge_class = 'bg-dark';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo $announcement['badge_type']; ?></span>
                                <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
                                <p><?php echo htmlspecialchars($announcement['content']); ?></p>
                                <div class="announcement-date"><?php echo format_date($announcement['created_at']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">No announcements at this time.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Need Assistance Section -->
    <section class="assistance-section py-5 bg-primary text-white">
        <div class="container text-center">
            <h2>Need Assistance?</h2>
            <p class="lead">Our staff is here to help you with any questions about our services</p>
            <a href="about-contact.php#contact-form" class="btn btn-light mt-3">Contact Us</a>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
