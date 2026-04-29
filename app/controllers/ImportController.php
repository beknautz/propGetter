<?php
/**
 * PropIntel CRM - Import Controller
 */

class ImportController
{
    /** GET /import */
    public static function index(): void
    {
        $history = CSVImportService::getHistory(20);
        View::render('imports/index', [
            'pageTitle' => 'Import Leads',
            'history'   => $history,
        ]);
    }

    /** POST /import/upload */
    public static function upload(): void
    {
        if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] === UPLOAD_ERR_NO_FILE) {
            View::render('imports/index', [
                'pageTitle' => 'Import Leads',
                'error'     => 'Please select a CSV file to upload.',
                'history'   => CSVImportService::getHistory(20),
            ]);
            return;
        }

        $sourceType = $_POST['source_type'] ?? 'custom';

        try {
            $result = CSVImportService::handleUpload($_FILES['csv_file'], $sourceType);
            View::render('imports/map', [
                'pageTitle'   => 'Map Import Fields',
                'jobId'       => $result['job_id'],
                'headers'     => $result['headers'],
                'preview'     => $result['preview'],
                'total'       => $result['total'],
                'preset'      => $result['preset'],
                'fieldMap'    => $result['field_map'],
                'dbFields'    => CSVImportService::DB_FIELDS,
            ]);
        } catch (RuntimeException $e) {
            View::render('imports/index', [
                'pageTitle' => 'Import Leads',
                'error'     => $e->getMessage(),
                'history'   => CSVImportService::getHistory(20),
            ]);
        }
    }

    /** GET /import/:id/map */
    public static function mapFields(int $id): void
    {
        $job = CSVImportService::getJobById($id);
        if (!$job) {
            View::redirect('/import');
            return;
        }

        // Re-read the header from stored field_map
        $fieldMap = $job['field_map'] ? json_decode($job['field_map'], true) : [];
        $headers  = array_keys($fieldMap);

        View::render('imports/map', [
            'pageTitle' => 'Map Import Fields',
            'jobId'     => $id,
            'headers'   => $headers,
            'preview'   => [],
            'total'     => $job['total_rows'],
            'preset'    => $job['source_type'],
            'fieldMap'  => $fieldMap,
            'dbFields'  => CSVImportService::DB_FIELDS,
        ]);
    }

    /** POST /import/:id/run */
    public static function runImport(int $id): void
    {
        $job = CSVImportService::getJobById($id);
        if (!$job || $job['status'] === 'completed') {
            View::redirect('/import/history');
            return;
        }

        // Collect field map from form
        $fieldMap = [];
        foreach ($_POST as $k => $v) {
            if (str_starts_with($k, 'map_')) {
                $col = urldecode(substr($k, 4));
                $fieldMap[$col] = $v;
            }
        }

        $duplicateAction = $_POST['duplicate_action'] ?? 'skip';

        try {
            $counters = CSVImportService::runImport($id, $fieldMap, $duplicateAction);

            View::render('imports/result', [
                'pageTitle' => 'Import Complete',
                'counters'  => $counters,
                'jobId'     => $id,
            ]);
        } catch (Throwable $e) {
            Logger::error('Import run failed: ' . $e->getMessage());
            View::render('imports/index', [
                'pageTitle' => 'Import Leads',
                'error'     => 'Import failed: ' . $e->getMessage(),
                'history'   => CSVImportService::getHistory(20),
            ]);
        }
    }

    /** GET /import/history */
    public static function history(): void
    {
        $history = CSVImportService::getHistory(50);
        View::render('imports/history', [
            'pageTitle' => 'Import History',
            'history'   => $history,
        ]);
    }
}
