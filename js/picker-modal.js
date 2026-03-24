// ============================================================
// PROJECT TRINITY — picker-modal.js
// Shared relational picker modal system.
// Loaded once by dashboardLayout.php.
// Any form fragment can trigger it via openPickerModal().  
// ============================================================

// ------------------------------------------------------------
// PICKER DATA
// Hardcoded sample data — replace with fetch() DB calls later.
// ------------------------------------------------------------
const PICKER_DATA = {
    world: [
        { id: 'w1', name: 'The Ashen Expanse',  desc: 'A vast, post-collapse wasteland blanketed in volcanic ash and fractured civilisations.' },
        { id: 'w2', name: 'Velundra',            desc: 'An oceanic world of floating archipelagos governed by ancient maritime pacts.' },
        { id: 'w3', name: 'The Mirrorlands',     desc: 'A parallel dimension that reflects the living world — but distorted, and ruled by echoes.' },
        { id: 'w4', name: 'Undercroft',          desc: 'A subterranean network of tunnels and cavern-cities, sealed from the surface for centuries.' },
        { id: 'w5', name: 'New Caelum',          desc: 'A sprawling urban megastructure built atop the ruins of an older, unknown civilisation.' },
    ],
    equipment: [
        { id: 'e1', name: 'Veil Shard Blade',    desc: 'A short blade forged from crystallised veil energy. Cuts through both matter and memory.' },
        { id: 'e2', name: 'Ashen Cloak',          desc: 'A full-length cloak woven from compressed ash-fibre. Grants near-invisibility in low light.' },
        { id: 'e3', name: 'Resonance Compass',    desc: 'Navigational tool that detects dimensional rifts and anomaly signatures within a 5km radius.' },
        { id: 'e4', name: 'Hollow Sigil Band',    desc: 'A wrist-worn band bearing the seal of the Hollow Collective. Acts as a key and identifier.' },
        { id: 'e5', name: 'Ember Canteen',        desc: 'A self-heating canteen that purifies any liquid and maintains temperature indefinitely.' },
        { id: 'e6', name: 'Mirrorglass Lens',     desc: 'A monocle-like lens that reveals reflected-dimension overlaps when worn over one eye.' },
    ],
};

const PICKER_META = {
    world:     { eyebrow: 'Linked Worlds',    title: 'Select Worlds'    },
    equipment: { eyebrow: 'Linked Equipment', title: 'Select Equipment' },
};

// ------------------------------------------------------------
// MODAL INIT — called once after pickerModal.php is injected
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
    if (!modal || !backdrop) return;

    let currentType     = null;
    let currentChipsEl  = null;
    let currentHiddenEl = null;
    let selectedItems   = [];
    let allItems        = [];

    // ── Open ──
    window.openPickerModal = function (type, chipsEl, hiddenEl) {
        currentType     = type;
        currentChipsEl  = chipsEl;
        currentHiddenEl = hiddenEl;
        allItems        = PICKER_DATA[type] || [];

        // Pre-populate from existing hidden value
        const existing = hiddenEl.value ? hiddenEl.value.split(',') : [];
        selectedItems  = allItems.filter(item => existing.includes(item.id));

        const meta = PICKER_META[type] || { eyebrow: type, title: type };
        eyebrow.textContent = meta.eyebrow;
        titleEl.textContent = meta.title;
        searchInput.value   = '';

        renderList(allItems);
        updateCount();

        backdrop.classList.add('open');
        modal.classList.add('open');
        searchInput.focus();
    };

    // ── Close ──
    function closeModal() {
        backdrop.classList.remove('open');
        modal.classList.remove('open');
        currentType = null;
    }

    // ── Render list ──
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

    // ── Update count ──
    function updateCount() {
        const n = selectedItems.length;
        countEl.textContent = n === 0 ? '0 selected' : `${n} selected`;
    }

    // ── Toggle item ──
    listEl.addEventListener('click', e => {
        const li = e.target.closest('.picker-list-item');
        if (!li) return;
        const id   = li.dataset.id;
        const name = li.dataset.name;
        const idx  = selectedItems.findIndex(s => s.id === id);

        if (idx > -1) {
            selectedItems.splice(idx, 1);
        } else {
            selectedItems.push({ id, name });
        }

        updateCount();
        const isNowSelected = selectedItems.some(s => s.id === id);
        li.classList.toggle('selected', isNowSelected);
        li.querySelector('.picker-item-check').textContent = isNowSelected ? '✓' : '';
    });

    // ── Search ──
    searchInput.addEventListener('input', () => {
        const q = searchInput.value.trim().toLowerCase();
        const filtered = q
            ? allItems.filter(i =>
                i.name.toLowerCase().includes(q) ||
                i.desc.toLowerCase().includes(q))
            : allItems;
        renderList(filtered);
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

        // Wire chip remove buttons
        currentChipsEl.querySelectorAll('.picker-chip-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.closest('.picker-chip')?.remove();
                const vals = currentHiddenEl.value
                    .split(',')
                    .filter(v => v !== btn.dataset.id);
                currentHiddenEl.value = vals.join(',');
            });
        });

        closeModal();
    });

    // ── Close triggers ──
    closeBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
    });
}

// ------------------------------------------------------------
// PICKER TRIGGER BINDING
// Called by initCreatePage() after every form fragment fetch.
// Binds .picker-trigger buttons to openPickerModal().
// ------------------------------------------------------------
function initPickerTriggers() {
    document.querySelectorAll('.picker-trigger').forEach(btn => {
        if (btn.dataset.pickerBound) return;   // prevent double-binding
        btn.dataset.pickerBound = 'true';

        btn.addEventListener('click', () => {
            const type     = btn.dataset.picker;
            const chipsEl  = document.getElementById(btn.dataset.targetChips);
            const hiddenEl = document.getElementById(btn.dataset.targetHidden);
            if (type && chipsEl && hiddenEl) {
                window.openPickerModal(type, chipsEl, hiddenEl);
            }
        });
    });
}