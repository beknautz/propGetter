<?php /** @var array $analysis @var array $lead */ ?>
<?php $a = $analysis ?? $latest ?? []; if (!$a) return; ?>

<div class="row g-3">

    <!-- Summary -->
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Investment Summary</div>
            <div class="card-body"><?= nl2br(View::e($a['summary'] ?? 'Not available.')) ?></div>
        </div>
    </div>

    <!-- Offer Range + Financials -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-currency-dollar me-2 text-success"></i>Suggested Offer Range</div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="text-center">
                        <div class="text-muted small">Low</div>
                        <div class="fs-4 fw-bold text-success"><?= View::money((float)($a['offer_range_low'] ?? 0)) ?></div>
                    </div>
                    <div class="text-muted fs-4">—</div>
                    <div class="text-center">
                        <div class="text-muted small">High</div>
                        <div class="fs-4 fw-bold text-warning"><?= View::money((float)($a['offer_range_high'] ?? 0)) ?></div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge <?= $a['brrrr_potential'] ? 'bg-success' : 'bg-secondary' ?>">
                        <i class="bi bi-arrow-repeat me-1"></i>BRRRR <?= $a['brrrr_potential'] ? 'Good' : 'Limited' ?>
                    </span>
                    <span class="badge <?= $a['flip_potential'] ? 'bg-warning text-dark' : 'bg-secondary' ?>">
                        <i class="bi bi-tools me-1"></i>Flip <?= $a['flip_potential'] ? 'Potential' : 'Limited' ?>
                    </span>
                </div>
                <?php if ($a['rental_estimate']): ?>
                <div class="mt-2 small">
                    <strong>Est. Rent:</strong> <?= View::money((float)$a['rental_estimate']) ?>/mo
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Motivation + Repairs -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-person-fill-exclamation me-2 text-warning"></i>Seller Motivation</div>
            <div class="card-body">
                <p><?= nl2br(View::e($a['motivation_est'] ?? 'Not analyzed.')) ?></p>
                <hr>
                <div class="fw-semibold small mb-1">Repair Risk Notes:</div>
                <p class="small text-muted"><?= nl2br(View::e($a['repair_notes'] ?? '—')) ?></p>
            </div>
        </div>
    </div>

    <!-- Seller Letter -->
    <?php if (!empty($a['seller_letter'])): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-envelope-paper me-2 text-info"></i>AI-Generated Seller Letter</span>
                <button class="btn btn-sm btn-outline-secondary" data-copy="#aiLetter">
                    <i class="bi bi-clipboard me-1"></i>Copy
                </button>
            </div>
            <div class="card-body">
                <pre id="aiLetter" style="white-space:pre-wrap;font-family:inherit;font-size:.875rem;"><?= View::e($a['seller_letter']) ?></pre>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- SMS + Call Script -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-chat-dots me-2 text-primary"></i>SMS Opener</span>
                <button class="btn btn-sm btn-outline-secondary" data-copy="#aiSms"><i class="bi bi-clipboard"></i></button>
            </div>
            <div class="card-body">
                <p id="aiSms" class="mb-0"><?= View::e($a['sms_opener'] ?? '—') ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-telephone me-2 text-success"></i>Cold Call Script</span>
                <button class="btn btn-sm btn-outline-secondary" data-copy="#aiScript"><i class="bi bi-clipboard"></i></button>
            </div>
            <div class="card-body" style="max-height:300px;overflow-y:auto;">
                <pre id="aiScript" style="white-space:pre-wrap;font-family:inherit;font-size:.8rem;"><?= View::e($a['call_script'] ?? '—') ?></pre>
            </div>
        </div>
    </div>

    <div class="col-12 text-muted small">
        <i class="bi bi-info-circle me-1"></i>
        Analysis generated <?= View::date($a['created_at'], 'M j, Y g:ia') ?> by <?= View::e($a['ai_provider'] ?? 'AI') ?>.
    </div>
</div>
