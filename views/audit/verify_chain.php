<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-journal-text"></i></div>
            <div class="stat-content">
                <span class="stat-label">Total Log Entries</span>
                <span class="stat-value"><?= $result['total'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">Verified</span>
                <span class="stat-value"><?= $result['verified'] ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-<?= $result['valid'] ? 'success' : 'danger' ?>">
            <div class="stat-icon"><i class="bi bi-shield-<?= $result['valid'] ? 'check' : 'exclamation' ?>"></i></div>
            <div class="stat-content">
                <span class="stat-label">Chain Status</span>
                <span class="stat-value"><?= $result['valid'] ? 'INTACT' : 'BROKEN' ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-content">
                <span class="stat-label">Broken Rows</span>
                <span class="stat-value"><?= count($result['broken']) ?></span>
            </div>
        </div>
    </div>
</div>

<?php if ($result['valid']): ?>
    <div class="alert alert-success">
        <h5><i class="bi bi-shield-check"></i> Audit Log Integrity Verified</h5>
        <p class="mb-0">
            All <?= $result['total'] ?> audit log entries form a valid cryptographic chain.
            No tampering detected. Every entry is linked to the previous one via SHA-256 hash.
        </p>
    </div>
<?php else: ?>
    <div class="alert alert-danger">
        <h5><i class="bi bi-x-octagon-fill"></i> Audit Log Integrity Compromised</h5>
        <p class="mb-0">
            <?= count($result['broken']) ?> row(s) do not match the expected cryptographic chain.
            <strong>Note:</strong> Historical rows created before the hash chain was implemented
            will show as broken because they were backfilled with zero hashes.
            New entries from this point forward will chain correctly.
        </p>
    </div>

    <?php if (count($result['broken']) <= 10): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bug"></i> Broken Rows (<?= count($result['broken']) ?>)</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>Issue</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['broken'] as $b): ?>
                            <tr>
                                <td><strong>#<?= $b['log_id'] ?></strong></td>
                                <td><span class="badge bg-warning"><?= htmlspecialchars($b['issue']) ?></span></td>
                                <td><small><code><?= htmlspecialchars(json_encode($b)) ?></code></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Showing first 10 of <?= count($result['broken']) ?> broken rows. 
            These are historical entries created before the hash chain was implemented.
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="card mt-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-info-circle"></i> How the Audit Chain Works</h5>
    </div>
    <div class="card-body">
        <p>Each audit log entry stores two hashes:</p>
        <ul>
            <li><strong>previous_hash</strong> — the hash of the immediately preceding entry</li>
            <li><strong>row_hash</strong> — SHA-256 of (previous_hash + this row's data)</li>
        </ul>
        <p class="mb-0">
            Any modification to a row's data would change its <code>row_hash</code>, breaking the chain for all subsequent rows.
            This makes tampering detectable.
        </p>
    </div>
</div>

<a href="<?= BASE_URL ?>/index.php?page=audit" class="btn btn-outline-secondary mt-3">
    <i class="bi bi-arrow-left"></i> Back to Audit Logs
</a>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
