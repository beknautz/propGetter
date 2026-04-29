<?php /** @var array $results @var array $filters @var array $statuses @var array $propTypes @var array $campaigns */ ?>

<div class="page-header">
    <h1 class="page-title"><i class="bi bi-house-door me-2 text-primary"></i>Lead Database</h1>
    <div class="d-flex gap-2">
        <a href="/leads/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Lead</a>
        <a href="/import"       class="btn btn-outline-secondary btn-sm"><i class="bi bi-upload me-1"></i>Import CSV</a>
    </div>
</div>

<!-- ── Filter Panel ─────────────────────────────────────────────────────────── -->
<div class="filter-panel">
    <form id="filterForm"
          hx-get="/leads"
          hx-target="#leadResults"
          hx-trigger="change, submit"
          hx-indicator="#filterSpinner"
          hx-push-url="true">

        <div class="row g-2 align-items-end">
            <!-- Search -->
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Address, owner, ZIP…"
                       value="<?= View::e($filters['q'] ?? '') ?>"
                       hx-trigger="keyup changed delay:400ms">
            </div>

            <!-- Status -->
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $s): ?>
                    <option value="<?= View::e($s) ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= View::e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Property type -->
            <div class="col-md-2">
                <label class="form-label">Property Type</label>
                <select name="property_type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <?php foreach ($propTypes as $pt): ?>
                    <option value="<?= View::e($pt) ?>" <?= ($filters['property_type'] ?? '') === $pt ? 'selected' : '' ?>><?= View::e($pt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- ZIP -->
            <div class="col-md-1">
                <label class="form-label">ZIP</label>
                <input type="text" name="zip" class="form-control form-control-sm"
                       value="<?= View::e($filters['zip'] ?? '') ?>" placeholder="ZIP">
            </div>

            <!-- Score range -->
            <div class="col-md-2">
                <label class="form-label">Score Range</label>
                <div class="input-group input-group-sm">
                    <input type="number" name="score_min" class="form-control" placeholder="Min"
                           value="<?= View::e($filters['score_min'] ?? '') ?>" min="0" max="100">
                    <span class="input-group-text">–</span>
                    <input type="number" name="score_max" class="form-control" placeholder="Max"
                           value="<?= View::e($filters['score_max'] ?? '') ?>" min="0" max="100">
                </div>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Search
                    <span id="filterSpinner" class="htmx-indicator ms-1">
                        <i class="bi bi-arrow-clockwise spin"></i>
                    </span>
                </button>
            </div>
        </div>

        <!-- Flag filters -->
        <div class="row g-2 mt-1">
            <div class="col-auto">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="absentee_owner" value="1"
                           id="f_absentee" <?= !empty($filters['absentee_owner']) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="f_absentee">Absentee Owner</label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="vacant" value="1"
                           id="f_vacant" <?= !empty($filters['vacant']) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="f_vacant">Vacant</label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="pre_foreclosure" value="1"
                           id="f_preforeclosure" <?= !empty($filters['pre_foreclosure']) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="f_preforeclosure">Pre-Foreclosure</label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="tax_delinquent" value="1"
                           id="f_tax" <?= !empty($filters['tax_delinquent']) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="f_tax">Tax Delinquent</label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="probate" value="1"
                           id="f_probate" <?= !empty($filters['probate']) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="f_probate">Probate</label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="high_equity" value="1"
                           id="f_highequity" <?= !empty($filters['high_equity']) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="f_highequity">High Equity</label>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="follow_up_due" value="1"
                           id="f_followup" <?= !empty($filters['follow_up_due']) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="f_followup">Follow-Up Due</label>
                </div>
            </div>
            <div class="col-auto ms-auto">
                <a href="/leads" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Clear Filters
                </a>
            </div>
        </div>
    </form>
</div>

<!-- ── Results Table ──────────────────────────────────────────────────────── -->
<div id="leadResults">
    <?php include __DIR__ . '/partials/results_table.php'; ?>
</div>
