<?php
/**
 * PropIntel CRM - AI Analysis Controller
 */

class AIController
{
    /** GET /leads/:id/analyze — show existing or prompt to generate */
    public static function analyze(int $id): void
    {
        $lead     = Lead::findById($id);
        if (!$lead) { http_response_code(404); exit; }

        $analyses = AIService::getAll($id);

        View::render('ai/analyze', [
            'pageTitle' => 'AI Analysis — ' . $lead['address'],
            'lead'      => $lead,
            'analyses'  => $analyses,
            'latest'    => $analyses[0] ?? null,
        ]);
    }

    /** POST /leads/:id/analyze — run AI analysis */
    public static function runAnalysis(int $id): void
    {
        $lead = Lead::findById($id);
        if (!$lead) {
            View::json(['error' => 'Lead not found'], 404);
        }

        try {
            $ai         = new AIService();
            $analysisId = $ai->analyzeLead($lead);

            if (isset($_SERVER['HTTP_HX_REQUEST'])) {
                $analysis = Database::fetchOne('SELECT * FROM deal_analyses WHERE id = ?', [$analysisId]);
                View::render('ai/partials/analysis_result', [
                    'analysis' => $analysis,
                    'lead'     => $lead,
                ], null);
                return;
            }

            View::redirect("/leads/{$id}/analyze?done=1");
        } catch (RuntimeException $e) {
            Logger::error('AI analysis error: ' . $e->getMessage(), ['lead_id' => $id]);

            if (isset($_SERVER['HTTP_HX_REQUEST'])) {
                http_response_code(422);
                echo '<div class="alert alert-danger"><strong>AI Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
                return;
            }
            View::redirect("/leads/{$id}/analyze?error=" . urlencode($e->getMessage()));
        }
    }
}
