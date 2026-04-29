<?php
/**
 * PropIntel CRM - Front Controller
 *
 * All HTTP requests are routed here via .htaccess.
 */

declare(strict_types=1);

// ── Bootstrap ─────────────────────────────────────────────────────────────────
require_once dirname(__DIR__) . '/app/config/config.php';
require_once APP_PATH . '/config/database.php';
require_once APP_PATH . '/core/Auth.php';
require_once APP_PATH . '/core/Logger.php';
require_once APP_PATH . '/core/View.php';
require_once APP_PATH . '/core/Validator.php';
require_once APP_PATH . '/core/Router.php';
require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/Lead.php';
require_once APP_PATH . '/models/Campaign.php';
require_once APP_PATH . '/models/Task.php';
require_once APP_PATH . '/models/ActivityLog.php';
require_once APP_PATH . '/services/LeadScoringService.php';
require_once APP_PATH . '/services/CSVImportService.php';
require_once APP_PATH . '/services/SendGridService.php';
require_once APP_PATH . '/services/TwilioService.php';
require_once APP_PATH . '/services/AIService.php';
require_once APP_PATH . '/controllers/DashboardController.php';
require_once APP_PATH . '/controllers/LeadController.php';
require_once APP_PATH . '/controllers/ImportController.php';
require_once APP_PATH . '/controllers/CampaignController.php';
require_once APP_PATH . '/controllers/TaskController.php';
require_once APP_PATH . '/controllers/AIController.php';
require_once APP_PATH . '/controllers/CalculatorController.php';
require_once APP_PATH . '/controllers/UserController.php';
require_once APP_PATH . '/controllers/SettingsController.php';

// Start session
Auth::startSession();

// Share current user with all views
View::share('currentUser', Auth::user());
View::share('appName',     APP_NAME);

// ── Routes ────────────────────────────────────────────────────────────────────
$router = new Router();
$auth   = [Router::authMiddleware()];
$csrf   = [Router::authMiddleware(), Router::csrfMiddleware()];
$admin      = [Router::roleMiddleware('admin')];
$adminCsrf  = [Router::roleMiddleware('admin'), Router::csrfMiddleware()];

// Dashboard
$router->get('/',          fn() => DashboardController::index(),  $auth);
$router->get('/dashboard', fn() => DashboardController::index(),  $auth);

// Leads
$router->get('/leads',                fn()       => LeadController::index(),             $auth);
$router->get('/leads/create',         fn()       => LeadController::create(),            $auth);
$router->post('/leads/create',        fn()       => LeadController::store(),             $csrf);
$router->get('/leads/:id',            fn($p)     => LeadController::show((int)$p['id']), $auth);
$router->get('/leads/:id/edit',       fn($p)     => LeadController::edit((int)$p['id']), $auth);
$router->post('/leads/:id/edit',      fn($p)     => LeadController::update((int)$p['id']), $csrf);
$router->post('/leads/:id/delete',    fn($p)     => LeadController::destroy((int)$p['id']), $csrf);
$router->post('/leads/:id/status',    fn($p)     => LeadController::updateStatus((int)$p['id']), $csrf);
$router->post('/leads/:id/note',      fn($p)     => LeadController::addNote((int)$p['id']), $csrf);
$router->post('/leads/:id/task',      fn($p)     => LeadController::addTask((int)$p['id']), $csrf);
$router->post('/leads/:id/score',     fn($p)     => LeadController::rescore((int)$p['id']), $csrf);
$router->get('/leads/search',         fn()       => LeadController::search(),            $auth);

// Import
$router->get('/import',               fn()       => ImportController::index(),           $auth);
$router->post('/import/upload',       fn()       => ImportController::upload(),          $csrf);
$router->get('/import/:id/map',       fn($p)     => ImportController::mapFields((int)$p['id']), $auth);
$router->post('/import/:id/run',      fn($p)     => ImportController::runImport((int)$p['id']), $csrf);
$router->get('/import/history',       fn()       => ImportController::history(),         $auth);

