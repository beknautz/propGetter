<?php
/**
 * PropIntel CRM - CSV Import Service
 *
 * Handles upload validation, preview, field mapping, duplicate detection,
 * and actual row import into the leads tables.
 */

class CSVImportService
{
    // Known preset column maps for popular data sources
    private const PRESETS = [
        'propstream' => [
            'Property Address'   => 'address',
            'City'               => 'city',
            'State'              => 'state',
            'Zip'                => 'zip',
            'County'             => 'county',
            'APN'                => 'apn',
            'Property Type'      => 'property_type',
            'Bedrooms'           => 'beds',
            'Bathrooms'          => 'baths',
            'Sq Ft'              => 'sqft',
            'Lot Size'           => 'lot_size',
            'Year Built'         => 'year_built',
            'Estimated Value'    => 'estimated_value',
            'Estimated Equity'   => 'equity_estimate',
            'Owner Name'         => 'owner_name',
            'Mailing Address'    => 'mailing_address',
            'Mailing City'       => 'mailing_city',
            'Mailing State'      => 'mailing_state',
            'Mailing Zip'        => 'mailing_zip',
            'Absentee Owner'     => 'is_absentee_owner',
            'Vacant'             => 'is_vacant',
            'Pre-Foreclosure'    => 'is_pre_foreclosure',
            'Tax Delinquent'     => 'is_tax_delinquent',
        ],
        'regrid' => [
            'address'            => 'address',
            'city'               => 'city',
            'state2'             => 'state',
            'zip'                => 'zip',
            'county'             => 'county',
            'parcelnumb'         => 'apn',
            'owner'              => 'owner_name',
            'mailadd'            => 'mailing_address',
            'mail_city'          => 'mailing_city',
            'mail_state2'        => 'mailing_state',
            'mail_zip'           => 'mailing_zip',
            'yearbuilt'          => 'year_built',
            'sqft'               => 'sqft',
            'bedrooms'           => 'beds',
            'bathrooms'          => 'baths',
        ],
    ];

    // All mappable DB fields with display labels
    public const DB_FIELDS = [
        // Lead core
        'address'             => 'Property Address',
        'city'                => 'City',
        'state'               => 'State',
        'zip'                 => 'ZIP Code',
        'county'              => 'County',
        'apn'                 => 'APN / Parcel Number',
        'property_type'       => 'Property Type',
        // Property details
        'beds'                => 'Bedrooms',
        'baths'               => 'Bathrooms',
        'sqft'                => 'Square Footage',
        'lot_size'            => 'Lot Size',
        'year_built'          => 'Year Built',
        'estimated_value'     => 'Estimated Value',
        'estimated_rent'      => 'Estimated Rent',
        'loan_balance'        => 'Loan Balance',
        'equity_estimate'     => 'Equity Estimate',
        'last_sale_date'      => 'Last Sale Date',
        'last_sale_price'     => 'Last Sale Price',
        // Owner details
        'owner_name'          => 'Owner Name',
        'owner_first_name'    => 'Owner First Name',
        'owner_last_name'     => 'Owner Last Name',
        'owner_phone'         => 'Owner Phone',
        'owner_email'         => 'Owner Email',
        'mailing_address'     => 'Mailing Address',
        'mailing_city'        => 'Mailing City',
        'mailing_state'       => 'Mailing State',
        'mailing_zip'         => 'Mailing ZIP',
        'ownership_years'     => 'Ownership Years',
        // Flags
        'is_absentee_owner'   => 'Absentee Owner (1/0)',
        'is_vacant'           => 'Vacant (1/0)',
        'is_pre_foreclosure'  => 'Pre-Foreclosure (1/0)',
        'is_tax_delinquent'   => 'Tax Delinquent (1/0)',
        'is_probate'          => 'Probate (1/0)',
        'is_tired_landlord'   => 'Tired Landlord (1/0)',
        'is_high_equity'      => 'High Equity (1/0)',
        // Skip
        '_skip'               => '— Skip this column —',
    ];

