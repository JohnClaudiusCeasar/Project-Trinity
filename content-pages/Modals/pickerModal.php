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

    <div class="picker-modal-search">
        <input type="text" class="picker-search-input" id="pickerSearchInput" placeholder="Search…" autocomplete="off">
    </div>

    <ul class="picker-modal-list" id="pickerModalList"></ul>

    <div class="picker-modal-footer">
        <span class="picker-selected-count" id="pickerSelectedCount">0 selected</span>
        <button type="button" class="btn" id="pickerConfirmBtn">Confirm</button>
    </div>

</div>