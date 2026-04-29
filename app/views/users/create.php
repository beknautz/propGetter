<?php /** @var array $errors @var array $old */ ?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-person-plus me-2 text-primary"></i>Add User</h1>
    <a href="/users" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
<div class="col-lg-6">
<div class="card">
    <div class="card-body">
        <form method="POST" action="/users/create">
            <?= Auth::csrfField() ?>

            <div class="mb-3">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                       value="<?= View::e($old['name'] ?? '') ?>" required>
                <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= View::e($errors['name']) ?></div><?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                       value="<?= View::e($old['email'] ?? '') ?>" required>
                <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= View::e($errors['email']) ?></div><?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                       placeholder="Minimum 8 characters" required>
                <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= View::e($errors['password']) ?></div><?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <select name="role" class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>">
                    <?php foreach (['viewer' => 'Viewer', 'marketing' => 'Marketing', 'acquisitions' => 'Acquisitions', 'admin' => 'Admin'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($old['role'] ?? 'viewer') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['role'])): ?><div class="invalid-feedback"><?= View::e($errors['role']) ?></div><?php endif; ?>
                <div class="form-text">
                    <strong>Viewer</strong> — read only &nbsp;|&nbsp;
                    <strong>Marketing</strong> — campaigns &amp; letters &nbsp;|&nbsp;
                    <strong>Acquisitions</strong> — full lead access &nbsp;|&nbsp;
                    <strong>Admin</strong> — everything
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="active" id="active" value="1"
                           <?= !isset($old) || !empty($old['active']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="active">Active (can log in)</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create User</button>
                <a href="/users" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
