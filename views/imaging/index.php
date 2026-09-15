<?php
$pageTitle = 'Medical Imaging Records';
require APP_ROOT . '/views/layouts/main.php';
?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="imaging">
            
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Patient name, hospital no., body part..." 
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="imaging_type" class="form-select">
                    <option value="">All</option>
                    <?php foreach (IMAGING_TYPES as $type): ?>
                        <option value="<?= $type ?>" <?= $imagingType === $type ? 'selected' : '' ?>><?= $type ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-images"></i> Imaging Records (<?= $total ?>)</h5>
        <?php if (Auth::hasPermission('upload_image')): ?>
        <a href="<?= BASE_URL ?>/index.php?page=imaging&action=upload" class="btn btn-sm btn-success">
            <i class="bi bi-cloud-upload"></i> Upload New
        </a>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <?php if (empty($images)): ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>No imaging records found</h5>
                <p>Try adjusting your filters or upload a new image.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Type</th>
                            <th>Study Date</th>
                            <th>Body Part</th>
                            <th>Uploaded</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($images as $img): ?>
                            <tr>
                                <td>#<?= $img['image_id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($img['patient_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($img['hospital_number']) ?></small>
                                </td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($img['imaging_type']) ?></span></td>
                                <td><?= date('d M Y', strtotime($img['study_date'])) ?></td>
                                <td><?= htmlspecialchars($img['body_part']) ?></td>
                                <td>
                                    <small><?= date('d M Y H:i', strtotime($img['uploaded_at'])) ?></small><br>
                                    <small class="text-muted"><?= htmlspecialchars($img['uploaded_by_name'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $img['record_status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= htmlspecialchars($img['record_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/index.php?page=imaging&action=view&id=<?= $img['image_id'] ?>" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/index.php?page=imaging&action=download&id=<?= $img['image_id'] ?>" 
                                           class="btn btn-outline-success" title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/index.php?page=patients&action=view&id=<?= $img['patient_id'] ?>" 
                                           class="btn btn-outline-info" title="Patient">
                                            <i class="bi bi-person"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <div class="card-footer">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=imaging&page_num=<?= $i ?>&search=<?= urlencode($search) ?>&imaging_type=<?= urlencode($imagingType) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>