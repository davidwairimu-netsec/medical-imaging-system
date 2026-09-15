<?php
$pageTitle = 'View Medical Image';
require APP_ROOT . '/views/layouts/main.php';
?>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-image"></i> Image Viewer</h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary" onclick="zoomIn()" title="Zoom In"><i class="bi bi-zoom-in"></i></button>
                    <button class="btn btn-outline-secondary" onclick="zoomOut()" title="Zoom Out"><i class="bi bi-zoom-out"></i></button>
                    <button class="btn btn-outline-secondary" onclick="resetView()" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></button>
                    <button class="btn btn-outline-secondary" onclick="rotate()" title="Rotate"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
            </div>
            <div class="card-body text-center viewer-container">
                <img id="viewerImage" 
                     src="<?= BASE_URL ?>/index.php?page=imaging&action=serve&id=<?= $image['image_id'] ?>" 
                     alt="Medical Image"
                     class="viewer-image">
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="<?= BASE_URL ?>/index.php?page=imaging&action=download&id=<?= $image['image_id'] ?>" class="btn btn-success btn-sm">
                    <i class="bi bi-download"></i> Download Original
                </a>
                <?php if (Auth::hasPermission('archive_image')): ?>
                <form method="POST" action="<?= BASE_URL ?>/index.php?page=imaging&action=archive" class="d-inline" onsubmit="return confirm('Archive this image record?');">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="id" value="<?= $image['image_id'] ?>">
                    <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-archive"></i> Archive</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-info-circle"></i> Image Metadata</h6></div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-5">Image ID</dt><dd class="col-7">#<?= $image['image_id'] ?></dd>
                    <dt class="col-5">Patient</dt>
                    <dd class="col-7"><a href="<?= BASE_URL ?>/index.php?page=patients&action=view&id=<?= $image['patient_id'] ?>"><?= htmlspecialchars($image['patient_name']) ?></a></dd>
                    <dt class="col-5">Hospital No.</dt><dd class="col-7"><code><?= htmlspecialchars($image['hospital_number']) ?></code></dd>
                    <dt class="col-5">Type</dt><dd class="col-7"><span class="badge bg-primary"><?= htmlspecialchars($image['imaging_type']) ?></span></dd>
                    <dt class="col-5">Body Part</dt><dd class="col-7"><?= htmlspecialchars($image['body_part']) ?></dd>
                    <dt class="col-5">Study Date</dt><dd class="col-7"><?= date('d M Y', strtotime($image['study_date'])) ?></dd>
                    <dt class="col-5">Clinician</dt><dd class="col-7"><?= htmlspecialchars($image['referring_clinician'] ?? '-') ?></dd>
                    <dt class="col-5">Uploaded</dt><dd class="col-7"><?= date('d M Y H:i', strtotime($image['uploaded_at'])) ?></dd>
                    <dt class="col-5">Uploaded By</dt><dd class="col-7"><?= htmlspecialchars($image['uploaded_by_name'] ?? '-') ?></dd>
                    <dt class="col-5">File Size</dt><dd class="col-7"><?= number_format($image['file_size'] / 1024, 1) ?> KB</dd>
                    <dt class="col-5">Original Name</dt><dd class="col-7"><small><?= htmlspecialchars($image['original_file_name']) ?></small></dd>
                    <dt class="col-5">SHA-256</dt><dd class="col-7"><small class="text-break"><?= htmlspecialchars(substr($image['file_hash'], 0, 16)) ?>...</small></dd>
                    <dt class="col-5">Status</dt>
                    <dd class="col-7"><span class="badge bg-<?= $image['record_status'] === 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($image['record_status']) ?></span></dd>
                </dl>
            </div>
        </div>
        
        <?php if (!empty($image['clinical_notes'])): ?>
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-file-medical"></i> Clinical Notes</h6></div>
            <div class="card-body small"><?= nl2br(htmlspecialchars($image['clinical_notes'])) ?></div>
        </div>
        <?php endif; ?>
        
        <div class="alert alert-info small mt-3">
            <i class="bi bi-info-circle"></i> <strong>Academic Prototype:</strong> This viewer is for demonstration only.
        </div>
    </div>
</div>

<script>
let zoom = 1, rotation = 0;
const img = document.getElementById('viewerImage');
function updateTransform() { img.style.transform = `scale(${zoom}) rotate(${rotation}deg)`; }
function zoomIn() { zoom = Math.min(zoom + 0.2, 5); updateTransform(); }
function zoomOut() { zoom = Math.max(zoom - 0.2, 0.2); updateTransform(); }
function resetView() { zoom = 1; rotation = 0; updateTransform(); }
function rotate() { rotation = (rotation + 90) % 360; updateTransform(); }
</script>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
