<?php
/**
 * PropIntel CRM - Twilio SMS Service
 *
 * Sends outbound SMS, logs to sms_logs, handles inbound webhooks.
 */

class TwilioService
{
    private string $accountSid;
    private string $authToken;
    private string $fromNumber;

    public function __construct()
    {
        $this->accountSid = TWILIO_ACCOUNT_SID;
        $this->authToken  = TWILIO_AUTH_TOKEN;
        $this->fromNumber = TWILIO_FROM_NUMBER;
    }

    /**
     * Send an SMS.
     *
     * @return array ['success' => bool, 'sid' => string|null, 'error' => string|null]
     */
    public function send(string $toPhone, string $body, ?int $leadId = null, ?int $campaignId = null): array
    {
        if (!$this->accountSid || !$this->authToken || !$this->fromNumber) {
            return $this->fail('Twilio is not configured.', $toPhone, $body, $leadId, $campaignId);
        }

        $toPhone = $this->normalizePhone($toPhone);
        if (!$toPhone) {
            return $this->fail('Invalid phone number.', $toPhone, $body, $leadId, $campaignId);
        }

        $url  = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";
        $data = http_build_query([
            'From' => $this->fromNumber,
            'To'   => $toPhone,
            'Body' => $body,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_USERPWD        => "{$this->accountSid}:{$this->authToken}",
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = json_decode(curl_exec($ch), true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201 && !empty($response['sid'])) {
            $this->logSms($toPhone, $body, 'sent', $response['sid'], null, $leadId, $campaignId, 'outbound');
            Logger::activity('sms_sent', $leadId, "SMS sent to {$toPhone}");
            return ['success' => true, 'sid' => $response['sid'], 'error' => null];
        }

        $error = $response['message'] ?? "Twilio returned HTTP {$httpCode}";
        Logger::error('Twilio send failed: ' . $error, ['to' => $toPhone]);
        $this->logSms($toPhone, $body, 'failed', null, $error, $leadId, $campaignId, 'outbound');
        return ['success' => false, 'sid' => null, 'error' => $error];
    }

    /**
     * Handle inbound Twilio webhook (TwiML).
     * Attaches the inbound SMS to the matching lead's timeline.
     */
    public function handleInbound(array $post): string
    {
        $from  = $this->normalizePhone($post['From'] ?? '');
        $body  = trim($post['Body'] ?? '');
        $sid   = $post['MessageSid'] ?? '';

        if (!$from || !$body) {
            return '<?xml version="1.0" encoding="UTF-8"?><Response></Response>';
        }

        // Try to find matching lead by phone
        $owner = Database::fetchOne(
            'SELECT lead_id FROM lead_owner_details WHERE owner_phone = ? OR owner_phone2 = ? LIMIT 1',
            [$from, $from]
        );

        $leadId = $owner ? (int)$owner['lead_id'] : null;

        $this->logSms($from, $body, 'received', $sid, null, $leadId, null, 'inbound');

        if ($leadId) {
            // Attach to lead notes timeline
            Lead::addNote($leadId, "Inbound SMS from {$from}: {$body}", 'sms');
            Logger::activity('sms_received', $leadId, "Inbound SMS from {$from}");
        }

        // Return empty TwiML response
        return '<?xml version="1.0" encoding="UTF-8"?><Response></Response>';
    }

    /**
     * Verify a Twilio webhook signature.
     * Call this in your webhook endpoint to prevent spoofing.
     */
    public function verifySignature(string $url, array $params, string $signature): bool
    {
        if (!$this->authToken) return false;

        ksort($params);
        $data = $url . http_build_query($params, '', '');
        $expected = base64_encode(hash_hmac('sha1', $data, $this->authToken, true));
        return hash_equals($expected, $signature);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 10) return '+1' . $digits;
        if (strlen($digits) === 11 && $digits[0] === '1') return '+' . $digits;
        if (str_starts_with($phone, '+')) return $phone;
        return '';
    }

    private function logSms(
        string  $toPhone,
        string  $body,
        string  $status,
        ?string $sid,
        ?string $error,
        ?int    $leadId,
        ?int    $campaignId,
        string  $direction
    ): void {
        try {
            Database::query(
                'INSERT INTO sms_logs (lead_id, campaign_id, user_id, to_phone, from_phone, body, direction, status, twilio_sid, sent_at, error_message)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                [
                    $leadId,
                    $campaignId,
                    Auth::id(),
                    $toPhone,
                    $this->fromNumber,
                    $body,
                    $direction,
                    $status,
                    $sid,
                    in_array($status, ['sent','received']) ? date('Y-m-d H:i:s') : null,
                    $error,
                ]
            );
        } catch (Throwable $e) {
            Logger::error('Failed to log SMS: ' . $e->getMessage());
        }
    }

    private function fail(string $error, string $toPhone, string $body, ?int $leadId, ?int $campaignId): array
    {
        Logger::warning($error);
        $this->logSms($toPhone, $body, 'failed', null, $error, $leadId, $campaignId, 'outbound');
        return ['success' => false, 'sid' => null, 'error' => $error];
    }
}
