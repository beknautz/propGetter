<?php /** @var array $lead @var array $notes @var array $tasks @var array $activity @var array $campaigns @var array $statuses @var array $taskTypes */ ?>

<div class="page-header">
    <div>
        <a href="/leads" class="text-muted small"><i class="bi bi-arrow-left"></i> Back to Leads</a>
        <h1 class="page-title mt-1"><?= View::e($lead['address']) ?></h1>
        <div class="text-muted"><?= View::e($lead['city'] . ', ' . $lead['state'] . ' ' . $lead['zip']) ?></div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?= View::scoreBadge((int)$lead['lead_score']) ?>
        <?= View::statusBadge($lead['status']) ?>
        <a href="/leads/<?= $lead['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="/leads/<?= $lead['id'] ?>/letter" class="btn btn-sm btn-outline-info">
            <i class="bi bi-file-earmark-text me-1"></i>Letter
        </a>
        <a href="/leads/<?= $lead['id'] ?>/analyze" class="btn btn-sm btn-outline-purple">
            <i class="bi bi-robot me-1"></i>AI Analysis
        </a>
        <a href="/leads/<?= $lead['id'] ?>/calculator" class="btn btn-sm btn-outline-success">
            <i class="bi bi-calculator me-1"></i>Calculator
        </a>
    </div>
</div>

