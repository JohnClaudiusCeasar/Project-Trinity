<?php
$pageTitle = "PROJECT TRINITY - Login";
$extraCSS = "css/login-styles.css";
?>

<?php include 'includes/header.php'; ?>

<!-- Login Section -->
<section class="login-section">
    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tab-buttons">
            <button class="tab-button tab-active" data-tab="login">LOGIN</button>
            <div class="tab-divider"></div>
            <a href="register.php" class="tab-button tab-link">REGISTER</a>
        </div>
    </div>
 
    <!-- Login Form -->
    <form class="login-form" id="loginForm">
        <div class="form-wrapper">
            <!-- Email/Username Input -->
            <div class="form-group">
                <label for="email-username" class="form-label">EMAIL / USERNAME</label>
                <input 
                    type="text" 
                    id="email-username" 
                    name="login" 
                    class="form-input" 
                    placeholder=""
                    required
                />
                <div class="form-underline"></div>
            </div>
 
            <!-- Password Input -->
            <div class="form-group">
                <label for="password" class="form-label">PASSWORD</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input" 
                    placeholder=""
                    required
                />
                <div class="form-underline"></div>
            </div>
 
            <!-- Enter Button -->
            <button type="submit" class="enter-button">ENTER</button>
        </div>
    </form>
</section>

<?php
$extraJS = "js/login-script.js";
include 'includes/footer.php';
?>