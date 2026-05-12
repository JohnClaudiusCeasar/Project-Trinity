<!-- View/Edit Entry Modal -->
<!-- Displays full entry information with edit mode support -->
<div class="modal-backdrop" id="viewModalBackdrop" aria-hidden="true"></div>
<div class="modal view-modal" id="viewModal" role="dialog" aria-modal="true">
    <div class="modal-header">
        <span class="modal-logo">✦</span>
        <h3 id="viewModalTitle">Entry Details</h3>
        <span class="entry-type-badge" id="viewModalType"></span>
        <button type="button" class="modal-close" id="viewModalClose" aria-label="Close">✕</button>
    </div>
    <div class="modal-body view-modal-body" id="viewModalBody">
        <div class="loading-state">Loading...</div>
    </div>
    <div class="edit-form-container" id="editFormContainer" style="display: none;">
        <div class="edit-form-wrapper" id="editFormWrapper"></div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="viewModalExportPdfBtn" style="display: none;">Export PDF</button>
        <button type="button" class="btn btn-primary" id="viewModalSaveBtn" style="display: none;">Save Changes</button>
        <button type="button" class="btn btn-secondary" id="viewModalCloseBtn">Close</button>
    </div>
</div>

<style>
.modal-header {
    display: flex;
    align-items: center;
    gap: 10px;
}
.modal-logo {
    font-size: 24px;
    color: var(--primary-color, #4a90d9);
    line-height: 1;
}
.edit-form-container {
    max-height: 70vh;
    overflow-y: auto;
    padding: 20px;
}
.edit-form-wrapper {
    max-width: 800px;
    margin: 0 auto;
}

@media print {
    .modal-backdrop, .modal-header, .modal-footer, .edit-form-container,
    .modal-close, .relation-chip, .entry-action-btn, .view-toggle,
    .filter-container, .dashboard-hero, .sidebar, .navbar {
        display: none !important;
    }
    .view-modal {
        position: static !important;
        transform: none !important;
        width: 100% !important;
        max-height: none !important;
        box-shadow: none !important;
        border: none !important;
        overflow: visible !important;
    }
    .view-modal-body {
        overflow: visible !important;
        padding: 0 !important;
    }
    .view-entry-layout {
        grid-template-columns: 1fr !important;
    }
    .view-entry-image {
        width: 120px !important;
        height: 120px !important;
    }
    .view-section {
        break-inside: avoid;
        page-break-inside: avoid;
    }
    body { background: white !important; }
}
</style>

<script>
function renderViewModalContent(entry, type) {
    const body = document.getElementById('viewModalBody');
    const title = document.getElementById('viewModalTitle');
    const typeBadge = document.getElementById('viewModalType');

    title.textContent = entry.name || entry.title || 'Untitled';
    typeBadge.textContent = type.charAt(0).toUpperCase() + type.slice(1);
    typeBadge.className = 'entry-type-badge type-' + type;

    const imageHtml = entry.image
        ? `<div class="view-entry-image"><img src="${escapeHtml(entry.image)}" alt="${escapeHtml(entry.name || entry.title)}"></div>`
        : `<div class="view-entry-image view-entry-image-placeholder">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
            </svg>
        </div>`;

    let detailsHtml = '';

    // =====================
    // CHARACTER TEMPLATE
    // =====================
    if (type === 'character') {
        detailsHtml = `
            <div class="view-section">
                <h4 class="view-section-title">Basic Information</h4>
                <div class="view-field"><span class="view-label">Name:</span> ${escapeHtml(entry.name || '')}</div>
                ${entry.type_name ? `<div class="view-field"><span class="view-label">Type:</span> ${escapeHtml(entry.type_name)}</div>` : ''}
                ${entry.nickname ? `<div class="view-field"><span class="view-label">Nickname:</span> ${escapeHtml(entry.nickname)}</div>` : ''}
                ${entry.age ? `<div class="view-field"><span class="view-label">Age:</span> ${escapeHtml(entry.age)}</div>` : ''}
                ${entry.gender ? `<div class="view-field"><span class="view-label">Gender:</span> ${formatGender(entry.gender)}</div>` : ''}
                ${entry.faction ? `<div class="view-field"><span class="view-label">Faction:</span> ${formatFaction(entry.faction)}</div>` : ''}
            </div>
            ${entry.appearance ? `
            <div class="view-section">
                <h4 class="view-section-title">Appearance</h4>
                <p class="view-description">${escapeHtml(entry.appearance)}</p>
            </div>` : ''}
            ${entry.abilities ? `
            <div class="view-section">
                <h4 class="view-section-title">Abilities / Powers</h4>
                <p class="view-description">${escapeHtml(entry.abilities)}</p>
            </div>` : ''}
            ${entry.bio ? `
            <div class="view-section">
                <h4 class="view-section-title">Biography</h4>
                <p class="view-description">${escapeHtml(entry.bio)}</p>
            </div>` : ''}
            ${renderRelatedEntities('worlds', entry.worlds, 'World')}
            ${renderRelatedEntities('equipment', entry.equipment, 'Equipment')}
            ${renderTags(entry.tags)}
            ${renderMetaDate(entry.created_at)}
        `;
    }

    // =====================
    // WORLD TEMPLATE
    // =====================
    else if (type === 'world') {
        detailsHtml = `
            <div class="view-section">
                <h4 class="view-section-title">Basic Information</h4>
                <div class="view-field"><span class="view-label">Name:</span> ${escapeHtml(entry.name || '')}</div>
                ${entry.type_name ? `<div class="view-field"><span class="view-label">Type:</span> ${escapeHtml(entry.type_name)}</div>` : ''}
                ${entry.description ? `<div class="view-field"><span class="view-label">Description:</span> ${escapeHtml(entry.description)}</div>` : ''}
            </div>
            ${entry.location ? `
            <div class="view-section">
                <h4 class="view-section-title">Location</h4>
                <p class="view-description">${escapeHtml(entry.location)}</p>
            </div>` : ''}
            ${entry.era ? `
            <div class="view-section">
                <h4 class="view-section-title">Era / Time Period</h4>
                <p class="view-description">${escapeHtml(entry.era)}</p>
            </div>` : ''}
            ${entry.rules ? `
            <div class="view-section">
                <h4 class="view-section-title">Rules / Laws</h4>
                <p class="view-description">${escapeHtml(entry.rules)}</p>
            </div>` : ''}
            ${entry.history ? `
            <div class="view-section">
                <h4 class="view-section-title">History</h4>
                <p class="view-description">${escapeHtml(entry.history)}</p>
            </div>` : ''}
            ${renderRelatedEntities('characters', entry.characters, 'Character')}
            ${renderRelatedEntities('equipment', entry.equipment, 'Equipment')}
            ${renderTags(entry.tags)}
            ${renderMetaDate(entry.created_at)}
        `;
    }

    // =====================
    // EQUIPMENT TEMPLATE
    // =====================
    else if (type === 'equipment') {
        detailsHtml = `
            <div class="view-section">
                <h4 class="view-section-title">Basic Information</h4>
                <div class="view-field"><span class="view-label">Name:</span> ${escapeHtml(entry.name || '')}</div>
                ${entry.type_name ? `<div class="view-field"><span class="view-label">Type:</span> ${escapeHtml(entry.type_name)}</div>` : ''}
                ${entry.age ? `<div class="view-field"><span class="view-label">Age:</span> ${escapeHtml(entry.age)}</div>` : ''}
                ${entry.status ? `<div class="view-field"><span class="view-label">Status:</span> ${formatStatus(entry.status)}</div>` : ''}
            </div>
            ${entry.description ? `
            <div class="view-section">
                <h4 class="view-section-title">Description</h4>
                <p class="view-description">${escapeHtml(entry.description)}</p>
            </div>` : ''}
            ${entry.history ? `
            <div class="view-section">
                <h4 class="view-section-title">History</h4>
                <p class="view-description">${escapeHtml(entry.history)}</p>
            </div>` : ''}
            ${renderRelatedEntities('worlds', entry.worlds, 'World')}
            ${entry.current_owner ? `
            <div class="view-section">
                <h4 class="view-section-title">Current Owner</h4>
                <div class="view-relations">
                    <button class="relation-chip" data-id="${entry.current_owner.id}" data-type="character">
                        ${escapeHtml(entry.current_owner.name)}
                    </button>
                </div>
            </div>` : ''}
            ${renderRelatedEntities('previous_owners', entry.previous_owners, 'Character', 'Previous Owners')}
            ${renderTags(entry.tags)}
            ${renderMetaDate(entry.created_at)}
        `;
    }

    // =====================
    // FACTION TEMPLATE
    // =====================
    else if (type === 'faction') {
        const typesList = entry.types && Array.isArray(entry.types) && entry.types.length
            ? entry.types.map(t => t.name).join(', ')
            : '';

        detailsHtml = `
            <div class="view-section">
                <h4 class="view-section-title">Basic Information</h4>
                <div class="view-field"><span class="view-label">Name:</span> ${escapeHtml(entry.name || '')}</div>
                ${typesList ? `<div class="view-field"><span class="view-label">Types:</span> ${escapeHtml(typesList)}</div>` : ''}
                ${entry.description ? `<div class="view-field"><span class="view-label">Description:</span> ${escapeHtml(entry.description)}</div>` : ''}
            </div>
            ${entry.economic_status ? `
            <div class="view-section">
                <h4 class="view-section-title">Economic Status</h4>
                <p class="view-description">${escapeHtml(entry.economic_status)}</p>
            </div>` : ''}
            ${entry.social_status ? `
            <div class="view-section">
                <h4 class="view-section-title">Social Status</h4>
                <p class="view-description">${escapeHtml(entry.social_status)}</p>
            </div>` : ''}
            ${entry.history ? `
            <div class="view-section">
                <h4 class="view-section-title">Historical Origins</h4>
                <p class="view-description">${escapeHtml(entry.history)}</p>
            </div>` : ''}
            ${renderRelatedEntities('worlds', entry.worlds, 'Location')}
            ${renderRelatedEntities('founders', entry.founders, 'Founder')}
            ${entry.primary_leader ? `
            <div class="view-section">
                <h4 class="view-section-title">Primary Leader</h4>
                <div class="view-relations">
                    <button class="relation-chip" data-id="${entry.primary_leader.id}" data-type="character">
                        ${escapeHtml(entry.primary_leader.name)}
                    </button>
                </div>
            </div>` : ''}
            ${entry.secondary_leader ? `
            <div class="view-section">
                <h4 class="view-section-title">Secondary Leader</h4>
                <div class="view-relations">
                    <button class="relation-chip" data-id="${entry.secondary_leader.id}" data-type="character">
                        ${escapeHtml(entry.secondary_leader.name)}
                    </button>
                </div>
            </div>` : ''}
            ${renderRelatedEntities('members', entry.members, 'Member')}
            ${entry.sacred_treasure ? `
            <div class="view-section">
                <h4 class="view-section-title">Sacred Treasure</h4>
                <div class="view-relations">
                    <button class="relation-chip" data-id="${entry.sacred_treasure.id}" data-type="equipment">
                        ${escapeHtml(entry.sacred_treasure.name)}
                    </button>
                </div>
            </div>` : ''}
            ${entry.secret_treasure ? `
            <div class="view-section">
                <h4 class="view-section-title">Secret / Forbidden Treasure</h4>
                <div class="view-relations">
                    <button class="relation-chip" data-id="${entry.secret_treasure.id}" data-type="equipment">
                        ${escapeHtml(entry.secret_treasure.name)}
                    </button>
                </div>
            </div>` : ''}
            ${renderRelatedEntities('other_treasures', entry.other_treasures, 'Equipment', 'Other Treasures')}
            ${renderMetaDate(entry.created_at)}
        `;
    }

    // =====================
    // STORY TEMPLATE
    // =====================
    else if (type === 'story') {
        const wordCount = entry.word_count ? parseInt(entry.word_count) : 0;
        const formattedWordCount = wordCount >= 1000 ? (wordCount / 1000).toFixed(1) + 'k' : wordCount;
        
        detailsHtml = `
            <div class="view-section">
                <h4 class="view-section-title">Basic Information</h4>
                <div class="view-field"><span class="view-label">Title:</span> ${escapeHtml(entry.title || '')}</div>
                ${entry.genre ? `<div class="view-field"><span class="view-label">Genre:</span> ${escapeHtml(entry.genre)}</div>` : ''}
                ${wordCount ? `<div class="view-field"><span class="view-label">Word Count:</span> ${formattedWordCount} words</div>` : ''}
            </div>
            ${entry.logline ? `
            <div class="view-section">
                <h4 class="view-section-title">Logline</h4>
                <p class="view-description">${escapeHtml(entry.logline)}</p>
            </div>` : ''}
            ${entry.synopsis ? `
            <div class="view-section">
                <h4 class="view-section-title">Synopsis</h4>
                <p class="view-description">${escapeHtml(entry.synopsis)}</p>
            </div>` : ''}
            ${entry.notes ? `
            <div class="view-section">
                <h4 class="view-section-title">Notes</h4>
                <p class="view-description">${escapeHtml(entry.notes)}</p>
            </div>` : ''}
            ${renderRelatedEntities('characters', entry.characters, 'Character')}
            ${renderRelatedEntities('worlds', entry.worlds, 'World')}
            ${renderRelatedEntities('equipment', entry.equipment, 'Equipment')}
            ${renderMetaDate(entry.created_at)}
        `;
    }

    body.innerHTML = `
        <div class="view-entry-layout">
            <div class="view-entry-primary">
                ${imageHtml}
            </div>
            <div class="view-entry-details">
                ${detailsHtml}
            </div>
        </div>
    `;

    // Attach click handlers for relation chips
    body.querySelectorAll('.relation-chip').forEach(chip => {
        chip.addEventListener('click', (e) => {
            const relId = chip.dataset.id;
            const relType = chip.dataset.type;
            if (relId && relType) {
                openViewModal(relId, relType);
            }
        });
    });
}

function renderRelatedEntities(key, items, singularLabel, customTitle = null) {
    if (!items || !Array.isArray(items) || items.length === 0) return '';
    
    const title = customTitle || singularLabel + (items.length > 1 ? 's' : '');
    const chips = items.map(item => `
        <button class="relation-chip" data-id="${item.id}" data-type="${key === 'characters' || key === 'founders' || key === 'members' ? 'character' : (key === 'worlds' ? 'world' : 'equipment')}">
            ${escapeHtml(item.name)}
        </button>
    `).join('');

    return `
        <div class="view-section">
            <h4 class="view-section-title">${title}</h4>
            <div class="view-relations">${chips}</div>
        </div>
    `;
}

function renderTags(tags) {
    if (!tags) return '';
    const tagList = tags.split(',').map(t => t.trim()).filter(t => t);
    if (tagList.length === 0) return '';
    
    const tagChips = tagList.map(tag => `<span class="tag-chip-view">${escapeHtml(tag)}</span>`).join('');
    return `
        <div class="view-section">
            <h4 class="view-section-title">Tags</h4>
            <div class="view-tags">${tagChips}</div>
        </div>
    `;
}

function renderMetaDate(createdAt) {
    if (!createdAt) return '';
    const date = new Date(createdAt);
    const formatted = date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    return `
        <div class="view-section view-meta">
            <span class="view-meta-date">Created: ${formatted}</span>
        </div>
    `;
}

function formatGender(gender) {
    const map = {
        'male': 'Male',
        'female': 'Female',
        'non-binary': 'Non-binary',
        'genderfluid': 'Genderfluid',
        'agender': 'Agender',
        'unknown': 'Unknown',
        'other': 'Other'
    };
    return map[gender] || gender;
}

function formatFaction(faction) {
    const map = {
        'the-veil-accord': 'The Veil Accord',
        'order-of-the-ashen-mark': 'Order of the Ashen Mark',
        'freelance-independent': 'Freelance / Independent',
        'the-hollow-collective': 'The Hollow Collective',
        'unaffiliated': 'Unaffiliated'
    };
    return map[faction] || faction;
}

function formatStatus(status) {
    const map = {
        'active': 'Currently in Circulation',
        'inactive': 'Retired from Service',
        'unused': 'Awaiting Discovery',
        'destroyed': 'Lost to Time'
    };
    return map[status] || status;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function exportViewPdf() {
    const element = document.getElementById('viewModalBody');
    const titleEl = document.getElementById('viewModalTitle');
    if (!element || !titleEl) return;

    const title = titleEl.textContent || 'entry';
    const filename = title.replace(/[^a-zA-Z0-9]/g, '_') + '.pdf';

    const opt = {
        margin: [0.4, 0.4, 0.4, 0.4],
        filename: filename,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, letterRendering: true },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save();
}
</script>