// ============================================================
// PROJECT TRINITY — dashboard-script.js
// ============================================================

// ============================================================
// NAV ROUTER
// Maps page keys to their content source.
// - type: 'fetch'       → loads a .php fragment via fetch()
// - type: 'placeholder' → renders a "Content in Development" card
// ============================================================
const NAV_ROUTES = {
    archives: { type: 'fetch', src: 'content-pages/Dashboard/dashboardArchive.php' },
    create:   { type: 'fetch', src: 'content-pages/Dashboard/dashboardCreate.php'  },
    view:     { type: 'fetch', src: 'content-pages/Dashboard/dashboardView.php'     },
    profile:  { type: 'fetch', src: 'content-pages/Dashboard/dashboardProfile.php'  },
    projects: { type: 'placeholder', label: 'Projects' },
    explore:  { type: 'fetch', src: 'content-pages/Dashboard/dashboardExplore.php' },
    guides:   { type: 'placeholder', label: 'Guides'   },
};

function buildPlaceholder(label) {
    return `
        <section class="dashboard-hero">
            <h1>${label}</h1>
        </section>
        <div class="placeholder-dev">
            <div class="placeholder-dev-inner">
                <span class="placeholder-icon">✦</span>
                <p class="placeholder-title">Content in Development</p>
                <p class="placeholder-sub">This section is being built. Check back soon.</p>
            </div>
        </div>
    `;
}

async function loadPage(pageKey) {
    const main  = document.getElementById('mainContent');
    const route = NAV_ROUTES[pageKey];
    if (!route || !main) return;

    main.dataset.section = pageKey;

    if (route.type === 'fetch') {
        try {
            const res = await fetch(route.src);
            if (!res.ok) throw new Error(`Failed to load ${route.src}`);
            main.innerHTML = await res.text();
            initDashboardFeatures();
            initCreatePage();
            if (pageKey === 'view') {
                initViewPage();
            }
            if (pageKey === 'archives') {
                initArchivePage();
            }
            if (pageKey === 'explore') {
                initExplorePage();
            }
        } catch (err) {
            console.error(err);
            main.innerHTML = buildPlaceholder(pageKey);
        }
    } else {
        main.innerHTML = buildPlaceholder(route.label);
    }
}

function setActiveNav(pageKey) {
    document.querySelectorAll('[data-page]').forEach(el => {
        el.classList.toggle('active', el.dataset.page === pageKey);
    });
}

// ============================================================
// SIDEBAR
// ============================================================
(function () {
    const sidebar     = document.getElementById('sidebar');
    const pageWrapper = document.getElementById('pageWrapper');
    const backdrop    = document.getElementById('sidebarBackdrop');
    const logoBtn     = document.getElementById('logoContainer');
    const closeBtn    = document.getElementById('sidebarCloseBtn');
    const sidebarLogo = document.getElementById('sidebarLogoClose');

    function openSidebar() {
        sidebar?.classList.add('open');
        pageWrapper?.classList.add('sidebar-open');
        backdrop?.classList.add('visible');
    }

    window.closeSidebar = function () {
        sidebar?.classList.remove('open');
        pageWrapper?.classList.remove('sidebar-open');
        backdrop?.classList.remove('visible');
    };

    function toggleSidebar() {
        sidebar?.classList.contains('open') ? window.closeSidebar() : openSidebar();
    }

    logoBtn?.addEventListener('click', toggleSidebar);
    closeBtn?.addEventListener('click', window.closeSidebar);
    sidebarLogo?.addEventListener('click', window.closeSidebar);
    backdrop?.addEventListener('click', window.closeSidebar);

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') window.closeSidebar();
    });
})();

// ============================================================
// NAV CLICK HANDLER — sidebar + header via [data-page]
// ============================================================
document.querySelectorAll('[data-page]').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const pageKey = link.dataset.page;
        setActiveNav(pageKey);
        loadPage(pageKey);
        window.closeSidebar();
    });
});

// ============================================================
// VIEW TOGGLE — Global state for list/grid view
// ============================================================
let currentViewMode = 'list';

function setViewMode(view) {
    if (view !== 'list' && view !== 'grid') return;
    currentViewMode = view;
    applyViewMode(view);
    localStorage.setItem('entryViewMode', view);
}

function applyViewMode(view) {
    const entryList = document.querySelector('.entry-list');
    if (!entryList) return;
    
    if (view === 'grid') {
        entryList.classList.add('grid-view');
    } else {
        entryList.classList.remove('grid-view');
    }
    
    // Update toggle checkbox state
    const toggle = document.getElementById('viewModeToggle');
    if (toggle) {
        toggle.checked = (view === 'grid');
    }
}

// ============================================================
// ARCHIVE FEATURES — re-initialised after fetch
// ============================================================
function initDashboardFeatures() {
    initStatToggle();
    initCategoryFilter();
    initTypeGridScroll();
    initTypeGridDragScroll();
}

function initStatToggle() {
    const header    = document.querySelector('.total-entries-header');
    const breakdown = document.querySelector('.category-breakdown');
    const icon      = document.querySelector('.toggle-icon');
    if (!header || !breakdown) return;

    header.addEventListener('click', () => {
        breakdown.classList.toggle('expanded');
        icon?.classList.toggle('expanded');
    });
}

function initCategoryFilter() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const entryItems = document.querySelectorAll('.entry-item');
    if (!filterBtns.length) return;

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const category = btn.dataset.category;
            entryItems.forEach(item => {
                const show = category === 'all' || item.dataset.category === category;
                item.style.display = show ? 'block' : 'none';
                if (show) item.style.animation = 'fadeInUp 0.3s ease-out';
            });
        });
    });
}

// ============================================================
// INIT TYPE GRID SCROLL (Horizontal scroll with mouse wheel)
// ============================================================
function initTypeGridScroll() {
    const typeGrid = document.querySelector('.create-type-grid');
    if (!typeGrid) return;

    typeGrid.addEventListener('wheel', (e) => {
        const isScrollable = typeGrid.scrollWidth > typeGrid.clientWidth;
        if (isScrollable) {
            e.preventDefault();
            typeGrid.scrollLeft += e.deltaY;
        }
    }, { passive: false });
}

