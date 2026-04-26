<!-- Image Crop Modal -->
<!-- Used for cropping images before upload in forms -->

<div class="image-crop-modal-backdrop" id="imageCropBackdrop" aria-hidden="true"></div>
<div class="image-crop-modal" id="imageCropModal" role="dialog" aria-modal="true" aria-labelledby="imageCropTitle">

    <div class="image-crop-modal-header">
        <h3 class="image-crop-modal-title" id="imageCropTitle">Crop Image</h3>
        <button type="button" class="image-crop-modal-close" id="imageCropClose" aria-label="Close">✕</button>
    </div>

    <div class="image-crop-modal-body">
        <div class="image-crop-container">
            <img id="imageCropSource" src="" alt="Image to crop" style="max-width: 100%;">
        </div>

        <div class="image-crop-preview-section">
            <div class="image-crop-preview-label">Preview</div>
            <div class="image-crop-preview-container">
                <canvas id="imageCropPreviewCanvas"></canvas>
            </div>
        </div>
    </div>

    <div class="image-crop-modal-controls">
        <div class="image-crop-toolbar">
            <button type="button" class="image-crop-tool" id="imageCropZoomOut" title="Zoom Out">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M12 12l4.5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                    <path d="M5.5 8h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                </svg>
            </button>
            <button type="button" class="image-crop-tool" id="imageCropZoomIn" title="Zoom In">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <circle cx="8" cy="8" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M12 12l4.5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                    <path d="M5.5 8h5M8 5.5v5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                </svg>
            </button>
            <button type="button" class="image-crop-tool" id="imageCropRotateLeft" title="Rotate Left">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M4 2v5a7 7 0 0014 0v-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                    <path d="M2 5l2-2 2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="square"/>
                </svg>
            </button>
            <button type="button" class="image-crop-tool" id="imageCropRotateRight" title="Rotate Right">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M14 2v5a7 7 0 01-14 0v-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                    <path d="M16 5l-2-2-2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="square"/>
                </svg>
            </button>
            <button type="button" class="image-crop-tool" id="imageCropReset" title="Reset">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M3 9a6 6 0 1011.5 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
                    <path d="M3 5v4h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="square"/>
                </svg>
            </button>
        </div>

        <div class="image-crop-aspect-label">Aspect Ratio: 1:1 (Square)</div>
    </div>

    <div class="image-crop-modal-footer">
        <button type="button" class="btn btn-secondary" id="imageCropCancel">Cancel</button>
        <button type="button" class="btn" id="imageCropApply">Apply Crop</button>
    </div>
</div>

<input type="file" id="imageCropFileInput" accept="image/jpeg,image/png,image/webp" style="display: none;">