/**
 * Profile Photo Crop Modal
 * Uses Cropper.js for image cropping before upload.
 */
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('photo-file-input');
    const cropModal = document.getElementById('cropPhotoModal');
    const cropImage = document.getElementById('crop-image');
    const previewCircle = document.getElementById('crop-preview');
    const saveBtn = document.getElementById('crop-save-btn');
    const cancelBtn = document.getElementById('crop-cancel-btn');
    const triggerBtn = document.getElementById('change-photo-btn');
    const currentPhoto = document.getElementById('current-photo');

    if (!fileInput || !cropModal) return;

    let cropper = null;

    // Trigger file input
    triggerBtn?.addEventListener('click', function () {
        fileInput.click();
    });

    // File selected
    fileInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file type
        if (!file.type.match('image.*')) {
            alert('Hanya file gambar yang diizinkan (JPG, PNG, JPEG).');
            fileInput.value = '';
            return;
        }

        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB.');
            fileInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            cropImage.src = event.target.result;
            // Show modal
            cropModal.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Initialize cropper after image loads
            cropImage.onload = function () {
                if (cropper) {
                    cropper.destroy();
                }
                cropper = new Cropper(cropImage, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.8,
                    responsive: true,
                    zoomable: false,
                    zoomOnWheel: false,
                    crop: function (event) {
                        const canvas = cropper.getCroppedCanvas({
                            width: 300,
                            height: 300,
                        });
                        if (previewCircle && canvas) {
                            previewCircle.src = canvas.toDataURL('image/jpeg', 0.9);
                        }
                    },
                });
            };
        };
        reader.readAsDataURL(file);
    });

    // Save cropped image
    saveBtn?.addEventListener('click', function () {
        if (!cropper) return;

        const canvas = cropper.getCroppedCanvas({
            width: 300,
            height: 300,
        });

        canvas.toBlob(function (blob) {
            const file = new File([blob], 'profile-photo.jpg', {
                type: 'image/jpeg',
                lastModified: Date.now(),
            });

            // Create a DataTransfer to set the file input
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;

            // Update the current photo preview
            if (currentPhoto) {
                currentPhoto.src = URL.createObjectURL(blob);
            }

            // Close modal
            closeCropModal();
        }, 'image/jpeg', 0.9);
    });

    // Cancel
    cancelBtn?.addEventListener('click', function () {
        closeCropModal();
    });

    // Close on backdrop click
    cropModal.addEventListener('click', function (e) {
        if (e.target === cropModal) {
            closeCropModal();
        }
    });

    function closeCropModal() {
        cropModal.classList.remove('active');
        document.body.style.overflow = '';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        // Reset preview to current photo
        if (previewCircle && currentPhoto) {
            previewCircle.src = currentPhoto.src;
        }
    }
});
