<?php
session_start();

// Auth check - redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'] ?? 'Creator';
$initial = strtoupper(substr($username, 0, 1));
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Trinity – Creator Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inria+Sans:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Base styles -->
    <link rel="stylesheet" href="css/styles.css">
    
    <!-- Authentication and user management styles (if separate) -->
    <link rel="stylesheet" href="css/dashboard-layout.css">
    <link rel="stylesheet" href="css/dashboard-styles.css">
    <link rel="stylesheet" href="css/dashboard-archives.css">
    <link rel="stylesheet" href="css/dashboard-create.css">

    <!-- Content Form and modal styles -->
    <link rel="stylesheet" href="css/form-character.css">
    <link rel="stylesheet" href="css/form-story.css">
    <link rel="stylesheet" href="css/modal-picker.css">
    <link rel="stylesheet" href="css/entry-card.css">
    <link rel="stylesheet" href="css/dashboard-profile.css">

    <!-- Cropper.js for image cropping -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <link rel="stylesheet" href="css/image-crop.css">
</head>
<body>
    <!-- Sidebar Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- ===================== SIDEBAR ===================== -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-header">
            <div class="sidebar-logo-container" id="sidebarLogoClose">
                <img src="assets/Trinity.svg" alt="Trinity Logo" class="logo-img">
                <span class="logo-text">Trinity</span>
            </div>
            <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close sidebar">✕</button>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-nav-label">Menu</div>
            <a href="#archives" class="sidebar-nav-item active" data-page="archives">
                <span class="nav-icon">◈</span>
                <span>Archives</span>
            </a>
            <a href="#view" class="sidebar-nav-item" data-page="view">
                <span class="nav-icon">◎</span>
                <span>View</span>
            </a>
            <a href="#create" class="sidebar-nav-item" data-page="create">
                <span class="nav-icon">✦</span>
                <span>Create</span>
            </a>
            <a href="#projects" class="sidebar-nav-item" data-page="projects">
                <span class="nav-icon">⬡</span>
                <span>Projects</span>
            </a>
        </nav>

        <div class="sidebar-profile">
            <div class="sidebar-divider"></div>
            <div class="profile-card">
                <div class="profile-avatar" data-page="profile"><?php echo htmlspecialchars($initial); ?></div>
                <div class="profile-info">
                    <div class="profile-name"><?php echo htmlspecialchars($username); ?></div>
                    <div class="profile-role">Creator</div>
                </div>
                <a href="index.php" class="profile-logout" title="Log out">⏻</a>
            </div>
        </div>

    </aside>

    <!-- ===================== PAGE WRAPPER ===================== -->
    <div class="page-wrapper" id="pageWrapper">

        <header class="site-header">
            <div class="logo-container" id="logoContainer">
                <img src="assets/Trinity.svg" alt="Trinity Logo" class="logo-img">
                <span class="logo-text">Trinity</span>
            </div>
            <nav class="header-nav">
                <a href="#archives" class="header-nav-item active" data-page="archives">Archives</a>
                <a href="#explore" class="header-nav-item" data-page="explore">Explore</a>
                <a href="#guides" class="header-nav-item" data-page="guides">Guides</a>
            </nav>
            <div class="user-menu">
                <span>Welcome, <span class="username-display"><?php echo htmlspecialchars($username); ?></span></span>
                <a href="#profile" class="header-avatar" data-page="profile"><?php echo htmlspecialchars($initial); ?></a>
            </div>
        </header>

        <!-- Main Content Slot — injected dynamically by dashboard-script.js -->
        <main class="site-main" id="mainContent" data-section="archives"></main>

    </div><!-- /.page-wrapper -->

    <div id="pickerModalContainer"></div>
    <div id="viewModalContainer">
        <?php include 'content-pages/Modals/viewModal.php'; ?>
    </div>
    <div id="confirmModalContainer">
        <?php include 'content-pages/Modals/confirmModal.php'; ?>
    </div>
    <div id="imageCropModalContainer">
        <?php include 'content-pages/Modals/imageCropModal.php'; ?>
    </div>

    <script src="js/dashboard-script.js"></script>
    <script src="js/picker-modal.js"></script>

    <!-- Cropper.js for image cropping -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script src="js/image-crop.js"></script>
</body>
</html>