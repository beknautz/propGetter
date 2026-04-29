<?php /** @var array $lead @var array $analyses @var array|null $latest */ ?>

<div class="page-header">
    <div>
        <a href="/leads/<?= $lead['id'] ?>" class="text-muted small"><i class="bi bi-arrow-left"></i> Back to Lead</a>
        <h1 class="page-title mt-1"><i class="bi bi-robot me-2"></i>AI Deal Analysis</h1>
        <div class="text-muted"><?= View::e($lead['address'] . ', ' . $lead['city'] . ', ' . $lead['state']) ?></div>
    </div>
    <div>
        <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger"><?= View::e($_GET['error']) ?></div>
        <?php endif; ?>
        <form hx-post="/leads/<?= $lead['id'] ?>/analyze"
              hx-target="#analysisContainer"
              hx-swap="innerHTML">
            <?= Auth::csrfField() ?>
            <button class="btn btn-primary">
                <i class="bi bi-robot me-2"></i>
                <?= $latest ? 'Re-analyze' : 'Generate AI Analysis' ?>
                <span class="htmx-indicator ms-1"><i class="bi bi-arrow-clockwise spin"></i></span>
            </button>
        </form>
    </div>
</div>

<div id="analysisContainer">
    <?php if ($latest): ?>
        <?php include __DIR__ . '/partials/analysis_result.php'; ?>
    <?php else: ?>
    <div class="card text-center py-5 text-muted">
        <i class="bi bi-robot fs-1 d-block mb-3"></i>
        <p>No AI analysis yet. Click "Generate AI Analysis" to analyze this property.</p>
        <p class="small">The AI will generate an investment summary, offer range, seller letter, SMS script, and cold call script.</p>
    </div>
    <?php endif; ?>
</div>

<?php if (count($analyses) > 1): ?>
<div class="mt-3">
    <h6 class="fw-bold">Previous Analyses</h6>
    <div class="list-group">
        <?php foreach (array_slice($analyses, 1) as $a): ?>
        <div class="list-group-item">
            <small class="text-muted"><?= View::date($a['created_at'], 'M j, Y g:ia') ?> — <?= View::e($a['ai_provider']) ?></small>
            <p class="mb-0 small mt-1"><?= View::e(substr($a['summary'] ?? '', 0, 120)) ?>…</p>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
