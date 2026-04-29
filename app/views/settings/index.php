<?php /** @var array $settings @var array $providers */ ?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-gear me-2 text-primary"></i>Settings</h1>
</div>

<?= View::flashHtml() ?>

<ul class="nav nav-tabs mb-4" id="settingsTabs">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#general">General</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#outreach">Outreach</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ai">AI &amp; Scoring</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#api">API Keys</a></li>
</ul>

<!-- ── System Settings Form ─────────────────────────────────────────────────── -->
<form method="POST" action="/settings/save">
    <?= Auth::csrfField() ?>

    <div class="tab-content">

        <!-- General -->
        <div class="tab-pane fade show active" id="general">
            <div class="card">
                <div class="card-header fw-semibold">Company &amp; Display</div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach (array_merge($settings['general'] ?? [], $settings['display'] ?? []) as $s): ?>
                        <div class="col-md-6">
                            <?= settingField($s) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </div>

        <!-- Outreach -->
        <div class="tab-pane fade" id="outreach">
            <div class="card">
                <div class="card-header fw-semibold">Email &amp; SMS Defaults</div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach (array_merge($settings['outreach'] ?? [], $settings['sendgrid'] ?? [], $settings['twilio'] ?? []) as $s): ?>
                        <div class="col-md-6">
                            <?= settingField($s) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </div>

        <!-- AI & Scoring -->
        <div class="tab-pane fade" id="ai">
            <div class="card">
                <div class="card-header fw-semibold">AI Provider &amp; Lead Scoring</div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach (array_merge($settings['ai'] ?? [], $settings['scoring'] ?? []) as $s): ?>
                        <div class="col-md-6">
                            <?= settingField($s) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </div>

        <!-- API Keys (separate forms per provider) -->
        <div class="tab-pane fade" id="api">
            <div class="row g-3">
                <?php foreach ($providers as $p): ?>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="fw-semibold"><?= View::e($p['name']) ?></span>
                            <?php if ($p['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if ($p['description']): ?>
                            <p class="text-muted small mb-3"><?= View::e($p['description']) ?></p>
                            <?php endif; ?>
                            <form method="POST" action="/settings/provider/<?= $p['id'] ?>">
                                <?= Auth::csrfField() ?>

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">API Key</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" name="api_key" class="form-control form-control-sm font-monospace"
                                               value="<?= View::e($p['api_key'] ?? '') ?>"
                                               placeholder="Paste API key…"
                                               id="key-<?= $p['id'] ?>">
                                        <button type="button" class="btn btn-outline-secondary"
                                                onclick="toggleVisible('key-<?= $p['id'] ?>')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <?php if (in_array($p['slug'], ['twilio', 'propstream', 'batchleads'])): ?>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold mb-1">
                                        <?= $p['slug'] === 'twilio' ? 'Auth Token' : 'API Secret' ?>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" name="api_secret" class="form-control form-control-sm font-monospace"
                                               value="<?= View::e($p['api_secret'] ?? '') ?>"
                                               placeholder="Secret…"
                                               id="secret-<?= $p['id'] ?>">
                                        <button type="button" class="btn btn-outline-secondary"
                                                onclick="toggleVisible('secret-<?= $p['id'] ?>')">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" name="is_active" id="active-<?= $p['id'] ?>" value="1"
                                           <?= $p['is_active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="active-<?= $p['id'] ?>">Enable this provider</label>
                                </div>

                                <button type="submit" class="btn btn-sm btn-primary w-100">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div><!-- /.tab-content -->
</form>

<?php
function settingField(array $s): string {
    $key   = htmlspecialchars($s['setting_key'], ENT_QUOTES);
    $label = htmlspecialchars($s['label'] ?? $s['setting_key'], ENT_QUOTES);
    $val   = htmlspecialchars($s['value'] ?? '', ENT_QUOTES);

    if ($s['type'] === 'boolean') {
        $chk = $s['value'] ? 'checked' : '';
        return "<div class=\"form-check mt-2\">
            <input class=\"form-check-input\" type=\"checkbox\" name=\"{$key}\" id=\"{$key}\" value=\"1\" {$chk}>
            <label class=\"form-check-label fw-semibold\" for=\"{$key}\">{$label}</label>
        </div>";
    }

    return "<label class=\"form-label fw-semibold mb-1\">{$label}</label>
        <input type=\"text\" name=\"{$key}\" class=\"form-control form-control-sm\" value=\"{$val}\">";
}
?>

<script>
function toggleVisible(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}

// Activate tab from URL hash
document.addEventListener('DOMContentLoaded', function () {
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector('[href="' + hash + '"]');
        if (tab) bootstrap.Tab.getOrCreateInstance(tab).show();
    }
});
</script>