// ============================================================
// INIT TYPE GRID DRAG SCROLL (Click and drag to scroll)
// ============================================================
function initTypeGridDragScroll() {
    const typeGrid = document.querySelector('.create-type-grid');
    if (!typeGrid) return;

    let isDragging = false;
    let startX;
    let startScrollLeft;

    typeGrid.style.cursor = 'grab';

    typeGrid.addEventListener('mousedown', (e) => {
        isDragging = true;
        typeGrid.style.cursor = 'grabbing';
        startX = e.pageX - typeGrid.offsetLeft;
        startScrollLeft = typeGrid.scrollLeft;
    });

    ['mouseleave', 'mouseup'].forEach(evt => {
        typeGrid.addEventListener(evt, () => {
            isDragging = false;
            typeGrid.style.cursor = 'grab';
        });
    });

    typeGrid.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        e.preventDefault();
        const x = e.pageX - typeGrid.offsetLeft;
        const walk = (x - startX) * 1.5;
        typeGrid.scrollLeft = startScrollLeft - walk;
    });
}

// ============================================================
// CREATE PAGE — card picker → type-specific form flow
// ============================================================
const CREATE_TYPE_META = {
    story:     { icon: '◎', label: 'Story'             },
    character: { icon: '◈', label: 'Character'         },
    world:     { icon: '⬡', label: 'World'             },
    equipment: { icon: '✦', label: 'Object / Artifact' },
};

// Form fragment routes
const CREATE_FORM_ROUTES = {
    story:     'content-pages/Forms/storyForm.php',
    character: 'content-pages/Forms/characterForm.php',
    world:     'content-pages/Forms/worldForm.php',
    equipment: 'content-pages/Forms/equipmentForm.php',
    faction:   'content-pages/Forms/factionForm.php',
};

async function loadFormFragment(type, container) {
    const src = CREATE_FORM_ROUTES[type];
    if (!src) {
        container.innerHTML = `
            <div class="form-skeleton-notice">
                <span>✦</span> Form fields for this type are coming in a future build.
            </div>`;
        return;
    }
    try {
        const res = await fetch(src);
        if (!res.ok) throw new Error(`Failed to load form fragment: ${src}`);
        container.innerHTML = await res.text();
    } catch (err) {
        console.error(err);
        container.innerHTML = `
            <div class="form-skeleton-notice">
                <span>✦</span> Could not load form. Please try again.
            </div>`;
    }
}

// ============================================================
// INIT CREATE PAGE
// ============================================================
function initCreatePage() {
    const picker      = document.getElementById('createPicker');
    const formWrapper = document.getElementById('createFormWrapper');
    const formFields  = document.getElementById('createFormFields');
    const backBtn     = document.getElementById('createBackBtn');
    const cancelBtn   = document.getElementById('createCancelBtn');
    const formIcon    = document.getElementById('createFormIcon');
    const formLabel   = document.getElementById('createFormTypeLabel');
    if (!picker || !formWrapper) return;

    picker.querySelectorAll('.create-type-card').forEach(card => {
        card.addEventListener('click', async () => {
            const type = card.dataset.type;
            const meta = CREATE_TYPE_META[type] || { icon: '✦', label: type };

            if (formIcon)  formIcon.textContent  = meta.icon;
            if (formLabel) formLabel.textContent = meta.label;

            const mainContainer = document.querySelector('.create-main');
            if (mainContainer) mainContainer.classList.add('is-form-active');

            picker.style.display = 'none';
            formWrapper.classList.add('visible');

            // Fetch and inject type-specific form fragment
            if (formFields) {
                await loadFormFragment(type, formFields);
                initTagsInputs();
                initPickerTriggers();
                initFormSubmission(type);

                // Image upload initialization for character, equipment, world
                if (type === 'character') {
                    initImageUpload('character', 'charImagePreview', 'charImageHidden', 'charImageRemove', 'charImageWrapper');
                } else if (type === 'equipment') {
                    initImageUpload('equipment', 'equipImagePreview', 'equipImageHidden', 'equipImageRemove', 'equipImageWrapper');
                } else if (type === 'world') {
                    initImageUpload('world', 'worldImagePreview', 'worldImageHidden', 'worldImageRemove', 'worldImageWrapper');
                }

                // Story-specific initializations
                if (type === 'story') {
                    initSegmentedControl();
                    initRichTextEditor();
                }

                // Faction-specific initialization
                if (type === 'faction') {
                    initFactionForm();
                }
            }
        });
    });

    backBtn?.addEventListener('click', returnToPicker);
    cancelBtn?.addEventListener('click', returnToPicker);
}

// ============================================================
// INIT FACTION FORM
// ============================================================
function initFactionForm() {
    // Multi-select Type Dropdown Logic
    const typeTrigger = document.getElementById('factionTypeTrigger');
    const typeDropdown = document.getElementById('factionTypeDropdown');
    const typeHidden = document.getElementById('factionTypeHidden');
    const typeCheckboxes = typeDropdown?.querySelectorAll('.multiselect-checkbox');
    const typeCount = typeTrigger?.querySelector('.multiselect-count');
    const typePlaceholder = typeTrigger?.querySelector('.multiselect-placeholder');

    if (typeTrigger && typeDropdown) {
        typeTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const isNowOpen = typeDropdown.hidden;
            typeDropdown.hidden = !isNowOpen;
            typeTrigger.setAttribute('aria-expanded', !isNowOpen);
        });

        // Prevent closing when clicking inside dropdown
        typeDropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Close on outside click
        document.addEventListener('click', () => {
            if (!typeDropdown.hidden) {
                typeDropdown.hidden = true;
                typeTrigger.setAttribute('aria-expanded', 'false');
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !typeDropdown.hidden) {
                typeDropdown.hidden = true;
                typeTrigger.setAttribute('aria-expanded', 'false');
            }
        });

        typeCheckboxes?.forEach(cb => {
            cb.addEventListener('change', function() {
                const checked = typeDropdown.querySelectorAll('.multiselect-checkbox:checked');
                if (checked.length > 2) {
                    this.checked = false;
                    return;
                }
                const selected = Array.from(checked).map(c => c.value);
                typeHidden.value = selected.join(',');
                typeCount.textContent = `${selected.length}/2 selected`;
                typePlaceholder.textContent = selected.length > 0 ? selected.join(', ') : 'Select types…';
            });
        });
    }

    // Collapsible Persons In Authority Section (with smooth animation)
    const collapsibleToggles = document.querySelectorAll('.collapsible-toggle');
    collapsibleToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const expanded = this.getAttribute('aria-expanded') === 'true';
            const newExpanded = !expanded;
            this.setAttribute('aria-expanded', newExpanded);
            const bodyId = this.getAttribute('aria-controls');
            const body = document.getElementById(bodyId);
            if (body) {
                if (newExpanded) {
                    body.classList.remove('collapsible-body--hidden');
                    body.getBoundingClientRect(); // force reflow to register 0 state
                    body.style.maxHeight = body.scrollHeight + 'px';
                } else {
                    body.style.maxHeight = body.scrollHeight + 'px';
                    body.getBoundingClientRect(); // force reflow
                    body.style.maxHeight = '0';
                    body.addEventListener('transitionend', () => {
                        if (this.getAttribute('aria-expanded') !== 'true') {
                            body.classList.add('collapsible-body--hidden');
                            body.style.maxHeight = '';
                        }
                    }, { once: true });
                }
                const arrow = this.querySelector('.collapsible-arrow');
                if (arrow) arrow.textContent = newExpanded ? '↑' : '↓';
            }
        });
    });
}

