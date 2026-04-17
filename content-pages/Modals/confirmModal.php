<!-- Confirm Modal -->
<!-- Reusable confirmation dialog for delete and other confirmations -->
<div class="modal-backdrop" id="confirmModalBackdrop" aria-hidden="true"></div>
<div class="modal confirm-modal" id="confirmModal" role="dialog" aria-modal="true">
    <div class="modal-header">
        <h3 id="confirmModalTitle">Confirm Action</h3>
        <button type="button" class="modal-close" id="confirmModalClose" aria-label="Close">✕</button>
    </div>
    <div class="modal-body">
        <p id="confirmModalMessage">Are you sure?</p>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="confirmModalCancel">Cancel</button>
        <button type="button" class="btn" id="confirmModalConfirm">Confirm</button>
    </div>
</div>
