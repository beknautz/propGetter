<?php
/**
 * PropIntel CRM - Logout
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/config/config.php';
require_once APP_PATH . '/core/Auth.php';
require_once APP_PATH . '/core/Logger.php';
require_once APP_PATH . '/config/database.php';

Auth::startSession();
Auth::logout();

header('Location: /login.php');
exit;
