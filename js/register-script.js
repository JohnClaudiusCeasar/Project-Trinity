/* ========================================
   PROJECT TRINITY - REGISTER PAGE SCRIPT
   ======================================== */
 
document.addEventListener('DOMContentLoaded', function() {
    console.log('Project Trinity Register Page loaded');
    
    // Initialize form handling
    initializeRegisterForm();
    
    // Initialize input focus effects
    initializeInputEffects();
    
    // Initialize password strength indicator
    initializePasswordStrength();
    
    // Initialize tab navigation
    initializeTabNavigation();
});
 
/**
 * Initialize register form submission
 */
function initializeRegisterForm() {
    const registerForm = document.getElementById('registerForm');
    
    if (!registerForm) return;
    
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        handleRegister();
    });
}
 
/**
 * Handle register form submission
 */
function handleRegister() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const confirmPassword = document.getElementById('confirm-password').value.trim();
    const email = document.getElementById('email').value.trim();
    
    // Basic validation
    if (!username || !password || !confirmPassword || !email) {
        console.warn('Please fill in all fields');
        alert('Please fill in all fields');
        return;
    }
    
    // Validate password match
    if (password !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    // Basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address');
        return;
    }
    
    // Prepare form data
    const formData = new FormData();
    formData.append('username', username);
    formData.append('password', password);
    formData.append('confirm_password', confirmPassword);
    formData.append('email', email);
    
    // Send to PHP backend
    fetch('php/register-process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Registration successful');
            alert('Registration successful! Redirecting...');
            // Redirect to dashboard
            window.location.href = data.redirect;
        } else {
            // Show error messages
            if (data.errors && Array.isArray(data.errors)) {
                const errorMessage = data.errors.join('\n');
                console.error('Registration errors:', errorMessage);
                alert('Registration failed:\n' + errorMessage);
            } else {
                const errorMessage = data.message || 'Registration failed. Please try again.';
                console.error('Registration error:', errorMessage);
                alert(errorMessage);
            }
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
 * Initialize password strength indicator
 */
function initializePasswordStrength() {
    const passwordInput = document.getElementById('password');
    
    if (!passwordInput) return;
    
    passwordInput.addEventListener('input', function() {
        updatePasswordStrength(this.value);
    });
}
 
/**
 * Update password strength indicator based on input
 */
function updatePasswordStrength(password) {
    const strengthIndicator = document.getElementById('strength-indicator');
    const strengthBar = document.getElementById('strengthBar');
    
    if (!strengthIndicator || !strengthBar) return;
    
    let strength = 'Pitiful';
    let color = '#999999'; // Grey - for empty input
    let width = '0%';
    
    // Only show color if password has input
    if (password.length > 0) {
        strength = 'Mediocre';
        color = '#d9534f'; // Red
        width = '20%';
    }
    
    if (password.length >= 8) {
        strength = 'Honorable';
        color = '#f0ad4e'; // Yellow
        width = '50%';
    }
    
    if (password.length >= 12 && /[A-Z]/.test(password) && /[0-9]/.test(password)) {
        strength = 'Profound';
        color = '#5cb85c'; // Green
        width = '75%';
    }
    
    if (password.length >= 16 && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[!@#$%^&*]/.test(password)) {
        strength = 'Profound';
        color = '#5cb85c'; // Green
        width = '100%';
    }
    
    strengthIndicator.textContent = strength;
    strengthIndicator.style.color = color;
    strengthBar.style.backgroundColor = color;
    strengthBar.style.width = width;
}
 
/**
 * Initialize tab navigation
 */
function initializeTabNavigation() {
    const tabButtons = document.querySelectorAll('.tab-button');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // If it's the REGISTER button, prevent default
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
    if (event.key === 'Enter') {
        const registerForm = document.getElementById('registerForm');
        const activeElement = document.activeElement;
        if (registerForm && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')) {
            event.preventDefault();
            registerForm.dispatchEvent(new Event('submit'));
        }
    }
});