    // ── Upload ────────────────────────────────────────────────────────────────

    /**
     * Validate and store an uploaded CSV file.
     * Returns ['job_id' => int, 'filename' => string, 'headers' => array, 'preview' => array]
     */
    public static function handleUpload(array $file, string $sourceType = 'custom', int $userId = 0): array
    {
        // Validate upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload error code: ' . $file['error']);
        }
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            throw new RuntimeException('File exceeds maximum size of ' . (UPLOAD_MAX_SIZE / 1024 / 1024) . ' MB.');
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, UPLOAD_ALLOWED_TYPES, true) && !str_ends_with($file['name'], '.csv')) {
            throw new RuntimeException('Only CSV files are allowed.');
        }

        // Store the file
        $dir = UPLOAD_PATH;
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $stored = uniqid('import_', true) . '.csv';
        $dest   = $dir . '/' . $stored;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('Failed to save uploaded file.');
        }

        // Read preview
        [$headers, $preview, $totalRows] = self::readPreview($dest);

        // Detect preset map
        $preset    = self::detectPreset($headers);
        $fieldMap  = self::applyPreset($headers, $preset);

        // Create import job
        Database::query(
            'INSERT INTO import_jobs (user_id, filename, original_name, source_type, total_rows, status)
             VALUES (?,?,?,?,?,?)',
            [$userId ?: Auth::id(), $stored, $file['name'], $sourceType, $totalRows, 'pending']
        );
        $jobId = (int)Database::lastInsertId();

        return [
            'job_id'    => $jobId,
            'filename'  => $stored,
            'headers'   => $headers,
            'preview'   => $preview,
            'total'     => $totalRows,
            'preset'    => $preset,
            'field_map' => $fieldMap,
        ];
    }

    /** Run the actual import for a job (called after field-map confirmation) */
    public static function runImport(int $jobId, array $fieldMap, string $duplicateAction = 'skip'): array
    {
        $job = Database::fetchOne('SELECT * FROM import_jobs WHERE id = ?', [$jobId]);
        if (!$job) throw new RuntimeException("Import job {$jobId} not found.");

        $file = UPLOAD_PATH . '/' . $job['filename'];
        if (!file_exists($file)) throw new RuntimeException('Upload file not found.');

        // Mark as processing
        Database::query(
            'UPDATE import_jobs SET status = ?, started_at = NOW(), field_map = ? WHERE id = ?',
            ['processing', json_encode($fieldMap), $jobId]
        );

        $counters = ['imported' => 0, 'updated' => 0, 'skipped' => 0, 'error' => 0];
        $errors   = [];
        $rowNum   = 0;

        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle); // skip header row
        $headers = array_map('trim', $headers);

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) !== count($headers)) {
                $counters['error']++;
                $errors[] = "Row {$rowNum}: column count mismatch";
                continue;
            }

            $raw  = array_combine($headers, $row);
            $data = self::mapRow($raw, $fieldMap);

            if (empty($data['address']) || empty($data['zip'])) {
                $counters['skipped']++;
                self::logRow($jobId, null, $rowNum, $raw, 'skipped', 'Missing address or zip');
                continue;
            }

            try {
                // Duplicate check: by address+zip first, then APN
                $existing = Lead::findByAddressZip($data['address'], $data['zip']);
                if (!$existing && !empty($data['apn'])) {
                    $existing = Lead::findByApn($data['apn']);
                }

                if ($existing) {
                    if ($duplicateAction === 'update') {
                        Lead::update($existing['id'], $data);
                        $counters['updated']++;
                        self::logRow($jobId, $existing['id'], $rowNum, $raw, 'updated');
                    } else {
                        $counters['skipped']++;
                        self::logRow($jobId, $existing['id'], $rowNum, $raw, 'skipped', 'Duplicate');
                    }
                } else {
                    $leadId = Lead::create($data);
                    // Auto-score
                    try { LeadScoringService::scoreAndSave($leadId); } catch (Throwable $se) {}
                    $counters['imported']++;
                    self::logRow($jobId, $leadId, $rowNum, $raw, 'imported');
                }
            } catch (Throwable $e) {
                $counters['error']++;
                $errors[] = "Row {$rowNum}: " . $e->getMessage();
                self::logRow($jobId, null, $rowNum, $raw, 'error', $e->getMessage());
                Logger::error("Import row {$rowNum} error: " . $e->getMessage());
            }
        }

        fclose($handle);

        // Mark completed
        Database::query(
            'UPDATE import_jobs SET status="completed", completed_at=NOW(),
             imported_rows=?, updated_rows=?, skipped_rows=?, error_rows=?, error_log=?
             WHERE id=?',
            [
                $counters['imported'], $counters['updated'],
                $counters['skipped'],  $counters['error'],
                $errors ? implode("\n", array_slice($errors, 0, 50)) : null,
                $jobId,
            ]
        );

        Logger::activity('import_completed', null, "Import job #{$jobId} completed", $counters);

        return $counters;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function readPreview(string $filepath): array
    {
        $handle  = fopen($filepath, 'r');
        $headers = array_map('trim', fgetcsv($handle));
        $preview = [];
        $total   = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $total++;
            if ($total <= 5) {
                $preview[] = array_combine($headers, array_pad($row, count($headers), ''));
            }
        }
        fclose($handle);
        return [$headers, $preview, $total];
    }

    private static function detectPreset(array $headers): string
    {
        foreach (self::PRESETS as $preset => $map) {
            $presetKeys = array_keys($map);
            $matches    = array_intersect($headers, $presetKeys);
            if (count($matches) >= min(3, count($presetKeys))) {
                return $preset;
            }
        }
        return 'custom';
    }

    private static function applyPreset(array $headers, string $preset): array
    {
        $map       = self::PRESETS[$preset] ?? [];
        $fieldMap  = [];
        foreach ($headers as $col) {
            $fieldMap[$col] = $map[$col] ?? '_skip';
        }
        return $fieldMap;
    }

    private static function mapRow(array $raw, array $fieldMap): array
    {
        $data = [];
        foreach ($fieldMap as $csvCol => $dbField) {
            if ($dbField === '_skip' || !array_key_exists($csvCol, $raw)) continue;
            $value = trim($raw[$csvCol]);

            // Normalize boolean-ish fields
            if (str_starts_with($dbField, 'is_')) {
                $value = in_array(strtolower($value), ['1','yes','true','y','x'], true) ? 1 : 0;
            }

            // Normalize numeric fields
            if (in_array($dbField, ['estimated_value','equity_estimate','loan_balance','last_sale_price','estimated_rent'])) {
                $value = (float)preg_replace('/[^0-9.]/', '', $value) ?: null;
            }
            if (in_array($dbField, ['beds','baths','sqft','year_built','ownership_years'])) {
                $value = $value !== '' ? (float)$value : null;
            }

            $data[$dbField] = $value;
        }
        return $data;
    }

    private static function logRow(int $jobId, ?int $leadId, int $rowNum, array $raw, string $status, ?string $error = null): void
    {
        try {
            Database::query(
                'INSERT INTO import_rows (job_id, lead_id, row_number, raw_data, status, error_msg)
                 VALUES (?,?,?,?,?,?)',
                [$jobId, $leadId, $rowNum, json_encode($raw), $status, $error]
            );
        } catch (Throwable $e) {
            Logger::error('Failed to log import row: ' . $e->getMessage());
        }
    }

    public static function getHistory(int $limit = 20): array
    {
        return Database::fetchAll(
            'SELECT ij.*, u.name AS user_name
             FROM import_jobs ij
             LEFT JOIN users u ON u.id = ij.user_id
             ORDER BY ij.created_at DESC
             LIMIT ?',
            [$limit]
        );
    }

    public static function getJobById(int $id): ?array
    {
        return Database::fetchOne('SELECT * FROM import_jobs WHERE id = ?', [$id]);
    }
}
