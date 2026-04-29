<?php
/**
 * PropIntel CRM - Campaign Model
 */

class Campaign
{
    public const TYPES    = ['direct_mail', 'email', 'sms', 'cold_call', 'mixed'];
    public const STATUSES = ['draft', 'active', 'paused', 'completed', 'archived'];

    public static function findById(int $id): ?array
    {
        return Database::fetchOne(
            'SELECT c.*, u.name AS created_by_name
             FROM campaigns c
             LEFT JOIN users u ON u.id = c.created_by
             WHERE c.id = ?',
            [$id]
        );
    }

    public static function all(string $status = ''): array
    {
        $where  = $status ? 'WHERE c.status = ?' : '';
        $params = $status ? [$status] : [];
        return Database::fetchAll(
            "SELECT c.*, u.name AS created_by_name,
                    (SELECT COUNT(*) FROM campaign_leads cl WHERE cl.campaign_id = c.id) AS lead_count
             FROM campaigns c
             LEFT JOIN users u ON u.id = c.created_by
             {$where}
             ORDER BY c.created_at DESC",
            $params
        );
    }

    public static function create(array $data): int
    {
        Database::query(
            'INSERT INTO campaigns (name, description, type, status, start_date, end_date, created_by)
             VALUES (?,?,?,?,?,?,?)',
            [
                trim($data['name']),
                $data['description'] ?? null,
                $data['type']        ?? 'mixed',
                $data['status']      ?? 'draft',
                $data['start_date']  ?? null,
                $data['end_date']    ?? null,
                Auth::id(),
            ]
        );
        $id = (int)Database::lastInsertId();
        Logger::activity('campaign_created', null, "Campaign created: {$data['name']}");
        return $id;
    }

    public static function update(int $id, array $data): void
    {
        Database::query(
            'UPDATE campaigns SET name=?, description=?, type=?, status=?, start_date=?, end_date=? WHERE id=?',
            [
                trim($data['name']),
                $data['description'] ?? null,
                $data['type']        ?? 'mixed',
                $data['status']      ?? 'draft',
                $data['start_date']  ?? null,
                $data['end_date']    ?? null,
                $id,
            ]
        );
    }

    public static function addLead(int $campaignId, int $leadId): bool
    {
        $exists = Database::fetchOne(
            'SELECT id FROM campaign_leads WHERE campaign_id = ? AND lead_id = ?',
            [$campaignId, $leadId]
        );
        if ($exists) return false;

        Database::query(
            'INSERT INTO campaign_leads (campaign_id, lead_id) VALUES (?,?)',
            [$campaignId, $leadId]
        );
        Database::query('UPDATE campaigns SET total_leads = total_leads + 1 WHERE id = ?', [$campaignId]);
        return true;
    }

    public static function removeLeads(int $campaignId, array $leadIds): void
    {
        foreach ($leadIds as $lid) {
            Database::query(
                'DELETE FROM campaign_leads WHERE campaign_id = ? AND lead_id = ?',
                [$campaignId, (int)$lid]
            );
        }
        // Recalculate total
        $cnt = Database::fetchOne('SELECT COUNT(*) AS c FROM campaign_leads WHERE campaign_id = ?', [$campaignId])['c'];
        Database::query('UPDATE campaigns SET total_leads = ? WHERE id = ?', [$cnt, $campaignId]);
    }

    public static function getLeads(int $campaignId, int $page = 1, int $perPage = ITEMS_PER_PAGE): array
    {
        $offset = ($page - 1) * $perPage;
        $rows = Database::fetchAll(
            'SELECT l.id, l.address, l.city, l.state, l.zip, l.status, l.lead_score,
                    lod.owner_name, lod.owner_phone,
                    cl.status AS campaign_status, cl.current_step, cl.added_at
             FROM campaign_leads cl
             JOIN leads l ON l.id = cl.lead_id
             LEFT JOIN lead_owner_details lod ON lod.lead_id = l.id
             WHERE cl.campaign_id = ?
             ORDER BY cl.added_at DESC
             LIMIT ? OFFSET ?',
            [$campaignId, $perPage, $offset]
        );
        $total = Database::fetchOne('SELECT COUNT(*) AS c FROM campaign_leads WHERE campaign_id = ?', [$campaignId])['c'] ?? 0;
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'last_page' => (int)ceil($total / $perPage)];
    }

    public static function getSteps(int $campaignId): array
    {
        return Database::fetchAll(
            'SELECT cs.*, mt.name AS template_name
             FROM campaign_steps cs
             LEFT JOIN message_templates mt ON mt.id = cs.template_id
             WHERE cs.campaign_id = ?
             ORDER BY cs.step_number',
            [$campaignId]
        );
    }

    public static function addStep(int $campaignId, array $data): int
    {
        Database::query(
            'INSERT INTO campaign_steps (campaign_id, step_number, step_type, delay_days, template_id, subject, body)
             VALUES (?,?,?,?,?,?,?)',
            [
                $campaignId,
                (int)($data['step_number'] ?? 1),
                $data['step_type'],
                (int)($data['delay_days'] ?? 0),
                $data['template_id'] ?? null,
                $data['subject']     ?? null,
                $data['body']        ?? null,
            ]
        );
        return (int)Database::lastInsertId();
    }

    public static function getTemplates(string $type = ''): array
    {
        $where  = $type ? 'WHERE type = ? AND is_active = 1' : 'WHERE is_active = 1';
        $params = $type ? [$type] : [];
        return Database::fetchAll("SELECT * FROM message_templates {$where} ORDER BY name", $params);
    }

    public static function forSelect(): array
    {
        return Database::fetchAll("SELECT id, name FROM campaigns WHERE status IN ('draft','active') ORDER BY name");
    }
}
