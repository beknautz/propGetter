<?php /** @var array $campaign @var array $types @var array $statuses */ ?>
<div class="page-header">
    <div>
        <a href="/campaigns/<?= $campaign['id'] ?>" class="text-muted small"><i class="bi bi-arrow-left"></i> Back</a>
        <h1 class="page-title mt-1">Edit Campaign</h1>
    </div>
</div>
<div class="row justify-content-center"><div class="col-lg-6">
<div class="card">
    <div class="card-body">
        <form method="POST" action="/campaigns/<?= $campaign['id'] ?>/edit">
            <?= Auth::csrfField() ?>
            <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" value="<?= View::e($campaign['name']) ?>" required></div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= View::e($campaign['description'] ?? '') ?></textarea></div>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <?php foreach ($types as $t): ?><option value="<?= $t ?>" <?= $campaign['type'] === $t ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$t)) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach ($statuses as $s): ?><option value="<?= $s ?>" <?= $campaign['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6"><label class="form-label">Start Date</label><input type="date" name="start_date" class="form-control" value="<?= View::e($campaign['start_date'] ?? '') ?>"></div>
                <div class="col-6"><label class="form-label">End Date</label><input type="date" name="end_date" class="form-control" value="<?= View::e($campaign['end_date'] ?? '') ?>"></div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-floppy me-2"></i>Save</button>
                <a href="/campaigns/<?= $campaign['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
