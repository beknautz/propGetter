<?php /** @var array $user @var array $errors */
$isSelf = (int)$user['id'] === Auth::id();
?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-person-gear me-2 text-primary"></i>Edit User</h1>
    <a href="/users" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
<div class="col-lg-6">
<div class="card">
    <div class="card-body">
        <form method="POST" action="/users/<?= $user['id'] ?>/edit">
            <?= Auth::csrfField() ?>

            <div class="mb-3">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                       value="<?= View::e($user['name']) ?>" required>
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= View::e($errors['name']) ?></div><?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                       value="<?= View::e($user['email']) ?>" required>
                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= View::e($errors['email']) ?></div><?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">New Password <span class="text-muted fw-normal">(leave blank to keep current)</span></label>
                <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                       placeholder="Minimum 8 characters">
                <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= View::e($errors['password']) ?></div><?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <select name="role" class="form-select" <?= $isSelf ? 'disabled' : '' ?>>
                    <?php foreach (['viewer' => 'Viewer', 'marketing' => 'Marketing', 'acquisitions' => 'Acquisitions', 'admin' => 'Admin'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $user['role'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($isSelf): ?>
                <input type="hidden" name="role" value="admin">
                <div class="form-text text-warning"><i class="bi bi-lock me-1"></i>You cannot change your own role.</div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                           <?= $user['active'] ? 'checked' : '' ?> <?= $isSelf ? 'disabled' : '' ?>>
                    <label class="form-check-label" for="active">Active (can log in)</label>
                </div>
                <?php if ($isSelf): ?>
                <input type="hidden" name="active" value="1">
                <?php endif; ?>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/users" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<div class="card mt-3 border-secondary">
    <div class="card-body">
        <div class="text-muted small">
            <div><strong>Created:</strong> <?= View::date($user['created_at'], 'M j, Y g:ia') ?></div>
            <div><strong>Last Login:</strong> <?= $user['last_login'] ? View::date($user['last_login'], 'M j, Y g:ia') : 'Never' ?></div>
        </div>
    </div>
</div>

</div>
</div>
