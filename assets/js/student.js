/* ──────────────────────────────────────────
   STUDENT PAGES - JAVASCRIPT
   ────────────────────────────────────────── */

/**
 * Handle profile image upload
 * Validates, uploads, and updates preview in real-time
 */
function handleProfileImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        showUploadStatus('❌ Format invalide (JPEG, PNG, GIF, WebP uniquement)', 'error');
        return;
    }
    
    // Validate file size (5MB max)
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        showUploadStatus('❌ Fichier trop volumineux (5MB max)', 'error');
        return;
    }
    
    // Show loading status
    showUploadStatus('⏳ Téléchargement...', 'loading');
    
    // Prepare form data
    const formData = new FormData();
    formData.append('profile_image', file);
    
    // Send upload request
    fetch('upload_profile_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update profile image in dashboard
            const container = document.getElementById('profile-image-container');
            if (container) {
                container.innerHTML = `<img src="${data.image_url}?t=${Date.now()}" alt="Profil" style="width: 100%; height: 100%; object-fit: cover;">`;
            }
            
            // Update sidebar profile image if it exists
            const sidebarAvatar = document.querySelector('.sidebar-avatar');
            if (sidebarAvatar) {
                const img = sidebarAvatar.querySelector('img');
                if (img) {
                    img.src = data.image_url + '?t=' + Date.now();
                } else {
                    sidebarAvatar.innerHTML = `<img src="${data.image_url}" alt="Profil" style="width: 100%; height: 100%; object-fit: cover;">`;
                }
            }
            
            showUploadStatus('✅ Photo mise à jour!', 'success');
            
            // Clear file input
            event.target.value = '';
        } else {
            showUploadStatus(`❌ ${data.message}`, 'error');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        showUploadStatus('❌ Erreur lors de l\'upload', 'error');
    });
}

/**
 * Display upload status message
 */
function showUploadStatus(message, type) {
    const statusEl = document.getElementById('upload-status');
    if (!statusEl) return;
    
    statusEl.textContent = message;
    statusEl.style.display = 'block';
    statusEl.className = 'upload-' + type;
    
    // Auto-hide success messages after 3 seconds
    if (type === 'success') {
        setTimeout(() => {
            statusEl.style.display = 'none';
        }, 3000);
    }
}

/**
 * Initialize event listeners for student pages
 */
document.addEventListener('DOMContentLoaded', function() {
    // Profile image upload
    const profileInput = document.getElementById('profile-image-input');
    if (profileInput) {
        profileInput.addEventListener('change', handleProfileImageUpload);
    }
});