// ============================================================
// INIT VIEW PAGE — includes view toggle logic
// ============================================================
let loadEntriesCallback = null;

function initViewPage() {
    const entryList = document.getElementById('viewEntryList');
    if (!entryList) return;

    // Load saved view preference
    const savedView = localStorage.getItem('entryViewMode');
    if (savedView === 'grid' || savedView === 'list') {
        currentViewMode = savedView;
    }
    // Auto-switch to grid on mobile
    if (window.innerWidth <= 768) {
        currentViewMode = 'grid';
    }
    applyViewMode(currentViewMode);

    const filterBtns = document.querySelectorAll('#viewEntryList ~ .filter-container .filter-btn, .filter-container .filter-btn');
    
    async function loadEntries(category = 'all') {
        entryList.innerHTML = '<div class="loading-state">Loading entries...</div>';
        
        try {
            const url = category === 'all' ? 'api/get-entries-html.php' : `api/get-entries-html.php?category=${category}`;
            const res = await fetch(url);
            const html = await res.text();
            
            if (html.trim()) {
                entryList.innerHTML = html;
                initEntryActions();
                // Apply saved view mode after loading entries
                applyViewMode(currentViewMode);
            } else {
                entryList.innerHTML = '<p class="empty-state">No entries yet. Create your first entry!</p>';
            }
        } catch (err) {
            console.error('Error loading entries:', err);
            entryList.innerHTML = '<p class="empty-state">Failed to load entries. Please try again.</p>';
        }
    }

    // Expose loadEntries globally for use outside initViewPage
    loadEntriesCallback = loadEntries;

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const category = btn.dataset.category || 'all';
            loadEntries(category);
        });
    });

    // View toggle switch
    const viewToggle = document.getElementById('viewModeToggle');
    if (viewToggle) {
        viewToggle.addEventListener('change', () => {
            const newView = viewToggle.checked ? 'grid' : 'list';
            if (newView !== currentViewMode) {
                setViewMode(newView);
            }
        });
    }

    loadEntries();
}

// ============================================================
// INIT ARCHIVE PAGE
// ============================================================
function initArchivePage() {
    const entryList = document.getElementById('archiveEntryList');
    if (!entryList) return;

    const filterBtns = document.querySelectorAll('#archiveEntryList ~ .filter-container .filter-btn, .archive-section .filter-btn');

    async function loadArchiveEntries(category = 'all') {
        entryList.innerHTML = '<div class="loading-state">Loading entries...</div>';

        try {
            const url = category === 'all' ? 'api/get-entries-html.php' : `api/get-entries-html.php?category=${category}`;
            const res = await fetch(url);
            const html = await res.text();

            if (html.trim()) {
                entryList.innerHTML = html;
                initEntryActions();
            } else {
                entryList.innerHTML = '<p class="empty-state">No entries yet. Create your first entry!</p>';
            }
        } catch (err) {
            console.error('Error loading archive entries:', err);
            entryList.innerHTML = '<p class="empty-state">Failed to load entries. Please try again.</p>';
        }
    }

    // Expose loadArchiveEntries for list refreshing after edits
    loadEntriesCallback = loadArchiveEntries;

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const category = btn.dataset.category || 'all';
            loadArchiveEntries(category);
        });
    });

    loadArchiveEntries();
}

