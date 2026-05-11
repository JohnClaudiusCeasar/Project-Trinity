<!-- Archive Content Fragment -->
<section class="dashboard-hero">
    <h1>Your Archive</h1>
    <p>Manage, explore, and share your creative works within the Trinity Archives</p>
</section>

<!-- Stats -->
<div class="stats-grid">

    <!-- Collapsible Total Entries Card -->
    <div class="total-entries-card" id="totalEntriesCard">
        <div class="total-entries-header">
            <div class="total-entries-title">
                <div class="total-entries-label">Total Entries</div>
                <div class="total-entries-value" id="totalEntriesValue">0</div>
            </div>
            <div class="toggle-icon">▼</div>
        </div>
        <div class="category-breakdown">
            <div class="category-item">
                <div class="category-item-label">Stories</div>
                <div class="category-item-value" id="countStory">0</div>
            </div>
            <div class="category-item">
                <div class="category-item-label">Characters</div>
                <div class="category-item-value" id="countCharacter">0</div>
            </div>
            <div class="category-item">
                <div class="category-item-label">Worlds</div>
                <div class="category-item-value" id="countWorld">0</div>
            </div>
            <div class="category-item">
                <div class="category-item-label">Objects</div>
                <div class="category-item-value" id="countObject">0</div>
            </div>
        </div>
    </div>

    <script>
    (function() {
        fetch('api/get-archive-stats.php')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.stats) {
                    document.getElementById('totalEntriesValue').textContent = data.stats.total;
                    document.getElementById('countStory').textContent = data.stats.story;
                    document.getElementById('countCharacter').textContent = data.stats.character;
                    document.getElementById('countWorld').textContent = data.stats.world;
                    document.getElementById('countObject').textContent = data.stats.object;
                }
            })
            .catch(err => console.error('Failed to load archive stats:', err));
    })();
    </script>

    <!-- Static Stat Cards -->
    <div class="stat-card">
        <div class="stat-label">Collaborators</div>
        <div class="stat-value">8</div>
        <div class="stat-change positive">↑ 2 new</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Views</div>
        <div class="stat-value">342</div>
        <div class="stat-change positive">↑ 18%</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Contributions</div>
        <div class="stat-value">28</div>
        <div class="stat-change negative">↓ 2 pending</div>
    </div>

</div>

<!-- Content Grid -->
<div class="content-grid">

    <!-- Main Archive Section -->
    <section class="archive-section">
        <h2 class="section-title">Recent Entries</h2>

        <div class="filter-container">
            <button class="filter-btn active" data-category="all">All</button>
            <button class="filter-btn" data-category="story">Stories</button>
            <button class="filter-btn" data-category="character">Characters</button>
            <button class="filter-btn" data-category="world">Worlds</button>
            <button class="filter-btn" data-category="creature">Creatures</button>
            <button class="filter-btn" data-category="object">Objects</button>
            <button class="filter-btn" data-category="collaborative">Collaborative</button>
        </div>

        <div class="entry-list" id="archiveEntryList">
            <div class="loading-state">Loading entries...</div>
        </div>
    </section>

    <!-- Dashboard Aside -->
    <aside class="dashboard-aside">
        <div class="sidebar-card">
            <h3>Your Team</h3>
            <div class="creator-list">
                <div class="creator-item">
                    <div class="creator-avatar">A</div>
                    <div class="creator-info">
                        <div class="creator-name">Arthyr</div>
                        <div class="creator-role">Co-Creator</div>
                    </div>
                </div>
                <div class="creator-item">
                    <div class="creator-avatar">S</div>
                    <div class="creator-info">
                        <div class="creator-name">SephinxXie</div>
                        <div class="creator-role">Co-Creator</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sidebar-card">
            <h3>Actions</h3>
            <div class="action-buttons">
                <button class="btn">+ New Entry</button>
                <button class="btn btn-secondary">View Archive</button>
            </div>
        </div>
    </aside>

</div>