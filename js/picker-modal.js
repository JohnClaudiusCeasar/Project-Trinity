// ============================================================
// PROJECT TRINITY — picker-modal.js
// Shared relational picker modal system.
// Loaded once by dashboardLayout.php.
// Any form fragment can trigger it via openPickerModal().
// ============================================================

// ------------------------------------------------------------
// PICKER DATA
// Hardcoded sample data — replace with fetch() DB calls later.
// 'type' and 'date' are stubs. 'desc' feeds the world summary panel.
// 'characters' and 'artifacts' are stub counts — replace with DB queries.
// ---------------------------------------------------  ---------
const PICKER_DATA = {
    world: [
        { id: 'w1', name: 'The Ashen Expanse',   type: '', date: '', characters: 0, artifacts: 0, desc: 'A vast, post-collapse wasteland blanketed in volcanic ash and fractured civilisations. Survivors cling to fortified settlements while factions war over dwindling resources beneath a sky choked with ash clouds.' },
        { id: 'w2', name: 'Velundra',            type: '', date: '', characters: 0, artifacts: 0, desc: 'An oceanic world of floating archipelagos governed by ancient maritime pacts. No landmass exceeds a kilometre in diameter, and the ocean floor has never been charted.' },
        { id: 'w3', name: 'The Mirrorlands',     type: '', date: '', characters: 0, artifacts: 0, desc: 'A parallel dimension that reflects the living world — but distorted, and ruled by echoes of the dead. Time moves differently here; an hour inside may be a year outside.' },
        { id: 'w4', name: 'Undercroft',          type: '', date: '', characters: 0, artifacts: 0, desc: 'A subterranean network of tunnels and cavern-cities, sealed from the surface for centuries. Its inhabitants have adapted to total darkness and distrust all light-dwellers.' },
        { id: 'w5', name: 'New Caelum',          type: '', date: '', characters: 0, artifacts: 0, desc: 'A sprawling urban megastructure built atop the ruins of an older, unknown civilisation. Every excavation risks awakening something buried intentionally.' },
    ],
    equipment: [
        { id: 'e1', name: 'Veil Shard Blade',     type: '', date: '', desc: 'A short blade forged from crystallised veil energy. Cuts through both matter and memory.' },
        { id: 'e2', name: 'Ashen Cloak',          type: '', date: '', desc: 'A full-length cloak woven from compressed ash-fibre. Grants near-invisibility in low light.' },
        { id: 'e3', name: 'Resonance Compass',    type: '', date: '', desc: 'Navigational tool that detects dimensional rifts and anomaly signatures within a 5km radius.' },
        { id: 'e4', name: 'Hollow Sigil Band',    type: '', date: '', desc: 'A wrist-worn band bearing the seal of the Hollow Collective. Acts as a key and identifier.' },
        { id: 'e5', name: 'Ember Canteen',        type: '', date: '', desc: 'A self-heating canteen that purifies any liquid and maintains temperature indefinitely.' },
        { id: 'e6', name: 'Mirrorglass Lens',     type: '', date: '', desc: 'A monocle-like lens that reveals reflected-dimension overlaps when worn over one eye.' },
    ],
};

const PICKER_META = {
    world:     { eyebrow: 'Linked Worlds',    title: 'Select Worlds'    },
    equipment: { eyebrow: 'Linked Equipment', title: 'Select Equipment' },
};

const PICKER_FILTER_OPTIONS = {
    world:     ['Universe', 'Dimension', 'Planet', 'Continent', 'City', 'Neighborhood'],
    equipment: ['Armor', 'Weapon', 'Accessories'],
};

const WORLD_ROLE_OPTIONS = [
    { value: '',           label: 'Select role…' },
    { value: 'native',     label: 'Native'       },
    { value: 'exile',      label: 'Exile'        },
    { value: 'visitor',    label: 'Visitor'      },
    { value: 'ruler',      label: 'Ruler'        },
    { value: 'guardian',   label: 'Guardian'     },
    { value: 'outcast',    label: 'Outcast'      },
    { value: 'refugee',    label: 'Refugee'      },
    { value: 'conqueror',  label: 'Conqueror'    },
    { value: 'unknown',    label: 'Unknown'      },
    { value: 'other',      label: 'Other'        },
];

