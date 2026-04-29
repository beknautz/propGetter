<?php /** @var array $notes @var int $leadId */ ?>
<?php if (empty($notes)): ?>
    <p class="text-muted text-center py-3">No notes yet.</p>
<?php else: ?>
<div class="timeline">
    <?php foreach ($notes as $note): ?>
    <div class="timeline-item <?= View::e($note['note_type']) ?>">
        <div class="d-flex justify-content-between timeline-meta mb-1">
            <span>
                <strong><?= View::e($note['user_name'] ?? 'System') ?></strong>
                <span class="badge bg-secondary ms-1"><?= View::e($note['note_type']) ?></span>
            </span>
            <span><?= View::date($note['created_at'], 'M j, Y g:ia') ?></span>
        </div>
        <div class="timeline-body"><?= nl2br(View::e($note['note'])) ?></div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
