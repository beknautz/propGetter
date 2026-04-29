<?php /** @var array $stats @var array $dueTasks @var array $upcomingTasks */ ?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h1>
    <div class="d-flex gap-2">
        <a href="/leads/create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Add Lead
        </a>
        <a href="/import" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-upload me-1"></i>Import
        </a>
    </div>
</div>

<!-- ── Stat Cards ──────────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10">
                <i class="bi bi-house-door text-primary"></i>
            </div>
            <div>
                <div class="stat-label">Total Leads</div>
                <div class="stat-value"><?= number_format($stats['total']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10">
                <i class="bi bi-plus-circle text-success"></i>
            </div>
            <div>
                <div class="stat-label">New This Week</div>
                <div class="stat-value text-success"><?= number_format($stats['newThisWeek']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon bg-danger bg-opacity-10">
                <i class="bi bi-fire text-danger"></i>
            </div>
            <div>
                <div class="stat-label">Hot Leads</div>
                <div class="stat-value text-danger"><?= number_format($stats['hotLeads']) ?></div>
                <div class="stat-sub">Score ≥ 75</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon bg-warning bg-opacity-10">
                <i class="bi bi-megaphone text-warning"></i>
            </div>
            <div>
                <div class="stat-label">Active Campaigns</div>
                <div class="stat-value"><?= number_format($stats['activeCamp']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon bg-info bg-opacity-10">
                <i class="bi bi-calendar-check text-info"></i>
            </div>
            <div>
                <div class="stat-label">Follow-Ups Due</div>
                <div class="stat-value <?= $stats['followUps'] > 0 ? 'text-warning' : '' ?>"><?= number_format($stats['followUps']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,.1)">
                <i class="bi bi-currency-dollar text-green"></i>
            </div>
            <div>
                <div class="stat-label">Est. Equity Pool</div>
                <div class="stat-value text-green"><?= View::money((float)$stats['equitySum']) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- ── Main content row ───────────────────────────────────────────────────── -->
<div class="row g-3">

    <!-- Status breakdown -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-bar-chart me-2 text-primary"></i>Leads by Status</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($stats['statusCounts'] as $sc): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                        <?= View::statusBadge($sc['status']) ?>
                        <span class="fw-bold"><?= number_format($sc['cnt']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Top ZIPs -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pin-map me-2 text-warning"></i>Top ZIP Codes</div>
            <div class="card-body">
                <?php foreach ($stats['topZips'] as $i => $z): ?>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-secondary" style="width:1.5rem;"><?= $i + 1 ?></span>
                    <a href="/leads?zip=<?= View::e($z['zip']) ?>" class="fw-semibold text-decoration-none"><?= View::e($z['zip']) ?></a>
                    <span class="ms-auto text-muted small"><?= $z['cnt'] ?> leads</span>
                    <div class="progress flex-grow-1" style="height:6px;max-width:80px;">
                        <div class="progress-bar bg-primary" style="width:<?= min(100, ($z['cnt'] / max(1,$stats['total'])) * 100 * 5) ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Overdue tasks -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Overdue Tasks</span>
                <a href="/tasks" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($dueTasks)): ?>
                    <div class="text-center text-muted py-4"><i class="bi bi-check2-all fs-2 d-block mb-2 text-success"></i>No overdue tasks!</div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($dueTasks as $task): ?>
                    <li class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold small"><?= View::e($task['title']) ?></span>
                            <span class="badge bg-danger"><?= View::date($task['due_date']) ?></span>
                        </div>
                        <a href="/leads/<?= $task['lead_id'] ?>" class="text-muted small"><?= View::e($task['address'] . ', ' . $task['city']) ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-activity me-2 text-info"></i>Recent Activity</div>
            <div class="card-body p-0">
                <div class="timeline p-3">
                    <?php foreach ($stats['recentActivity'] as $act): ?>
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between timeline-meta mb-1">
                            <span>
                                <i class="bi <?= ActivityLog::icon($act['action']) ?> me-1"></i>
                                <strong><?= ActivityLog::label($act['action']) ?></strong>
                                <?php if ($act['address']): ?>
                                — <a href="/leads/<?= $act['lead_id'] ?>"><?= View::e($act['address'] . ', ' . $act['city']) ?></a>
                                <?php endif; ?>
                            </span>
                            <span><?= View::date($act['created_at'], 'M j, g:ia') ?></span>
                        </div>
                        <?php if ($act['description']): ?>
                        <div class="timeline-body"><?= View::e($act['description']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($stats['recentActivity'])): ?>
                    <p class="text-muted text-center">No activity yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>
