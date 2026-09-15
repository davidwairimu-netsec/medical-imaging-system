<?php
$pageTitle = 'Patient Management';
require APP_ROOT . '/views/layouts/main.php';
?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="patients">
            
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Name, hospital number, ID..." 
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">All</option>
                    <option value="Male" <?= $gender === 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $gender === 'Female' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
            <div class="col-md-2">
                <?php if (Auth::hasPermission('register_patient')): ?>
                <a href="<?= BASE_URL ?>/index.php?page=patients&action=create" class="btn btn-success w-100">
                    <i class="bi bi-plus-circle"></i> New Patient
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people"></i> Patients (<?= $total ?>)</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($patients)): ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>No patients found</h5>
                <p>Try adjusting your search criteria or register a new patient.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Hospital No.</th>
                            <th>Patient Name</th>
                            <th>DOB</th>
                            <th>Gender</th>
                            <th>Phone</th>
                            <th>Images</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $p): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($p['hospital_number']) ?></code></td>
                                <td>
                                    <strong><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></strong>
                                </td>
                                <td><?= date('d M Y', strtotime($p['date_of_birth'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $p['gender'] === 'Male' ? 'primary' : 'info' ?>">
                                        <?= htmlspecialchars($p['gender']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($p['phone'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?= $p['image_count'] ?? 0 ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $p['record_status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= htmlspecialchars($p['record_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/index.php?page=patients&action=view&id=<?= $p['patient_id'] ?>" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (Auth::hasPermission('edit_patient')): ?>
                                        <a href="<?= BASE_URL ?>/index.php?page=patients&action=edit&id=<?= $p['patient_id'] ?>" 
                                           class="btn btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/index.php?page=patients&action=history&id=<?= $p['patient_id'] ?>" 
                                           class="btn btn-outline-info" title="Imaging History">
                                            <i class="bi bi-images"></i>
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
                                    <a class="page-link" href="?page=patients&page_num=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&gender=<?= urlencode($gender) ?>">
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