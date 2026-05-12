<!-- Explore Content Fragment -->
<section class="dashboard-hero explore-hero">
    <h1>Explore</h1>
    <p>Discover creations from the Trinity community</p>
</section>

<!-- Spotlight Section -->
<section class="explore-spotlight" id="exploreSpotlight">
    <div class="explore-spotlight-label">Featured</div>
    <div class="spotlight-grid" id="spotlightGrid"></div>
</section>

<!-- Search Bar -->
<div class="explore-search">
    <div class="explore-search-bar">
        <span class="explore-search-icon">&#128269;</span>
        <input type="text" class="explore-search-input" id="exploreSearchInput" placeholder="Search the Trinity Archives..." autocomplete="off">
        <button class="explore-search-clear" id="exploreSearchClear">&#10005;</button>
    </div>
</div>

<!-- Filters & Sort -->
<div class="explore-controls">
    <div class="explore-filters">
        <button class="explore-filter-btn active" data-category="all">All</button>
        <button class="explore-filter-btn" data-category="story">Stories</button>
        <button class="explore-filter-btn" data-category="character">Characters</button>
        <button class="explore-filter-btn" data-category="world">Worlds</button>
        <button class="explore-filter-btn" data-category="object">Objects</button>
        <button class="explore-filter-btn" data-category="faction">Factions</button>
    </div>
    <select class="explore-sort" id="exploreSort">
        <option value="latest">Latest</option>
        <option value="popular">Most Popular</option>
    </select>
</div>

<!-- Entry Grid -->
<div class="explore-grid" id="exploreGrid">
    <div class="loading-state">Loading entries...</div>
</div>

<!-- Load More -->
<div class="explore-load-more" id="exploreLoadMore">
    <button class="explore-load-btn" id="exploreLoadBtn">Load More</button>
</div>

<link rel="stylesheet" href="css/dashboard-explore.css">
