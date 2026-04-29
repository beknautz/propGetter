<?php
/**
 * PropIntel CRM - SendGrid Email Service
 *
 * Sends transactional emails via the SendGrid Web API v3.
 * Logs all sends to email_logs.
 */

class SendGridService
{
    private string $apiKey;
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        $this->apiKey    = SENDGRID_API_KEY;
        $this->fromEmail = SENDGRID_FROM_EMAIL;
        $this->fromName  = SENDGRID_FROM_NAME;
    }

    /**
     * Send a single email.
     *
     * @param string   $toEmail
     * @param string   $toName
     * @param string   $subject
     * @param string   $htmlBody
     * @param int|null $leadId
     * @param int|null $campaignId
     * @return array   ['success' => bool, 'message_id' => string|null, 'error' => string|null]
     */
    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        ?int $leadId     = null,
        ?int $campaignId = null
    ): array {
        if (!$this->apiKey || !$this->fromEmail) {
            return $this->fail('SendGrid is not configured. Set SENDGRID_API_KEY and SENDGRID_FROM_EMAIL.', $toEmail, $subject, $htmlBody, $leadId, $campaignId);
        }

        $payload = json_encode([
            'personalizations' => [[
                'to'      => [['email' => $toEmail, 'name' => $toName]],
                'subject' => $subject,
            ]],
            'from'    => ['email' => $this->fromEmail, 'name' => $this->fromName],
            'content' => [['type' => 'text/html', 'value' => $htmlBody]],
            'tracking_settings' => [
                'click_tracking' => ['enable' => true],
                'open_tracking'  => ['enable' => true],
            ],
            'asm' => [
                'group_id' => 0,   // placeholder — configure in SendGrid dashboard
            ],
        ]);

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response   = curl_exec($ch);
        $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $messageId  = curl_getinfo($ch, CURLINFO_HEADER_OUT); // headers not available; SG returns X-Message-Id in response headers
        curl_close($ch);

        if ($httpCode === 202) {
            $this->logEmail($toEmail, $subject, $htmlBody, 'sent', null, $leadId, $campaignId);
            Logger::activity('email_sent', $leadId, "Email sent to {$toEmail}: {$subject}");
            return ['success' => true, 'message_id' => null, 'error' => null];
        }

        $error = "SendGrid returned HTTP {$httpCode}: {$response}";
        Logger::error($error, ['to' => $toEmail, 'subject' => $subject]);
        $this->logEmail($toEmail, $subject, $htmlBody, 'failed', $error, $leadId, $campaignId);
        return ['success' => false, 'message_id' => null, 'error' => $error];
    }

    /** Render a template with variable substitution and send */
    public function sendTemplate(
        string $toEmail,
        string $toName,
        string $subject,
        string $templateBody,
        array  $vars        = [],
        ?int   $leadId      = null,
        ?int   $campaignId  = null
    ): array {
        $html = $this->mergeVars($templateBody, $vars);
        return $this->send($toEmail, $toName, $subject, $html, $leadId, $campaignId);
    }

    /** Handle SendGrid event webhook (for open/click tracking) */
    public function handleWebhook(string $rawBody): void
    {
        $events = json_decode($rawBody, true);
        if (!is_array($events)) return;

        foreach ($events as $event) {
            $sgId  = $event['sg_message_id'] ?? '';
            $type  = $event['event']         ?? '';

            if (!$sgId) continue;

            $status = match ($type) {
                'delivered' => 'delivered',
                'open'      => 'opened',
                'click'     => 'clicked',
                'bounce'    => 'bounced',
                'spamreport'=> 'spam',
                default     => null,
            };

            if ($status) {
                Database::query(
                    'UPDATE email_logs SET status = ? WHERE sendgrid_id = ?',
                    [$status, $sgId]
                );
            }
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function mergeVars(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $template = str_replace('{{' . $key . '}}', htmlspecialchars((string)$value, ENT_QUOTES), $template);
        }
        return $template;
    }

    private function logEmail(
        string  $toEmail,
        string  $subject,
        string  $body,
        string  $status,
        ?string $error,
        ?int    $leadId,
        ?int    $campaignId
    ): void {
        try {
            Database::query(
                'INSERT INTO email_logs (lead_id, campaign_id, user_id, to_email, from_email, subject, body, status, sent_at, error_message)
                 VALUES (?,?,?,?,?,?,?,?,?,?)',
                [
                    $leadId,
                    $campaignId,
                    Auth::id(),
                    $toEmail,
                    $this->fromEmail,
                    $subject,
                    $body,
                    $status,
                    $status === 'sent' ? date('Y-m-d H:i:s') : null,
                    $error,
                ]
            );
        } catch (Throwable $e) {
            Logger::error('Failed to log email: ' . $e->getMessage());
        }
    }

    private function fail(string $error, string $toEmail, string $subject, string $body, ?int $leadId, ?int $campaignId): array
    {
        Logger::warning($error);
        $this->logEmail($toEmail, $subject, $body, 'failed', $error, $leadId, $campaignId);
        return ['success' => false, 'message_id' => null, 'error' => $error];
    }
}