// ============================================================
// EXPLORE PAGE
// ============================================================
function initExplorePage() {
    const grid = document.getElementById('exploreGrid');
    if (!grid) return;

    let currentCategory = 'all';
    let currentSearch   = '';
    let currentSort     = 'latest';
    let currentPage     = 1;
    let isLoading       = false;
    let hasMore         = true;

    const filterBtns = document.querySelectorAll('.explore-filter-btn');
    const searchInput = document.getElementById('exploreSearchInput');
    const searchClear = document.getElementById('exploreSearchClear');
    const sortSelect  = document.getElementById('exploreSort');
    const loadBtn     = document.getElementById('exploreLoadBtn');
    const spotlightGrid = document.getElementById('spotlightGrid');

    // ---- Spotlight ----
    async function loadSpotlight() {
        if (!spotlightGrid) return;
        try {
            const res = await fetch('api/get-spotlight-entries.php');
            const data = await res.json();
            if (data.success && data.spotlight.length) {
                spotlightGrid.innerHTML = data.spotlight.map((entry, i) => {
                    const isFeatured = i === 0;
                    const name = entry.name || entry.title || 'Untitled';
                    const desc = entry.description || entry.synopsis || '';
                    const truncDesc = desc.length > 120 ? desc.substring(0, 120) + '...' : desc;
                    const wordInfo = entry.word_count ? ' &middot; ' + formatExploreWordCount(entry.word_count) + ' words' : '';
                    const imgHtml = entry.image
                        ? '<img src="' + entry.image + '" alt="' + name + '">'
                        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
                    const imgContainer = entry.image
                        ? '<div class="spotlight-card-image">' + imgHtml + '</div>'
                        : '<div class="spotlight-card-image-placeholder">' + imgHtml + '</div>';

                    return '<div class="spotlight-card' + (isFeatured ? ' featured' : '') + '" data-id="' + entry.id + '" data-type="' + entry.type + '">'
                        + imgContainer
                        + '<div class="spotlight-card-body">'
                        + (isFeatured ? '<div class="spotlight-card-badge">&#9733; Featured Entry</div>' : '')
                        + '<h3 class="spotlight-card-title">' + name + '</h3>'
                        + (truncDesc ? '<p class="spotlight-card-desc">' + truncDesc + '</p>' : '')
                        + '<p class="spotlight-card-meta">by ' + entry.username + ' &middot; ' + entry.type + wordInfo + '</p>'
                        + '<button class="spotlight-card-action" data-id="' + entry.id + '" data-type="' + entry.type + '">View Entry</button>'
                        + '</div></div>';
                }).join('');

                spotlightGrid.querySelectorAll('.spotlight-card-action, .spotlight-card').forEach(el => {
                    el.addEventListener('click', function (e) {
                        const card = this.closest('.spotlight-card');
                        if (card) {
                            const id = card.dataset.id;
                            const type = card.dataset.type;
                            if (id && type) openViewModal(id, type);
                        }
                    });
                });
            } else {
                spotlightGrid.innerHTML = '';
                document.querySelector('.explore-spotlight')?.style.setProperty('display', 'none');
            }
        } catch (err) {
            console.error('Failed to load spotlight:', err);
        }
    }

    function formatExploreWordCount(count) {
        if (count >= 1000) return (count / 1000).toFixed(1) + 'k';
        return count;
    }

    // ---- Entries ----
    async function loadEntries(reset = true) {
        if (isLoading) return;
        isLoading = true;

        if (reset) {
            currentPage = 1;
            hasMore = true;
            grid.innerHTML = '<div class="loading-state">Loading entries...</div>';
        }

        try {
            const params = new URLSearchParams({
                category: currentCategory,
                sort: currentSort,
                page: currentPage,
                limit: 12
            });
            if (currentSearch) params.set('search', currentSearch);

            const res = await fetch('api/get-public-entries.php?' + params.toString());
            const data = await res.json();

            if (reset) grid.innerHTML = '';

            if (data.success && data.html) {
                if (reset) {
                    grid.innerHTML = data.html;
                } else {
                    grid.insertAdjacentHTML('beforeend', data.html);
                }
                hasMore = data.hasMore;
                loadBtn.style.display = hasMore ? 'block' : 'none';
                initExploreCardActions();
            } else {
                if (reset) grid.innerHTML = '<p class="empty-state">No entries found. Be the first to share!</p>';
                loadBtn.style.display = 'none';
            }
        } catch (err) {
            console.error('Error loading explore entries:', err);
            if (reset) grid.innerHTML = '<p class="empty-state">Failed to load entries. Try again.</p>';
        } finally {
            isLoading = false;
        }
    }

    function initExploreCardActions() {
        grid.querySelectorAll('.explore-card').forEach(card => {
            card.addEventListener('click', function (e) {
                if (e.target.closest('.explore-fav-btn')) return;
                const id = this.dataset.id;
                const type = this.dataset.type;
                if (id && type) openViewModal(id, type);
            });
        });
        grid.querySelectorAll('.explore-action-btn[data-id]').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const id = this.dataset.id;
                const type = this.dataset.type;
                if (id && type) openViewModal(id, type);
            });
        });
        grid.querySelectorAll('.explore-fav-btn').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                this.classList.toggle('faved');
                const svg = this.querySelector('svg');
                if (this.classList.contains('faved')) {
                    svg.style.fill = 'currentColor';
                } else {
                    svg.style.fill = 'none';
                }
            });
        });
    }

    // ---- Filters ----
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentCategory = btn.dataset.category || 'all';
            loadEntries(true);
        });
    });

    // ---- Search ----
    let searchTimeout;
    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentSearch = searchInput.value.trim();
            searchClear?.classList.toggle('visible', currentSearch.length > 0);
            loadEntries(true);
        }, 400);
    });

    searchClear?.addEventListener('click', () => {
        searchInput.value = '';
        currentSearch = '';
        searchClear.classList.remove('visible');
        loadEntries(true);
    });

    // ---- Sort ----
    sortSelect?.addEventListener('change', () => {
        currentSort = sortSelect.value;
        loadEntries(true);
    });

    // ---- Load More ----
    loadBtn?.addEventListener('click', () => {
        currentPage++;
        loadEntries(false);
    });

    // ---- Init ----
    loadSpotlight();
    loadEntries(true);
}

// ============================================================
// TAGS INPUT BEHAVIOUR
// ============================================================
function initTagsInputs() {
    document.querySelectorAll('.tags-input').forEach(input => {
        const listEl   = document.getElementById(input.id + 'List');
        const hiddenEl = document.getElementById(input.id + 'Hidden');
        if (!listEl || !hiddenEl) return;

        let tags = [];

        function renderTags() {
            listEl.innerHTML = tags.map((tag, i) => `
                <span class="tag-chip">
                    ${tag}
                    <button type="button" class="tag-remove" data-index="${i}" aria-label="Remove ${tag}">×</button>
                </span>
            `).join('');
            hiddenEl.value = tags.join(',');
        }

        function addTag(raw) {
            const val = raw.trim().replace(/,+$/, '');
            if (val && !tags.includes(val)) {
                tags.push(val);
                renderTags();
            }
            input.value = '';
        }

        input.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                addTag(input.value);
            } else if (e.key === 'Backspace' && input.value === '' && tags.length) {
                tags.pop();
                renderTags();
            }
        });

        input.addEventListener('blur', () => {
            if (input.value.trim()) addTag(input.value);
        });

        listEl.addEventListener('click', e => {
            const btn = e.target.closest('.tag-remove');
            if (!btn) return;
            tags.splice(Number(btn.dataset.index), 1);
            renderTags();
        });
    });
}

// ============================================================
// SEGMENTED CONTROL (Status Toggle)
// ============================================================
function initSegmentedControl() {
    document.querySelectorAll('.segmented-control').forEach(control => {
        const buttons = control.querySelectorAll('.segment-btn');
        const hiddenInput = control.parentElement.querySelector('input[type="hidden"]') ||
                          document.getElementById(control.id.replace('Field', ''));

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                buttons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                if (hiddenInput) {
                    hiddenInput.value = btn.dataset.value;
                }
            });
        });
    });
}

