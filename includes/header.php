<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Barangay Santo Niño 1 - E-Services Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <!-- Logo Section -->
        <div class="sidebar-header">
            <div class="logo-circle">
                <img src="assets/images/logo.png" alt="Barangay Santo Niño 1 Logo" class="barangay-logo">
            </div>
            <div class="brand-info">
                <div class="brand-title">Barangay Santo Niño 1</div>
                <div class="brand-subtitle">E-Services Portal</div>
            </div>
        </div>

        <!-- Minimize Button -->
        <button class="sidebar-minimize-btn" id="sidebarMinimize" title="Minimize Sidebar">
            <i class="fas fa-chevron-left"></i>
        </button>

        <!-- Navigation Links -->
        <ul class="sidebar-menu">
            <li class="<?php echo ($current_page == 'home') ? 'active' : ''; ?>">
                <a href="index.php">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'apply-id') ? 'active' : ''; ?>">
                <a href="apply-id.php">
                    <i class="fas fa-id-card"></i>
                    <span>Apply for ID</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'request-certification') ? 'active' : ''; ?>">
                <a href="request-certification.php">
                    <i class="fas fa-file-alt"></i>
                    <span>Request Certification</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'track') ? 'active' : ''; ?>">
                <a href="track-application.php">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Track Application</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'about') ? 'active' : ''; ?>">
                <a href="about-contact.php">
                    <i class="fas fa-info-circle"></i>
                    <span>About & Contact</span>
                </a>
            </li>
        </ul>

        <!-- User Section -->
        <div class="sidebar-user">
            <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])): ?>
                <div class="user-profile">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                        <div class="user-email"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></div>
                    </div>
                </div>
                <ul class="user-menu">
                    <li><a href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                    <li><a href="my-applications.php"><i class="fas fa-folder-open"></i> My Applications</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="login.php" class="btn-sidebar btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="register.php" class="btn-sidebar btn-register">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mobile Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content Wrapper -->
    <div class="main-content" id="mainContent">
