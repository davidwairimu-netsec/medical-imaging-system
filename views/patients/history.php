<?php
$pageTitle = 'Patient Imaging History';
require APP_ROOT . '/views/layouts/main.php';
?>

<div class="card mb-3">
    <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="bi bi-person-badge"></i> <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h5></div>
    <div class="card-body">
        <div class="row small">
            <div class="col-md-3"><strong>Hospital No:</strong> <?= htmlspecialchars($patient['hospital_number']) ?></div>
            <div class="col-md-3"><strong>DOB:</strong> <?= date('d M Y', strtotime($patient['date_of_birth'])) ?></div>
            <div class="col-md-3"><strong>Gender:</strong> <?= htmlspecialchars($patient['gender']) ?></div>
            <div class="col-md-3"><strong>Phone:</strong> <?= htmlspecialchars($patient['phone'] ?? '-') ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Imaging History (<?= count($patient['images']) ?>)</h5>
        <a href="<?= BASE_URL ?>/index.php?page=patients&action=view&id=<?= $patient['patient_id'] ?>" class="btn btn-sm btn-outline-secondary">Back to Patient</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($patient['images'])): ?>
            <div class="empty-state"><i class="bi bi-inbox"></i><h5>No imaging records</h5></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>ID</th><th>Type</th><th>Study Date</th><th>Body Part</th><th>Uploaded</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($patient['images'] as $img): ?>
                            <tr>
                                <td>#<?= $img['image_id'] ?></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($img['imaging_type']) ?></span></td>
                                <td><?= date('d M Y', strtotime($img['study_date'])) ?></td>
                                <td><?= htmlspecialchars($img['body_part']) ?></td>
                                <td><small><?= date('d M Y H:i', strtotime($img['uploaded_at'])) ?></small></td>
                                <td><a href="<?= BASE_URL ?>/index.php?page=imaging&action=view&id=<?= $img['image_id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
