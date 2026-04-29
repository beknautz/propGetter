<?php
/**
 * PropIntel CRM - Campaign Controller
 *
 * Manages campaigns, letter generation, email and SMS dispatch.
 */

class CampaignController
{
    /** GET /campaigns */
    public static function index(): void
    {
        $campaigns = Campaign::all();
        View::render('campaigns/index', [
            'pageTitle' => 'Campaign Manager',
            'campaigns' => $campaigns,
            'types'     => Campaign::TYPES,
        ]);
    }

    /** GET /campaigns/create */
    public static function create(): void
    {
        View::render('campaigns/create', [
            'pageTitle' => 'Create Campaign',
            'types'     => Campaign::TYPES,
        ]);
    }

    /** POST /campaigns/create */
    public static function store(): void
    {
        $v = Validator::make($_POST, [
            'name' => 'required|max:255',
            'type' => 'required|in:direct_mail,email,sms,cold_call,mixed',
        ]);

        if ($v->fails()) {
            View::render('campaigns/create', [
                'pageTitle' => 'Create Campaign',
                'errors'    => $v->errors(),
                'old'       => $_POST,
                'types'     => Campaign::TYPES,
            ]);
            return;
        }

        $id = Campaign::create($_POST);
        View::redirect("/campaigns/{$id}?created=1");
    }

    /** GET /campaigns/:id */
    public static function show(int $id): void
    {
        $campaign = self::requireCampaign($id);
        $leads    = Campaign::getLeads($id);
        $steps    = Campaign::getSteps($id);
        $templates= Campaign::getTemplates();

        View::render('campaigns/detail', [
            'pageTitle' => $campaign['name'],
            'campaign'  => $campaign,
            'leads'     => $leads,
            'steps'     => $steps,
            'templates' => $templates,
        ]);
    }

    /** GET /campaigns/:id/edit */
    public static function edit(int $id): void
    {
        $campaign = self::requireCampaign($id);
        View::render('campaigns/edit', [
            'pageTitle' => 'Edit Campaign',
            'campaign'  => $campaign,
            'types'     => Campaign::TYPES,
            'statuses'  => Campaign::STATUSES,
        ]);
    }

    /** POST /campaigns/:id/edit */
    public static function update(int $id): void
    {
        self::requireCampaign($id);
        Campaign::update($id, $_POST);
        View::redirect("/campaigns/{$id}?updated=1");
    }

    /** POST /campaigns/:id/leads — add selected leads to campaign */
    public static function addLeads(int $id): void
    {
        self::requireCampaign($id);
        $leadIds = (array)($_POST['lead_ids'] ?? []);
        $added   = 0;
        foreach ($leadIds as $lid) {
            if (Campaign::addLead($id, (int)$lid)) $added++;
        }
        Logger::activity('campaign_assigned', null, "{$added} leads added to campaign #{$id}");

        if (isset($_SERVER['HTTP_HX_REQUEST'])) {
            echo "<div class='alert alert-success'>{$added} lead(s) added to campaign.</div>";
            return;
        }
        View::redirect("/campaigns/{$id}?added={$added}");
    }

    /** POST /campaigns/:id/email — send email to all campaign leads */
    public static function sendEmail(int $id): void
    {
        $campaign = self::requireCampaign($id);
        $sg       = new SendGridService();
        $settings = self::getSenderSettings();

        $leads    = Database::fetchAll(
            'SELECT l.id, lod.owner_email, lod.owner_name, lod.owner_first_name, l.address, l.city, lpd.equity_estimate
             FROM campaign_leads cl
             JOIN leads l ON l.id = cl.lead_id AND l.deleted_at IS NULL
             LEFT JOIN lead_owner_details lod ON lod.lead_id = l.id
             LEFT JOIN lead_property_details lpd ON lpd.lead_id = l.id
             WHERE cl.campaign_id = ? AND cl.status = "active" AND lod.owner_email IS NOT NULL
               AND lod.do_not_contact = 0',
            [$id]
        );

        $subject  = trim($_POST['subject'] ?? 'Regarding Your Property');
        $body     = trim($_POST['body']    ?? '');
        $sent     = 0;

        foreach ($leads as $lead) {
            $vars = [
                'owner_name'       => $lead['owner_first_name'] ?: $lead['owner_name'],
                'property_address' => $lead['address'],
                'city'             => $lead['city'],
                'estimated_equity' => $lead['equity_estimate'] ? '$' . number_format($lead['equity_estimate']) : 'significant',
                'sender_name'      => $settings['sender_name'],
                'sender_phone'     => $settings['company_phone'],
            ];

            $result = $sg->sendTemplate($lead['owner_email'], $lead['owner_name'], $subject, $body, $vars, $lead['id'], $id);
            if ($result['success']) $sent++;
        }

        Database::query('UPDATE campaigns SET emails_sent = emails_sent + ? WHERE id = ?', [$sent, $id]);

        View::json(['success' => true, 'sent' => $sent]);
    }

    /** POST /campaigns/:id/sms — send SMS to all campaign leads */
    public static function sendSms(int $id): void
    {
        $campaign = self::requireCampaign($id);
        $twilio   = new TwilioService();
        $settings = self::getSenderSettings();

        $leads = Database::fetchAll(
            'SELECT l.id, lod.owner_phone, lod.owner_first_name, lod.owner_name, l.address
             FROM campaign_leads cl
             JOIN leads l ON l.id = cl.lead_id AND l.deleted_at IS NULL
             LEFT JOIN lead_owner_details lod ON lod.lead_id = l.id
             WHERE cl.campaign_id = ? AND cl.status = "active" AND lod.owner_phone IS NOT NULL
               AND lod.do_not_contact = 0',
            [$id]
        );

        $template = trim($_POST['sms_body'] ?? '');
        $sent     = 0;

        foreach ($leads as $lead) {
            $body = strtr($template, [
                '{{owner_name}}'        => $lead['owner_first_name'] ?: $lead['owner_name'],
                '{{property_address}}'  => $lead['address'],
                '{{sender_name}}'       => $settings['sender_name'],
                '{{sender_phone}}'      => $settings['company_phone'],
            ]);

            $result = $twilio->send($lead['owner_phone'], $body, $lead['id'], $id);
            if ($result['success']) $sent++;
        }

        Database::query('UPDATE campaigns SET sms_sent = sms_sent + ? WHERE id = ?', [$sent, $id]);

        View::json(['success' => true, 'sent' => $sent]);
    }

