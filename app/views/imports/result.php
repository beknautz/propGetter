<?php /** @var array $counters @var int $jobId */ ?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-check2-circle me-2 text-success"></i>Import Complete</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card border-left-green">
            <div class="stat-icon" style="background:rgba(16,185,129,.1)"><i class="bi bi-plus-circle text-success"></i></div>
            <div><div class="stat-label">Imported</div><div class="stat-value text-success"><?= number_format($counters['imported']) ?></div></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card border-left-blue">
            <div class="stat-icon" style="background:rgba(37,99,235,.1)"><i class="bi bi-pencil text-primary"></i></div>
            <div><div class="stat-label">Updated</div><div class="stat-value text-primary"><?= number_format($counters['updated']) ?></div></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card border-left-amber">
            <div class="stat-icon" style="background:rgba(245,158,11,.1)"><i class="bi bi-skip-forward text-warning"></i></div>
            <div><div class="stat-label">Skipped</div><div class="stat-value text-warning"><?= number_format($counters['skipped']) ?></div></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card border-left-red">
            <div class="stat-icon" style="background:rgba(239,68,68,.1)"><i class="bi bi-exclamation-triangle text-danger"></i></div>
            <div><div class="stat-label">Errors</div><div class="stat-value text-danger"><?= number_format($counters['error']) ?></div></div>
        </div>
    </div>
</div>

<div class="d-flex gap-2">
    <a href="/leads" class="btn btn-primary"><i class="bi bi-house-door me-2"></i>View Leads</a>
    <a href="/import" class="btn btn-outline-secondary"><i class="bi bi-upload me-2"></i>Import More</a>
</div>
