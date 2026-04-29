<?php
/**
 * PropIntel CRM - Lead Scoring Service
 *
 * Calculates a 0-100 score based on property/owner attributes.
 * Each factor contributes weighted points; the explanation is stored.
 */

class LeadScoringService
{
    // Point values for each scoring factor
    private const WEIGHTS = [
        'absentee_owner'       => 15,
        'high_equity'          => 20,
        'vacant'               => 12,
        'pre_foreclosure'      => 18,
        'tax_delinquent'       => 15,
        'probate'              => 14,
        'tired_landlord'       => 10,
        'out_of_state_owner'   => 8,
        'long_ownership'       => 10,   // >= 10 years
        'old_property'         => 5,    // built before 1980
        'high_rent_ratio'      => 8,    // rent/value >= 1%
    ];

    /**
     * Score a lead from its data array (merged lead + details + owner).
     * Returns ['score' => int, 'reason' => string]
     */
    public static function score(array $lead): array
    {
        $points  = 0;
        $factors = [];

        // Boolean flags
        if (!empty($lead['is_absentee_owner'])) {
            $points += self::WEIGHTS['absentee_owner'];
            $factors[] = 'Absentee owner (+' . self::WEIGHTS['absentee_owner'] . ')';
        }
        if (!empty($lead['is_high_equity'])) {
            $points += self::WEIGHTS['high_equity'];
            $factors[] = 'High equity (+' . self::WEIGHTS['high_equity'] . ')';
        }
        if (!empty($lead['is_vacant'])) {
            $points += self::WEIGHTS['vacant'];
            $factors[] = 'Vacant (+' . self::WEIGHTS['vacant'] . ')';
        }
        if (!empty($lead['is_pre_foreclosure'])) {
            $points += self::WEIGHTS['pre_foreclosure'];
            $factors[] = 'Pre-foreclosure (+' . self::WEIGHTS['pre_foreclosure'] . ')';
        }
        if (!empty($lead['is_tax_delinquent'])) {
            $points += self::WEIGHTS['tax_delinquent'];
            $factors[] = 'Tax delinquent (+' . self::WEIGHTS['tax_delinquent'] . ')';
        }
        if (!empty($lead['is_probate'])) {
            $points += self::WEIGHTS['probate'];
            $factors[] = 'Probate (+' . self::WEIGHTS['probate'] . ')';
        }
        if (!empty($lead['is_tired_landlord'])) {
            $points += self::WEIGHTS['tired_landlord'];
            $factors[] = 'Tired landlord (+' . self::WEIGHTS['tired_landlord'] . ')';
        }

        // Owner attributes
        if (!empty($lead['out_of_state_owner'])) {
            $points += self::WEIGHTS['out_of_state_owner'];
            $factors[] = 'Out-of-state owner (+' . self::WEIGHTS['out_of_state_owner'] . ')';
        }

        $ownerYears = (int)($lead['ownership_years'] ?? 0);
        if ($ownerYears >= 10) {
            $points += self::WEIGHTS['long_ownership'];
            $factors[] = "Long ownership ({$ownerYears} yrs, +" . self::WEIGHTS['long_ownership'] . ')';
        }

        // Property age
        $yearBuilt = (int)($lead['year_built'] ?? date('Y'));
        if ($yearBuilt > 0 && $yearBuilt < 1980) {
            $points += self::WEIGHTS['old_property'];
            $factors[] = "Older property ({$yearBuilt}, +" . self::WEIGHTS['old_property'] . ')';
        }

        // Rent-to-value ratio
        $rent  = (float)($lead['estimated_rent']  ?? 0);
        $value = (float)($lead['estimated_value'] ?? 0);
        if ($rent > 0 && $value > 0 && ($rent / $value) >= 0.01) {
            $points += self::WEIGHTS['high_rent_ratio'];
            $factors[] = 'Good rent ratio (+' . self::WEIGHTS['high_rent_ratio'] . ')';
        }

        // Cap score at 100
        $score  = min(100, $points);
        $reason = "Score {$score}: " . implode(', ', $factors) . '.';

        if (!$factors) {
            $reason = "Score {$score}: No strong motivating factors detected.";
        }

        return ['score' => $score, 'reason' => $reason];
    }

    /**
     * Score a lead by ID, persist result, return score array.
     */
    public static function scoreAndSave(int $leadId): array
    {
        $lead   = Lead::findById($leadId);
        if (!$lead) {
            throw new RuntimeException("Lead {$leadId} not found");
        }

        $result = self::score($lead);
        Lead::updateScore($leadId, $result['score'], $result['reason']);
        return $result;
    }

    /**
     * Bulk re-score all leads (e.g. after a weight change).
     * Runs in batches to avoid memory issues.
     */
    public static function rescoreAll(int $batchSize = 100): int
    {
        $offset  = 0;
        $updated = 0;

        do {
            $rows = Database::fetchAll(
                'SELECT l.id FROM leads l WHERE l.deleted_at IS NULL LIMIT ? OFFSET ?',
                [$batchSize, $offset]
            );

            foreach ($rows as $row) {
                try {
                    self::scoreAndSave((int)$row['id']);
                    $updated++;
                } catch (Throwable $e) {
                    Logger::error("Score failed for lead {$row['id']}: " . $e->getMessage());
                }
            }

            $offset += $batchSize;
        } while (count($rows) === $batchSize);

        return $updated;
    }

    /** Return the weight map (for display in settings) */
    public static function weights(): array
    {
        return self::WEIGHTS;
    }
}
