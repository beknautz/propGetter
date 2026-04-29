<?php /** @var array $users */

function roleBadge(string $role): string {
    $map = ['admin' => 'danger', 'acquisitions' => 'primary', 'marketing' => 'warning', 'viewer' => 'secondary'];
    $c   = $map[$role] ?? 'secondary';
    return "<span class=\"badge bg-{$c}\">".ucfirst($role)."</span>";
}
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-people me-2 text-primary"></i>Users</h1>
    <a href="/users/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Add User
    </a>
</div>

<?= View::flashHtml() ?>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td class="fw-semibold"><?= View::e($u['name']) ?></td>
                    <td class="text-muted small"><?= View::e($u['email']) ?></td>
                    <td><?= roleBadge($u['role']) ?></td>
                    <td>
                        <?php if ($u['active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small">
                        <?= $u['last_login'] ? View::date($u['last_login'], 'M j, Y g:ia') : '—' ?>
                    </td>
                    <td class="text-muted small"><?= View::date($u['created_at'], 'M j, Y') ?></td>
                    <td class="text-end">
                        <a href="/users/<?= $u['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <?php if ((int)$u['id'] !== Auth::id()): ?>
                        <form method="POST" action="/users/<?= $u['id'] ?>/delete" class="d-inline"
                              onsubmit="return confirm('Delete <?= View::e(addslashes($u['name'])) ?>? This cannot be undone.')">
                            <?= Auth::csrfField() ?>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
