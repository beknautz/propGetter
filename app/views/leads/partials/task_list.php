<?php /** @var array $tasks @var int $leadId @var array $users */ ?>
<?php if (empty($tasks)): ?>
    <p class="text-muted text-center py-3">No tasks yet.</p>
<?php else: ?>
<ul class="list-group list-group-flush">
    <?php foreach ($tasks as $task): ?>
    <li class="list-group-item px-0 py-2" id="task-row-<?= $task['id'] ?>">
        <div class="d-flex align-items-start gap-2">
            <form hx-post="/tasks/<?= $task['id'] ?>/complete"
                  hx-target="#task-row-<?= $task['id'] ?>"
                  hx-swap="outerHTML">
                <?= Auth::csrfField() ?>
                <button class="btn btn-sm btn-outline-<?= $task['status'] === 'completed' ? 'success' : 'secondary' ?> p-1"
                        title="Mark complete" <?= $task['status'] === 'completed' ? 'disabled' : '' ?>>
                    <i class="bi bi-check2"></i>
                </button>
            </form>
            <div class="flex-grow-1">
                <div class="fw-semibold small <?= $task['status'] === 'completed' ? 'text-decoration-line-through text-muted' : '' ?>">
                    <?= View::e($task['title']) ?>
                </div>
                <div class="d-flex gap-2 flex-wrap mt-1">
                    <span class="badge bg-secondary"><?= View::e($task['task_type']) ?></span>
                    <?php if ($task['due_date']): ?>
                    <span class="badge <?= strtotime($task['due_date']) < time() && $task['status'] !== 'completed' ? 'bg-danger' : 'bg-light text-dark' ?>">
                        <i class="bi bi-calendar me-1"></i><?= View::date($task['due_date']) ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($task['assigned_name']): ?>
                    <span class="text-muted small"><?= View::e($task['assigned_name']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($task['notes']): ?>
                <div class="text-muted small mt-1"><?= View::e($task['notes']) ?></div>
                <?php endif; ?>
            </div>
            <form hx-post="/tasks/<?= $task['id'] ?>/delete"
                  hx-target="#task-row-<?= $task['id'] ?>"
                  hx-swap="outerHTML"
                  hx-confirm="Delete this task?">
                <?= Auth::csrfField() ?>
                <button class="btn btn-sm btn-outline-danger p-1"><i class="bi bi-trash"></i></button>
            </form>
        </div>
    </li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>
