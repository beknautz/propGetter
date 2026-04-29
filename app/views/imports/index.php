<?php /** @var array $history @var string|null $error */ ?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-upload me-2 text-primary"></i>Import Leads</h1>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= View::e($error) ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Upload CSV File</div>
            <div class="card-body">
                <form method="POST" action="/import/upload" enctype="multipart/form-data">
                    <?= Auth::csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label">Data Source</label>
                        <select name="source_type" class="form-select">
                            <option value="custom">Custom / Generic CSV</option>
                            <option value="propstream">PropStream Export</option>
                            <option value="regrid">Regrid Export</option>
                            <option value="assessor">County Assessor Export</option>
                            <option value="batchleads">BatchLeads Export</option>
                        </select>
                        <div class="form-text">Selecting your source helps auto-map columns.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">CSV File *</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                        <div class="form-text">Max 10 MB. CSV format only.</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-upload me-2"></i>Upload & Preview
                    </button>
                </form>

                <hr>
                <h6 class="fw-bold">Supported Formats</h6>
                <ul class="small text-muted">
                    <li>PropStream (standard export)</li>
                    <li>BatchLeads CSV export</li>
                    <li>Regrid parcel export</li>
                    <li>County assessor data</li>
                    <li>Any custom CSV with headers</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history me-2"></i>Recent Imports</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>File</th>
                            <th>Source</th>
                            <th>Imported</th>
                            <th>Updated</th>
                            <th>Skipped</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No imports yet.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($history as $job): ?>
                        <tr>
                            <td class="small"><?= View::e($job['original_name']) ?></td>
                            <td><span class="badge bg-secondary"><?= View::e($job['source_type']) ?></span></td>
                            <td class="text-success fw-bold"><?= $job['imported_rows'] ?></td>
                            <td class="text-info"><?= $job['updated_rows'] ?></td>
                            <td class="text-warning"><?= $job['skipped_rows'] ?></td>
                            <td>
                                <?php $sc = ['completed'=>'success','processing'=>'warning','pending'=>'secondary','failed'=>'danger']; ?>
                                <span class="badge bg-<?= $sc[$job['status']] ?? 'secondary' ?>"><?= $job['status'] ?></span>
                            </td>
                            <td class="small"><?= View::date($job['created_at'], 'M j, Y') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
