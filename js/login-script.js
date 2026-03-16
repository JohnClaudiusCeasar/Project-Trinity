/* ========================================
   PROJECT TRINITY - LOGIN PAGE SCRIPT
   ======================================== */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Project Trinity Login Page loaded');
    
    // Initialize form handling
    initializeLoginForm();
    
    // Initialize input focus effects
    initializeInputEffects();
    
    // Initialize tab navigation
    initializeTabNavigation();
});

/**
 * Initialize login form submission
 */
function initializeLoginForm() {
    const loginForm = document.getElementById('loginForm');
    
    if (!loginForm) return;
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        handleLogin();
    });
}

/**
 * Handle login form submission
 */
function handleLogin() {
    const login = document.getElementById('email-username').value.trim();
    const password = document.getElementById('password').value.trim();
    
    // Basic validation
    if (!login || !password) {
        console.warn('Please fill in all fields');
        alert('Please fill in all fields');
        return;
    }
    
    // Prepare form data
    const formData = new FormData();
    formData.append('login', login);
    formData.append('password', password);
    
    // Send to PHP backend
    fetch('php/login-process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Login successful');
            alert('Login successful! Redirecting...');
            // Redirect to dashboard
            window.location.href = data.redirect;
        } else {
            // Show error message
            const errorMessage = data.message || 'Login failed. Please try again.';
            console.error('Login error:', errorMessage);
            alert(errorMessage);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

/**
 * Initialize input focus effects
 */
function initializeInputEffects() {
    const inputs = document.querySelectorAll('.form-input');
    
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
}

/**
 * Initialize tab navigation
 */
function initializeTabNavigation() {
    const tabButtons = document.querySelectorAll('.tab-button');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // If it's the LOGIN button, prevent default
            if (this.classList.contains('tab-active')) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Handle keyboard events
 */
document.addEventListener('keydown', function(event) {
    // Allow Enter key on password field to submit form
    if (event.key === 'Enter') {
        const passwordField = document.getElementById('password');
        if (document.activeElement === passwordField) {
            event.preventDefault();
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.dispatchEvent(new Event('submit'));
            }
        }
    }
});