// ============================================================
// RICH TEXT EDITOR
// ============================================================
function initRichTextEditor() {
    document.querySelectorAll('.rich-text-editor').forEach(editor => {
        const content = editor.querySelector('.rte-content');
        const hiddenInput = editor.parentElement.querySelector('input[type="hidden"]');
        const toolbar = editor.querySelector('.rte-toolbar');
        const wordCountEl = editor.parentElement.querySelector('.rte-wordcount span');

        if (!content) return;

        toolbar?.querySelectorAll('.rte-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const command = btn.dataset.command;
                document.execCommand(command, false, null);
                content.focus();
            });
        });

        content.addEventListener('keydown', (e) => {
            if (e.key === '"') {
                e.preventDefault();
                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    const container = range.startContainer;
                    const offset = range.startOffset;
                    
                    if (container.nodeType !== 3) return;
                    
                    container.insertData(offset, '""');
                    range.setStart(container, offset + 1);
                    range.setEnd(container, offset + 1);
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
            }
        });

        content.addEventListener('keyup', (e) => {
            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft' || e.key === 'Tab') {
                checkQuoteExit(content, hiddenInput);
            }
        });

        content.addEventListener('click', () => {
            checkQuoteExit(content, hiddenInput);
        });

        content.addEventListener('input', () => {
            if (hiddenInput) {
                hiddenInput.value = content.innerHTML;
            }
            if (wordCountEl) {
                const text = content.innerText || '';
                const words = text.trim() ? text.trim().split(/\s+/) : [];
                wordCountEl.textContent = words.length;
            }
        });

        content.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = e.clipboardData.getData('text/plain');
            document.execCommand('insertText', false, text);
        });
    });
}

function checkQuoteExit(content, hiddenInput) {
    const selection = window.getSelection();
    if (selection.rangeCount === 0) return;

    const range = selection.getRangeAt(0);
    const container = range.startContainer;
    const offset = range.startOffset;

    if (container.nodeType !== 3) return;

    const text = container.textContent;
    const beforeCursor = text.substring(0, offset);
    const afterCursor = text.substring(offset);

    const closeQuoteIdx = afterCursor.indexOf('"');
    const openQuoteIdx = beforeCursor.lastIndexOf('"');

    if (openQuoteIdx !== -1 && closeQuoteIdx !== -1) {
        const textBetween = text.substring(openQuoteIdx + 1, offset + closeQuoteIdx);
        if (textBetween.length > 0) {
            const rangeStart = document.createRange();
            
            try {
                rangeStart.setStart(container, openQuoteIdx);
                rangeStart.setEnd(container, openQuoteIdx + closeQuoteIdx + 2);
                rangeStart.surroundContents(document.createElement('em'));
            } catch (e) {
                const before = text.substring(0, openQuoteIdx);
                const middle = textBetween;
                const after = text.substring(openQuoteIdx + closeQuoteIdx + 2);
                container.textContent = before + middle + after;
                
                const em = document.createElement('em');
                em.textContent = middle;
                container.parentNode.insertBefore(em, container.nextSibling);
            }

            if (hiddenInput) {
                hiddenInput.value = content.innerHTML;
            }
        }
    }
}

// ============================================================
// FORM SUBMISSION HANDLER
// ============================================================
function initFormSubmission(type, isEdit = false) {
    const formId = isEdit ? 'editEntryForm' : 'createForm';
    const form = document.getElementById(formId);
    if (!form) return;

    const submitBtn = document.getElementById(isEdit ? 'editSubmitBtn' : 'createSubmitBtn');
    const modalSaveBtn = isEdit ? document.getElementById('viewModalSaveBtn') : null;

    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = isEdit ? 'Save Changes' : 'Save Entry';
    }
    if (modalSaveBtn) {
        modalSaveBtn.disabled = false;
        modalSaveBtn.textContent = 'Save Changes';
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
        }
        if (modalSaveBtn) {
            modalSaveBtn.disabled = true;
            modalSaveBtn.textContent = 'Saving...';
        }

        const formData = new FormData(form);

        if (isEdit && currentEditEntryId) {
            formData.append('entry_id', currentEditEntryId);
        }

        // Handle rich text editor hidden inputs before submission
        document.querySelectorAll('.rich-text-editor').forEach(editor => {
            const content = editor.querySelector('.rte-content');
            const hiddenInput = editor.parentElement.querySelector('input[type="hidden"]');
            if (content && hiddenInput) {
                hiddenInput.name = hiddenInput.id.replace('Hidden', '');
                formData.set(hiddenInput.name, content.innerHTML);
            }
        });

        // Serialize all picker hidden inputs to JSON arrays (non-faction forms)
        // The handlers for world/character/equipment/story expect JSON arrays
        if (type !== 'faction') {
            document.querySelectorAll('.picker-field input[type="hidden"]').forEach(hiddenEl => {
                const chipsId = hiddenEl.id.replace('Hidden', 'Chips');
                const chipsEl = document.getElementById(chipsId);
                if (!chipsEl || !chipsEl.children.length) return;

                const items = [];
                const hasRelationCards = chipsEl.querySelector('.relation-card');

                if (hasRelationCards) {
                    Array.from(chipsEl.children).forEach(card => {
                        if (!card.classList.contains('relation-card')) return;
                        const id = card.dataset.itemId;
                        if (!id) return;
                        const entry = { id: parseInt(id) };
                        card.querySelectorAll('input, textarea, select').forEach(input => {
                            const match = input.name.match(/\[(\w+)\]$/);
                            if (match) entry[match[1]] = input.value;
                        });
                        items.push(entry);
                    });
                } else {
                    Array.from(chipsEl.children).forEach(el => {
                        const id = el.dataset.id;
                        if (id) items.push({ id: parseInt(id) });
                    });
                }

                hiddenEl.value = items.length ? JSON.stringify(items) : '';
                formData.set(hiddenEl.name, hiddenEl.value);
            });
        }

        formData.append('type', type);

        try {
            const res = await fetch(`php/${type}-process.php`, {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                alert(isEdit ? 'Entry updated successfully!' : 'Entry created successfully!');
                closeEditModal();
                if (loadEntriesCallback) {
                    loadEntriesCallback();
                }
            } else {
                if (data.errors && Array.isArray(data.errors)) {
                    alert('Please fix the following:\n' + data.errors.join('\n'));
                } else {
                    alert(data.message || (isEdit ? 'Failed to update entry' : 'Failed to create entry'));
                }
            }
        } catch (err) {
            console.error('Error:', err);
            alert('An error occurred. Please try again.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = isEdit ? 'Save Changes' : 'Save Entry';
            }
            if (modalSaveBtn) {
                modalSaveBtn.disabled = false;
                modalSaveBtn.textContent = 'Save Changes';
            }
        }
    });
}