// Campaigns
$router->get('/campaigns',            fn()       => CampaignController::index(),         $auth);
$router->get('/campaigns/create',     fn()       => CampaignController::create(),        $auth);
$router->post('/campaigns/create',    fn()       => CampaignController::store(),         $csrf);
$router->get('/campaigns/:id',        fn($p)     => CampaignController::show((int)$p['id']), $auth);
$router->get('/campaigns/:id/edit',   fn($p)     => CampaignController::edit((int)$p['id']), $auth);
$router->post('/campaigns/:id/edit',  fn($p)     => CampaignController::update((int)$p['id']), $csrf);
$router->post('/campaigns/:id/leads', fn($p)     => CampaignController::addLeads((int)$p['id']), $csrf);
$router->post('/campaigns/:id/email', fn($p)     => CampaignController::sendEmail((int)$p['id']), $csrf);
$router->post('/campaigns/:id/sms',   fn($p)     => CampaignController::sendSms((int)$p['id']), $csrf);
$router->get('/campaigns/:id/export', fn($p)     => CampaignController::exportMailMerge((int)$p['id']), $auth);

// Letters
$router->get('/letters',              fn()       => CampaignController::letters(),       $auth);
$router->get('/leads/:id/letter',     fn($p)     => CampaignController::generateLetter((int)$p['id']), $auth);
$router->post('/leads/:id/letter',    fn($p)     => CampaignController::saveLetter((int)$p['id']), $csrf);

// Tasks
$router->get('/tasks',                fn()       => TaskController::index(),             $auth);
$router->post('/tasks/:id/complete',  fn($p)     => TaskController::complete((int)$p['id']), $csrf);
$router->post('/tasks/:id/status',    fn($p)     => TaskController::updateStatus((int)$p['id']), $csrf);
$router->post('/tasks/:id/delete',    fn($p)     => TaskController::destroy((int)$p['id']), $csrf);

// AI
$router->get('/leads/:id/analyze',    fn($p)     => AIController::analyze((int)$p['id']), $auth);
$router->post('/leads/:id/analyze',   fn($p)     => AIController::runAnalysis((int)$p['id']), $csrf);

// Calculator
$router->get('/leads/:id/calculator', fn($p)     => CalculatorController::show((int)$p['id']), $auth);
$router->post('/leads/:id/calculator',fn($p)     => CalculatorController::calculate((int)$p['id']), $csrf);

// Users (admin only)
$router->get('/users',                  fn()   => UserController::index(),              $admin);
$router->get('/users/create',           fn()   => UserController::create(),             $admin);
$router->post('/users/create',          fn()   => UserController::store(),              $adminCsrf);
$router->get('/users/:id/edit',         fn($p) => UserController::edit((int)$p['id']), $admin);
$router->post('/users/:id/edit',        fn($p) => UserController::update((int)$p['id']), $adminCsrf);
$router->post('/users/:id/delete',      fn($p) => UserController::destroy((int)$p['id']), $adminCsrf);

// Settings (admin only)
$router->get('/settings',               fn()   => SettingsController::index(),          $admin);
$router->post('/settings/save',         fn()   => SettingsController::save(),           $adminCsrf);
$router->post('/settings/provider/:id', fn($p) => SettingsController::saveProvider((int)$p['id']), $adminCsrf);

// Twilio inbound webhook (no auth - verified by signature)
$router->post('/webhooks/twilio/sms', fn() => (new TwilioService())->handleInbound($_POST));
$router->post('/webhooks/sendgrid',   fn() => (new SendGridService())->handleWebhook(file_get_contents('php://input')));

// ── Dispatch ──────────────────────────────────────────────────────────────────
try {
    $router->dispatch();
} catch (Throwable $e) {
    Logger::error('Unhandled exception: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);

    if (APP_DEBUG) {
        echo '<pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>';
    } else {
        http_response_code(500);
        include VIEW_PATH . '/errors/500.php';
    }
}
