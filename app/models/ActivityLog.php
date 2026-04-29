<?php
/**
 * PropIntel CRM - Activity Log Model
 */

class ActivityLog
{
    public static function forLead(int $leadId, int $limit = 50): array
    {
        return Database::fetchAll(
            'SELECT al.*, u.name AS user_name
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE al.lead_id = ?
             ORDER BY al.created_at DESC
             LIMIT ?',
            [$leadId, $limit]
        );
    }

    public static function recent(int $limit = 20): array
    {
        return Database::fetchAll(
            'SELECT al.*, u.name AS user_name, l.address, l.city
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             LEFT JOIN leads l ON l.id = al.lead_id
             ORDER BY al.created_at DESC
             LIMIT ?',
            [$limit]
        );
    }

    /** Human-readable label for an action code */
    public static function label(string $action): string
    {
        $map = [
            'lead_created'       => 'Lead Created',
            'lead_imported'      => 'Lead Imported',
            'lead_updated'       => 'Lead Updated',
            'lead_deleted'       => 'Lead Deleted',
            'status_changed'     => 'Status Changed',
            'score_updated'      => 'Score Updated',
            'note_added'         => 'Note Added',
            'task_created'       => 'Task Created',
            'email_sent'         => 'Email Sent',
            'sms_sent'           => 'SMS Sent',
            'letter_generated'   => 'Letter Generated',
            'ai_analysis'        => 'AI Analysis Generated',
            'campaign_created'   => 'Campaign Created',
            'campaign_assigned'  => 'Added to Campaign',
            'user_created'       => 'User Created',
            'import_completed'   => 'Import Completed',
        ];
        return $map[$action] ?? ucwords(str_replace('_', ' ', $action));
    }

    /** Bootstrap icon for an action code */
    public static function icon(string $action): string
    {
        $map = [
            'lead_created'       => 'bi-plus-circle text-success',
            'lead_imported'      => 'bi-upload text-primary',
            'lead_updated'       => 'bi-pencil text-info',
            'status_changed'     => 'bi-arrow-left-right text-warning',
            'score_updated'      => 'bi-bar-chart text-info',
            'note_added'         => 'bi-chat-left-text text-secondary',
            'task_created'       => 'bi-check2-square text-primary',
            'email_sent'         => 'bi-envelope text-success',
            'sms_sent'           => 'bi-chat-dots text-success',
            'letter_generated'   => 'bi-file-earmark-text text-secondary',
            'ai_analysis'        => 'bi-robot text-purple',
            'campaign_created'   => 'bi-megaphone text-warning',
            'campaign_assigned'  => 'bi-person-plus text-info',
        ];
        return $map[$action] ?? 'bi-activity text-muted';
    }
}
