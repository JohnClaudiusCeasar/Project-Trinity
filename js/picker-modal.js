// ============================================================
// PROJECT TRINITY — picker-modal.js
// Shared relational picker modal system.
// Loaded once by dashboardLayout.php.
// Any form fragment can trigger it via openPickerModal().
// ============================================================

const PICKER_META = {
    world:       { eyebrow: 'Linked Worlds',    title: 'Select Worlds'    },
    equipment:   { eyebrow: 'Linked Equipment', title: 'Select Equipment' },
    character:  { eyebrow: 'Linked Characters', title: 'Select Characters' },
    story:      { eyebrow: 'Linked Stories', title: 'Select Stories' },
    faction:    { eyebrow: 'Linked Factions', title: 'Select Factions' },
};

const WORLD_ROLE_OPTIONS = [
    { value: '',           label: 'Select role…' },
    { value: 'native',     label: 'Native'       },
    { value: 'exile',      label: 'Exile'        },
    { value: 'visitor',    label: 'Visitor'      },
    { value: 'ruler',      label: 'Ruler'        },
    { value: 'guardian',   label: 'Guardian'      },
    { value: 'outcast',    label: 'Outcast'       },
    { value: 'refugee',    label: 'Refugee'       },
    { value: 'conqueror',  label: 'Conqueror'     },
    { value: 'unknown',    label: 'Unknown'       },
    { value: 'other',      label: 'Other'         },
];

