// Collapsible Total Entries Card
document.addEventListener('DOMContentLoaded', () => {
    const totalEntriesHeader = document.querySelector('.total-entries-header');
    const categoryBreakdown = document.querySelector('.category-breakdown');
    const toggleIcon = document.querySelector('.toggle-icon');
 
    if (totalEntriesHeader) {
        totalEntriesHeader.addEventListener('click', () => {
            categoryBreakdown.classList.toggle('expanded');
            toggleIcon.classList.toggle('expanded');
        });
    }
 
    // Filter Functionality
    const filterBtns = document.querySelectorAll('.filter-btn');
    const entryItems = document.querySelectorAll('.entry-item');
 
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const category = btn.dataset.category;
 
            // Update active button
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
 
            // Filter entries
            entryItems.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                    item.style.animation = 'fadeInUp 0.3s ease-out';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});