function returnToPicker() {
    const picker = document.getElementById('createPicker');
    const formWrapper = document.getElementById('createFormWrapper');
    const formFields = document.getElementById('createFormFields');
    const mainContainer = document.querySelector('.create-main');
    if (mainContainer) mainContainer.classList.remove('is-form-active');

    formWrapper?.classList.remove('visible');
    if (picker) picker.style.display = '';
    if (formFields) formFields.innerHTML = '';
}

// ============================================================
// ENTRY ACTIONS HANDLERS
// ============================================================
function initEntryActions() {
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const id = btn.dataset.id;
            const type = btn.dataset.type;
            openViewModal(id, type);
        });
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const id = btn.dataset.id;
            const type = btn.dataset.type;
            openEditModal(id, type);
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const id = btn.dataset.id;
            const type = btn.dataset.type;
            showDeleteConfirm(id, type);
        });
    });
}

async function openViewModal(id, type) {
    const modal = document.getElementById('viewModal');
    const backdrop = document.getElementById('viewModalBackdrop');
    const body = document.getElementById('viewModalBody');

    if (!modal || !backdrop || !body) {
        console.error('View modal elements not found');
        return;
    }

    body.innerHTML = '<div class="loading-state">Loading...</div>';
    modal.classList.add('visible');
    backdrop.classList.add('visible');

    try {
        const res = await fetch(`api/get-entry-details.php?id=${id}&type=${type}`);
        const data = await res.json();

        if (data.success && data.entry) {
            renderViewModalContent(data.entry, data.entry_type);
            const exportBtn = document.getElementById('viewModalExportPdfBtn');
            if (exportBtn) exportBtn.style.display = '';
        } else {
            body.innerHTML = '<p class="empty-state">Entry not found or you do not have permission to view it.</p>';
        }
    } catch (err) {
        console.error('Error loading entry:', err);
        body.innerHTML = '<p class="empty-state">Failed to load entry details. Please try again.</p>';
    }
}

function closeViewModal() {
    const modal = document.getElementById('viewModal');
    const backdrop = document.getElementById('viewModalBackdrop');
    if (modal) modal.classList.remove('visible');
    if (backdrop) backdrop.classList.remove('visible');

    const modeToggle = document.getElementById('viewModalModeToggle');
    const saveBtn = document.getElementById('viewModalSaveBtn');
    const exportBtn = document.getElementById('viewModalExportPdfBtn');
    const formContainer = document.getElementById('editFormContainer');
    const body = document.getElementById('viewModalBody');

    if (modeToggle) modeToggle.style.display = 'none';
    if (saveBtn) saveBtn.style.display = 'none';
    if (exportBtn) exportBtn.style.display = 'none';
    if (formContainer) formContainer.style.display = 'none';
    if (body) body.style.display = '';

    currentEditEntryId = null;
    currentEditEntryType = null;
}

function initViewModal() {
    const closeBtn = document.getElementById('viewModalClose');
    const closeBtnFooter = document.getElementById('viewModalCloseBtn');
    const exportBtn = document.getElementById('viewModalExportPdfBtn');
    const saveBtn = document.getElementById('viewModalSaveBtn');
    const backdrop = document.getElementById('viewModalBackdrop');

    const closeHandler = () => closeViewModal();

    closeBtn?.addEventListener('click', () => {
        if (currentEditEntryId) {
            closeEditModal();
        } else {
            closeHandler();
        }
    });

    closeBtnFooter?.addEventListener('click', () => {
        if (currentEditEntryId) {
            closeEditModal();
        } else {
            closeHandler();
        }
    });

    exportBtn?.addEventListener('click', () => {
        if (typeof exportViewPdf === 'function') {
            exportViewPdf();
        } else {
            alert('PDF export library not yet loaded. Please try again.');
        }
    });

    backdrop?.addEventListener('click', () => {
        if (currentEditEntryId) {
            closeEditModal();
        } else {
            closeHandler();
        }
    });

    saveBtn?.addEventListener('click', () => {
        const editSubmitBtn = document.getElementById('editSubmitBtn');
        if (editSubmitBtn) {
            editSubmitBtn.click();
        }
    });
}

function showDeleteConfirm(id, type) {
    const modal = document.getElementById('confirmModal');
    const backdrop = document.getElementById('confirmModalBackdrop');
    const message = document.getElementById('confirmModalMessage');
    const confirmBtn = document.getElementById('confirmModalConfirm');

    if (!modal || !backdrop || !message || !confirmBtn) {
        console.error('Confirm modal elements not found');
        return;
    }

    message.textContent = `Are you sure you want to delete this ${type}? This action cannot be undone.`;

    modal.classList.add('visible');
    backdrop.classList.add('visible');

    const handleConfirm = async () => {
        try {
            const formData = new FormData();
            formData.append('id', id);
            formData.append('type', type);

            const res = await fetch('api/delete-entry.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                closeConfirmModal();
                // Reload entries to reflect deletion
                if (loadEntriesCallback) {
                    loadEntriesCallback();
                }
            } else {
                alert('Failed to delete: ' + data.message);
            }
        } catch (err) {
            console.error('Error deleting entry:', err);
            alert('Failed to delete entry');
        }
    };

    const closeHandler = () => {
        closeConfirmModal();
        confirmBtn.removeEventListener('click', handleConfirm);
    };

    confirmBtn.addEventListener('click', handleConfirm);
    document.getElementById('confirmModalClose')?.addEventListener('click', closeHandler);
    document.getElementById('confirmModalCancel')?.addEventListener('click', closeHandler);
    backdrop.addEventListener('click', closeHandler);
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    const backdrop = document.getElementById('confirmModalBackdrop');
    if (modal) modal.classList.remove('visible');
    if (backdrop) backdrop.classList.remove('visible');
}

// ============================================================
// EDIT MODAL FUNCTIONALITY
// ============================================================
let currentEditEntryId = null;
let currentEditEntryType = null;

