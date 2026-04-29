<?php
/**
 * PropIntel CRM - Task Model
 */

class Task
{
    public const TYPES    = ['Call','Text','Email','Research','Drive-by','Make Offer','Follow-up','Other'];
    public const STATUSES = ['pending','in_progress','completed','cancelled'];

    public static function findById(int $id): ?array
    {
        return Database::fetchOne(
            'SELECT lt.*, l.address, l.city, l.state,
                    u.name AS assigned_name, cb.name AS created_by_name
             FROM lead_tasks lt
             JOIN leads l ON l.id = lt.lead_id
             LEFT JOIN users u  ON u.id  = lt.assigned_to
             LEFT JOIN users cb ON cb.id = lt.created_by
             WHERE lt.id = ?',
            [$id]
        );
    }

    public static function create(array $data): int
    {
        Database::query(
            'INSERT INTO lead_tasks (lead_id, assigned_to, created_by, task_type, title, notes, due_date, status)
             VALUES (?,?,?,?,?,?,?,?)',
            [
                (int)$data['lead_id'],
                $data['assigned_to'] ?? Auth::id(),
                Auth::id(),
                $data['task_type']   ?? 'Call',
                trim($data['title']),
                $data['notes']       ?? null,
                $data['due_date']    ?? null,
                'pending',
            ]
        );
        $id = (int)Database::lastInsertId();
        Logger::activity('task_created', (int)$data['lead_id'], "Task created: {$data['title']}");
        return $id;
    }

    public static function complete(int $id): void
    {
        Database::query(
            'UPDATE lead_tasks SET status = "completed", completed_at = NOW() WHERE id = ?',
            [$id]
        );
    }

    public static function updateStatus(int $id, string $status): void
    {
        Database::query('UPDATE lead_tasks SET status = ? WHERE id = ?', [$status, $id]);
    }

    public static function delete(int $id): void
    {
        Database::query('UPDATE lead_tasks SET status = "cancelled" WHERE id = ?', [$id]);
    }

    /** Tasks due today or overdue for the current (or given) user */
    public static function getDueSoon(?int $userId = null, int $limit = 20): array
    {
        $where  = $userId ? 'AND lt.assigned_to = ?' : '';
        $params = ['pending', 'in_progress'];
        if ($userId) $params[] = $userId;

        return Database::fetchAll(
            "SELECT lt.*, l.address, l.city, l.state, l.zip, u.name AS assigned_name
             FROM lead_tasks lt
             JOIN leads l ON l.id = lt.lead_id AND l.deleted_at IS NULL
             LEFT JOIN users u ON u.id = lt.assigned_to
             WHERE lt.status IN (?, ?) AND lt.due_date <= CURDATE() {$where}
             ORDER BY lt.due_date ASC
             LIMIT {$limit}",
            $params
        );
    }

    public static function getUpcoming(?int $userId = null, int $limit = 20): array
    {
        $where  = $userId ? 'AND lt.assigned_to = ?' : '';
        $params = ['pending', date('Y-m-d'), date('Y-m-d', strtotime('+7 days'))];
        if ($userId) $params[] = $userId;

        return Database::fetchAll(
            "SELECT lt.*, l.address, l.city, l.state, u.name AS assigned_name
             FROM lead_tasks lt
             JOIN leads l ON l.id = lt.lead_id AND l.deleted_at IS NULL
             LEFT JOIN users u ON u.id = lt.assigned_to
             WHERE lt.status = ? AND lt.due_date BETWEEN ? AND ? {$where}
             ORDER BY lt.due_date ASC
             LIMIT {$limit}",
            $params
        );
    }

    public static function getForLead(int $leadId): array
    {
        return Database::fetchAll(
            'SELECT lt.*, u.name AS assigned_name
             FROM lead_tasks lt
             LEFT JOIN users u ON u.id = lt.assigned_to
             WHERE lt.lead_id = ? AND lt.status != "cancelled"
             ORDER BY lt.due_date ASC, lt.created_at DESC',
            [$leadId]
        );
    }

    /** Total overdue task count */
    public static function overdueCount(?int $userId = null): int
    {
        $where  = $userId ? 'AND assigned_to = ?' : '';
        $params = $userId ? [$userId] : [];
        $row = Database::fetchOne(
            "SELECT COUNT(*) AS c FROM lead_tasks
             WHERE status IN ('pending','in_progress') AND due_date < CURDATE() {$where}",
            $params
        );
        return (int)($row['c'] ?? 0);
    }
}
