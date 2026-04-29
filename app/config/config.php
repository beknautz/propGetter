<?php
/**
 * PropIntel CRM - Application Configuration
 * Copy this file to config.local.php for environment-specific overrides.
 */

// ── Environment ───────────────────────────────────────────────────────────────
define('APP_ENV',  getenv('APP_ENV')  ?: 'development');   // development | production
define('APP_DEBUG', APP_ENV !== 'production');

// ── Paths ─────────────────────────────────────────────────────────────────────
define('ROOT_PATH',    dirname(__DIR__, 2));
define('APP_PATH',     ROOT_PATH . '/app');
define('PUBLIC_PATH',  ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('VIEW_PATH',    APP_PATH  . '/views');

// ── Application ───────────────────────────────────────────────────────────────
define('APP_NAME',    'PropIntel CRM');
define('APP_VERSION', '1.0.0');
define('APP_URL',     getenv('APP_URL') ?: 'http://localhost');

// ── Session ───────────────────────────────────────────────────────────────────
define('SESSION_NAME',     'propintel_session');
define('SESSION_LIFETIME', 7200);   // 2 hours

// ── Database  (override in config.local.php) ──────────────────────────────────
define('DB_HOST',    getenv('DB_HOST')    ?: '127.0.0.1');
define('DB_PORT',    getenv('DB_PORT')    ?: '3306');
define('DB_NAME',    getenv('DB_NAME')    ?: 'propintel');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');

// ── SendGrid ──────────────────────────────────────────────────────────────────
define('SENDGRID_API_KEY',   getenv('SENDGRID_API_KEY')   ?: '');
define('SENDGRID_FROM_EMAIL',getenv('SENDGRID_FROM_EMAIL')?: '');
define('SENDGRID_FROM_NAME', getenv('SENDGRID_FROM_NAME') ?: APP_NAME);

// ── Twilio ────────────────────────────────────────────────────────────────────
define('TWILIO_ACCOUNT_SID', getenv('TWILIO_ACCOUNT_SID') ?: '');
define('TWILIO_AUTH_TOKEN',  getenv('TWILIO_AUTH_TOKEN')  ?: '');
define('TWILIO_FROM_NUMBER', getenv('TWILIO_FROM_NUMBER') ?: '');

// ── AI ────────────────────────────────────────────────────────────────────────
define('OPENAI_API_KEY',  getenv('OPENAI_API_KEY')  ?: '');
define('OPENAI_MODEL',    getenv('OPENAI_MODEL')    ?: 'gpt-4o');
define('CLAUDE_API_KEY',  getenv('CLAUDE_API_KEY')  ?: '');
define('CLAUDE_MODEL',    getenv('CLAUDE_MODEL')    ?: 'claude-sonnet-4-6');
define('AI_PROVIDER',     getenv('AI_PROVIDER')     ?: 'openai');   // openai | claude

// ── Upload / storage ──────────────────────────────────────────────────────────
define('UPLOAD_MAX_SIZE',      10 * 1024 * 1024);  // 10 MB
define('UPLOAD_ALLOWED_TYPES', ['text/csv', 'application/csv', 'application/vnd.ms-excel', 'text/plain']);
define('UPLOAD_PATH',          STORAGE_PATH . '/uploads');
define('LOG_PATH',             STORAGE_PATH . '/logs');
define('EXPORT_PATH',          STORAGE_PATH . '/exports');

// ── Pagination ────────────────────────────────────────────────────────────────
define('ITEMS_PER_PAGE', 25);

// ── CSRF ─────────────────────────────────────────────────────────────────────
define('CSRF_TOKEN_NAME', '_csrf_token');

// ── Load local overrides if present ──────────────────────────────────────────
$localConfig = __DIR__ . '/config.local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}

// ── Error reporting ───────────────────────────────────────────────────────────
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ── Timezone ──────────────────────────────────────────────────────────────────
date_default_timezone_set(getenv('TZ') ?: 'America/Los_Angeles');
