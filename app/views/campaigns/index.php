<?php /** @var array $campaigns */ ?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-megaphone me-2 text-warning"></i>Campaign Manager</h1>
    <a href="/campaigns/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Campaign</a>
</div>

<div class="row g-3">
    <?php if (empty($campaigns)): ?>
    <div class="col-12">
        <div class="card text-center py-5">
            <div class="text-muted"><i class="bi bi-megaphone fs-1 d-block mb-3"></i>No campaigns yet. Create your first campaign.</div>
            <a href="/campaigns/create" class="btn btn-primary mt-2 d-inline-block mx-auto" style="width:fit-content;">Create Campaign</a>
        </div>
    </div>
    <?php endif; ?>

    <?php foreach ($campaigns as $c): ?>
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title mb-0"><?= View::e($c['name']) ?></h5>
                    <?php $sc=['active'=>'success','draft'=>'secondary','paused'=>'warning','completed'=>'info','archived'=>'dark']; ?>
                    <span class="badge bg-<?= $sc[$c['status']] ?? 'secondary' ?>"><?= ucfirst($c['status']) ?></span>
                </div>
                <?php if ($c['description']): ?>
                <p class="text-muted small"><?= View::e($c['description']) ?></p>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-tag me-1"></i><?= ucfirst(str_replace('_',' ',$c['type'])) ?>
                    </span>
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-people me-1"></i><?= $c['lead_count'] ?> leads
                    </span>
                </div>

                <div class="row g-1 text-center small mb-3">
                    <div class="col-4"><div class="text-muted">Emails</div><div class="fw-bold"><?= $c['emails_sent'] ?></div></div>
                    <div class="col-4"><div class="text-muted">SMS</div><div class="fw-bold"><?= $c['sms_sent'] ?></div></div>
                    <div class="col-4"><div class="text-muted">Letters</div><div class="fw-bold"><?= $c['letters_sent'] ?></div></div>
                </div>

                <?php if ($c['start_date'] || $c['end_date']): ?>
                <div class="text-muted small mb-2">
                    <?= $c['start_date'] ? View::date($c['start_date']) : '?' ?>
                    — <?= $c['end_date'] ? View::date($c['end_date']) : 'ongoing' ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-transparent d-flex gap-2">
                <a href="/campaigns/<?= $c['id'] ?>" class="btn btn-sm btn-primary flex-grow-1">View</a>
                <a href="/campaigns/<?= $c['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
                <a href="/campaigns/<?= $c['id'] ?>/export" class="btn btn-sm btn-outline-info" title="Export Mail Merge">
                    <i class="bi bi-download"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
