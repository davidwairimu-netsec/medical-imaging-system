<?php
$pageTitle = 'Patient Details';
require APP_ROOT . '/views/layouts/main.php';
?>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Patient Information</h5>
            </div>
            <div class="card-body">
                <div class="patient-profile">
                    <div class="patient-avatar-lg">
                        <?= strtoupper(substr($patient['first_name'], 0, 1) . substr($patient['last_name'], 0, 1)) ?>
                    </div>
                    <h4><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['middle_name'] . ' ' . $patient['last_name']) ?></h4>
                    <code class="fs-6"><?= htmlspecialchars($patient['hospital_number']) ?></code>
                </div>
                
                <hr>
                
                <dl class="row mb-0 small">
                    <dt class="col-5">Date of Birth</dt>
                    <dd class="col-7"><?= date('d M Y', strtotime($patient['date_of_birth'])) ?></dd>
                    
                    <dt class="col-5">Gender</dt>
                    <dd class="col-7"><?= htmlspecialchars($patient['gender']) ?></dd>
                    
                    <dt class="col-5">National ID</dt>
                    <dd class="col-7"><?= htmlspecialchars($patient['national_id'] ?? '-') ?></dd>
                    
                    <dt class="col-5">Phone</dt>
                    <dd class="col-7"><?= htmlspecialchars($patient['phone'] ?? '-') ?></dd>
                    
                    <dt class="col-5">Address</dt>
                    <dd class="col-7"><?= htmlspecialchars($patient['address'] ?? '-') ?></dd>
                    
                    <dt class="col-5">Emergency</dt>
                    <dd class="col-7"><?= htmlspecialchars($patient['emergency_contact'] ?? '-') ?></dd>
                    
                    <dt class="col-5">Status</dt>
                    <dd class="col-7">
                        <span class="badge bg-<?= $patient['record_status'] === 'active' ? 'success' : 'secondary' ?>">
                            <?= htmlspecialchars($patient['record_status']) ?>
                        </span>
                    </dd>
                    
                    <dt class="col-5">Registered</dt>
                    <dd class="col-7"><?= date('d M Y', strtotime($patient['created_at'])) ?></dd>
                </dl>
            </div>
            <div class="card-footer d-flex gap-2">
                <?php if (Auth::hasPermission('edit_patient')): ?>
                <a href="<?= BASE_URL ?>/index.php?page=patients&action=edit&id=<?= $patient['patient_id'] ?>" 
                   class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/index.php?page=patients&action=history&id=<?= $patient['patient_id'] ?>" 
                   class="btn btn-sm btn-outline-info">
                    <i class="bi bi-images"></i> Imaging History
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-images"></i> Imaging Records (<?= count($patient['images']) ?>)</h5>
                <?php if (Auth::hasPermission('upload_image')): ?>
                <a href="<?= BASE_URL ?>/index.php?page=imaging&action=upload&patient_id=<?= $patient['patient_id'] ?>" 
                   class="btn btn-sm btn-success">
                    <i class="bi bi-cloud-upload"></i> Upload Image
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($patient['images'])): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <h5>No imaging records</h5>
                        <p>This patient has no medical images on file.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Study Date</th>
                                    <th>Body Part</th>
                                    <th>Uploaded By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($patient['images'] as $img): ?>
                                    <tr>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($img['imaging_type']) ?></span></td>
                                        <td><?= date('d M Y', strtotime($img['study_date'])) ?></td>
                                        <td><?= htmlspecialchars($img['body_part']) ?></td>
                                        <td><?= htmlspecialchars($img['uploaded_by_name'] ?? '-') ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/index.php?page=imaging&action=view&id=<?= $img['image_id'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>