// ------------------------------------------------------------
// HELPER FUNCTIONS FOR PICKER CARDS
// ------------------------------------------------------------
function truncateWords(text, maxWords) {
    if (!text) return '';
    const words = text.split(/\s+/);
    if (words.length <= maxWords) return text;
    return words.slice(0, maxWords).join(' ');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function renderPickerCard(item, type) {
    const fullDesc = item.desc || '';
    const truncatedDesc = truncateWords(fullDesc, 200);
    const needsExpand = fullDesc.split(/\s+/).length > 200;
    
    const typeIcon = {
        equipment: '⚔',
        character: '♟',
        story: '📜'
    }[type] || '▸';

    return `
        <div class="picker-card" data-id="${item.id}">
            <div class="picker-card-header">
                <span class="picker-card-icon">${typeIcon}</span>
                <span class="picker-card-name">${escapeHtml(item.name)}</span>
                <button type="button" class="picker-card-remove" data-id="${item.id}" aria-label="Remove ${escapeHtml(item.name)}">×</button>
            </div>
            <div class="picker-card-body">
                <p class="picker-card-desc">${escapeHtml(truncatedDesc)}${needsExpand ? '<span class="picker-card-expand">...see more</span>' : ''}</p>
            </div>
            ${fullDesc ? `<div class="picker-card-full-desc" hidden>${escapeHtml(fullDesc)}</div>` : ''}
        </div>
    `;
}

// ------------------------------------------------------------
// WORLD RELATION CARDS
// ------------------------------------------------------------
function syncWorldRelationCards(relationsEl, items) {
    if (!relationsEl) return;

    const existingData = {};
    relationsEl.querySelectorAll('.world-relation-card').forEach(card => {
        const id = card.dataset.worldId;
        existingData[id] = {
            role:     card.querySelector('.world-relation-role')?.value || '',
            desc:     card.querySelector('.world-relation-desc')?.value || '',
            expanded: card.classList.contains('is-expanded'),
        };
    });

    if (!items.length) {
        relationsEl.innerHTML = '';
        return;
    }

    relationsEl.innerHTML = items.map(item => {
        const prev = existingData[item.id] || { role: '', desc: '', expanded: false };
        const worldData = window.PICKER_DB_DATA?.world?.find(w => String(w.id) === String(item.id)) || {};

        const fullDesc = worldData.desc || '';
        const words = fullDesc.split(/\s+/);
        const truncated = words.length > 150 ? words.slice(0, 150).join(' ') + '…' : fullDesc;

        const charCount = worldData.characters ?? 0;
        const artCount = worldData.artifacts ?? 0;

        const roleOptions = WORLD_ROLE_OPTIONS.map(opt =>
            `<option value="${opt.value}" ${prev.role === opt.value ? 'selected' : ''}>${opt.label}</option>`
        ).join('');

        const isExpanded = prev.expanded;

        return `
        <div class="world-relation-card ${isExpanded ? 'is-expanded' : ''}" data-world-id="${item.id}">
            <button type="button" class="world-relation-toggle" aria-expanded="${isExpanded}" aria-controls="worldRelBody_${item.id}">
                <span class="world-relation-icon">⬡</span>
                <span class="world-relation-name">${item.name}</span>
                <span class="world-relation-arrow" aria-hidden="true">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                    </svg>
                </span>
            </button>

            <div class="world-relation-collapsible" id="worldRelBody_${item.id}" ${isExpanded ? '' : 'hidden'}>
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

                <div class="world-relation-divider"></div>

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

    relationsEl.querySelectorAll('.world-relation-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const card = btn.closest('.world-relation-card');
            const collapsible = card.querySelector('.world-relation-collapsible');
            const isNowOpen = !card.classList.contains('is-expanded');

            card.classList.toggle('is-expanded', isNowOpen);
            btn.setAttribute('aria-expanded', isNowOpen);

            if (isNowOpen) {
                collapsible.removeAttribute('hidden');
                collapsible.style.maxHeight = collapsible.scrollHeight + 'px';
            } else {
                collapsible.style.maxHeight = collapsible.scrollHeight + 'px';
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

    relationsEl.querySelectorAll('.world-relation-card').forEach((card, i) => {
        card.style.animationDelay = `${i * 60}ms`;
        card.classList.add('world-relation-card--enter');
    });
}

// ------------------------------------------------------------
// MODAL INIT
// ------------------------------------------------------------
function initPickerModal() {
    const backdrop = document.getElementById('pickerModalBackdrop');
    const modal = document.getElementById('pickerModal');
    const eyebrow = document.getElementById('pickerModalEyebrow');
    const titleEl = document.getElementById('pickerModalTitle');
    const listEl = document.getElementById('pickerModalList');
    const searchInput = document.getElementById('pickerSearchInput');
    const closeBtn = document.getElementById('pickerModalClose');
    const confirmBtn = document.getElementById('pickerConfirmBtn');
    const countEl = document.getElementById('pickerSelectedCount');
    const filterBtn = document.getElementById('pickerFilterBtn');
    const filterDropdown = document.getElementById('pickerFilterDropdown');
    const filterList = document.getElementById('pickerFilterList');
    const filterIndicator = document.getElementById('pickerFilterIndicator');
    const sortBtn = document.getElementById('pickerSortBtn');
    const sortDropdown = document.getElementById('pickerSortDropdown');
    const sortList = document.getElementById('pickerSortList');
    const sortIndicator = document.getElementById('pickerSortIndicator');

    if (!modal || !backdrop) return;

    let currentType = null;
    let currentChipsEl = null;
    let currentHiddenEl = null;
    let currentRelationsEl = null;
    let selectedItems = [];
    let allItems = [];
    let availableFilters = [];
    let activeFilter = null;
    let activeSort = 'az';

    // ── Open ──
    window.openPickerModal = async function (type, chipsEl, hiddenEl, relationsEl) {
        currentType = type;
        currentChipsEl = chipsEl;
        currentHiddenEl = hiddenEl;
        currentRelationsEl = relationsEl || null;

        listEl.innerHTML = '<li class="picker-list-empty">Loading archives...</li>';
        backdrop.classList.add('open');
        modal.classList.add('open');
        searchInput.focus();

        try {
            const res = await fetch(`api/get-picker-items.php?type=${type}`);
            if (!res.ok) throw new Error('Failed to fetch items');
            const data = await res.json();
            
            allItems = data.items || [];
            availableFilters = data.filters || [];
            
            // Keep a local copy for syncWorldRelationCards if needed
            if (!window.PICKER_DB_DATA) window.PICKER_DB_DATA = {};
            window.PICKER_DB_DATA[type] = allItems;
            window.PICKER_DB_DATA.filters = window.PICKER_DB_DATA.filters || {};
            window.PICKER_DB_DATA.filters[type] = availableFilters;

        } catch (err) {
            console.error('[Picker] Error fetching data:', err);
            listEl.innerHTML = '<li class="picker-list-empty">Error loading archives.</li>';
            allItems = [];
            availableFilters = [];
        }

        // Map existing hidden values to selectedItems
        const existingIds = hiddenEl.value ? hiddenEl.value.split(',') : [];
        selectedItems = allItems.filter(item => existingIds.includes(String(item.id)));

        const meta = PICKER_META[type] || { eyebrow: type, title: type };
        eyebrow.textContent = meta.eyebrow;
        titleEl.textContent = meta.title;
        searchInput.value = '';

        activeFilter = null;
        activeSort = 'az';

        buildFilterOptions(type);
        updateFilterIndicator();
        updateSortIndicator();
        closeAllDropdowns();
        renderList(getDisplayItems());
        updateCount();
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
        filterList.innerHTML = availableFilters.map(opt => `
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
                (i.desc && i.desc.toLowerCase().includes(q))
            );
        }

        if (activeFilter) {
            const filtered = items.filter(i => i.type === activeFilter);
            if (filtered.length > 0) items = filtered;
        }

        if (activeSort === 'az') {
            items.sort((a, b) => a.name.localeCompare(b.name));
        } else if (activeSort === 'za') {
            items.sort((a, b) => b.name.localeCompare(a.name));
        } else if (activeSort === 'recent') {
            items.sort((a, b) => new Date(b.date) - new Date(a.date));
        }

        return items;
    }

    function refreshList() {
        renderList(getDisplayItems());
    }

    function renderList(items) {
        if (!items.length) {
            listEl.innerHTML = '<li class="picker-list-empty">No entries found.</li>';
            return;
        }
        listEl.innerHTML = items.map(item => {
            const isSelected = selectedItems.some(s => String(s.id) === String(item.id));
            return `
            <li class="picker-list-item ${isSelected ? 'selected' : ''}"
                data-id="${item.id}" data-name="${item.name}">
                <div class="picker-item-check">${isSelected ? '✓' : ''}</div>
                <div>
                    <div class="picker-item-name">${item.name}</div>
                    <div class="picker-item-desc">${item.desc || ''}</div>
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
            li.querySelector('.picker-dropdown-check').textContent = sel ? '✓' : ' ';
        });
    }

    // ── Event Listeners ──
    listEl.addEventListener('click', e => {
        const li = e.target.closest('.picker-list-item');
        if (!li) return;
        const id = li.dataset.id, name = li.dataset.name;
        const idx = selectedItems.findIndex(s => String(s.id) === String(id));
        if (idx > -1) selectedItems.splice(idx, 1);
        else selectedItems.push({ id, name });
        updateCount();
        const isNowSelected = selectedItems.some(s => String(s.id) === String(id));
        li.classList.toggle('selected', isNowSelected);
        li.querySelector('.picker-item-check').textContent = isNowSelected ? '✓' : '';
    });

    searchInput.addEventListener('input', refreshList);
    filterBtn.addEventListener('click', e => { e.stopPropagation(); toggleDropdown(filterDropdown, filterBtn); });
    sortBtn.addEventListener('click', e => { e.stopPropagation(); toggleDropdown(sortDropdown, sortBtn); });

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
        activeSort = li.dataset.sort;
        updateSortIndicator();
        refreshList();
        closeAllDropdowns();
    });

    // ── Confirm ─
    confirmBtn.addEventListener('click', () => {
        if (!currentChipsEl || !currentHiddenEl) return;

        currentHiddenEl.value = selectedItems.map(s => s.id).join(',');

        // Render as cards for all types except world (world has special relation cards)
        if (currentType !== 'world') {
            currentChipsEl.innerHTML = selectedItems.map(item => renderPickerCard(item, currentType)).join('');
            
            // Add expand/collapse handlers for "see more"
            currentChipsEl.querySelectorAll('.picker-card-expand').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const card = e.target.closest('.picker-card');
                    const descEl = card.querySelector('.picker-card-desc');
                    const fullDescEl = card.querySelector('.picker-card-full-desc');
                    const isExpanded = card.classList.contains('is-expanded');
                    
                    card.classList.toggle('is-expanded');
                    descEl.textContent = isExpanded ? truncateWords(fullDescEl.textContent, 200) : fullDescEl.textContent;
                    e.target.textContent = isExpanded ? '...see more' : ' see less';
                });
            });
        } else {
            // Keep simple chips for world (they get rendered as relation cards separately)
            currentChipsEl.innerHTML = selectedItems.map(s => `
                <span class="picker-chip" data-id="${s.id}">
                    ${s.name}
                    <button type="button" class="picker-chip-remove"
                            data-id="${s.id}" aria-label="Remove ${s.name}">×</button>
                </span>
            `).join('');
        }

        currentChipsEl.querySelectorAll('.picker-chip-remove, .picker-card-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                const removedId = btn.dataset.id;
                const chipOrCard = btn.closest('.picker-chip') || btn.closest('.picker-card');
                chipOrCard?.remove();
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
            const type = btn.dataset.picker;
            const chipsEl = document.getElementById(btn.dataset.targetChips);
            const hiddenEl = document.getElementById(btn.dataset.targetHidden);
            const relationsEl = btn.dataset.targetRelations
                ? document.getElementById(btn.dataset.targetRelations)
                : null;
            if (type && chipsEl && hiddenEl) {
                window.openPickerModal(type, chipsEl, hiddenEl, relationsEl);
            }
        });
    });
}