<?php
/**
 * PropIntel CRM - File-based Logger (PSR-3 inspired)
 */

class Logger
{
    private static string $logFile = '';

    private static function logFile(): string
    {
        if (!self::$logFile) {
            self::$logFile = LOG_PATH . '/app-' . date('Y-m-d') . '.log';
        }
        return self::$logFile;
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        if (APP_DEBUG) {
            self::write('DEBUG', $message, $context);
        }
    }

    private static function write(string $level, string $message, array $context): void
    {
        $dir = LOG_PATH;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $userId = Auth::id() ?? 0;
        $ip     = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $ctx    = $context ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : '';

        $line = sprintf(
            "[%s] [%s] [user:%d] [%s] %s%s\n",
            date('Y-m-d H:i:s'),
            $level,
            $userId,
            $ip,
            $message,
            $ctx
        );

        file_put_contents(self::logFile(), $line, FILE_APPEND | LOCK_EX);
    }

    /** Log an activity to the database activity_logs table */
    public static function activity(
        string $action,
        ?int $leadId      = null,
        ?string $description = null,
        array $meta       = []
    ): void {
        try {
            Database::query(
                'INSERT INTO activity_logs (lead_id, user_id, action, description, meta, ip_address)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $leadId,
                    Auth::id(),
                    $action,
                    $description,
                    $meta ? json_encode($meta) : null,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                ]
            );
        } catch (Throwable $e) {
            self::error('Failed to write activity log: ' . $e->getMessage());
        }
    }
}
