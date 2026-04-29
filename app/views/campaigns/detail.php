<?php /** @var array $campaign @var array $leads @var array $steps @var array $templates */ ?>

<div class="page-header">
    <div>
        <a href="/campaigns" class="text-muted small"><i class="bi bi-arrow-left"></i> Back to Campaigns</a>
        <h1 class="page-title mt-1"><?= View::e($campaign['name']) ?></h1>
    </div>
    <div class="d-flex gap-2">
        <?php $sc=['active'=>'success','draft'=>'secondary','paused'=>'warning','completed'=>'info','archived'=>'dark']; ?>
        <span class="badge bg-<?= $sc[$campaign['status']] ?? 'secondary' ?> fs-6"><?= ucfirst($campaign['status']) ?></span>
        <a href="/campaigns/<?= $campaign['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="/campaigns/<?= $campaign['id'] ?>/export" class="btn btn-sm btn-outline-info">
            <i class="bi bi-download me-1"></i>Mail Merge
        </a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="stat-card border-left-blue"><div class="stat-icon" style="background:rgba(37,99,235,.1)"><i class="bi bi-people text-primary"></i></div><div><div class="stat-label">Total Leads</div><div class="stat-value"><?= $campaign['total_leads'] ?></div></div></div></div>
    <div class="col-md-3"><div class="stat-card border-left-green"><div class="stat-icon" style="background:rgba(16,185,129,.1)"><i class="bi bi-envelope text-success"></i></div><div><div class="stat-label">Emails Sent</div><div class="stat-value"><?= $campaign['emails_sent'] ?></div></div></div></div>
    <div class="col-md-3"><div class="stat-card border-left-amber"><div class="stat-icon" style="background:rgba(245,158,11,.1)"><i class="bi bi-chat-dots text-warning"></i></div><div><div class="stat-label">SMS Sent</div><div class="stat-value"><?= $campaign['sms_sent'] ?></div></div></div></div>
    <div class="col-md-3"><div class="stat-card border-left-red"><div class="stat-icon" style="background:rgba(239,68,68,.1)"><i class="bi bi-file-earmark-text text-danger"></i></div><div><div class="stat-label">Letters</div><div class="stat-value"><?= $campaign['letters_sent'] ?></div></div></div></div>
</div>

<!-- Send email/sms to all leads -->
<div class="row g-3 mb-3">
    <!-- Send Email -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-envelope me-2 text-success"></i>Send Email to All Leads</div>
            <div class="card-body">
                <form hx-post="/campaigns/<?= $campaign['id'] ?>/email"
                      hx-target="#emailResult"
                      hx-swap="innerHTML">
                    <?= Auth::csrfField() ?>
                    <div class="mb-2">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control form-control-sm" required value="Regarding Your Property">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Body (HTML)</label>
                        <textarea name="body" class="form-control form-control-sm" rows="4" required
                                  placeholder="Use {{owner_name}}, {{property_address}}, etc."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-send me-1"></i>Send Emails
                    </button>
                    <div id="emailResult" class="mt-2"></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Send SMS -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-chat-dots me-2 text-primary"></i>Send SMS to All Leads</div>
            <div class="card-body">
                <form hx-post="/campaigns/<?= $campaign['id'] ?>/sms"
                      hx-target="#smsResult"
                      hx-swap="innerHTML">
                    <?= Auth::csrfField() ?>
                    <div class="mb-2">
                        <label class="form-label">SMS Message (max 160 chars)</label>
                        <textarea name="sms_body" class="form-control form-control-sm" rows="3" maxlength="160" required
                                  placeholder="Hi {{owner_name}}, this is {{sender_name}}..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-send me-1"></i>Send SMS
                    </button>
                    <div id="smsResult" class="mt-2"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Leads table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-2"></i>Campaign Leads (<?= $leads['total'] ?>)</span>
        <a href="/leads?sort=l.lead_score&dir=DESC" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-plus me-1"></i>Add Leads from Database
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover leads-table mb-0">
            <thead class="table-light">
                <tr>
                    <th>Score</th><th>Address</th><th>Owner</th><th>Phone</th><th>Status</th><th>Added</th><th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads['rows'] as $lead): ?>
                <tr>
                    <td><?= View::scoreBadge((int)$lead['lead_score']) ?></td>
                    <td>
                        <a href="/leads/<?= $lead['id'] ?>" class="fw-semibold"><?= View::e($lead['address']) ?></a>
                        <div class="text-muted small"><?= View::e($lead['city'] . ', ' . $lead['state']) ?></div>
                    </td>
                    <td><?= View::e($lead['owner_name'] ?? '—') ?></td>
                    <td class="small"><?= View::e($lead['owner_phone'] ?? '—') ?></td>
                    <td><?= View::statusBadge($lead['status']) ?></td>
                    <td class="small"><?= View::date($lead['added_at'], 'M j') ?></td>
                    <td>
                        <a href="/leads/<?= $lead['id'] ?>" class="btn btn-sm btn-outline-secondary py-0 px-2">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($leads['rows'])): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No leads assigned to this campaign.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
