<?php
$pageTitle = 'User Management';
require APP_ROOT . '/views/layouts/main.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-person-gear"></i> System Users</h5>
        <a href="<?= BASE_URL ?>/index.php?page=users&action=create" class="btn btn-sm btn-success">
            <i class="bi bi-plus-circle"></i> Create User
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>#<?= $u['user_id'] ?></td>
                            <td><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($u['role_name'])) ?></span></td>
                            <td>
                                <span class="badge bg-<?= $u['account_status'] === 'active' ? 'success' : ($u['account_status'] === 'locked' ? 'danger' : 'warning') ?>">
                                    <?= htmlspecialchars($u['account_status']) ?>
                                </span>
                            </td>
                            <td><small><?= $u['last_login'] ? date('d M Y H:i', strtotime($u['last_login'])) : 'Never' ?></small></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/index.php?page=users&action=edit&id=<?= $u['user_id'] ?>" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($u['user_id'] !== Auth::id()): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/index.php?page=users&action=toggleStatus" class="d-inline" onsubmit="return confirm('Change status for <?= htmlspecialchars($u['username']) ?>?');">
                                        <?= CSRF::field() ?>
                                        <input type="hidden" name="id" value="<?= $u['user_id'] ?>">
                                        <button type="submit" class="btn btn-outline-<?= $u['account_status'] === 'active' ? 'warning' : 'success' ?>">
                                            <i class="bi bi-<?= $u['account_status'] === 'active' ? 'pause-circle' : 'play-circle' ?>"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