// ------------------------------------------------------------
// WORLD RELATION CARDS
// Each selected world gets a collapsible card with:
//   - Header: toggle arrow + world name
//   - Summary panel (collapsed by default):
//       world description (max 150 words), character count, artifact count
//   - Body: Role/Status dropdown + Connection textarea
// ------------------------------------------------------------
function syncWorldRelationCards(relationsEl, items) {
    if (!relationsEl) return;

    // Preserve existing user input before rebuild
    const existingData = {};
    relationsEl.querySelectorAll('.world-relation-card').forEach(card => {
        const id = card.dataset.worldId;
        existingData[id] = {
            role:      card.querySelector('.world-relation-role')?.value || '',
            desc:      card.querySelector('.world-relation-desc')?.value || '',
            expanded:  card.classList.contains('is-expanded'),
        };
    });

    if (!items.length) {
        relationsEl.innerHTML = '';
        return;
    }

    relationsEl.innerHTML = items.map(item => {
        const prev       = existingData[item.id] || { role: '', desc: '', expanded: false };
        const worldData  = PICKER_DATA.world.find(w => w.id === item.id) || {};

        // Truncate description to 150 words
        const fullDesc   = worldData.desc || '';
        const words      = fullDesc.split(/\s+/);
        const truncated  = words.length > 150
            ? words.slice(0, 150).join(' ') + '…'
            : fullDesc;

        const charCount  = worldData.characters ?? 0;
        const artCount   = worldData.artifacts  ?? 0;

        const roleOptions = WORLD_ROLE_OPTIONS.map(opt =>
            `<option value="${opt.value}" ${prev.role === opt.value ? 'selected' : ''}>${opt.label}</option>`
        ).join('');

        const isExpanded = prev.expanded;

        return `
        <div class="world-relation-card ${isExpanded ? 'is-expanded' : ''}" data-world-id="${item.id}">

            <!-- Header — click to toggle -->
            <button type="button" class="world-relation-toggle" aria-expanded="${isExpanded}" aria-controls="worldRelBody_${item.id}">
                <span class="world-relation-icon">⬡</span>
                <span class="world-relation-name">${item.name}</span>
                <span class="world-relation-arrow" aria-hidden="true">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                    </svg>
                </span>
            </button>

            <!-- Collapsible body -->
            <div class="world-relation-collapsible" id="worldRelBody_${item.id}" ${isExpanded ? '' : 'hidden'}>

                <!-- World Summary Panel -->
                <div class="world-summary-panel">
                    <p class="world-summary-desc">${truncated}</p>
                    <div class="world-summary-stats">
                        <div class="world-summary-stat">
                            <span class="world-summary-stat-label">No. of Characters</span>
                            <span class="world-summary-stat-value">${charCount}</span>
                        </div>
                        <div class="world-summary-stat">
                            <span class="world-summary-stat-label">No. of Artifacts</span>
                            <span class="world-summary-stat-value">${artCount}</span>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="world-relation-divider"></div>

                <!-- Relation Fields -->
                <div class="world-relation-body">
                    <div class="world-relation-field">
                        <label class="world-relation-label" for="worldRole_${item.id}">Role / Status</label>
                        <select class="form-input form-select world-relation-role"
                                id="worldRole_${item.id}"
                                name="worldRole[${item.id}]">
                            ${roleOptions}
                        </select>
                    </div>
                    <div class="world-relation-field">
                        <label class="world-relation-label" for="worldDesc_${item.id}">Connection</label>
                        <textarea class="form-input form-textarea world-relation-desc"
                                  id="worldDesc_${item.id}"
                                  name="worldDesc[${item.id}]"
                                  rows="2"
                                  placeholder="Describe the character's tie to this world…">${prev.desc}</textarea>
                    </div>
                </div>

            </div>
        </div>`;
    }).join('');

    // Bind toggle buttons
    relationsEl.querySelectorAll('.world-relation-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const card         = btn.closest('.world-relation-card');
            const collapsible  = card.querySelector('.world-relation-collapsible');
            const isNowOpen    = !card.classList.contains('is-expanded');

            card.classList.toggle('is-expanded', isNowOpen);
            btn.setAttribute('aria-expanded', isNowOpen);

            if (isNowOpen) {
                collapsible.removeAttribute('hidden');
                // Kick off height animation
                collapsible.style.maxHeight = collapsible.scrollHeight + 'px';
            } else {
                collapsible.style.maxHeight = collapsible.scrollHeight + 'px';
                // Force reflow so transition fires
                collapsible.getBoundingClientRect();
                collapsible.style.maxHeight = '0';
                collapsible.addEventListener('transitionend', () => {
                    if (!card.classList.contains('is-expanded')) {
                        collapsible.setAttribute('hidden', '');
                        collapsible.style.maxHeight = '';
                    }
                }, { once: true });
            }
        });
    });

    // Staggered entrance animation
    relationsEl.querySelectorAll('.world-relation-card').forEach((card, i) => {
        card.style.animationDelay = `${i * 60}ms`;
        card.classList.add('world-relation-card--enter');
    });
}

