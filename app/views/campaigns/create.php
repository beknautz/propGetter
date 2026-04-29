<?php /** @var array $types @var array|null $errors @var array|null $old */ ?>
<div class="page-header">
    <div>
        <a href="/campaigns" class="text-muted small"><i class="bi bi-arrow-left"></i> Back</a>
        <h1 class="page-title mt-1">Create Campaign</h1>
    </div>
</div>
<div class="row justify-content-center">
<div class="col-lg-6">
<div class="card">
    <div class="card-body">
        <form method="POST" action="/campaigns/create">
            <?= Auth::csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Campaign Name *</label>
                <input type="text" name="name" class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>"
                       value="<?= View::e($old['name'] ?? '') ?>" required>
                <?php if (!empty($errors['name'])): ?><div class="invalid-feedback"><?= View::e($errors['name'][0]) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= View::e($old['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Campaign Type *</label>
                <select name="type" class="form-select">
                    <?php foreach ($types as $t): ?>
                    <option value="<?= $t ?>" <?= ($old['type'] ?? 'mixed') === $t ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$t)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= View::e($old['start_date'] ?? '') ?>">
                </div>
                <div class="col-6">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= View::e($old['end_date'] ?? '') ?>">
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Create Campaign</button>
                <a href="/campaigns" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
