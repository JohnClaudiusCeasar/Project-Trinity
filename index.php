<?php
$pageTitle = "PROJECT TRINITY - Archives";
$extraCSS = "css/index-styles.css";
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section (Fixed on page load) -->
<section class="hero-section">
    <h1 class="hero-title">PROJECT TRINITY</h1>
    <nav class="nav-buttons">
        <a href="login.php" class="nav-link">LOGIN</a>
        <div class="nav-divider"></div>
        <a href="register.php" class="nav-link">REGISTER</a>
    </nav>
</section>

<!-- Scrollable content -->
<div class="scrollable-content">
    <!-- About Us section -->
    <section class="about-section">
        <h2 class="section-title">ABOUT US:</h2>
        <p class="section-text">
            <span class="drop-cap">P</span>ROJECT TRINITY is a non-profit, personal creative group developed by a pair of creative minds, to establish an online creative environment where users can store, and outline a cohesive narrative blueprint to better visualize their project. Think of the SCP wiki website, but for a broad environment of creativity.
        </p>
    </section>

    <!-- Creators section -->
    <section class="creators-section">
        <h2 class="section-title">CREATORS:</h2>
        <div class="creators-grid">
            <div class="creator-card">
                <div class="creator-pfp"></div>
                <p class="creator-name">@Arthyr</p>
            </div>
            <div class="creator-card">
                <div class="creator-pfp"></div>
                <p class="creator-name">@SephinxXie</p>
            </div>
        </div>
    </section>
</div>

<?php
$extraJS = "js/index-script.js";
include 'includes/footer.php';
?>