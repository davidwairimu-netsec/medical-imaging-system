<?php
$pageTitle = 'Upload Medical Image';
require APP_ROOT . '/views/layouts/main.php';
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-cloud-upload"></i> Upload Medical Image</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($duplicateWarning)): ?>
                    <div class="alert alert-warning">
                        <h6><i class="bi bi-exclamation-triangle-fill"></i> Duplicate Image Detected</h6>
                        <p class="mb-2">This image already exists in the system:</p>
                        <ul class="mb-2">
                            <li><strong>Patient:</strong> <?= htmlspecialchars($duplicateWarning['patient_name']) ?></li>
                            <li><strong>Type:</strong> <?= htmlspecialchars($duplicateWarning['imaging_type']) ?></li>
                            <li><strong>Study Date:</strong> <?= date('d M Y', strtotime($duplicateWarning['study_date'])) ?></li>
                        </ul>
                        <a href="<?= BASE_URL ?>/index.php?page=imaging&action=view&id=<?= $duplicateWarning['image_id'] ?>" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> View Existing Record
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" novalidate id="uploadForm">
                    <?= CSRF::field() ?>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Select patient...</option>
                                <?php foreach ($patients as $p): ?>
                                    <option value="<?= $p['patient_id'] ?>" 
                                        <?= ($old['patient_id'] ?? $_GET['patient_id'] ?? '') == $p['patient_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['hospital_number'] . ' - ' . $p['first_name'] . ' ' . $p['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Imaging Type <span class="text-danger">*</span></label>
                            <select name="imaging_type" class="form-select" required>
                                <option value="">Select...</option>
                                <?php foreach (IMAGING_TYPES as $type): ?>
                                    <option value="<?= $type ?>" <?= ($old['imaging_type'] ?? '') === $type ? 'selected' : '' ?>>
                                        <?= $type ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Study Date <span class="text-danger">*</span></label>
                            <input type="date" name="study_date" class="form-control" required
                                   max="<?= date('Y-m-d') ?>"
                                   value="<?= htmlspecialchars($old['study_date'] ?? date('Y-m-d')) ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Body Part <span class="text-danger">*</span></label>
                            <input type="text" name="body_part" class="form-control" required
                                   placeholder="e.g., Chest, Head, Left Knee"
                                   value="<?= htmlspecialchars($old['body_part'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Referring Clinician</label>
                            <input type="text" name="referring_clinician" class="form-control"
                                   value="<?= htmlspecialchars($old['referring_clinician'] ?? '') ?>">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Clinical Notes</label>
                            <textarea name="clinical_notes" class="form-control" rows="3"><?= htmlspecialchars($old['clinical_notes'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Image File <span class="text-danger">*</span></label>
                            <input type="file" name="image_file" class="form-control" required
                                   accept=".jpg,.jpeg,.png,image/jpeg,image/png" id="imageFile">
                            <div class="form-text">
                                Allowed: JPEG, PNG. Maximum size: <?= MAX_FILE_SIZE / 1024 / 1024 ?> MB.
                                A SHA-256 hash is computed to detect duplicates.
                            </div>
                            <div id="filePreview" class="mt-3" style="display:none;">
                                <img id="previewImage" src="" alt="Preview" class="img-thumbnail" style="max-height:200px;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-cloud-upload"></i> Upload Image
                        </button>
                        <a href="<?= BASE_URL ?>/index.php?page=imaging" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-shield-check"></i> Duplicate Detection</h6>
            </div>
            <div class="card-body small">
                <p>When you upload an image, the system:</p>
                <ol class="mb-0">
                    <li>Validates file type and size</li>
                    <li>Calculates a SHA-256 hash</li>
                    <li>Checks for existing identical files</li>
                    <li>Warns you if a duplicate is found</li>
                    <li>Logs the attempt in the audit trail</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('imageFile')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('previewImage').src = ev.target.result;
            document.getElementById('filePreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('uploadForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Uploading...';
});
</script>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>