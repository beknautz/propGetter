<?php /** @var array $results */ ?>
<?php $rows = $results['rows'] ?? []; ?>
<?php if (empty($rows)): ?>
    <div class="qs-item text-muted">No results found.</div>
<?php else: ?>
    <?php foreach ($rows as $lead): ?>
    <a href="/leads/<?= $lead['id'] ?>" class="qs-item d-block text-decoration-none text-dark">
        <div class="fw-semibold"><?= View::e($lead['address']) ?></div>
        <div class="text-muted small d-flex gap-2">
            <span><?= View::e($lead['city'] . ', ' . $lead['state']) ?></span>
            <?= View::scoreBadge((int)$lead['lead_score']) ?>
            <?= View::statusBadge($lead['status']) ?>
        </div>
    </a>
    <?php endforeach; ?>
<?php endif; ?>
