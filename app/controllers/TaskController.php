<?php
/**
 * PropIntel CRM - Task Controller
 */

class TaskController
{
    /** GET /tasks */
    public static function index(): void
    {
        $userId    = Auth::hasRole('admin') ? null : Auth::id();
        $overdue   = Task::getDueSoon($userId, 30);
        $upcoming  = Task::getUpcoming($userId, 20);
        $users     = User::forSelect();

        View::render('tasks/index', [
            'pageTitle' => 'Tasks & Follow-Ups',
            'overdue'   => $overdue,
            'upcoming'  => $upcoming,
            'users'     => $users,
            'taskTypes' => Task::TYPES,
        ]);
    }

    /** POST /tasks/:id/complete */
    public static function complete(int $id): void
    {
        Task::complete($id);
        if (isset($_SERVER['HTTP_HX_REQUEST'])) {
            echo '<span class="badge bg-success">Completed</span>';
            return;
        }
        View::redirect('/tasks?completed=1');
    }

    /** POST /tasks/:id/status */
    public static function updateStatus(int $id): void
    {
        $status = $_POST['status'] ?? 'pending';
        if (!in_array($status, Task::STATUSES, true)) {
            View::json(['error' => 'Invalid status'], 422);
        }
        Task::updateStatus($id, $status);
        View::json(['success' => true]);
    }

    /** POST /tasks/:id/delete */
    public static function destroy(int $id): void
    {
        Task::delete($id);
        if (isset($_SERVER['HTTP_HX_REQUEST'])) {
            echo ''; // Remove row via HTMX hx-target="#task-row-{id}" swap
            return;
        }
        View::redirect('/tasks?deleted=1');
    }
}
