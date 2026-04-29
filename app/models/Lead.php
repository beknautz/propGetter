<?php
/**
 * PropIntel CRM - Lead Model
 *
 * Handles all database operations for leads + related tables.
 */

class Lead
{
    // ── Constants ──────────────────────────────────────────────────────────────

    public const STATUSES = [
        'New', 'Researching', 'Skip Trace Needed', 'Contacted',
        'Follow-Up', 'Appointment Set', 'Offer Made',
        'Under Contract', 'Closed', 'Dead',
    ];

    public const PROPERTY_TYPES = [
        'Single Family', 'Multi Family', 'Condo', 'Townhouse',
        'Mobile Home', 'Land', 'Commercial', 'Other',
    ];

    // ── Read ──────────────────────────────────────────────────────────────────

    public static function findById(int $id): ?array
    {
        return Database::fetchOne(
            'SELECT l.*,
                    lpd.beds, lpd.baths, lpd.sqft, lpd.lot_size, lpd.year_built,
                    lpd.estimated_value, lpd.estimated_rent, lpd.loan_balance,
                    lpd.equity_estimate, lpd.last_sale_date, lpd.last_sale_price, lpd.arv_estimate,
                    lod.owner_name, lod.owner_first_name, lod.owner_last_name,
                    lod.owner_phone, lod.owner_phone2, lod.owner_email,
                    lod.mailing_address, lod.mailing_city, lod.mailing_state, lod.mailing_zip,
                    lod.owner_occupied, lod.out_of_state_owner, lod.ownership_years,
                    lod.do_not_contact, lod.skip_traced, lod.skip_trace_date,
                    u.name AS created_by_name
             FROM leads l
             LEFT JOIN lead_property_details lpd ON lpd.lead_id = l.id
             LEFT JOIN lead_owner_details    lod ON lod.lead_id = l.id
             LEFT JOIN users u                   ON u.id = l.created_by
             WHERE l.id = ? AND l.deleted_at IS NULL',
            [$id]
        );
    }

