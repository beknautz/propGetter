<?php
/**
 * PropIntel CRM - Lead Controller
 *
 * Handles CRUD, status updates, notes, tasks, scoring.
 * HTMX-aware: returns partials when HX-Request header is present.
 */

class LeadController
{
    /** GET /leads */
    public static function index(): void
    {
        $filters  = self::collectFilters();
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $results  = Lead::search($filters, $page);
        $campaigns= Campaign::forSelect();

        // HTMX partial response (filter update)
        if (isset($_SERVER['HTTP_HX_REQUEST'])) {
            View::render('leads/partials/results_table', [
                'results'  => $results,
                'filters'  => $filters,
                'campaigns'=> $campaigns,
            ], null);
            return;
        }

        View::render('leads/index', [
            'pageTitle' => 'Lead Database',
            'results'   => $results,
            'filters'   => $filters,
            'campaigns' => $campaigns,
            'statuses'  => Lead::STATUSES,
            'propTypes' => Lead::PROPERTY_TYPES,
        ]);
    }

    /** GET /leads/search  (HTMX quick search) */
    public static function search(): void
    {
        $filters = ['q' => trim($_GET['q'] ?? '')];
        $results = Lead::search($filters, 1, 10);
        View::render('leads/partials/quick_search_results', [
            'results' => $results,
        ], null);
    }

    /** GET /leads/create */
    public static function create(): void
    {
        View::render('leads/create', [
            'pageTitle' => 'Add New Lead',
            'statuses'  => Lead::STATUSES,
            'propTypes' => Lead::PROPERTY_TYPES,
            'users'     => User::forSelect(),
        ]);
    }

    /** POST /leads/create */
    public static function store(): void
    {
        $v = Validator::make($_POST, [
            'address'       => 'required|max:255',
            'city'          => 'required|max:100',
            'state'         => 'required|max:2',
            'zip'           => 'required|max:10',
            'property_type' => 'required',
        ]);

        if ($v->fails()) {
            View::render('leads/create', [
                'pageTitle' => 'Add New Lead',
                'errors'    => $v->errors(),
                'old'       => $_POST,
                'statuses'  => Lead::STATUSES,
                'propTypes' => Lead::PROPERTY_TYPES,
                'users'     => User::forSelect(),
            ]);
            return;
        }

        try {
            $leadId = Lead::create($_POST);

            // Auto-score
            try { LeadScoringService::scoreAndSave($leadId); } catch (Throwable $e) {}

            View::redirect("/leads/{$leadId}?created=1");
        } catch (Throwable $e) {
            Logger::error('Lead create error: ' . $e->getMessage());
            View::render('leads/create', [
                'pageTitle' => 'Add New Lead',
                'errors'    => ['general' => ['Failed to create lead. Please try again.']],
                'old'       => $_POST,
                'statuses'  => Lead::STATUSES,
                'propTypes' => Lead::PROPERTY_TYPES,
                'users'     => User::forSelect(),
            ]);
        }
    }

    /** GET /leads/:id */
    public static function show(int $id): void
    {
        $lead = self::requireLead($id);

        $notes    = Lead::getNotes($id);
        $tasks    = Task::getForLead($id);
        $activity = ActivityLog::forLead($id, 30);
        $analysis = AIService::getLatest($id);
        $campaigns= Campaign::forSelect();
        $users    = User::forSelect();

        // Email and SMS counts
        $emailCount = Database::fetchOne('SELECT COUNT(*) AS c FROM email_logs WHERE lead_id = ?', [$id])['c'] ?? 0;
        $smsCount   = Database::fetchOne('SELECT COUNT(*) AS c FROM sms_logs   WHERE lead_id = ?', [$id])['c'] ?? 0;
        $letterCount= Database::fetchOne('SELECT COUNT(*) AS c FROM letter_logs WHERE lead_id = ?', [$id])['c'] ?? 0;
        $emailLogs  = Database::fetchAll('SELECT * FROM email_logs WHERE lead_id = ? ORDER BY created_at DESC LIMIT 5', [$id]);
        $smsLogs    = Database::fetchAll('SELECT * FROM sms_logs   WHERE lead_id = ? ORDER BY created_at DESC LIMIT 5', [$id]);

        View::render('leads/detail', [
            'pageTitle'   => $lead['address'] . ', ' . $lead['city'],
            'lead'        => $lead,
            'notes'       => $notes,
            'tasks'       => $tasks,
            'activity'    => $activity,
            'analysis'    => $analysis,
            'campaigns'   => $campaigns,
            'users'       => User::forSelect(),
            'statuses'    => Lead::STATUSES,
            'taskTypes'   => Task::TYPES,
            'emailCount'  => $emailCount,
            'smsCount'    => $smsCount,
            'letterCount' => $letterCount,
            'emailLogs'   => $emailLogs,
            'smsLogs'     => $smsLogs,
        ]);
    }

