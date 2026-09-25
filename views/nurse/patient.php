<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="<?= BASE_URL ?>/index.php?page=nurse" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to My Patients
    </a>
    <span class="badge bg-warning text-dark">
        <i class="bi bi-shield-lock"></i> Read-Only Access
    </span>
</div>

<div class="row g-3">
    <!-- Patient Info Card -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Patient Information</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="patient-avatar-lg">
                        <?= strtoupper(substr($patient['first_name'], 0, 1) . substr($patient['last_name'], 0, 1)) ?>
                    </div>
                    <h5 class="mt-2 mb-0">
                        <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['middle_name'] . ' ' . $patient['last_name']) ?>
                    </h5>
                    <code><?= htmlspecialchars($patient['hospital_number']) ?></code>
                </div>

                <dl class="row mb-0 small">
                    <dt class="col-5">Date of Birth</dt>
                    <dd class="col-7"><?= date('d M Y', strtotime($patient['date_of_birth'])) ?></dd>

                    <dt class="col-5">Age</dt>
                    <dd class="col-7"><?= (new DateTime($patient['date_of_birth']))->diff(new DateTime())->y ?> years</dd>

                    <dt class="col-5">Gender</dt>
                    <dd class="col-7"><?= htmlspecialchars($patient['gender']) ?></dd>

                    <dt class="col-5">Phone</dt>
                    <dd class="col-7"><?= htmlspecialchars($patient['phone'] ?? '-') ?></dd>

                    <dt class="col-5">Emergency</dt>
                    <dd class="col-7"><?= htmlspecialchars($patient['emergency_contact'] ?? '-') ?></dd>
                </dl>
            </div>
        </div>

        <?php if ($currentAssignment): ?>
        <div class="card mt-3">
            <div class="card-header"><h6 class="mb-0"><i class="bi bi-clipboard-check"></i> Assignment</h6></div>
            <div class="card-body small">
                <p class="mb-1"><strong>Primary Nurse:</strong></p>
                <p class="mb-2">
                    <?= htmlspecialchars($currentAssignment['nurse_first'] . ' ' . $currentAssignment['nurse_last']) ?><br>
                    <small class="text-muted"><?= htmlspecialchars($currentAssignment['staff_number']) ?></small><br>
                    <small><?= htmlspecialchars($currentAssignment['nurse_phone'] ?? '') ?></small>
                </p>
                <p class="mb-1"><strong>Priority:</strong>
                    <span class="badge bg-<?= $currentAssignment['priority'] === 'critical' ? 'danger' : ($currentAssignment['priority'] === 'high' ? 'warning' : 'secondary') ?>">
                        <?= htmlspecialchars($currentAssignment['priority']) ?>
                    </span>
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Medications & Imaging -->
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-capsule"></i> Current Medications</h5>
                <span class="badge bg-secondary"><?= count($medications) ?> active</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($medications)): ?>
                    <div class="empty-state small">
                        <p class="mb-0">No active medications on record.</p>
                    </div>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Medication</th>
                                <th>Dosage</th>
                                <th>Frequency</th>
                                <th>Route</th>
                                <th>Start</th>
                                <th>Prescriber</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($medications as $m): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($m['medication_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($m['dosage']) ?></td>
                                    <td><?= htmlspecialchars($m['frequency']) ?></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($m['route']) ?></span></td>
                                    <td><small><?= date('d M Y', strtotime($m['start_date'])) ?></small></td>
                                    <td><small><?= htmlspecialchars($m['prescriber_name'] ?? '-') ?></small></td>
                                </tr>
                                <?php if (!empty($m['notes'])): ?>
                                    <tr>
                                        <td colspan="6" class="bg-light">
                                            <small><strong>Notes:</strong> <?= htmlspecialchars($m['notes']) ?></small>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-images"></i> Recent Imaging Records</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($images)): ?>
                    <div class="empty-state small">
                        <p class="mb-0">No imaging records on file.</p>
                    </div>
                <?php else: ?>
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Study Date</th>
                                <th>Body Part</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($images as $img): ?>
                                <tr>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($img['imaging_type']) ?></span></td>
                                    <td><?= date('d M Y', strtotime($img['study_date'])) ?></td>
                                    <td><?= htmlspecialchars($img['body_part']) ?></td>
                                    <td><small><?= htmlspecialchars(substr($img['clinical_notes'] ?? '', 0, 80)) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="alert alert-info small mt-3">
            <i class="bi bi-shield-lock"></i>
            <strong>Read-only mode:</strong> Nurses can view patient data but cannot add, modify, or delete records.
            Contact the Head Nurse for any changes.
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