async function openEditModal(id, type) {
    const modal = document.getElementById('viewModal');
    const backdrop = document.getElementById('viewModalBackdrop');
    const body = document.getElementById('viewModalBody');
    const formContainer = document.getElementById('editFormContainer');
    const formWrapper = document.getElementById('editFormWrapper');
    const saveBtn = document.getElementById('viewModalSaveBtn');

    if (!modal || !backdrop || !body) {
        console.error('Edit modal elements not found');
        return;
    }

    currentEditEntryId = parseInt(id);
    currentEditEntryType = type;

    body.innerHTML = '';
    body.style.display = 'none';
    formContainer.style.display = 'block';
    formWrapper.innerHTML = '<div class="loading-state">Loading form...</div>';
    saveBtn.style.display = 'inline-block';

    modal.classList.add('visible');
    backdrop.classList.add('visible');

    try {
        const [detailsRes, formRes] = await Promise.all([
            fetch(`api/get-entry-details.php?id=${id}&type=${type}`),
            loadFormFragmentForEdit(type)
        ]);

        const detailsData = await detailsRes.json();

        if (!detailsData.success || !detailsData.entry) {
            formWrapper.innerHTML = '<p class="empty-state">Entry not found.</p>';
            return;
        }

        formWrapper.innerHTML = `
            <form id="editEntryForm" class="edit-form">
                <div id="editFormFields"></div>
                <button type="submit" id="editSubmitBtn" style="display: none;">Save Changes</button>
            </form>
        `;

        const formFields = document.getElementById('editFormFields');
        if (formRes) {
            formFields.innerHTML = formRes;
        }

        initTagsInputs();
        initPickerTriggers();
        initFormSubmission(type, true);

        if (type === 'character') {
            initImageUpload('character', 'charImagePreview', 'charImageHidden', 'charImageRemove', 'charImageWrapper');
        } else if (type === 'equipment') {
            initImageUpload('equipment', 'equipImagePreview', 'equipImageHidden', 'equipImageRemove', 'equipImageWrapper');
        } else if (type === 'world') {
            initImageUpload('world', 'worldImagePreview', 'worldImageHidden', 'worldImageRemove', 'worldImageWrapper');
        }

        if (type === 'story') {
            initSegmentedControl();
            initRichTextEditor();
        }

        setTimeout(() => {
            populateFormFields(detailsData.entry, type);
        }, 100);

    } catch (err) {
        console.error('Error loading edit modal:', err);
        formWrapper.innerHTML = '<p class="empty-state">Failed to load form. Please try again.</p>';
    }
}

async function loadFormFragmentForEdit(type) {
    const src = CREATE_FORM_ROUTES[type];
    if (!src) return null;

    try {
        const res = await fetch(src);
        if (!res.ok) throw new Error(`Failed to load form fragment: ${src}`);
        return await res.text();
    } catch (err) {
        console.error(err);
        return null;
    }
}

function populateFormFields(entry, type) {
    if (type === 'character') {
        setInputValue('charName', entry.name);
        setSelectValue('charType', entry.type_id);
        setInputValue('charNickname', entry.nickname);
        setInputValue('charAge', entry.age);
        setSelectValue('charGender', entry.gender);
        setSelectValue('charFaction', entry.faction);
        setInputValue('charAppearance', entry.appearance);
        setInputValue('charAbilities', entry.abilities);
        setInputValue('charBio', entry.bio);
        setInputValue('charImageHidden', entry.image);
        setInputValue('charTags', entry.tags);
        renderTagsFromString('charTags', entry.tags);

        if (entry.image) {
            const preview = document.getElementById('charImagePreview');
            const removeBtn = document.getElementById('charImageRemove');
            if (preview) preview.innerHTML = `<img src="${entry.image}" alt="Preview">`;
            if (removeBtn) removeBtn.style.display = 'inline-block';
        }

        if (entry.worlds && Array.isArray(entry.worlds)) {
            renderPickerChips('charWorldChips', entry.worlds, 'charWorldHidden', 'charWorld');
        }

        if (entry.equipment && Array.isArray(entry.equipment)) {
            renderPickerChips('charEquipmentChips', entry.equipment, 'charEquipmentHidden', 'charEquipment');
        }
    }
    else if (type === 'world') {
        setInputValue('worldName', entry.name);
        setSelectValue('worldType', entry.type_id);
        setInputValue('worldDescription', entry.description);
        setInputValue('worldLocation', entry.location);
        setInputValue('worldEra', entry.era);
        setSelectValue('worldGovernment', entry.government);
        setInputValue('worldPopulation', entry.population);
        setInputValue('worldLanguage', entry.language);
        setInputValue('worldReligion', entry.religion);
        setInputValue('worldCurrency', entry.currency);
        setInputValue('worldImageHidden', entry.image);
        setInputValue('worldTags', entry.tags);
        renderTagsFromString('worldTags', entry.tags);

        if (entry.image) {
            const preview = document.getElementById('worldImagePreview');
            const removeBtn = document.getElementById('worldImageRemove');
            if (preview) preview.innerHTML = `<img src="${entry.image}" alt="Preview">`;
            if (removeBtn) removeBtn.style.display = 'inline-block';
        }

        const currentRulers = entry.characters ? entry.characters.filter(c => {
            const role = c.role || '';
            return role.toLowerCase().includes('current') || role.toLowerCase().includes('ruler');
        }) : [];
        if (currentRulers.length > 0) {
            renderPickerChips('worldCurrentRulersChips', currentRulers, 'worldCurrentRulersHidden', 'worldCurrentRulers');
        }

        const previousRulers = entry.characters ? entry.characters.filter(c => {
            const role = c.role || '';
            return role.toLowerCase().includes('previous') || role.toLowerCase().includes('former');
        }) : [];
        if (previousRulers.length > 0) {
            renderPickerChips('worldPreviousRulersChips', previousRulers, 'worldPreviousRulersHidden', 'worldPreviousRulers');
        }
    }
    else if (type === 'equipment') {
        setInputValue('equipName', entry.name);
        setInputValue('equipAge', entry.age);
        setInputValue('equipDescription', entry.description);
        setSelectValue('equipType', entry.type_id);
        setSelectValue('equipStatus', entry.status);
        setInputValue('equipAppearance', entry.appearance);
        setInputValue('equipFeatures', entry.features);
        setInputValue('equipAbilities', entry.abilities);
        setInputValue('equipImageHidden', entry.image);
        setInputValue('equipTags', entry.tags);
        renderTagsFromString('equipTags', entry.tags);

        if (entry.image) {
            const preview = document.getElementById('equipImagePreview');
            const removeBtn = document.getElementById('equipImageRemove');
            if (preview) preview.innerHTML = `<img src="${entry.image}" alt="Preview">`;
            if (removeBtn) removeBtn.style.display = 'inline-block';
        }

        if (entry.worlds && Array.isArray(entry.worlds)) {
            renderPickerChips('equipWorldChips', entry.worlds, 'equipWorldHidden', 'equipWorld');
        }

        if (entry.current_owner) {
            renderPickerChips('equipCurrentOwnerChips', [entry.current_owner], 'equipCurrentOwnerHidden', 'equipCurrentOwner');
        }

        if (entry.previous_owners && Array.isArray(entry.previous_owners)) {
            renderPickerChips('equipPreviousOwnersChips', entry.previous_owners, 'equipPreviousOwnersHidden', 'equipPreviousOwners');
        }
    }
    else if (type === 'story') {
        setInputValue('storyTitle', entry.title);
        setSelectValue('storyType', entry.type_id);
        setInputValue('storySynopsis', entry.synopsis);
        setInputValue('storyTags', entry.tags);
        renderTagsFromString('storyTags', entry.tags);

        if (entry.genre) {
            renderTagsFromString('storyGenre', entry.genre);
        }

        const statusField = document.querySelector('.segmented-control');
        if (statusField && entry.status) {
            const buttons = statusField.querySelectorAll('.segment-btn');
            buttons.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.value === entry.status);
            });
            const hiddenInput = document.getElementById('storyStatus');
            if (hiddenInput) hiddenInput.value = entry.status;
        }

        const rteContent = document.getElementById('storyEntry');
        const rteHidden = document.getElementById('storyEntryHidden');
        if (rteContent && entry.entry_content) {
            rteContent.innerHTML = entry.entry_content;
        }
        if (rteHidden && entry.entry_content) {
            rteHidden.value = entry.entry_content;
        }

        const wordCountEl = document.getElementById('storyWordCount');
        if (wordCountEl && entry.word_count) {
            wordCountEl.textContent = entry.word_count;
        }

        if (entry.characters && Array.isArray(entry.characters)) {
            renderPickerChips('storyCharactersChips', entry.characters, 'storyCharactersHidden', 'storyCharacters');
        }

        if (entry.worlds && Array.isArray(entry.worlds)) {
            renderPickerChips('storyWorldsChips', entry.worlds, 'storyWorldsHidden', 'storyWorlds');
        }

        if (entry.equipment && Array.isArray(entry.equipment)) {
            renderPickerChips('storyEquipmentChips', entry.equipment, 'storyEquipmentHidden', 'storyEquipment');
        }
    }
}

