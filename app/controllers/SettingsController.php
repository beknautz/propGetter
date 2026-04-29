<?php
class SettingsController
{
    public static function index(): void
    {
        Auth::requireRole('admin');

        $rows = Database::fetchAll(
            'SELECT * FROM system_settings ORDER BY group_name, id'
        );

        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['group_name']][] = $r;
        }

        $providers = Database::fetchAll(
            'SELECT * FROM api_providers ORDER BY name'
        );

        View::render('settings/index', [
            'pageTitle' => 'Settings',
            'settings'  => $settings,
            'providers' => $providers,
        ]);
    }

    public static function save(): void
    {
        Auth::requireRole('admin');
        Auth::requireCsrf();

        $rows = Database::fetchAll('SELECT setting_key, type FROM system_settings');

        foreach ($rows as $row) {
            $key  = $row['setting_key'];
            $type = $row['type'];

            if ($type === 'boolean') {
                $value = isset($_POST[$key]) ? '1' : '0';
            } else {
                $value = trim($_POST[$key] ?? '');
            }

            Database::query(
                'UPDATE system_settings SET value = ? WHERE setting_key = ?',
                [$value, $key]
            );
        }

        View::redirect('/settings', 'Settings saved.', 'success');
    }

    public static function saveProvider(int $id): void
    {
        Auth::requireRole('admin');
        Auth::requireCsrf();

        $provider = Database::fetchOne('SELECT * FROM api_providers WHERE id = ?', [$id]);
        if (!$provider) {
            View::redirect('/settings#api', 'Provider not found.', 'danger');
            return;
        }

        $fields = [];
        $params = [];

        if (isset($_POST['api_key'])) {
            $fields[] = 'api_key = ?';
            $params[]  = trim($_POST['api_key']);
        }
        if (isset($_POST['api_secret'])) {
            $fields[] = 'api_secret = ?';
            $params[]  = trim($_POST['api_secret']);
        }
        if (isset($_POST['endpoint_url'])) {
            $fields[] = 'endpoint_url = ?';
            $params[]  = trim($_POST['endpoint_url']);
        }

        $fields[]  = 'is_active = ?';
        $params[]  = isset($_POST['is_active']) ? 1 : 0;
        $params[]  = $id;

        if ($fields) {
            Database::query(
                'UPDATE api_providers SET ' . implode(', ', $fields) . ' WHERE id = ?',
                $params
            );
        }

        View::redirect('/settings#api', 'API provider updated.', 'success');
    }
}