    public static function search(array $filters = [], int $page = 1, int $perPage = ITEMS_PER_PAGE): array
    {
        [$where, $params] = self::buildWhereClause($filters);

        $offset = ($page - 1) * $perPage;

        $sort    = self::sanitizeSort($filters['sort'] ?? 'l.created_at');
        $dir     = strtoupper($filters['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT l.id, l.address, l.city, l.state, l.zip, l.county,
                       l.property_type, l.status, l.lead_score, l.score_reason,
                       l.is_absentee_owner, l.is_vacant, l.is_pre_foreclosure,
                       l.is_tax_delinquent, l.is_probate, l.is_high_equity,
                       l.follow_up_date, l.created_at,
                       lpd.estimated_value, lpd.equity_estimate, lpd.beds, lpd.baths, lpd.sqft,
                       lod.owner_name, lod.owner_phone
                FROM leads l
                LEFT JOIN lead_property_details lpd ON lpd.lead_id = l.id
                LEFT JOIN lead_owner_details    lod ON lod.lead_id = l.id
                {$where}
                ORDER BY {$sort} {$dir}
                LIMIT {$perPage} OFFSET {$offset}";

        $rows = Database::fetchAll($sql, $params);

        $countSql   = "SELECT COUNT(*) AS cnt FROM leads l
                       LEFT JOIN lead_property_details lpd ON lpd.lead_id = l.id
                       LEFT JOIN lead_owner_details    lod ON lod.lead_id = l.id
                       {$where}";
        $countRow   = Database::fetchOne($countSql, $params);
        $totalCount = (int)($countRow['cnt'] ?? 0);

        return [
            'rows'       => $rows,
            'total'      => $totalCount,
            'page'       => $page,
            'per_page'   => $perPage,
            'last_page'  => (int)ceil($totalCount / $perPage),
        ];
    }

    private static function buildWhereClause(array $filters): array
    {
        $conditions = ['l.deleted_at IS NULL'];
        $params     = [];

        if (!empty($filters['q'])) {
            $conditions[] = '(l.address LIKE ? OR l.city LIKE ? OR lod.owner_name LIKE ? OR l.zip LIKE ? OR l.apn LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params = array_merge($params, [$q, $q, $q, $q, $q]);
        }
        if (!empty($filters['zip']))  { $conditions[] = 'l.zip = ?';           $params[] = $filters['zip']; }
        if (!empty($filters['city'])) { $conditions[] = 'l.city LIKE ?';       $params[] = '%'.$filters['city'].'%'; }
        if (!empty($filters['state'])){ $conditions[] = 'l.state = ?';         $params[] = $filters['state']; }
        if (!empty($filters['county'])){ $conditions[] = 'l.county LIKE ?';    $params[] = '%'.$filters['county'].'%'; }
        if (!empty($filters['property_type'])) { $conditions[] = 'l.property_type = ?'; $params[] = $filters['property_type']; }
        if (!empty($filters['status'])) { $conditions[] = 'l.status = ?';      $params[] = $filters['status']; }

        // Boolean flags
        foreach (['absentee_owner','vacant','pre_foreclosure','tax_delinquent','probate','high_equity','tired_landlord','mls_listed'] as $flag) {
            if (isset($filters[$flag]) && $filters[$flag] !== '') {
                $conditions[] = "l.is_{$flag} = ?";
                $params[]      = (int)$filters[$flag];
            }
        }

        // Score range
        if (isset($filters['score_min']) && $filters['score_min'] !== '') {
            $conditions[] = 'l.lead_score >= ?';
            $params[]      = (int)$filters['score_min'];
        }
        if (isset($filters['score_max']) && $filters['score_max'] !== '') {
            $conditions[] = 'l.lead_score <= ?';
            $params[]      = (int)$filters['score_max'];
        }

        // Value range
        if (isset($filters['value_min']) && $filters['value_min'] !== '') {
            $conditions[] = 'lpd.estimated_value >= ?';
            $params[]      = (float)$filters['value_min'];
        }
        if (isset($filters['value_max']) && $filters['value_max'] !== '') {
            $conditions[] = 'lpd.estimated_value <= ?';
            $params[]      = (float)$filters['value_max'];
        }

        // Equity range
        if (isset($filters['equity_min']) && $filters['equity_min'] !== '') {
            $conditions[] = 'lpd.equity_estimate >= ?';
            $params[]      = (float)$filters['equity_min'];
        }
        if (isset($filters['equity_max']) && $filters['equity_max'] !== '') {
            $conditions[] = 'lpd.equity_estimate <= ?';
            $params[]      = (float)$filters['equity_max'];
        }

        // Follow-up date filter
        if (!empty($filters['follow_up_due'])) {
            $conditions[] = 'l.follow_up_date <= CURDATE()';
        }

        // Campaign filter
        if (!empty($filters['campaign_id'])) {
            $conditions[] = 'EXISTS (SELECT 1 FROM campaign_leads cl WHERE cl.lead_id = l.id AND cl.campaign_id = ?)';
            $params[]      = (int)$filters['campaign_id'];
        }

        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';
        return [$where, $params];
    }

    private static function sanitizeSort(string $sort): string
    {
        $allowed = [
            'l.created_at', 'l.address', 'l.city', 'l.zip', 'l.status',
            'l.lead_score', 'lpd.estimated_value', 'lpd.equity_estimate',
            'l.follow_up_date',
        ];
        return in_array($sort, $allowed, true) ? $sort : 'l.created_at';
    }

    // ── Create / Update / Delete ───────────────────────────────────────────────

    public static function create(array $data): int
    {
        Database::beginTransaction();
        try {
            // Core lead record
            Database::query(
                'INSERT INTO leads
                    (address, city, state, zip, county, apn, property_type, status,
                     is_absentee_owner, is_vacant, is_pre_foreclosure, is_tax_delinquent,
                     is_probate, is_tired_landlord, is_high_equity, is_mls_listed,
                     follow_up_date, created_by)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                [
                    trim($data['address']),
                    trim($data['city']),
                    strtoupper(trim($data['state'])),
                    trim($data['zip']),
                    $data['county']         ?? null,
                    $data['apn']            ?? null,
                    $data['property_type']  ?? 'Single Family',
                    $data['status']         ?? 'New',
                    (int)($data['is_absentee_owner']  ?? 0),
                    (int)($data['is_vacant']          ?? 0),
                    (int)($data['is_pre_foreclosure'] ?? 0),
                    (int)($data['is_tax_delinquent']  ?? 0),
                    (int)($data['is_probate']         ?? 0),
                    (int)($data['is_tired_landlord']  ?? 0),
                    (int)($data['is_high_equity']     ?? 0),
                    (int)($data['is_mls_listed']      ?? 0),
                    $data['follow_up_date'] ?? null,
                    Auth::id(),
                ]
            );
            $leadId = (int)Database::lastInsertId();

            // Property details
            Database::query(
                'INSERT INTO lead_property_details
                    (lead_id, beds, baths, sqft, lot_size, year_built,
                     estimated_value, estimated_rent, loan_balance, equity_estimate,
                     last_sale_date, last_sale_price, arv_estimate)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
                [
                    $leadId,
                    $data['beds']            ?? null,
                    $data['baths']           ?? null,
                    $data['sqft']            ?? null,
                    $data['lot_size']        ?? null,
                    $data['year_built']      ?? null,
                    $data['estimated_value'] ?? null,
                    $data['estimated_rent']  ?? null,
                    $data['loan_balance']    ?? null,
                    $data['equity_estimate'] ?? null,
                    $data['last_sale_date']  ?? null,
                    $data['last_sale_price'] ?? null,
                    $data['arv_estimate']    ?? null,
                ]
            );

            // Owner details
            Database::query(
                'INSERT INTO lead_owner_details
                    (lead_id, owner_name, owner_first_name, owner_last_name,
                     owner_phone, owner_phone2, owner_email,
                     mailing_address, mailing_city, mailing_state, mailing_zip,
                     owner_occupied, out_of_state_owner, ownership_years)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                [
                    $leadId,
                    $data['owner_name']        ?? null,
                    $data['owner_first_name']   ?? null,
                    $data['owner_last_name']    ?? null,
                    $data['owner_phone']        ?? null,
                    $data['owner_phone2']       ?? null,
                    $data['owner_email']        ?? null,
                    $data['mailing_address']    ?? null,
                    $data['mailing_city']       ?? null,
                    $data['mailing_state']      ?? null,
                    $data['mailing_zip']        ?? null,
                    (int)($data['owner_occupied']     ?? 0),
                    (int)($data['out_of_state_owner'] ?? 0),
                    $data['ownership_years']    ?? null,
                ]
            );

            Database::commit();

            Logger::activity('lead_created', $leadId, "Lead created: {$data['address']}, {$data['city']}");
            return $leadId;
        } catch (Throwable $e) {
            Database::rollBack();
            Logger::error('Lead create failed: ' . $e->getMessage(), ['data' => $data]);
            throw $e;
        }
    }

    public static function update(int $id, array $data): void
    {
        // Separate fields by table
        $leadFields = ['address','city','state','zip','county','apn','property_type','status',
            'is_absentee_owner','is_vacant','is_pre_foreclosure','is_tax_delinquent',
            'is_probate','is_tired_landlord','is_high_equity','is_mls_listed','follow_up_date'];

        $propFields = ['beds','baths','sqft','lot_size','year_built','estimated_value',
            'estimated_rent','loan_balance','equity_estimate','last_sale_date',
            'last_sale_price','arv_estimate'];

        $ownerFields = ['owner_name','owner_first_name','owner_last_name','owner_phone',
            'owner_phone2','owner_email','mailing_address','mailing_city','mailing_state',
            'mailing_zip','owner_occupied','out_of_state_owner','ownership_years',
            'do_not_contact','skip_traced','skip_trace_date'];

        Database::beginTransaction();
        try {
            self::updateTable('leads', 'id', $id, $data, $leadFields);
            self::upsertDetail('lead_property_details', 'lead_id', $id, $data, $propFields);
            self::upsertDetail('lead_owner_details',    'lead_id', $id, $data, $ownerFields);
            Database::commit();

            Logger::activity('lead_updated', $id, 'Lead updated');
        } catch (Throwable $e) {
            Database::rollBack();
            Logger::error('Lead update failed: ' . $e->getMessage(), ['lead_id' => $id]);
            throw $e;
        }
    }

    public static function updateStatus(int $id, string $status): void
    {
        $old = Database::fetchOne('SELECT status FROM leads WHERE id = ?', [$id]);
        Database::query('UPDATE leads SET status = ? WHERE id = ?', [$status, $id]);
        Logger::activity('status_changed', $id, "Status changed to {$status}", ['old' => $old['status'] ?? '']);
    }

    public static function updateScore(int $id, int $score, ?string $reason = null): void
    {
        Database::query('UPDATE leads SET lead_score = ?, score_reason = ? WHERE id = ?', [$score, $reason, $id]);
        Database::query(
            'INSERT INTO lead_scores (lead_id, score, reason, scored_by) VALUES (?, ?, ?, ?)',
            [$id, $score, $reason, Auth::id()]
        );
        Logger::activity('score_updated', $id, "Score updated to {$score}");
    }

    public static function softDelete(int $id): void
    {
        Database::query('UPDATE leads SET deleted_at = NOW() WHERE id = ?', [$id]);
        Logger::activity('lead_deleted', $id, 'Lead soft-deleted');
    }

    // ── Notes ─────────────────────────────────────────────────────────────────

    public static function addNote(int $leadId, string $note, string $type = 'general'): int
    {
        Database::query(
            'INSERT INTO lead_notes (lead_id, user_id, note, note_type) VALUES (?,?,?,?)',
            [$leadId, Auth::id(), $note, $type]
        );
        $noteId = (int)Database::lastInsertId();
        Logger::activity('note_added', $leadId, 'Note added');
        return $noteId;
    }

    public static function getNotes(int $leadId): array
    {
        return Database::fetchAll(
            'SELECT ln.*, u.name AS user_name
             FROM lead_notes ln
             LEFT JOIN users u ON u.id = ln.user_id
             WHERE ln.lead_id = ?
             ORDER BY ln.created_at DESC',
            [$leadId]
        );
    }

    // ── Tasks ─────────────────────────────────────────────────────────────────

    public static function getTasks(int $leadId): array
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

    // ── Dashboard stats ───────────────────────────────────────────────────────

    public static function dashboardStats(): array
    {
        $total      = Database::fetchOne('SELECT COUNT(*) AS c FROM leads WHERE deleted_at IS NULL')['c'] ?? 0;
        $newThisWeek= Database::fetchOne('SELECT COUNT(*) AS c FROM leads WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')['c'] ?? 0;
        $hotLeads   = Database::fetchOne('SELECT COUNT(*) AS c FROM leads WHERE deleted_at IS NULL AND lead_score >= 75')['c'] ?? 0;
        $followUps  = Database::fetchOne('SELECT COUNT(*) AS c FROM leads WHERE deleted_at IS NULL AND follow_up_date <= CURDATE() AND status NOT IN ("Closed","Dead")')['c'] ?? 0;
        $activeCamp = Database::fetchOne('SELECT COUNT(*) AS c FROM campaigns WHERE status = "active"')['c'] ?? 0;

        $equitySum  = Database::fetchOne('SELECT SUM(equity_estimate) AS s FROM lead_property_details lpd JOIN leads l ON l.id = lpd.lead_id WHERE l.deleted_at IS NULL')['s'] ?? 0;

        $topZips    = Database::fetchAll(
            'SELECT zip, COUNT(*) AS cnt FROM leads WHERE deleted_at IS NULL GROUP BY zip ORDER BY cnt DESC LIMIT 5'
        );

        $recentActivity = Database::fetchAll(
            'SELECT al.*, l.address, u.name AS user_name
             FROM activity_logs al
             LEFT JOIN leads l ON l.id = al.lead_id
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC LIMIT 10'
        );

        $statusCounts = Database::fetchAll(
            'SELECT status, COUNT(*) AS cnt FROM leads WHERE deleted_at IS NULL GROUP BY status ORDER BY cnt DESC'
        );

        return compact('total','newThisWeek','hotLeads','followUps','activeCamp',
            'equitySum','topZips','recentActivity','statusCounts');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function updateTable(string $table, string $pk, int $id, array $data, array $allowed): void
    {
        $sets   = [];
        $params = [];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $sets[]   = "{$col} = ?";
                $params[] = $data[$col] === '' ? null : $data[$col];
            }
        }
        if (!$sets) return;
        $params[] = $id;
        Database::query("UPDATE {$table} SET " . implode(', ', $sets) . " WHERE {$pk} = ?", $params);
    }

    private static function upsertDetail(string $table, string $fk, int $id, array $data, array $allowed): void
    {
        $sets   = [];
        $params = [];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $sets[]   = "{$col} = ?";
                $params[] = $data[$col] === '' ? null : $data[$col];
            }
        }
        if (!$sets) return;

        // Check if detail row exists
        $exists = Database::fetchOne("SELECT id FROM {$table} WHERE {$fk} = ?", [$id]);
        if ($exists) {
            $params[] = $id;
            Database::query("UPDATE {$table} SET " . implode(', ', $sets) . " WHERE {$fk} = ?", $params);
        } else {
            // Build INSERT
            $columns = array_filter($allowed, fn($c) => array_key_exists($c, $data));
            $colStr  = $fk . ', ' . implode(', ', $columns);
            $phStr   = '?, ' . implode(', ', array_fill(0, count($columns), '?'));
            Database::query("INSERT INTO {$table} ({$colStr}) VALUES ({$phStr})", array_merge([$id], $params));
        }
    }

    public static function findByAddressZip(string $address, string $zip): ?array
    {
        return Database::fetchOne(
            'SELECT id FROM leads WHERE address = ? AND zip = ? AND deleted_at IS NULL LIMIT 1',
            [trim($address), trim($zip)]
        );
    }

    public static function findByApn(string $apn): ?array
    {
        return Database::fetchOne(
            'SELECT id FROM leads WHERE apn = ? AND deleted_at IS NULL LIMIT 1',
            [trim($apn)]
        );
    }
}
