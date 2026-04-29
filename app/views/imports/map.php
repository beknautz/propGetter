<?php /** @var int $jobId @var array $headers @var array $preview @var int $total @var string $preset @var array $fieldMap @var array $dbFields */ ?>

<div class="page-header">
    <div>
        <a href="/import" class="text-muted small"><i class="bi bi-arrow-left"></i> Back to Import</a>
        <h1 class="page-title mt-1">Map Import Fields</h1>
    </div>
    <span class="badge bg-primary fs-6"><?= number_format($total) ?> rows to import</span>
</div>

<?php if (!empty($preset) && $preset !== 'custom'): ?>
<div class="alert alert-info">
    <i class="bi bi-magic me-2"></i>
    <strong><?= ucfirst($preset) ?> detected.</strong> Column mapping has been auto-configured. Review and confirm below.
</div>
<?php endif; ?>

<!-- Preview table -->
<?php if (!empty($preview)): ?>
<div class="card mb-3">
    <div class="card-header"><i class="bi bi-table me-2"></i>Data Preview (first 5 rows)</div>
    <div class="table-responsive" style="max-height:200px;overflow-y:auto;">
        <table class="table table-sm table-bordered mb-0" style="font-size:.75rem;">
            <thead class="table-dark">
                <tr><?php foreach ($headers as $h): ?><th><?= View::e($h) ?></th><?php endforeach; ?></tr>
            </thead>
            <tbody>
                <?php foreach ($preview as $row): ?>
                <tr><?php foreach ($headers as $h): ?><td><?= View::e(substr($row[$h] ?? '', 0, 30)) ?></td><?php endforeach; ?></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Field mapping form -->
<form method="POST" action="/import/<?= $jobId ?>/run">
    <?= Auth::csrfField() ?>

    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-link-45deg me-2"></i>Column Mapping</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>CSV Column</th>
                        <th>Sample Data</th>
                        <th>Maps To</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($headers as $col): ?>
                    <tr>
                        <td class="fw-semibold small"><?= View::e($col) ?></td>
                        <td class="text-muted small"><?= View::e(substr($preview[0][$col] ?? '', 0, 50)) ?></td>
                        <td>
                            <select name="map_<?= urlencode($col) ?>" class="form-select form-select-sm">
                                <?php foreach ($dbFields as $dbField => $dbLabel): ?>
                                <option value="<?= View::e($dbField) ?>"
                                        <?= ($fieldMap[$col] ?? '_skip') === $dbField ? 'selected' : '' ?>>
                                    <?= View::e($dbLabel) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-gear me-2"></i>Import Options</div>
        <div class="card-body">
            <label class="form-label">When duplicate found (same address + ZIP or APN):</label>
            <div class="d-flex gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="duplicate_action" value="skip" id="dup_skip" checked>
                    <label class="form-check-label" for="dup_skip">Skip duplicates</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="duplicate_action" value="update" id="dup_update">
                    <label class="form-check-label" for="dup_update">Update existing records</label>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success px-4">
            <i class="bi bi-play-fill me-2"></i>Run Import (<?= number_format($total) ?> rows)
        </button>
        <a href="/import" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
