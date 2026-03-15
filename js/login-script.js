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
    const emailUsername = document.getElementById('email-username').value.trim();
    const password = document.getElementById('password').value.trim();
    
    // Basic validation
    if (!emailUsername || !password) {
        console.warn('Please fill in all fields');
        alert('Please fill in all fields');
        return;
    }
    
    // Here you would normally send the data to your PHP backend
    console.log('Login attempt:', { emailUsername, password });
    
    // For now, just show a success message
    // In production, this would be handled by PHP
    alert('Login credentials submitted. This would be processed by your PHP backend.');
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

console.log('Project Trinity Login JavaScript initialized');

