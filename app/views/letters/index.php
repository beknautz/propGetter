<?php /** @var array $templates */ ?>
<div class="page-header">
    <h1 class="page-title"><i class="bi bi-file-earmark-text me-2 text-info"></i>Letter Templates</h1>
</div>
<div class="row g-3">
    <?php foreach ($templates as $t): ?>
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><?= View::e($t['name']) ?></h5>
                <span class="badge bg-secondary mb-2"><?= View::e($t['category'] ?? 'General') ?></span>
                <p class="text-muted small"><?= View::e(substr($t['body'], 0, 120)) ?>…</p>
            </div>
            <div class="card-footer bg-transparent">
                <small class="text-muted">Use this template when generating letters from a lead's detail page.</small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
