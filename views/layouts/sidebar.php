<?php
if (Auth::hasPermission('approve_deletion')) {
    try {
        $__db = Database::getInstance();
        $pendingDeletionCount = (int) $__db->query("SELECT COUNT(*) FROM deletion_requests WHERE request_status = 'pending'")->fetchColumn();
    } catch (Exception $e) {
        $pendingDeletionCount = 0;
    }
} else {
    $pendingDeletionCount = 0;
}
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-heart-pulse-fill"></i>
        <span><?= APP_NAME ?></span>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= BASE_URL ?>/index.php?page=dashboard"
           class="nav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <?php if (Auth::hasPermission('view_patient')): ?>
        <a href="<?= BASE_URL ?>/index.php?page=patients"
           class="nav-link <?= ($currentPage ?? '') === 'patients' ? 'active' : '' ?>">
            <i class="bi bi-people"></i>
            <span>Patients</span>
        </a>
        <?php endif; ?>

        <?php if (Auth::hasPermission('view_image')): ?>
        <a href="<?= BASE_URL ?>/index.php?page=imaging"
           class="nav-link <?= ($currentPage ?? '') === 'imaging' ? 'active' : '' ?>">
            <i class="bi bi-images"></i>
            <span>Medical Images</span>
        </a>
        <?php endif; ?>

        <?php if (Auth::hasPermission('upload_image')): ?>
        <a href="<?= BASE_URL ?>/index.php?page=imaging&action=upload"
           class="nav-link <?= ($currentPage ?? '') === 'imaging' && ($currentAction ?? '') === 'upload' ? 'active' : '' ?>">
            <i class="bi bi-cloud-upload"></i>
            <span>Upload Image</span>
        </a>
        <?php endif; ?>

        <?php if (Auth::hasPermission('manage_nurses')): ?>
        <div class="sidebar-divider">Nursing</div>

        <a href="<?= BASE_URL ?>/index.php?page=head_nurse"
           class="nav-link <?= ($currentPage ?? '') === 'head_nurse' && in_array($currentAction ?? '', ['', 'index'], true) ? 'active' : '' ?>">
            <i class="bi bi-clipboard-pulse"></i>
            <span>Nursing Dashboard</span>
        </a>

        <a href="<?= BASE_URL ?>/index.php?page=head_nurse&action=nurses"
           class="nav-link <?= ($currentPage ?? '') === 'head_nurse' && in_array($currentAction ?? '', ['nurses', 'createNurse', 'editNurse'], true) ? 'active' : '' ?>">
            <i class="bi bi-person-badge"></i>
            <span>Manage Nurses</span>
        </a>

        <a href="<?= BASE_URL ?>/index.php?page=head_nurse&action=assignments"
           class="nav-link <?= ($currentPage ?? '') === 'head_nurse' && ($currentAction ?? '') === 'assignments' ? 'active' : '' ?>">
            <i class="bi bi-diagram-3"></i>
            <span>Assignments</span>
        </a>

        <a href="<?= BASE_URL ?>/index.php?page=head_nurse&action=workload"
           class="nav-link <?= ($currentPage ?? '') === 'head_nurse' && ($currentAction ?? '') === 'workload' ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-line"></i>
            <span>Workload</span>
        </a>

        <a href="<?= BASE_URL ?>/index.php?page=head_nurse&action=shifts"
           class="nav-link <?= ($currentPage ?? '') === 'head_nurse' && ($currentAction ?? '') === 'shifts' ? 'active' : '' ?>">
            <i class="bi bi-calendar-week"></i>
            <span>Shifts</span>
        </a>

        <a href="<?= BASE_URL ?>/index.php?page=head_nurse&action=reports"
           class="nav-link <?= ($currentPage ?? '') === 'head_nurse' && ($currentAction ?? '') === 'reports' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-bar-graph"></i>
            <span>Nursing Reports</span>
        </a>
        <?php endif; ?>

        <div class="sidebar-divider">Account</div>

        <a href="<?= BASE_URL ?>/index.php?page=profile&action=changePassword"
           class="nav-link <?= ($currentPage ?? '') === 'profile' ? 'active' : '' ?>">
            <i class="bi bi-key"></i>
            <span>Change Password</span>
        </a>

        <?php if (Auth::isRole(ROLE_ADMIN)): ?>
        <div class="sidebar-divider">Administration</div>

        <a href="<?= BASE_URL ?>/index.php?page=imaging&action=deletionRequests"
           class="nav-link d-flex justify-content-between align-items-center <?= ($currentPage ?? '') === 'imaging' && ($currentAction ?? '') === 'deletionRequests' ? 'active' : '' ?>">
            <span>
                <i class="bi bi-hourglass-split"></i>
                <span>Deletion Requests</span>
            </span>
            <?php if (!empty($pendingDeletionCount) && $pendingDeletionCount > 0): ?>
                <span class="badge bg-danger"><?= $pendingDeletionCount ?></span>
            <?php endif; ?>
        </a>

        <a href="<?= BASE_URL ?>/index.php?page=imaging&action=recentlyDeleted"
           class="nav-link <?= ($currentPage ?? '') === 'imaging' && ($currentAction ?? '') === 'recentlyDeleted' ? 'active' : '' ?>">
            <i class="bi bi-archive"></i>
            <span>Recently Deleted</span>
        </a>

        <a href="<?= BASE_URL ?>/index.php?page=imaging&action=archived"
           class="nav-link <?= ($currentPage ?? '') === 'imaging' && ($currentAction ?? '') === 'archived' ? 'active' : '' ?>">
            <i class="bi bi-archive-fill"></i>
            <span>Archived Images</span>
        </a>

        <a href="<?= BASE_URL ?>/index.php?page=users"
           class="nav-link <?= ($currentPage ?? '') === 'users' ? 'active' : '' ?>">
            <i class="bi bi-person-gear"></i>
            <span>User Management</span>
        </a>

        <a href="<?= BASE_URL ?>/index.php?page=audit"
           class="nav-link <?= ($currentPage ?? '') === 'audit' && ($currentAction ?? '') !== 'integrity' ? 'active' : '' ?>">
            <i class="bi bi-journal-text"></i>
            <span>Audit Logs</span>
        </a>

        <a href="<?= BASE_URL ?>/index.php?page=audit&action=integrity"
           class="nav-link <?= ($currentPage ?? '') === 'audit' && ($currentAction ?? '') === 'integrity' ? 'active' : '' ?>">
            <i class="bi bi-shield-check"></i>
            <span>Storage Integrity</span>
        </a>

        <a href="<?= BASE_URL ?>/index.php?page=audit&action=verifyChain"
           class="nav-link <?= ($currentPage ?? '') === 'audit' && ($currentAction ?? '') === 'verifyChain' ? 'active' : '' ?>">
            <i class="bi bi-link-45deg"></i>
            <span>Audit Chain</span>
        </a>

        <a href="<?= BASE_URL ?>/index.php?page=backups"
           class="nav-link <?= ($currentPage ?? '') === 'backups' ? 'active' : '' ?>">
            <i class="bi bi-database-fill-gear"></i>
            <span>Backups</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr(Auth::user()['full_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="user-details">
                <span class="user-name"><?= htmlspecialchars(Auth::user()['full_name'] ?? 'User') ?></span>
                <span class="user-role"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', Auth::role() ?? ''))) ?></span>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline-light btn-sm w-100 mt-2">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</aside>
