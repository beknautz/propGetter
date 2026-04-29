<?php /** @var array $overdue @var array $upcoming @var array $users @var array $taskTypes */ ?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-check2-square me-2 text-primary"></i>Tasks & Follow-Ups</h1>
</div>

<!-- Overdue -->
<div class="card mb-3">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle text-danger"></i>
        <span class="fw-bold">Overdue Tasks</span>
        <?php if ($overdue): ?><span class="badge bg-danger"><?= count($overdue) ?></span><?php endif; ?>
    </div>
    <?php if (empty($overdue)): ?>
    <div class="card-body text-center text-muted py-4">
        <i class="bi bi-check2-all fs-2 d-block mb-2 text-success"></i>All caught up!
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Task</th><th>Type</th><th>Lead</th><th>Assigned</th><th>Due</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($overdue as $task): ?>
                <tr id="task-row-<?= $task['id'] ?>">
                    <td class="fw-semibold"><?= View::e($task['title']) ?></td>
                    <td><span class="badge bg-secondary"><?= View::e($task['task_type']) ?></span></td>
                    <td>
                        <a href="/leads/<?= $task['lead_id'] ?>"><?= View::e($task['address'] . ', ' . $task['city']) ?></a>
                    </td>
                    <td class="small"><?= View::e($task['assigned_name'] ?? '—') ?></td>
                    <td><span class="badge bg-danger"><?= View::date($task['due_date']) ?></span></td>
                    <td>
                        <form hx-post="/tasks/<?= $task['id'] ?>/complete"
                              hx-target="#task-row-<?= $task['id'] ?>"
                              hx-swap="outerHTML"
                              class="d-inline">
                            <?= Auth::csrfField() ?>
                            <button class="btn btn-sm btn-success py-0 px-2"><i class="bi bi-check2"></i> Done</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Upcoming -->
<div class="card">
    <div class="card-header"><i class="bi bi-calendar-week me-2 text-info"></i>Upcoming (Next 7 Days)</div>
    <?php if (empty($upcoming)): ?>
    <div class="card-body text-center text-muted py-4">No upcoming tasks in the next 7 days.</div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Task</th><th>Type</th><th>Lead</th><th>Assigned</th><th>Due</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($upcoming as $task): ?>
                <tr id="task-row-<?= $task['id'] ?>">
                    <td class="fw-semibold"><?= View::e($task['title']) ?></td>
                    <td><span class="badge bg-secondary"><?= View::e($task['task_type']) ?></span></td>
                    <td><a href="/leads/<?= $task['lead_id'] ?>"><?= View::e($task['address'] . ', ' . $task['city']) ?></a></td>
                    <td class="small"><?= View::e($task['assigned_name'] ?? '—') ?></td>
                    <td><?= View::date($task['due_date']) ?></td>
                    <td>
                        <form hx-post="/tasks/<?= $task['id'] ?>/complete"
                              hx-target="#task-row-<?= $task['id'] ?>"
                              hx-swap="outerHTML"
                              class="d-inline">
                            <?= Auth::csrfField() ?>
                            <button class="btn btn-sm btn-outline-success py-0 px-2"><i class="bi bi-check2"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
