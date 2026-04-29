<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Auth::csrfToken() ?>">
    <title><?= View::e($pageTitle ?? 'Dashboard') ?> — <?= APP_NAME ?></title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- App CSS -->
    <link rel="stylesheet" href="/assets/css/app.css">
    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.12" defer></script>
</head>
<body hx-headers='{"X-CSRF-Token": "<?= Auth::csrfToken() ?>"}'>

<div id="wrapper">

    <!-- ── Sidebar ──────────────────────────────────────────── -->
    <nav id="sidebar">
        <div class="sidebar-brand">
            <a href="/" class="brand-text d-block">Prop<span>Intel</span></a>
            <div style="font-size:.7rem;color:#6b7280;margin-top:2px;">Real Estate CRM</div>
        </div>

        <div class="pt-2 pb-2">
            <div class="nav-section-label">Main</div>
            <a href="/"          class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="/leads"     class="nav-link"><i class="bi bi-house-door"></i> Lead Database</a>
            <a href="/tasks"     class="nav-link">
                <i class="bi bi-check2-square"></i> Tasks
                <?php
                $overdueCount = Task::overdueCount(Auth::id());
                if ($overdueCount > 0):
                ?>
                <span class="badge bg-danger ms-auto"><?= $overdueCount ?></span>
                <?php endif; ?>
            </a>

            <div class="nav-section-label mt-2">Outreach</div>
            <a href="/campaigns" class="nav-link"><i class="bi bi-megaphone"></i> Campaigns</a>
            <a href="/letters"   class="nav-link"><i class="bi bi-file-earmark-text"></i> Letters</a>

            <div class="nav-section-label mt-2">Data</div>
            <a href="/import"    class="nav-link"><i class="bi bi-upload"></i> Import Leads</a>

            <?php if (Auth::hasRole(['admin'])): ?>
            <div class="nav-section-label mt-2">Admin</div>
            <a href="/admin/users"    class="nav-link"><i class="bi bi-people"></i> Users</a>
            <a href="/admin/settings" class="nav-link"><i class="bi bi-gear"></i> Settings</a>
            <?php endif; ?>
        </div>

        <div class="sidebar-footer mt-auto">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                     style="width:30px;height:30px;font-size:.8rem;flex-shrink:0;">
                    <?= strtoupper(substr(Auth::name(), 0, 1)) ?>
                </div>
                <div>
                    <div style="font-size:.8rem;color:#e5e7eb;font-weight:600;"><?= View::e(Auth::name()) ?></div>
                    <div style="font-size:.7rem;color:#6b7280;"><?= View::e(Auth::role()) ?></div>
                </div>
                <a href="/logout.php" class="ms-auto text-muted" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- ── Main Content ─────────────────────────────────────── -->
    <div id="main-content">

        <!-- Top Bar -->
        <div id="topbar">
            <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>

            <!-- Quick search -->
            <div class="search-wrap">
                <i class="bi bi-search"></i>
                <input
                    type="text"
                    id="quickSearch"
                    class="form-control search-input"
                    placeholder="Search leads, addresses, owners…"
                    autocomplete="off"
                    hx-get="/leads/search"
                    hx-trigger="keyup changed delay:300ms"
                    hx-target="#quick-search-results"
                    hx-include="[name='q']"
                    name="q"
                >
                <div id="quick-search-results"></div>
            </div>

            <div class="ms-auto d-flex align-items-center gap-3">
                <!-- Add lead quick button -->
                <a href="/leads/create" class="btn btn-sm btn-primary d-none d-md-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i> Add Lead
                </a>

                <!-- User menu -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i><?= View::e(Auth::name()) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-muted"><?= View::e(Auth::user()['email']) ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <main class="page-content">
            <?php if (!empty($_GET['created'])): ?>
                <div class="alert alert-success alert-auto-dismiss alert-dismissible">
                    <i class="bi bi-check-circle me-2"></i>Record created successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif (!empty($_GET['updated'])): ?>
                <div class="alert alert-success alert-auto-dismiss alert-dismissible">
                    <i class="bi bi-check-circle me-2"></i>Record updated successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif (!empty($_GET['deleted'])): ?>
                <div class="alert alert-warning alert-auto-dismiss alert-dismissible">
                    <i class="bi bi-trash me-2"></i>Record deleted.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>
