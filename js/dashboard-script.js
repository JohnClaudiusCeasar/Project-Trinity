/**
 * Project Trinity Dashboard - JavaScript
 * Handles interactive features: collapsible stats and entry filtering
 */

// ============================================
// COLLAPSIBLE TOTAL ENTRIES CARD
// ============================================

const totalEntriesCard = document.querySelector('.total-entries-card');
const categoryBreakdown = document.querySelector('.category-breakdown');
const toggleIcon = document.querySelector('.toggle-icon');
let entriesExpanded = false;

/**
 * Toggle the expansion of the Total Entries card
 * Reveals/hides the category breakdown with smooth animation
 */
totalEntriesCard.addEventListener('click', () => {
    entriesExpanded = !entriesExpanded;
    categoryBreakdown.classList.toggle('expanded');
    toggleIcon.classList.toggle('expanded');
});

// ============================================
// CATEGORY FILTER FUNCTIONALITY
// ============================================

const filterBtns = document.querySelectorAll('.filter-btn');
const entryItems = document.querySelectorAll('.entry-item');

/**
 * Filter entries by category
 * Handles button active state and entry visibility
 */
filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        // Update active button styling
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Get the selected category from button data attribute
        const selectedCategory = btn.getAttribute('data-category');

        // Filter entries with animation
        entryItems.forEach(item => {
            const itemCategories = item.getAttribute('data-category').split(' ');
            
            if (selectedCategory === 'all' || itemCategories.includes(selectedCategory)) {
                item.style.display = 'block';
                item.style.animation = 'fadeInUp 0.3s ease-out';
            } else {
                item.style.display = 'none';
            }
        });
    });
});