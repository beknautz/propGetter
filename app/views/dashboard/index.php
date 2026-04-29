<?php /** @var array $stats @var array $dueTasks @var array $upcomingTasks */ ?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h1>
    <div class="d-flex gap-2">
        <a href="/leads/create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Add Lead
        </a>
        <a href="/import" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-upload me-1"></i>Import CSV
        </a>
    </div>
</div>

<!-- ── KPI Stat Cards ──────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <?php
    $kpis = [
        ['label' => 'Total Leads',      'value' => number_format($stats['total']),        'icon' => 'bi-house-door',     'color' => 'primary',  'sub' => 'all time'],
        ['label' => 'New This Week',     'value' => number_format($stats['newThisWeek']),  'icon' => 'bi-plus-circle',    'color' => 'success',  'sub' => 'last 7 days'],
        ['label' => 'Hot Leads',         'value' => number_format($stats['hotLeads']),     'icon' => 'bi-fire',           'color' => 'danger',   'sub' => 'score ≥ 75'],
        ['label' => 'Active Campaigns',  'value' => number_format($stats['activeCamp']),   'icon' => 'bi-megaphone',      'color' => 'warning',  'sub' => 'running now'],
        ['label' => 'Follow-Ups Due',    'value' => number_format($stats['followUps']),    'icon' => 'bi-calendar-check', 'color' => 'info',     'sub' => 'need action'],
        ['label' => 'Est. Equity Pool',  'value' => View::money((float)$stats['equitySum']),'icon'=> 'bi-currency-dollar','color' => 'success',  'sub' => 'across all leads'],
    ];
    ?>
    <?php foreach ($kpis as $kpi): ?>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon bg-<?= $kpi['color'] ?> bg-opacity-10">
                <i class="bi <?= $kpi['icon'] ?> text-<?= $kpi['color'] ?>"></i>
            </div>
            <div>
                <div class="stat-label"><?= $kpi['label'] ?></div>
                <div class="stat-value text-<?= $kpi['color'] ?>"><?= $kpi['value'] ?></div>
                <div class="stat-sub"><?= $kpi['sub'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Second row ─────────────────────────────────────────────────────────── -->
<div class="row g-3 mb-3">

    <!-- Status Breakdown -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-bar-chart me-2 text-primary"></i>Leads by Status
            </div>
            <div class="card-body p-0">
                <?php if (empty($stats['statusCounts'])): ?>
                <p class="text-muted text-center py-4">No leads yet.</p>
                <?php else: ?>
                <?php
                $maxCount = max(array_column($stats['statusCounts'], 'cnt'));
                ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($stats['statusCounts'] as $sc): ?>
                    <li class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <?= View::statusBadge($sc['status']) ?>
                            <span class="fw-bold small"><?= number_format($sc['cnt']) ?></span>
                        </div>
                        <div class="progress" style="height:4px;">
                            <div class="progress-bar bg-primary"
                                 style="width:<?= $maxCount > 0 ? round(($sc['cnt'] / $maxCount) * 100) : 0 ?>%">
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top ZIP Codes -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-pin-map me-2 text-warning"></i>Top ZIP Codes
            </div>
            <div class="card-body">
                <?php if (empty($stats['topZips'])): ?>
                <p class="text-muted text-center py-4">No data yet.</p>
                <?php else: ?>
                <?php $maxZip = max(array_column($stats['topZips'], 'cnt')); ?>
                <?php foreach ($stats['topZips'] as $i => $z): ?>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-secondary rounded-pill" style="width:1.6rem;text-align:center;"><?= $i + 1 ?></span>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between mb-1">
                            <a href="/leads?zip=<?= View::e($z['zip']) ?>" class="fw-semibold text-decoration-none small">
                                <?= View::e($z['zip']) ?>
                            </a>
                            <span class="text-muted small"><?= $z['cnt'] ?> leads</span>
                        </div>
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar bg-warning"
                                 style="width:<?= $maxZip > 0 ? round(($z['cnt'] / $maxZip) * 100) : 0 ?>%">
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
                <a href="/leads" class="btn btn-sm btn-outline-secondary w-100 mt-1">
                    View All Leads
                </a>
            </div>
        </div>
    </div>

    <!-- Overdue Tasks -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-exclamation-circle me-2 text-danger"></i>Overdue Tasks</span>
                <a href="/tasks" class="btn btn-sm btn-outline-secondary">All Tasks</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($dueTasks)): ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-check2-all fs-2 d-block mb-2 text-success"></i>
                    No overdue tasks!
                </div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($dueTasks as $task): ?>
                    <li class="list-group-item px-3 py-2" id="dash-task-<?= $task['id'] ?>">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-semibold small text-truncate"><?= View::e($task['title']) ?></div>
                                <a href="/leads/<?= $task['lead_id'] ?>" class="text-muted small text-decoration-none">
                                    <?= View::e($task['address'] . ', ' . $task['city']) ?>
                                </a>
                            </div>
                            <div class="d-flex flex-column align-items-end gap-1">
                                <span class="badge bg-danger text-nowrap"><?= View::date($task['due_date']) ?></span>
                                <form hx-post="/tasks/<?= $task['id'] ?>/complete"
                                      hx-target="#dash-task-<?= $task['id'] ?>"
                                      hx-swap="outerHTML">
                                    <?= Auth::csrfField() ?>
                                    <button class="btn btn-xs btn-outline-success py-0 px-1" style="font-size:.7rem;">
                                        <i class="bi bi-check2"></i> Done
                                    </button>
                                </form>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Third row: Upcoming tasks + Recent activity ─────────────────────────── -->
