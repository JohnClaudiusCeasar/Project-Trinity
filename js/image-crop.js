// image-crop.js
// Handles image upload and cropping for forms

(function() {
    'use strict';

    let cropper = null;
    let currentEntityType = '';
    let currentPreviewId = '';
    let currentHiddenId = '';
    let currentRemoveBtnId = '';
    let currentWrapperId = '';

    const imageCropBackdrop = document.getElementById('imageCropBackdrop');
    const imageCropModal = document.getElementById('imageCropModal');
    const imageCropSource = document.getElementById('imageCropSource');
    const imageCropPreviewCanvas = document.getElementById('imageCropPreviewCanvas');
    const imageCropFileInput = document.getElementById('imageCropFileInput');

    function initImageUpload(entityType, previewId, hiddenId, removeBtnId, wrapperId) {
        currentEntityType = entityType;
        currentPreviewId = previewId;
        currentHiddenId = hiddenId;
        currentRemoveBtnId = removeBtnId;
        currentWrapperId = wrapperId;

        const wrapper = document.getElementById(wrapperId);
        const uploadBtn = wrapper ? wrapper.querySelector('.btn-upload') : null;
        const removeBtn = document.getElementById(removeBtnId);

        if (uploadBtn) {
            uploadBtn.addEventListener('click', function() {
                imageCropFileInput.click();
            });
        }

        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                removeImage();
            });
        }

        imageCropFileInput.addEventListener('change', handleFileSelect);
    }

    function handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            alert('Please select a valid image (JPG, PNG, or WebP)');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            imageCropSource.src = e.target.result;
            openCropModal();
        };
        reader.readAsDataURL(file);

        imageCropFileInput.value = '';
    }

    function openCropModal() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }

        imageCropBackdrop.classList.add('active');
        imageCropModal.classList.add('active');

        cropper = new Cropper(imageCropSource, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.9,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            preview: imageCropPreviewCanvas
        });

        updatePreview();
    }

    function closeCropModal() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }

        imageCropBackdrop.classList.remove('active');
        imageCropModal.classList.remove('active');
        imageCropSource.src = '';
    }

    function updatePreview() {
        if (!cropper) return;

        const canvas = cropper.getCroppedCanvas({
            width: 200,
            height: 200
        });

        if (canvas) {
            imageCropPreviewCanvas.width = 200;
            imageCropPreviewCanvas.height = 200;
            const ctx = imageCropPreviewCanvas.getContext('2d');
            ctx.drawImage(canvas, 0, 0);
        }
    }

    function applyCrop() {
        if (!cropper) return;

        const canvas = cropper.getCroppedCanvas({
            width: 500,
            height: 500,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        if (!canvas) {
            alert('Failed to crop image');
            return;
        }

        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);

        uploadImage(dataUrl);
    }

    function uploadImage(imageData) {
        const formData = new FormData();
        formData.append('entityType', currentEntityType);
        formData.append('image', imageData);

        // Get current image path to delete old one if replacing
        const hiddenInput = document.getElementById(currentHiddenId);
        const currentImagePath = hiddenInput ? hiddenInput.value : '';
        if (currentImagePath) {
            formData.append('oldImagePath', currentImagePath);
        }

        fetch('../api/image-upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const hiddenInput = document.getElementById(currentHiddenId);
                if (hiddenInput) {
                    hiddenInput.value = data.path;
                }

                const preview = document.getElementById(currentPreviewId);
                if (preview) {
                    preview.innerHTML = `<img src="${data.path}" alt="Preview">`;
                    preview.classList.add('has-image');
                }

                const removeBtn = document.getElementById(currentRemoveBtnId);
                if (removeBtn) {
                    removeBtn.style.display = 'inline-block';
                }

                closeCropModal();
            } else {
                alert('Failed to upload image: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error uploading image:', error);
            alert('Failed to upload image');
        });
    }

    async function removeImage() {
        const hiddenInput = document.getElementById(currentHiddenId);
        const currentImagePath = hiddenInput ? hiddenInput.value : '';

        // Delete image from server if exists
        if (currentImagePath) {
            try {
                const formData = new FormData();
                formData.append('imagePath', currentImagePath);

                await fetch('../api/delete-image.php', {
                    method: 'POST',
                    body: formData
                });
            } catch (err) {
                console.error('Error deleting image:', err);
            }
        }

        if (hiddenInput) {
            hiddenInput.value = '';
        }

        const preview = document.getElementById(currentPreviewId);
        if (preview) {
            preview.innerHTML = '<span class="image-preview-placeholder">No image</span>';
            preview.classList.remove('has-image');
        }

        const removeBtn = document.getElementById(currentRemoveBtnId);
        if (removeBtn) {
            removeBtn.style.display = 'none';
        }
    }

    document.getElementById('imageCropClose').addEventListener('click', closeCropModal);
    document.getElementById('imageCropCancel').addEventListener('click', closeCropModal);
    document.getElementById('imageCropApply').addEventListener('click', applyCrop);

    document.getElementById('imageCropZoomIn').addEventListener('click', function() {
        if (cropper) cropper.zoom(0.1);
    });

    document.getElementById('imageCropZoomOut').addEventListener('click', function() {
        if (cropper) cropper.zoom(-0.1);
    });

    document.getElementById('imageCropRotateLeft').addEventListener('click', function() {
        if (cropper) cropper.rotate(-90);
    });

    document.getElementById('imageCropRotateRight').addEventListener('click', function() {
        if (cropper) cropper.rotate(90);
    });

    document.getElementById('imageCropReset').addEventListener('click', function() {
        if (cropper) cropper.reset();
    });

    imageCropBackdrop.addEventListener('click', closeCropModal);

    window.initImageUpload = initImageUpload;
})();