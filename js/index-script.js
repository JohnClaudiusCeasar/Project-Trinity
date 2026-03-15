/* ========================================
   PROJECT TRINITY - INTRO DASHBOARD SCRIPT
   ======================================== */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Project Trinity Dashboard loaded');
    
    // Initialize scroll effects
    initializeScrollEffects();
    
    // Initialize hover effects
    initializeHoverEffects();
    
    // Initialize animations
    initializeAnimations();
});

/**
 * Initialize scroll effects
 */
function initializeScrollEffects() {
    const heroSection = document.querySelector('.hero-section');
    
    if (!heroSection) return;
    
    window.addEventListener('scroll', function() {
        // Optional: Add fade effect as user scrolls away from hero
        const scrollPercentage = (window.scrollY / window.innerHeight) * 100;
        
        if (scrollPercentage > 0 && scrollPercentage < 100) {
            const opacity = 1 - (scrollPercentage / 100);
            heroSection.style.opacity = opacity;
        } else if (scrollPercentage >= 100) {
            heroSection.style.opacity = '0';
            heroSection.style.pointerEvents = 'none';
        } else {
            heroSection.style.opacity = '1';
            heroSection.style.pointerEvents = 'auto';
        }
    });
}

/**
 * Initialize hover effects on interactive elements
 */
function initializeHoverEffects() {
    const navLinks = document.querySelectorAll('.nav-link');
    const creatorCards = document.querySelectorAll('.creator-card');
    
    // Navigation link hover
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transition = 'all 0.3s ease';
        });
    });
    
    // Creator card hover effect
    creatorCards.forEach(card => {
        const pfp = card.querySelector('.creator-pfp');
        
        card.addEventListener('mouseenter', function() {
            if (pfp) {
                pfp.style.transform = 'scale(1.1)';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (pfp) {
                pfp.style.transform = 'scale(1)';
            }
        });
    });
}

/**
 * Initialize fade-in animations for sections
 */
function initializeAnimations() {
    const sections = document.querySelectorAll('.about-section, .creators-section');
    
    sections.forEach((section, index) => {
        // Set initial opacity to 0
        section.style.opacity = '0';
        section.style.transition = 'opacity 0.6s ease-out';
        
        // Trigger animation after delay
        setTimeout(() => {
            section.style.opacity = '1';
        }, 200 + (index * 200));
    });
}

/**
 * Handle navigation clicks for smooth transitions
 */
document.addEventListener('click', function(event) {
    const navLink = event.target.closest('.nav-link');
    
    if (navLink) {
        const href = navLink.getAttribute('href');
        
        // Add fade-out effect before navigation
        document.body.style.transition = 'opacity 0.3s ease-out';
        document.body.style.opacity = '0';
        
        setTimeout(() => {
            window.location.href = href;
        }, 300);
    }
});

/**
 * Set initial page state on load
 */
window.addEventListener('load', function() {
    // Fade in body
    document.body.style.opacity = '1';
    document.body.style.transition = 'opacity 0.3s ease-out';
});

/**
 * Handle keyboard navigation
 */
document.addEventListener('keydown', function(event) {
    // Tab to navigate
    if (event.key === 'Tab') {
        const navLinks = document.querySelectorAll('.nav-link');
        const focusedElement = document.activeElement;
        
        if (navLinks.length > 0) {
            let focusIndex = Array.from(navLinks).indexOf(focusedElement);
            
            if (focusIndex === -1 && event.shiftKey) {
                navLinks[navLinks.length - 1].focus();
                event.preventDefault();
            } else if (focusIndex === -1) {
                navLinks[0].focus();
                event.preventDefault();
            }
        }
    }
});

/**
 * Debounce function for scroll events
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}