<div class="row g-3">

    <!-- Upcoming Tasks (next 7 days) -->
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-calendar-week me-2 text-info"></i>Upcoming — Next 7 Days
            </div>
            <div class="card-body p-0">
                <?php if (empty($upcomingTasks)): ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-calendar-check fs-2 d-block mb-2"></i>
                    No upcoming tasks.
                </div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($upcomingTasks as $task): ?>
                    <li class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-secondary me-1"><?= View::e($task['task_type']) ?></span>
                                <span class="small fw-semibold"><?= View::e($task['title']) ?></span>
                                <div class="text-muted small">
                                    <a href="/leads/<?= $task['lead_id'] ?>" class="text-decoration-none">
                                        <?= View::e($task['address'] . ', ' . $task['city']) ?>
                                    </a>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border text-nowrap">
                                <?= View::date($task['due_date']) ?>
                            </span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Activity Feed -->
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-activity me-2 text-info"></i>Recent Activity
            </div>
            <div class="card-body p-0" style="max-height:380px;overflow-y:auto;">
                <?php if (empty($stats['recentActivity'])): ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>No activity yet.
                </div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($stats['recentActivity'] as $act): ?>
                    <li class="list-group-item px-3 py-2 d-flex align-items-start gap-2">
                        <div class="mt-1 flex-shrink-0">
                            <i class="bi <?= ActivityLog::icon($act['action']) ?>"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex justify-content-between gap-2">
                                <span class="small fw-semibold"><?= ActivityLog::label($act['action']) ?></span>
                                <span class="text-muted small text-nowrap">
                                    <?= View::date($act['created_at'], 'M j, g:ia') ?>
                                </span>
                            </div>
                            <?php if ($act['address']): ?>
                            <a href="/leads/<?= $act['lead_id'] ?>" class="text-muted small text-decoration-none">
                                <?= View::e($act['address'] . ', ' . $act['city']) ?>
                            </a>
                            <?php endif; ?>
                            <?php if ($act['description']): ?>
                            <div class="text-muted small text-truncate"><?= View::e($act['description']) ?></div>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
            <?php if (!empty($stats['recentActivity'])): ?>
            <div class="card-footer bg-transparent text-center">
                <a href="/leads" class="small text-muted text-decoration-none">View all leads →</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>
