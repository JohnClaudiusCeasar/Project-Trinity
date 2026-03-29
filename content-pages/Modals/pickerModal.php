<!-- Relational Picker Modal -->
<!-- Loaded once by dashboardLayout.php into #pickerModalContainer -->
<!-- Survives SPA navigation — triggered globally via openPickerModal() -->

<div class="picker-modal-backdrop" id="pickerModalBackdrop" aria-hidden="true"></div>
<div class="picker-modal" id="pickerModal" role="dialog" aria-modal="true" aria-labelledby="pickerModalTitle">

    <div class="picker-modal-header">
        <div>
            <div class="picker-modal-eyebrow" id="pickerModalEyebrow"></div>
            <h3 class="picker-modal-title" id="pickerModalTitle"></h3>
        </div>
        <button type="button" class="picker-modal-close" id="pickerModalClose" aria-label="Close">✕</button>
    </div>

    <!-- Search bar row — Filter + Sort icon buttons sit inside on the right -->
    <div class="picker-modal-search">
        <input type="text" class="picker-search-input" id="pickerSearchInput" placeholder="Search…" autocomplete="off">

        <div class="picker-search-controls">

            <!-- Filter Button + Dropdown -->
            <div class="picker-control-wrap" id="pickerFilterWrap">
                <button type="button" class="picker-control-btn" id="pickerFilterBtn" aria-label="Filter" title="Filter">
                    <!-- Filter / funnel icon -->
                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M1 2.5h13M3.5 7.5h8M6 12.5h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                    </svg>
                    <span class="picker-control-label">Filter</span>
                    <span class="picker-control-indicator" id="pickerFilterIndicator"></span>
                </button>
                <div class="picker-dropdown" id="pickerFilterDropdown" aria-hidden="true">
                    <div class="picker-dropdown-header">Filter by type</div>
                    <ul class="picker-dropdown-list" id="pickerFilterList"></ul>
                </div>
            </div>

            <!-- Sort Button + Dropdown -->
            <div class="picker-control-wrap" id="pickerSortWrap">
                <button type="button" class="picker-control-btn" id="pickerSortBtn" aria-label="Sort" title="Sort">
                    <!-- Sort / arrows icon -->
                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4 2v11M4 13l-2-2.5M4 13l2-2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter"/>
                        <path d="M11 13V2M11 2l-2 2.5M11 2l2 2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter"/>
                    </svg>
                    <span class="picker-control-label">Sort</span>
                    <span class="picker-control-indicator" id="pickerSortIndicator"></span>
                </button>
                <div class="picker-dropdown" id="pickerSortDropdown" aria-hidden="true">
                    <div class="picker-dropdown-header">Sort by</div>
                    <ul class="picker-dropdown-list" id="pickerSortList">
                        <li class="picker-dropdown-item" data-sort="az">
                            <span class="picker-dropdown-check">✓</span>A – Z
                        </li>
                        <li class="picker-dropdown-item" data-sort="za">
                            <span class="picker-dropdown-check"></span>Z – A
                        </li>
                        <li class="picker-dropdown-item picker-dropdown-item--stub" data-sort="recent" title="Available once entries are linked to dates">
                            <span class="picker-dropdown-check"></span>Recent Items
                            <span class="picker-dropdown-stub-tag">Soon</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

    <ul class="picker-modal-list" id="pickerModalList"></ul>

    <div class="picker-modal-footer">
        <span class="picker-selected-count" id="pickerSelectedCount">0 selected</span>
        <button type="button" class="btn" id="pickerConfirmBtn">Confirm</button>
    </div>

</div>