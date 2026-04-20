<?php
$pageTitle = "PROJECT TRINITY - Register";
$extraCSS = "css/register-styles.css";
?>

<?php include 'includes/header.php'; ?>

<!-- Register Section -->
<section class="register-section">
    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tab-buttons">
            <a href="login.php" class="tab-button tab-link">LOGIN</a>
            <div class="tab-divider"></div>
            <button class="tab-button tab-active" data-tab="register">REGISTER</button>
        </div>
    </div>
 
    <!-- Register Form -->
    <form class="register-form" id="registerForm">
        <div class="form-wrapper">
            <!-- Username Input -->
            <div class="form-group">
                <label for="username" class="form-label">USERNAME</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-input" 
                    placeholder=""
                    required
                />
                <div class="form-underline"></div>
            </div>
 
            <!-- Password Input -->
            <div class="form-group password-group">
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
                <div class="password-meter-container">
                    <div class="password-strength-bar" id="strengthBar"></div>
                    <div class="password-strength">
                        <span class="strength-label">Password Strength:</span>
                        <span class="strength-indicator" id="strength-indicator">Pitiful</span>
                    </div>
                </div>
            </div>
 
            <!-- Confirm Password Input -->
            <div class="form-group">
                <label for="confirm-password" class="form-label">CONFIRM PASSWORD</label>
                <input 
                    type="password" 
                    id="confirm-password" 
                    name="confirm_password" 
                    class="form-input" 
                    placeholder=""
                    required
                />
                <div class="form-underline"></div>
            </div>
 
            <!-- Email Input -->
            <div class="form-group">
                <label for="email" class="form-label">E-MAIL</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
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
$extraJS = "js/register-script.js";
include 'includes/footer.php';
?>