<div class="row g-3">

    <!-- Left column: Property + Owner info -->
    <div class="col-lg-4">

        <!-- Score ring card -->
        <div class="card mb-3">
            <div class="card-body text-center py-4">
                <svg width="120" height="120" viewBox="0 0 120 120" style="transform:rotate(-90deg)">
                    <circle cx="60" cy="60" r="45" fill="none" stroke="#e5e7eb" stroke-width="10"/>
                    <circle id="scoreRing" cx="60" cy="60" r="45" fill="none"
                            stroke="#3b82f6" stroke-width="10"
                            stroke-dasharray="<?= 2 * M_PI * 45 ?>"
                            stroke-dashoffset="<?= 2 * M_PI * 45 * (1 - $lead['lead_score'] / 100) ?>"
                            stroke-linecap="round"
                            style="transition:stroke-dashoffset .6s ease;"/>
                </svg>
                <div style="margin-top:-80px;font-size:2rem;font-weight:800;" id="scoreLabel">
                    <?= $lead['lead_score'] ?>
                </div>
                <div class="text-muted small mt-2">Lead Score</div>
                <?php if ($lead['score_reason']): ?>
                <div class="text-muted small mt-1 px-2"><?= View::e($lead['score_reason']) ?></div>
                <?php endif; ?>
                <form hx-post="/leads/<?= $lead['id'] ?>/score"
                      hx-target="#scoreLabel"
                      class="mt-2">
                    <?= Auth::csrfField() ?>
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Rescore
                    </button>
                </form>
            </div>
        </div>

        <!-- Status update (HTMX inline) -->
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-arrow-left-right me-2"></i>Status</div>
            <div class="card-body">
                <form hx-post="/leads/<?= $lead['id'] ?>/status"
                      hx-target="#statusDisplay"
                      class="d-flex gap-2 align-items-center">
                    <?= Auth::csrfField() ?>
                    <select name="status" class="form-select form-select-sm">
                        <?php foreach ($statuses as $s): ?>
                        <option value="<?= View::e($s) ?>" <?= $lead['status'] === $s ? 'selected' : '' ?>><?= View::e($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-sm btn-primary">Update</button>
                </form>
                <div class="mt-2" id="statusDisplay">
                    <?= View::statusBadge($lead['status']) ?>
                </div>
            </div>
        </div>

        <!-- Property Info -->
        <div class="detail-section mb-3">
            <div class="detail-section-header">
                <i class="bi bi-house"></i> Property Info
            </div>
            <div class="detail-section-body">
                <div class="row g-2">
                    <?php $propFields = [
                        'Property Type' => $lead['property_type'],
                        'Beds / Baths'  => ($lead['beds'] ?? '—') . ' / ' . ($lead['baths'] ?? '—'),
                        'Sq Ft'         => $lead['sqft'] ? number_format($lead['sqft']) . ' sqft' : '—',
                        'Lot Size'      => $lead['lot_size'] ?? '—',
                        'Year Built'    => $lead['year_built'] ?? '—',
                        'APN'           => $lead['apn'] ?? '—',
                        'County'        => $lead['county'] ?? '—',
                    ]; ?>
                    <?php foreach ($propFields as $label => $val): ?>
                    <div class="col-6">
                        <div class="detail-label"><?= $label ?></div>
                        <div class="detail-value"><?= View::e((string)$val) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Financials -->
        <div class="detail-section mb-3">
            <div class="detail-section-header"><i class="bi bi-currency-dollar"></i> Financials</div>
            <div class="detail-section-body">
                <div class="row g-2">
                    <?php $finFields = [
                        'Est. Value'    => View::money($lead['estimated_value']),
                        'Est. Rent/mo'  => View::money($lead['estimated_rent']),
                        'Loan Balance'  => View::money($lead['loan_balance']),
                        'Est. Equity'   => View::money($lead['equity_estimate']),
                        'Last Sale'     => View::date($lead['last_sale_date']),
                        'Last Sale $'   => View::money($lead['last_sale_price']),
                    ]; ?>
                    <?php foreach ($finFields as $label => $val): ?>
                    <div class="col-6">
                        <div class="detail-label"><?= $label ?></div>
                        <div class="detail-value fw-semibold"><?= $val ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Flags -->
        <div class="detail-section mb-3">
            <div class="detail-section-header"><i class="bi bi-flag"></i> Lead Flags</div>
            <div class="detail-section-body">
                <?php $flags = [
                    'Absentee Owner'  => $lead['is_absentee_owner'],
                    'Vacant'          => $lead['is_vacant'],
                    'Pre-Foreclosure' => $lead['is_pre_foreclosure'],
                    'Tax Delinquent'  => $lead['is_tax_delinquent'],
                    'Probate'         => $lead['is_probate'],
                    'Tired Landlord'  => $lead['is_tired_landlord'],
                    'High Equity'     => $lead['is_high_equity'],
                    'MLS Listed'      => $lead['is_mls_listed'],
                ]; ?>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($flags as $label => $val): ?>
                    <span class="badge <?= $val ? 'bg-danger' : 'bg-light text-muted border' ?>">
                        <i class="bi <?= $val ? 'bi-check-circle-fill' : 'bi-dash-circle' ?> me-1"></i>
                        <?= $label ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Right column: Owner, notes, tasks, activity -->
    <div class="col-lg-8">

        <!-- Owner Info -->
        <div class="detail-section mb-3">
            <div class="detail-section-header"><i class="bi bi-person"></i> Owner Information</div>
            <div class="detail-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="detail-label">Owner Name</div>
                        <div class="detail-value fw-semibold"><?= View::e($lead['owner_name'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value">
                            <?php if ($lead['owner_phone']): ?>
                            <a href="tel:<?= View::e($lead['owner_phone']) ?>"><?= View::e($lead['owner_phone']) ?></a>
                            <?php else: ?>—<?php endif; ?>
                            <?php if ($lead['owner_phone2']): ?>
                            &nbsp; <a href="tel:<?= View::e($lead['owner_phone2']) ?>"><?= View::e($lead['owner_phone2']) ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">
                            <?php if ($lead['owner_email']): ?>
                            <a href="mailto:<?= View::e($lead['owner_email']) ?>"><?= View::e($lead['owner_email']) ?></a>
                            <?php else: ?>—<?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-label">Mailing Address</div>
                        <div class="detail-value">
                            <?php if ($lead['mailing_address']): ?>
                            <?= View::e($lead['mailing_address']) ?><br>
                            <?= View::e($lead['mailing_city'] . ', ' . $lead['mailing_state'] . ' ' . $lead['mailing_zip']) ?>
                            <?php else: ?>—<?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="detail-label">Ownership</div>
                        <div class="detail-value"><?= $lead['ownership_years'] ? $lead['ownership_years'] . ' years' : '—' ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="detail-label">Owner Occupied</div>
                        <div class="detail-value"><?= $lead['owner_occupied'] ? 'Yes' : 'No' ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="detail-label">Out-of-State</div>
                        <div class="detail-value"><?= $lead['out_of_state_owner'] ? 'Yes' : 'No' ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="detail-label">Skip Traced</div>
                        <div class="detail-value"><?= $lead['skip_traced'] ? '✓ ' . View::date($lead['skip_trace_date']) : 'No' ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outreach stats -->
        <div class="row g-2 mb-3">
            <div class="col-4">
                <div class="card text-center py-3">
                    <div class="fs-4 fw-bold text-success"><?= $emailCount ?></div>
                    <div class="small text-muted">Emails Sent</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card text-center py-3">
                    <div class="fs-4 fw-bold text-primary"><?= $smsCount ?></div>
                    <div class="small text-muted">SMS Sent</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card text-center py-3">
                    <div class="fs-4 fw-bold text-secondary"><?= $letterCount ?></div>
                    <div class="small text-muted">Letters</div>
                </div>
            </div>
        </div>

        <!-- Tasks -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-check2-square me-2"></i>Tasks</span>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                    <i class="bi bi-plus"></i> Add Task
                </button>
            </div>
            <div class="card-body" id="task-list-<?= $lead['id'] ?>">
                <?php include __DIR__ . '/partials/task_list.php'; ?>
            </div>
        </div>

        <!-- Notes -->
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-chat-left-text me-2"></i>Notes & Activity</div>
            <div class="card-body">
                <!-- Add note form (HTMX) -->
                <form hx-post="/leads/<?= $lead['id'] ?>/note"
                      hx-target="#notes-timeline"
                      hx-swap="innerHTML"
                      class="mb-3">
                    <?= Auth::csrfField() ?>
                    <div class="d-flex gap-2 mb-2">
                        <select name="note_type" class="form-select form-select-sm" style="width:auto;">
                            <option value="general">General</option>
                            <option value="call">Call</option>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                            <option value="visit">Visit</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <textarea name="note" class="form-control" rows="2" placeholder="Add a note…" required></textarea>
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-plus-circle me-1"></i>Add
                        </button>
                    </div>
                </form>

                <div id="notes-timeline">
                    <?php include __DIR__ . '/partials/notes_timeline.php'; ?>
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-activity me-2"></i>Activity Log</div>
            <div class="card-body p-0">
                <?php if (empty($activity)): ?>
                <p class="text-muted text-center py-3">No activity recorded.</p>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($activity as $act): ?>
                    <li class="list-group-item d-flex align-items-start gap-2 px-3 py-2">
                        <i class="bi <?= ActivityLog::icon($act['action']) ?> mt-1"></i>
                        <div class="flex-grow-1">
                            <span class="fw-semibold small"><?= ActivityLog::label($act['action']) ?></span>
                            <?php if ($act['description']): ?>
                            <div class="text-muted small"><?= View::e($act['description']) ?></div>
                            <?php endif; ?>
                        </div>
                        <span class="text-muted small text-nowrap"><?= View::date($act['created_at'], 'M j, g:ia') ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form hx-post="/leads/<?= $lead['id'] ?>/task"
                  hx-target="#task-list-<?= $lead['id'] ?>"
                  hx-swap="innerHTML"
                  hx-on::after-request="bootstrap.Modal.getInstance(document.getElementById('addTaskModal')).hide()">
                <?= Auth::csrfField() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Task Title *</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Call owner">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Task Type</label>
                            <select name="task_type" class="form-select">
                                <?php foreach ($taskTypes as $tt): ?>
                                <option value="<?= View::e($tt) ?>"><?= View::e($tt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control"
                                   value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="form-label">Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $u['id'] == Auth::id() ? 'selected' : '' ?>>
                                <?= View::e($u['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mt-2">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