    /** GET /campaigns/:id/export — mail merge CSV export */
    public static function exportMailMerge(int $id): void
    {
        self::requireCampaign($id);
        $leads = Database::fetchAll(
            'SELECT l.address, l.city, l.state, l.zip,
                    lod.owner_name, lod.owner_first_name, lod.owner_last_name,
                    lod.mailing_address, lod.mailing_city, lod.mailing_state, lod.mailing_zip,
                    lpd.estimated_value, lpd.equity_estimate
             FROM campaign_leads cl
             JOIN leads l ON l.id = cl.lead_id AND l.deleted_at IS NULL
             LEFT JOIN lead_owner_details    lod ON lod.lead_id = l.id
             LEFT JOIN lead_property_details lpd ON lpd.lead_id = l.id
             WHERE cl.campaign_id = ?',
            [$id]
        );

        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"campaign_{$id}_mailmerge.csv\"");

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Owner Name','First Name','Last Name','Property Address','Property City','Property State','Property ZIP',
                        'Mailing Address','Mailing City','Mailing State','Mailing ZIP','Est Value','Est Equity']);
        foreach ($leads as $r) {
            fputcsv($out, [
                $r['owner_name'], $r['owner_first_name'], $r['owner_last_name'],
                $r['address'], $r['city'], $r['state'], $r['zip'],
                $r['mailing_address'], $r['mailing_city'], $r['mailing_state'], $r['mailing_zip'],
                $r['estimated_value'], $r['equity_estimate'],
            ]);
        }
        fclose($out);
        exit;
    }

    /** GET /letters */
    public static function letters(): void
    {
        $templates = Campaign::getTemplates('letter');
        View::render('letters/index', [
            'pageTitle' => 'Letter Templates',
            'templates' => $templates,
        ]);
    }

    /** GET /leads/:id/letter — letter generation form */
    public static function generateLetter(int $id): void
    {
        $lead      = Lead::findById($id);
        if (!$lead) { http_response_code(404); exit; }
        $templates = Campaign::getTemplates('letter');

        View::render('letters/generate', [
            'pageTitle' => 'Generate Letter',
            'lead'      => $lead,
            'templates' => $templates,
            'settings'  => self::getSenderSettings(),
        ]);
    }

    /** POST /leads/:id/letter — render and save a letter */
    public static function saveLetter(int $id): void
    {
        $lead = Lead::findById($id);
        if (!$lead) { http_response_code(404); exit; }

        $templateId = (int)($_POST['template_id'] ?? 0);
        $settings   = self::getSenderSettings();

        $template = Database::fetchOne('SELECT * FROM message_templates WHERE id = ?', [$templateId]);
        if (!$template) {
            View::json(['error' => 'Template not found'], 404);
        }

        // Merge variables
        $vars = [
            'owner_name'        => $lead['owner_name']     ?? 'Property Owner',
            'property_address'  => $lead['address']        ?? '',
            'city'              => $lead['city']           ?? '',
            'mailing_address'   => $lead['mailing_address']?? $lead['address'],
            'mailing_city'      => $lead['mailing_city']   ?? $lead['city'],
            'mailing_state'     => $lead['mailing_state']  ?? $lead['state'],
            'mailing_zip'       => $lead['mailing_zip']    ?? $lead['zip'],
            'estimated_equity'  => $lead['equity_estimate'] ? '$' . number_format($lead['equity_estimate']) : 'significant equity',
            'sender_name'       => $_POST['sender_name']   ?? $settings['sender_name'],
            'sender_phone'      => $_POST['sender_phone']  ?? $settings['company_phone'],
            'company_address'   => $settings['company_address'],
            'custom_message'    => $_POST['custom_message'] ?? '',
            'today_date'        => date('F j, Y'),
        ];

        $content = $template['body'];
        foreach ($vars as $k => $v) {
            $content = str_replace('{{' . $k . '}}', $v, $content);
        }

        // Log the letter
        Database::query(
            'INSERT INTO letter_logs (lead_id, user_id, template_id, letter_type, content, status)
             VALUES (?,?,?,?,?,?)',
            [$id, Auth::id(), $templateId, $template['category'] ?? $template['name'], $content, 'generated']
        );
        Logger::activity('letter_generated', $id, "Letter generated: {$template['name']}");

        // Return printable view
        View::render('letters/preview', [
            'pageTitle' => 'Letter Preview',
            'content'   => $content,
            'lead'      => $lead,
            'template'  => $template,
        ], 'print');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function requireCampaign(int $id): array
    {
        $campaign = Campaign::findById($id);
        if (!$campaign) {
            http_response_code(404);
            include VIEW_PATH . '/errors/404.php';
            exit;
        }
        return $campaign;
    }

    private static function getSenderSettings(): array
    {
        $rows = Database::fetchAll(
            "SELECT setting_key, value FROM system_settings WHERE group_name IN ('general','outreach')"
        );
        $map  = [];
        foreach ($rows as $r) $map[$r['setting_key']] = $r['value'];
        return $map;
    }
}
