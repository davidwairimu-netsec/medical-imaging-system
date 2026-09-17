<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-database"></i></div>
            <div class="stat-content">
                <span class="stat-label">DB Records (active)</span>
                <span class="stat-value"><?= $stats['active_db'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-info">
            <div class="stat-icon"><i class="bi bi-files"></i></div>
            <div class="stat-content">
                <span class="stat-label">Files on Disk</span>
                <span class="stat-value"><?= $stats['disk_files'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="bi bi-hdd"></i></div>
            <div class="stat-content">
                <span class="stat-label">Disk Usage</span>
                <span class="stat-value"><?= $stats['disk_human'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card <?= $stats['is_consistent'] ? 'stat-card-success' : 'stat-card-warning' ?>">
            <div class="stat-icon"><i class="bi bi-shield-check"></i></div>
            <div class="stat-content">
                <span class="stat-label">Consistency</span>
                <span class="stat-value"><?= $stats['is_consistent'] ? 'OK' : 'MISMATCH' ?></span>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($report['missing'])): ?>
<div class="alert alert-danger">
    <h5><i class="bi bi-x-octagon-fill"></i> <?= count($report['missing']) ?> Missing Files</h5>
    <p class="mb-0">These DB records point to files that do NOT exist on disk.</p>
</div>
<div class="card mb-4">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Image ID</th><th>Patient ID</th><th>Path</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($report['missing'] as $img): ?>
                    <tr>
                        <td><strong>#<?= $img['image_id'] ?></strong></td>
                        <td><?= $img['patient_id'] ?></td>
                        <td><code class="small"><?= htmlspecialchars($img['file_path']) ?></code></td>
                        <td>
                            <form method="POST" action="<?= BASE_URL ?>/index.php?page=audit&action=fixIntegrity" class="d-inline">
                                <?= CSRF::field() ?>
                                <input type="hidden" name="action" value="archive_broken">
                                <input type="hidden" name="image_id" value="<?= $img['image_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Archive?');">
                                    <i class="bi bi-archive"></i> Archive
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($report['empty'])): ?>
<div class="alert alert-warning">
    <h5><i class="bi bi-exclamation-triangle-fill"></i> <?= count($report['empty']) ?> Empty Files</h5>
</div>
<div class="card mb-4">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Image ID</th><th>File</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($report['empty'] as $img): ?>
                    <tr>
                        <td><strong>#<?= $img['image_id'] ?></strong></td>
                        <td><code><?= htmlspecialchars($img['file_path']) ?></code></td>
                        <td>
                            <form method="POST" action="<?= BASE_URL ?>/index.php?page=audit&action=fixIntegrity" class="d-inline">
                                <?= CSRF::field() ?>
                                <input type="hidden" name="action" value="archive_broken">
                                <input type="hidden" name="image_id" value="<?= $img['image_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-warning"><i class="bi bi-archive"></i> Archive</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($report['orphaned'])): ?>
<div class="alert alert-info">
    <h5><i class="bi bi-info-circle-fill"></i> <?= count($report['orphaned']) ?> Orphaned Files</h5>
</div>
<div class="card mb-4">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>File</th><th>Size</th><th>Modified</th></tr></thead>
            <tbody>
                <?php foreach ($report['orphaned'] as $f): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($f['file_name']) ?></code></td>
                        <td><?= number_format($f['file_size']) ?> bytes</td>
                        <td><?= $f['modified'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (empty($report['missing']) && empty($report['empty']) && empty($report['orphaned'])): ?>
<div class="alert alert-success">
    <h5><i class="bi bi-check-circle-fill"></i> Storage is Consistent</h5>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h5 class="mb-0"><i class="bi bi-shield-check"></i> Verified Files (<?= count($report['ok']) ?>)</h5></div>
    <div class="card-body p-0">
        <?php if (empty($report['ok'])): ?>
            <div class="empty-state"><p>No verified files</p></div>
        <?php else: ?>
            <table class="table table-sm mb-0">
                <thead><tr><th>Image ID</th><th>File</th><th>Size</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($report['ok'] as $img): ?>
                        <tr>
                            <td>#<?= $img['image_id'] ?></td>
                            <td><code class="small"><?= htmlspecialchars($img['file_name']) ?></code></td>
                            <td><?= number_format($img['_integrity']['actual_size']) ?> bytes</td>
                            <td><span class="badge bg-success"><?= $img['_integrity']['status'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
