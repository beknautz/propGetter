<?php /** @var array $history */ ?>
<div class="page-header">
    <h1 class="page-title"><i class="bi bi-clock-history me-2"></i>Import History</h1>
    <a href="/import" class="btn btn-primary btn-sm"><i class="bi bi-upload me-1"></i>New Import</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>File</th><th>Source</th><th>Total</th><th>Imported</th><th>Updated</th><th>Skipped</th><th>Errors</th><th>Status</th><th>User</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php foreach ($history as $j): ?>
                <tr>
                    <td class="small"><?= View::e($j['original_name']) ?></td>
                    <td><span class="badge bg-secondary"><?= View::e($j['source_type']) ?></span></td>
                    <td><?= $j['total_rows'] ?></td>
                    <td class="text-success"><?= $j['imported_rows'] ?></td>
                    <td class="text-info"><?= $j['updated_rows'] ?></td>
                    <td class="text-warning"><?= $j['skipped_rows'] ?></td>
                    <td class="text-danger"><?= $j['error_rows'] ?></td>
                    <td><?php $sc=['completed'=>'success','processing'=>'warning','pending'=>'secondary','failed'=>'danger']; ?><span class="badge bg-<?= $sc[$j['status']]??'secondary' ?>"><?= $j['status'] ?></span></td>
                    <td class="small"><?= View::e($j['user_name'] ?? '—') ?></td>
                    <td class="small"><?= View::date($j['created_at'], 'M j, Y g:ia') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$history): ?><tr><td colspan="10" class="text-center text-muted py-4">No imports yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