// ------------------------------------------------------------
// MODAL INIT
// ------------------------------------------------------------
function initPickerModal() {
    const backdrop    = document.getElementById('pickerModalBackdrop');
    const modal       = document.getElementById('pickerModal');
    const eyebrow     = document.getElementById('pickerModalEyebrow');
    const titleEl     = document.getElementById('pickerModalTitle');
    const listEl      = document.getElementById('pickerModalList');
    const searchInput = document.getElementById('pickerSearchInput');
    const closeBtn    = document.getElementById('pickerModalClose');
    const confirmBtn  = document.getElementById('pickerConfirmBtn');
    const countEl     = document.getElementById('pickerSelectedCount');

    const filterBtn       = document.getElementById('pickerFilterBtn');
    const filterDropdown  = document.getElementById('pickerFilterDropdown');
    const filterList      = document.getElementById('pickerFilterList');
    const filterIndicator = document.getElementById('pickerFilterIndicator');

    const sortBtn         = document.getElementById('pickerSortBtn');
    const sortDropdown    = document.getElementById('pickerSortDropdown');
    const sortList        = document.getElementById('pickerSortList');
    const sortIndicator   = document.getElementById('pickerSortIndicator');

    if (!modal || !backdrop) return;

    let currentType        = null;
    let currentChipsEl     = null;
    let currentHiddenEl    = null;
    let currentRelationsEl = null;
    let selectedItems      = [];
    let allItems           = [];
    let activeFilter       = null;
    let activeSort         = 'az';

    // ── Open ──
    window.openPickerModal = function (type, chipsEl, hiddenEl, relationsEl) {
        currentType        = type;
        currentChipsEl     = chipsEl;
        currentHiddenEl    = hiddenEl;
        currentRelationsEl = relationsEl || null;
        allItems           = PICKER_DATA[type] || [];

        const existing = hiddenEl.value ? hiddenEl.value.split(',') : [];
        selectedItems  = allItems.filter(item => existing.includes(item.id));

        const meta = PICKER_META[type] || { eyebrow: type, title: type };
        eyebrow.textContent = meta.eyebrow;
        titleEl.textContent = meta.title;
        searchInput.value   = '';

        activeFilter = null;
        activeSort   = 'az';

        buildFilterOptions(type);
        updateFilterIndicator();
        updateSortIndicator();
        closeAllDropdowns();
        renderList(getDisplayItems());
        updateCount();

        backdrop.classList.add('open');
        modal.classList.add('open');
        searchInput.focus();
    };

    function closeModal() {
        backdrop.classList.remove('open');
        modal.classList.remove('open');
        closeAllDropdowns();
        currentType = null;
    }

    function closeAllDropdowns() {
        filterDropdown.classList.remove('open');
        filterDropdown.setAttribute('aria-hidden', 'true');
        filterBtn.classList.remove('active');
        sortDropdown.classList.remove('open');
        sortDropdown.setAttribute('aria-hidden', 'true');
        sortBtn.classList.remove('active');
    }

    function toggleDropdown(dropdown, btn) {
        const isOpen = dropdown.classList.contains('open');
        closeAllDropdowns();
        if (!isOpen) {
            dropdown.classList.add('open');
            dropdown.setAttribute('aria-hidden', 'false');
            btn.classList.add('active');
        }
    }

    function buildFilterOptions(type) {
        const options = PICKER_FILTER_OPTIONS[type] || [];
        filterList.innerHTML = options.map(opt => `
            <li class="picker-dropdown-item ${activeFilter === opt ? 'selected' : ''}" data-filter="${opt}">
                <span class="picker-dropdown-check">${activeFilter === opt ? '✓' : ''}</span>${opt}
            </li>
        `).join('');
    }

    function getDisplayItems() {
        let items = [...allItems];
        const q = searchInput.value.trim().toLowerCase();
        if (q) {
            items = items.filter(i =>
                i.name.toLowerCase().includes(q) ||
                i.desc.toLowerCase().includes(q)
            );
        }
        if (activeFilter) {
            const filtered = items.filter(i => i.type === activeFilter);
            if (filtered.length > 0) items = filtered;
            else console.info(`[Picker] Filter "${activeFilter}" active but no items carry a 'type' value yet. Awaiting DB.`);
        }
        if (activeSort === 'az')          items.sort((a, b) => a.name.localeCompare(b.name));
        else if (activeSort === 'za')     items.sort((a, b) => b.name.localeCompare(a.name));
        else if (activeSort === 'recent') console.info('[Picker] Sort by Recent — awaiting DB date fields.');
        return items;
    }

    function refreshList() { renderList(getDisplayItems()); }

    function renderList(items) {
        if (!items.length) {
            listEl.innerHTML = `<li class="picker-list-empty">No entries found.</li>`;
            return;
        }
        listEl.innerHTML = items.map(item => {
            const isSelected = selectedItems.some(s => s.id === item.id);
            return `
                <li class="picker-list-item ${isSelected ? 'selected' : ''}"
                    data-id="${item.id}" data-name="${item.name}">
                    <div class="picker-item-check">${isSelected ? '✓' : ''}</div>
                    <div>
                        <div class="picker-item-name">${item.name}</div>
                        <div class="picker-item-desc">${item.desc}</div>
                    </div>
                </li>`;
        }).join('');
    }

    function updateCount() {
        const n = selectedItems.length;
        countEl.textContent = n === 0 ? '0 selected' : `${n} selected`;
    }

    function updateFilterIndicator() {
        filterIndicator.classList.toggle('visible', activeFilter !== null);
        filterBtn.classList.toggle('has-active', activeFilter !== null);
    }

    function updateSortIndicator() {
        const isNonDefault = activeSort !== 'az';
        sortIndicator.classList.toggle('visible', isNonDefault);
        sortBtn.classList.toggle('has-active', isNonDefault);
        sortList.querySelectorAll('.picker-dropdown-item').forEach(li => {
            const sel = li.dataset.sort === activeSort;
            li.classList.toggle('selected', sel);
            li.querySelector('.picker-dropdown-check').textContent = sel ? '✓' : '';
        });
    }

    listEl.addEventListener('click', e => {
        const li = e.target.closest('.picker-list-item');
        if (!li) return;
        const id = li.dataset.id, name = li.dataset.name;
        const idx = selectedItems.findIndex(s => s.id === id);
        if (idx > -1) selectedItems.splice(idx, 1);
        else selectedItems.push({ id, name });
        updateCount();
        const isNowSelected = selectedItems.some(s => s.id === id);
        li.classList.toggle('selected', isNowSelected);
        li.querySelector('.picker-item-check').textContent = isNowSelected ? '✓' : '';
    });

    searchInput.addEventListener('input', refreshList);
    filterBtn.addEventListener('click', e => { e.stopPropagation(); toggleDropdown(filterDropdown, filterBtn); });
    sortBtn.addEventListener('click',   e => { e.stopPropagation(); toggleDropdown(sortDropdown, sortBtn); });

    filterList.addEventListener('click', e => {
        const li = e.target.closest('.picker-dropdown-item');
        if (!li) return;
        activeFilter = activeFilter === li.dataset.filter ? null : li.dataset.filter;
        buildFilterOptions(currentType);
        updateFilterIndicator();
        refreshList();
        closeAllDropdowns();
    });

    sortList.addEventListener('click', e => {
        const li = e.target.closest('.picker-dropdown-item');
        if (!li || li.classList.contains('picker-dropdown-item--stub')) return;
        if (li.dataset.sort === 'recent') console.info('[Picker] "Recent Items" — no-op until DB date fields.');
        activeSort = li.dataset.sort;
        updateSortIndicator();
        refreshList();
        closeAllDropdowns();
    });

    // ── Confirm ──
    confirmBtn.addEventListener('click', () => {
        if (!currentChipsEl || !currentHiddenEl) return;

        currentHiddenEl.value = selectedItems.map(s => s.id).join(',');

        currentChipsEl.innerHTML = selectedItems.map(s => `
            <span class="picker-chip" data-id="${s.id}">
                ${s.name}
                <button type="button" class="picker-chip-remove"
                        data-id="${s.id}" aria-label="Remove ${s.name}">×</button>
            </span>
        `).join('');

        currentChipsEl.querySelectorAll('.picker-chip-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                const removedId = btn.dataset.id;
                btn.closest('.picker-chip')?.remove();
                const vals = currentHiddenEl.value.split(',').filter(v => v !== removedId);
                currentHiddenEl.value = vals.join(',');

                if (currentRelationsEl) {
                    const card = currentRelationsEl.querySelector(`.world-relation-card[data-world-id="${removedId}"]`);
                    if (card) {
                        card.classList.add('world-relation-card--exit');
                        card.addEventListener('animationend', () => card.remove(), { once: true });
                    }
                }
            });
        });

        if (currentType === 'world' && currentRelationsEl) {
            syncWorldRelationCards(currentRelationsEl, selectedItems);
        }

        closeModal();
    });

    closeBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    document.addEventListener('click', e => {
        if (!modal.classList.contains('open')) return;
        const fw = document.getElementById('pickerFilterWrap');
        const sw = document.getElementById('pickerSortWrap');
        if (!fw.contains(e.target) && !sw.contains(e.target)) closeAllDropdowns();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal.classList.contains('open')) {
            const anyOpen = filterDropdown.classList.contains('open') || sortDropdown.classList.contains('open');
            anyOpen ? closeAllDropdowns() : closeModal();
        }
    });
}

// ------------------------------------------------------------
// PICKER TRIGGER BINDING
// ------------------------------------------------------------
function initPickerTriggers() {
    document.querySelectorAll('.picker-trigger').forEach(btn => {
        if (btn.dataset.pickerBound) return;
        btn.dataset.pickerBound = 'true';

        btn.addEventListener('click', () => {
            const type        = btn.dataset.picker;
            const chipsEl     = document.getElementById(btn.dataset.targetChips);
            const hiddenEl    = document.getElementById(btn.dataset.targetHidden);
            const relationsEl = btn.dataset.targetRelations
                ? document.getElementById(btn.dataset.targetRelations)
                : null;
            if (type && chipsEl && hiddenEl) {
                window.openPickerModal(type, chipsEl, hiddenEl, relationsEl);
            }
        });
    });
}