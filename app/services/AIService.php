<?php
/**
 * PropIntel CRM - AI Analysis Service
 *
 * Supports OpenAI (GPT-4o) and Claude (Anthropic) as providers.
 * Generates property summaries, deal analyses, seller letters, and scripts.
 */

class AIService
{
    private string $provider;
    private string $model;
    private string $apiKey;

    public function __construct()
    {
        $this->provider = AI_PROVIDER;
        if ($this->provider === 'claude') {
            $this->apiKey = CLAUDE_API_KEY;
            $this->model  = CLAUDE_MODEL;
        } else {
            $this->apiKey = OPENAI_API_KEY;
            $this->model  = OPENAI_MODEL;
        }
    }

    /**
     * Generate a full deal analysis for a lead.
     * Returns the persisted deal_analyses row ID.
     */
    public function analyzeLead(array $lead): int
    {
        $prompt = $this->buildAnalysisPrompt($lead);
        $raw    = $this->complete($prompt);

        // Parse structured JSON response from AI
        $parsed = $this->parseAnalysisResponse($raw);

        Database::query(
            'INSERT INTO deal_analyses
                (lead_id, user_id, ai_provider, summary, motivation_est, offer_range_low, offer_range_high,
                 repair_notes, rental_estimate, brrrr_potential, flip_potential,
                 seller_letter, sms_opener, call_script, raw_prompt, raw_response)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $lead['id'],
                Auth::id(),
                $this->provider,
                $parsed['summary']           ?? null,
                $parsed['motivation']        ?? null,
                $parsed['offer_low']         ?? null,
                $parsed['offer_high']        ?? null,
                $parsed['repair_notes']      ?? null,
                $parsed['rental_estimate']   ?? null,
                $parsed['brrrr']             ?? null,
                $parsed['flip']              ?? null,
                $parsed['seller_letter']     ?? null,
                $parsed['sms_opener']        ?? null,
                $parsed['call_script']       ?? null,
                $prompt,
                $raw,
            ]
        );

        $analysisId = (int)Database::lastInsertId();
        Logger::activity('ai_analysis', (int)$lead['id'], 'AI analysis generated', ['provider' => $this->provider]);
        return $analysisId;
    }

    /** Get the latest analysis for a lead */
    public static function getLatest(int $leadId): ?array
    {
        return Database::fetchOne(
            'SELECT * FROM deal_analyses WHERE lead_id = ? ORDER BY created_at DESC LIMIT 1',
            [$leadId]
        );
    }

    public static function getAll(int $leadId): array
    {
        return Database::fetchAll(
            'SELECT da.*, u.name AS user_name
             FROM deal_analyses da
             LEFT JOIN users u ON u.id = da.user_id
             WHERE da.lead_id = ?
             ORDER BY da.created_at DESC',
            [$leadId]
        );
    }

    // ── Prompt building ───────────────────────────────────────────────────────

    private function buildAnalysisPrompt(array $lead): string
    {
        $addr   = htmlspecialchars_decode(
            "{$lead['address']}, {$lead['city']}, {$lead['state']} {$lead['zip']}"
        );
        $value  = $lead['estimated_value']  ? '$' . number_format($lead['estimated_value'])  : 'unknown';
        $equity = $lead['equity_estimate']  ? '$' . number_format($lead['equity_estimate'])  : 'unknown';
        $rent   = $lead['estimated_rent']   ? '$' . number_format($lead['estimated_rent'])   : 'unknown';
        $loan   = $lead['loan_balance']     ? '$' . number_format($lead['loan_balance'])     : 'unknown';

        $flags  = [];
        if ($lead['is_absentee_owner'])  $flags[] = 'absentee owner';
        if ($lead['is_vacant'])          $flags[] = 'vacant property';
        if ($lead['is_pre_foreclosure']) $flags[] = 'pre-foreclosure';
        if ($lead['is_tax_delinquent'])  $flags[] = 'tax delinquent';
        if ($lead['is_probate'])         $flags[] = 'in probate';
        if ($lead['is_tired_landlord'])  $flags[] = 'tired landlord';
        if ($lead['is_high_equity'])     $flags[] = 'high equity';
        if ($lead['out_of_state_owner']) $flags[] = 'out-of-state owner';

        $flagStr = $flags ? implode(', ', $flags) : 'no special flags';
        $years   = $lead['ownership_years'] ?? 'unknown';

        return <<<PROMPT
You are an expert real estate investment analyst. Analyze the following property and provide a structured JSON response.

PROPERTY DATA:
- Address: {$addr}
- Property Type: {$lead['property_type']}
- Beds/Baths: {$lead['beds']}/{$lead['baths']}
- Square Footage: {$lead['sqft']} sqft
- Year Built: {$lead['year_built']}
- Estimated Value: {$value}
- Estimated Equity: {$equity}
- Estimated Rent: {$rent}/mo
- Loan Balance: {$loan}
- Owner Name: {$lead['owner_name']}
- Ownership Years: {$years}
- Flags: {$flagStr}
- Lead Score: {$lead['lead_score']}/100

Respond ONLY with a valid JSON object with these exact keys:
{
  "summary": "2-3 sentence property investment summary",
  "motivation": "Estimated seller motivation level and likely reasons (1-2 sentences)",
  "offer_low": <numeric dollar amount, no symbols>,
  "offer_high": <numeric dollar amount, no symbols>,
  "repair_notes": "Estimated repair risk and notes based on property age and type",
  "rental_estimate": <monthly rent numeric, no symbols>,
  "brrrr": <true or false>,
  "flip": <true or false>,
  "seller_letter": "A complete, persuasive, professional seller letter addressed to the owner",
  "sms_opener": "A brief, conversational SMS opener (max 160 chars)",
  "call_script": "A complete cold call script for a real estate investor calling this seller"
}

Base the offer range on typical investor criteria (65-75% of ARV minus repairs).
PROMPT;
    }

    private function parseAnalysisResponse(string $raw): array
    {
        // Extract JSON from the response (in case model wraps it in markdown)
        if (preg_match('/```(?:json)?\s*(\{.+\})\s*```/s', $raw, $m)) {
            $json = $m[1];
        } elseif (preg_match('/\{.+\}/s', $raw, $m)) {
            $json = $m[0];
        } else {
            $json = $raw;
        }

        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    // ── API Calls ─────────────────────────────────────────────────────────────

    private function complete(string $prompt): string
    {
        return match ($this->provider) {
            'claude' => $this->callClaude($prompt),
            default  => $this->callOpenAI($prompt),
        };
    }

    private function callOpenAI(string $prompt): string
    {
        if (!$this->apiKey) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $payload = json_encode([
            'model'       => $this->model,
            'messages'    => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.4,
            'max_tokens'  => 2000,
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response = json_decode(curl_exec($ch), true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $msg = $response['error']['message'] ?? "OpenAI HTTP {$httpCode}";
            throw new RuntimeException("OpenAI error: {$msg}");
        }

        return $response['choices'][0]['message']['content'] ?? '';
    }

    private function callClaude(string $prompt): string
    {
        if (!$this->apiKey) {
            throw new RuntimeException('Claude API key is not configured.');
        }

        $payload = json_encode([
            'model'      => $this->model,
            'max_tokens' => 2000,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01',
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response = json_decode(curl_exec($ch), true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $msg = $response['error']['message'] ?? "Claude HTTP {$httpCode}";
            throw new RuntimeException("Claude error: {$msg}");
        }

        return $response['content'][0]['text'] ?? '';
    }
}