function setInputValue(id, value) {
    const el = document.getElementById(id);
    if (el && value) el.value = value;
}

function setSelectValue(id, value) {
    const el = document.getElementById(id);
    if (el && value) el.value = value;
}

function renderTagsFromString(inputId, tagsString) {
    if (!tagsString) return;

    const input = document.getElementById(inputId);
    const list = document.getElementById(inputId + 'List');
    const hidden = document.getElementById(inputId + 'Hidden');

    if (!input || !list || !hidden) return;

    const tags = tagsString.split(',').map(t => t.trim()).filter(t => t);
    const listEl = list;
    listEl.innerHTML = tags.map((tag, i) => `
        <span class="tag-chip">
            ${escapeHtml(tag)}
            <button type="button" class="tag-remove" data-index="${i}" aria-label="Remove ${escapeHtml(tag)}">×</button>
        </span>
    `).join('');
    hidden.value = tags.join(',');

    listEl.querySelectorAll('.tag-remove').forEach(btn => {
        btn.addEventListener('click', () => {
            const idx = parseInt(btn.dataset.index);
            tags.splice(idx, 1);
            listEl.innerHTML = tags.map((tag, i) => `
                <span class="tag-chip">
                    ${escapeHtml(tag)}
                    <button type="button" class="tag-remove" data-index="${i}" aria-label="Remove ${escapeHtml(tag)}">×</button>
                </span>
            `).join('');
            hidden.value = tags.join(',');
        });
    });
}

function renderPickerChips(chipsId, items, hiddenId, fieldName) {
    const chipsEl = document.getElementById(chipsId);
    const hiddenEl = document.getElementById(hiddenId);

    if (!chipsEl || !hiddenEl || !items || !items.length) return;

    const itemsData = items.map(item => ({
        id: item.id,
        name: item.name || item.title || 'Unnamed'
    }));

    chipsEl.innerHTML = itemsData.map(item => `
        <span class="picker-chip">
            ${escapeHtml(item.name)}
            <button type="button" class="chip-remove" data-id="${item.id}" aria-label="Remove">×</button>
        </span>
    `).join('');

    hiddenEl.value = JSON.stringify(itemsData);

    chipsEl.querySelectorAll('.chip-remove').forEach(btn => {
        btn.addEventListener('click', () => {
            const idToRemove = parseInt(btn.dataset.id);
            const newItems = itemsData.filter(item => item.id !== idToRemove);
            chipsEl.innerHTML = newItems.map(item => `
                <span class="picker-chip">
                    ${escapeHtml(item.name)}
                    <button type="button" class="chip-remove" data-id="${item.id}" aria-label="Remove">×</button>
                </span>
            `).join('');
            hiddenEl.value = JSON.stringify(newItems);
        });
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function closeEditModal() {
    closeViewModal();

    const modeToggle = document.getElementById('viewModalModeToggle');
    const saveBtn = document.getElementById('viewModalSaveBtn');
    const formContainer = document.getElementById('editFormContainer');
    const body = document.getElementById('viewModalBody');

    if (modeToggle) modeToggle.style.display = 'none';
    if (saveBtn) saveBtn.style.display = 'none';
    if (formContainer) formContainer.style.display = 'none';
    if (body) body.style.display = '';

    currentEditEntryId = null;
    currentEditEntryType = null;
}

// ============================================================
// INIT — fetch picker modal once, then load default page
// ============================================================
document.addEventListener('DOMContentLoaded', async () => {
    // Initialize view modal
    initViewModal();

    // Load picker modal markup into its persistent container
    const pickerContainer = document.getElementById('pickerModalContainer');
    if (pickerContainer) {
        try {
            const res = await fetch('content-pages/Modals/pickerModal.php');
            if (res.ok) {
                pickerContainer.innerHTML = await res.text();
                initPickerModal();
            }
        } catch (err) {
            console.error('Could not load picker modal:', err);
        }
    }

    loadPage('archives');
});