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
            if (pageKey === 'view') {
                initViewPage();
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
            }
        });
    });

    backBtn?.addEventListener('click', returnToPicker);
    cancelBtn?.addEventListener('click', returnToPicker);
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
function initFormSubmission(type) {
    const form = document.getElementById('createForm');
    if (!form) return;

    const submitBtn = document.getElementById('createSubmitBtn');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save Entry';
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
        }

        const formData = new FormData(form);

        // Handle rich text editor hidden inputs before submission
        document.querySelectorAll('.rich-text-editor').forEach(editor => {
            const content = editor.querySelector('.rte-content');
            const hiddenInput = editor.parentElement.querySelector('input[type="hidden"]');
            if (content && hiddenInput) {
                hiddenInput.name = hiddenInput.id.replace('Hidden', '');
                formData.set(hiddenInput.name, content.innerHTML);
            }
        });

        formData.append('type', type);

        try {
            const res = await fetch(`php/${type}-process.php`, {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                alert('Entry created successfully!');
                returnToPicker();
                setActiveNav('view');
                loadPage('view');
            } else {
                if (data.errors && Array.isArray(data.errors)) {
                    alert('Please fix the following:\n' + data.errors.join('\n'));
                } else {
                    alert(data.message || 'Failed to create entry');
                }
            }
        } catch (err) {
            console.error('Error:', err);
            alert('An error occurred. Please try again.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Entry';
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
            alert('Edit feature coming soon');
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
}

function initViewModal() {
    const closeBtn = document.getElementById('viewModalClose');
    const closeBtnFooter = document.getElementById('viewModalCloseBtn');
    const backdrop = document.getElementById('viewModalBackdrop');

    const closeHandler = () => closeViewModal();

    closeBtn?.addEventListener('click', closeHandler);
    closeBtnFooter?.addEventListener('click', closeHandler);
    backdrop?.addEventListener('click', closeHandler);
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