    /** GET /leads/:id/edit */
    public static function edit(int $id): void
    {
        $lead = self::requireLead($id);
        View::render('leads/edit', [
            'pageTitle' => 'Edit Lead — ' . $lead['address'],
            'lead'      => $lead,
            'statuses'  => Lead::STATUSES,
            'propTypes' => Lead::PROPERTY_TYPES,
            'users'     => User::forSelect(),
        ]);
    }

    /** POST /leads/:id/edit */
    public static function update(int $id): void
    {
        self::requireLead($id);

        $v = Validator::make($_POST, [
            'address'       => 'required|max:255',
            'city'          => 'required|max:100',
            'state'         => 'required|max:2',
            'zip'           => 'required|max:10',
            'property_type' => 'required',
        ]);

        if ($v->fails()) {
            $lead = Lead::findById($id);
            View::render('leads/edit', [
                'pageTitle' => 'Edit Lead',
                'lead'      => array_merge($lead ?? [], $_POST),
                'errors'    => $v->errors(),
                'statuses'  => Lead::STATUSES,
                'propTypes' => Lead::PROPERTY_TYPES,
                'users'     => User::forSelect(),
            ]);
            return;
        }

        Lead::update($id, $_POST);
        View::redirect("/leads/{$id}?updated=1");
    }

    /** POST /leads/:id/delete */
    public static function destroy(int $id): void
    {
        self::requireLead($id);
        Lead::softDelete($id);
        View::redirect('/leads?deleted=1');
    }

    /** POST /leads/:id/status  (HTMX inline status update) */
    public static function updateStatus(int $id): void
    {
        self::requireLead($id);
        $status = $_POST['status'] ?? '';
        if (!in_array($status, Lead::STATUSES, true)) {
            View::json(['error' => 'Invalid status'], 422);
        }
        Lead::updateStatus($id, $status);

        // Return updated badge HTML for HTMX swap
        echo View::statusBadge($status);
    }

    /** POST /leads/:id/note  (HTMX inline notes) */
    public static function addNote(int $id): void
    {
        self::requireLead($id);

        $note = trim($_POST['note'] ?? '');
        $type = $_POST['note_type'] ?? 'general';
        if (!$note) {
            http_response_code(422);
            echo '<div class="alert alert-danger">Note cannot be empty.</div>';
            return;
        }

        Lead::addNote($id, $note, $type);

        // Return refreshed notes partial for HTMX
        $notes = Lead::getNotes($id);
        View::render('leads/partials/notes_timeline', ['notes' => $notes, 'leadId' => $id], null);
    }

    /** POST /leads/:id/task  (HTMX inline task add) */
    public static function addTask(int $id): void
    {
        self::requireLead($id);

        $data = array_merge($_POST, ['lead_id' => $id]);
        $v    = Validator::make($data, ['title' => 'required|max:255']);

        if ($v->fails()) {
            http_response_code(422);
            echo '<div class="alert alert-danger">' . htmlspecialchars($v->firstError('title')) . '</div>';
            return;
        }

        Task::create($data);
        $tasks = Task::getForLead($id);
        View::render('leads/partials/task_list', ['tasks' => $tasks, 'leadId' => $id, 'users' => User::forSelect()], null);
    }

    /** POST /leads/:id/score  (manual rescore) */
    public static function rescore(int $id): void
    {
        self::requireLead($id);
        $result = LeadScoringService::scoreAndSave($id);
        View::json($result);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function requireLead(int $id): array
    {
        $lead = Lead::findById($id);
        if (!$lead) {
            http_response_code(404);
            include VIEW_PATH . '/errors/404.php';
            exit;
        }
        return $lead;
    }

    private static function collectFilters(): array
    {
        $keys = [
            'q','zip','city','state','county','property_type','status',
            'absentee_owner','vacant','pre_foreclosure','tax_delinquent',
            'probate','high_equity','tired_landlord','mls_listed',
            'score_min','score_max','value_min','value_max',
            'equity_min','equity_max','follow_up_due','campaign_id',
            'sort','dir',
        ];
        $filters = [];
        foreach ($keys as $k) {
            $val = $_GET[$k] ?? '';
            if ($val !== '') $filters[$k] = $val;
        }
        return $filters;
    }
}
