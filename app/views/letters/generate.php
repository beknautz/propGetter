<?php /** @var array $lead @var array $templates @var array $settings */ ?>
<div class="page-header">
    <div>
        <a href="/leads/<?= $lead['id'] ?>" class="text-muted small"><i class="bi bi-arrow-left"></i> Back to Lead</a>
        <h1 class="page-title mt-1">Generate Letter</h1>
        <div class="text-muted"><?= View::e($lead['address'] . ', ' . $lead['city']) ?></div>
    </div>
</div>
<div class="row g-3">
<div class="col-lg-5">
<div class="card">
    <div class="card-header"><i class="bi bi-gear me-2"></i>Letter Settings</div>
    <div class="card-body">
        <form method="POST" action="/leads/<?= $lead['id'] ?>/letter" target="_blank">
            <?= Auth::csrfField() ?>
            <div class="mb-3">
                <label class="form-label">Template</label>
                <select name="template_id" class="form-select" required>
                    <?php foreach ($templates as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= View::e($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Sender Name</label>
                <input type="text" name="sender_name" class="form-control"
                       value="<?= View::e($settings['sender_name'] ?? Auth::name()) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Sender Phone</label>
                <input type="tel" name="sender_phone" class="form-control"
                       value="<?= View::e($settings['company_phone'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Custom Message (optional)</label>
                <textarea name="custom_message" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-file-earmark-text me-2"></i>Generate & Preview Letter
            </button>
        </form>
    </div>
</div>
</div>
<div class="col-lg-7">
<div class="card">
    <div class="card-header"><i class="bi bi-info-circle me-2"></i>Lead Info (will be merged into letter)</div>
    <div class="card-body">
        <div class="row g-2">
            <?php $fields = [
                'Owner Name'     => $lead['owner_name']     ?? '—',
                'Property'       => $lead['address'] . ', ' . $lead['city'],
                'Mailing Address'=> ($lead['mailing_address'] ?? $lead['address']) . ', ' . ($lead['mailing_city'] ?? $lead['city']),
                'Equity Est.'    => View::money($lead['equity_estimate']),
            ]; ?>
            <?php foreach ($fields as $label => $val): ?>
            <div class="col-6"><div class="detail-label"><?= $label ?></div><div class="detail-value"><?= View::e($val) ?></div></div>
            <?php endforeach; ?>
        </div>
        <hr>
        <h6>Available Variables</h6>
        <div class="d-flex flex-wrap gap-1">
            <?php foreach (['owner_name','property_address','city','mailing_address','estimated_equity','sender_name','sender_phone','today_date','custom_message'] as $v): ?>
            <code class="badge bg-light text-dark border">{{<?= $v ?>}}</code>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div>
</div>
