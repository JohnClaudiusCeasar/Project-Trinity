<!-- View Content Fragment -->
<section class="dashboard-hero">
    <h1>Your Entries</h1>
    <p>View and manage your created content</p>
</section>

<!-- Filter Tabs -->
<div class="filter-container">
    <div class="filter-tabs">
        <button class="filter-btn active" data-category="all">All</button>
        <button class="filter-btn" data-category="character">Characters</button>
        <button class="filter-btn" data-category="world">Worlds</button>
        <button class="filter-btn" data-category="object">Objects</button>
    </div>
    <div class="view-toggle">
        <button class="view-toggle-btn active" data-view="list" title="List View">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
        <button class="view-toggle-btn" data-view="grid" title="Grid View">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
        </button>
    </div>
</div>

<!-- Entry List (populated dynamically) -->
<div class="entry-list" id="viewEntryList">
    <div class="loading-state">Loading entries...</div>
</div>
