// ============================================================
// PROJECT TRINITY — dashboard-script.js
// ============================================================

// ------------------------------------------------------------
// NAV ROUTER
// Maps page keys to their content source.
// - type: 'fetch'       → loads a .php fragment via fetch()
// - type: 'placeholder' → renders a "Content in Development" card
// ------------------------------------------------------------
const NAV_ROUTES = {
    archives: { type: 'fetch', src: 'content-pages/Dashboard/dashboardArchive.php' },
    create:   { type: 'fetch', src: 'content-pages/Dashboard/dashboardCreate.php'  },
    view:     { type: 'placeholder', label: 'View'     },
    projects: { type: 'placeholder', label: 'Projects' },
    explore:  { type: 'placeholder', label: 'Explore'  },
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

// ------------------------------------------------------------
// SIDEBAR
// ------------------------------------------------------------
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

// ------------------------------------------------------------
// NAV CLICK HANDLER — sidebar + header via [data-page]
// ------------------------------------------------------------
document.querySelectorAll('[data-page]').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const pageKey = link.dataset.page;
        setActiveNav(pageKey);
        loadPage(pageKey);
        window.closeSidebar();
    });
});

// ------------------------------------------------------------
// ARCHIVE FEATURES
// Re-initialised after every fetch inject
// ------------------------------------------------------------
function initDashboardFeatures() {
    initStatToggle();
    initCategoryFilter();
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

// ------------------------------------------------------------
// CREATE PAGE — card picker → type-specific form flow
// ------------------------------------------------------------
const CREATE_TYPE_META = {
    story:     { icon: '◎', label: 'Story'             },
    character: { icon: '◈', label: 'Character'         },
    world:     { icon: '⬡', label: 'World'             },
    object:    { icon: '✦', label: 'Object / Artifact'  },
};

// ------------------------------------------------------------
// FORM FRAGMENT ROUTES
// Maps each entry type to its standalone PHP form fragment.
// Add a new entry here when a new type form is created.
// ------------------------------------------------------------
const CREATE_FORM_ROUTES = {
    character: 'content-pages/Forms/characterForm.php',
    // story:  'content-pages/Dashboard/dashboardCreate-story.php',
    // world:  'content-pages/Dashboard/dashboardCreate-world.php',
    // object: 'content-pages/Dashboard/dashboardCreate-object.php',
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


// ------------------------------------------------------------
// TAGS INPUT BEHAVIOUR
// ------------------------------------------------------------
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

// ------------------------------------------------------------
// INIT CREATE PAGE
// ------------------------------------------------------------
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
                initPickerTriggers();   // defined in picker-modal.js
            }
        });
    });

    function returnToPicker() {
        const mainContainer = document.querySelector('.create-main');
        if (mainContainer) mainContainer.classList.remove('is-form-active');

        formWrapper.classList.remove('visible');
        picker.style.display = '';
        if (formFields) formFields.innerHTML = '';
    }

    backBtn?.addEventListener('click', returnToPicker);
    cancelBtn?.addEventListener('click', returnToPicker);
}

// ------------------------------------------------------------
// INIT — fetch picker modal once, then load default page
// ------------------------------------------------------------
document.addEventListener('DOMContentLoaded', async () => {
    // Load picker modal markup into its persistent container
    // (lives outside #mainContent so it survives SPA navigation)
    const pickerContainer = document.getElementById('pickerModalContainer');
    if (pickerContainer) {
        try {
            const res = await fetch('content-pages/Modals/pickerModal.php');
            if (res.ok) {
                pickerContainer.innerHTML = await res.text();
                initPickerModal();   // defined in picker-modal.js
            }
        } catch (err) {
            console.error('Could not load picker modal:', err);
        }
    }

    loadPage('archives');
});