/* ========================================
   PROJECT TRINITY - GLOBAL SCRIPT
   ======================================== */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Project Trinity Global Script loaded');
    
    // Initialize global page transitions
    initializePageTransitions();
});

/**
 * Initialize smooth page fade transitions
 */
function initializePageTransitions() {
    // Fade in page on load
    document.body.style.opacity = '1';
    document.body.style.transition = 'opacity 0.3s ease-out';
}

/**
 * Handle navigation link clicks for smooth transitions
 */
document.addEventListener('click', function(event) {
    const navLink = event.target.closest('.nav-link, .tab-link, a[href]');
    
    if (navLink && navLink.getAttribute('href')) {
        const href = navLink.getAttribute('href');
        
        // Don't fade out for hash links or same-page navigation
        if (href.startsWith('#') || href === window.location.pathname) {
            return;
        }
        
        // Add fade-out effect before navigation
        document.body.style.transition = 'opacity 0.3s ease-out';
        document.body.style.opacity = '0';
        
        setTimeout(() => {
            window.location.href = href;
        }, 300);
    }
});

/**
 * Debounce utility function
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
    }
}