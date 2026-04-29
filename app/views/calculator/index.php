<?php /** @var array $lead @var array $scenarios */ ?>

<div class="page-header">
    <div>
        <a href="/leads/<?= $lead['id'] ?>" class="text-muted small"><i class="bi bi-arrow-left"></i> Back to Lead</a>
        <h1 class="page-title mt-1"><i class="bi bi-calculator me-2 text-success"></i>Deal Calculator</h1>
        <div class="text-muted"><?= View::e($lead['address'] . ', ' . $lead['city'] . ', ' . $lead['state']) ?></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <!-- Calculator type tabs -->
                <div class="d-flex gap-2 flex-wrap">
                    <?php $calcs = ['buy_hold'=>'Buy & Hold','brrrr'=>'BRRRR','flip'=>'Flip','wholesale'=>'Wholesale']; ?>
                    <?php foreach ($calcs as $key => $label): ?>
                    <button class="btn btn-sm calc-tab <?= $key === 'buy_hold' ? 'btn-primary active' : 'btn-outline-secondary' ?>"
                            data-calc="<?= $key ?>">
                        <?= $label ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card-body">
                <form hx-post="/leads/<?= $lead['id'] ?>/calculator"
                      hx-target="#calcResult"
                      hx-swap="innerHTML">
                    <?= Auth::csrfField() ?>
                    <input type="hidden" name="calc_type" id="calc_type" value="buy_hold">

                    <!-- Common inputs -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Purchase Price ($)</label>
                            <input type="number" name="purchase_price" class="form-control" value="<?= View::e($lead['estimated_value'] ?? '') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">ARV ($)</label>
                            <input type="number" name="arv" class="form-control" value="<?= View::e($lead['arv_estimate'] ?? $lead['estimated_value'] ?? '') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Repairs ($)</label>
                            <input type="number" name="repairs" class="form-control" value="20000">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Closing Costs ($)</label>
                            <input type="number" name="closing_costs" class="form-control" value="3000">
                        </div>
                    </div>

                    <!-- Buy & Hold / BRRRR specific -->
                    <div id="form-buy_hold" class="calc-form">
                        <div class="row g-2">
                            <div class="col-6"><label class="form-label">Monthly Rent ($)</label><input type="number" name="rent" class="form-control" value="<?= View::e($lead['estimated_rent'] ?? '') ?>"></div>
                            <div class="col-6"><label class="form-label">Down Payment ($)</label><input type="number" name="down_payment" class="form-control"></div>
                            <div class="col-6"><label class="form-label">Interest Rate (%)</label><input type="number" name="interest_rate" class="form-control" step=".125" value="7.0"></div>
                            <div class="col-6"><label class="form-label">Loan Term (years)</label><input type="number" name="loan_term_years" class="form-control" value="30"></div>
                            <div class="col-6"><label class="form-label">Vacancy (%)</label><input type="number" name="vacancy_pct" class="form-control" value="5"></div>
                            <div class="col-6"><label class="form-label">Mgmt (%)</label><input type="number" name="mgmt_pct" class="form-control" value="8"></div>
                            <div class="col-6"><label class="form-label">Annual Taxes ($)</label><input type="number" name="taxes" class="form-control"></div>
                            <div class="col-6"><label class="form-label">Annual Insurance ($)</label><input type="number" name="insurance" class="form-control"></div>
                        </div>
                    </div>

                    <!-- BRRRR specific (hidden by default) -->
                    <div id="form-brrrr" class="calc-form d-none">
                        <div class="row g-2">
                            <div class="col-6"><label class="form-label">Monthly Rent ($)</label><input type="number" name="rent" class="form-control" value="<?= View::e($lead['estimated_rent'] ?? '') ?>"></div>
                            <div class="col-6"><label class="form-label">Holding Costs ($)</label><input type="number" name="holding_costs" class="form-control" value="2000"></div>
                            <div class="col-6"><label class="form-label">Refi LTV (%)</label><input type="number" name="refi_ltv" class="form-control" value="75"></div>
                            <div class="col-6"><label class="form-label">Interest Rate (%)</label><input type="number" name="interest_rate" class="form-control" step=".125" value="7.0"></div>
                            <div class="col-6"><label class="form-label">Vacancy (%)</label><input type="number" name="vacancy_pct" class="form-control" value="5"></div>
                            <div class="col-6"><label class="form-label">Mgmt (%)</label><input type="number" name="mgmt_pct" class="form-control" value="8"></div>
                        </div>
                    </div>

                    <!-- Flip specific -->
                    <div id="form-flip" class="calc-form d-none">
                        <div class="row g-2">
                            <div class="col-6"><label class="form-label">Holding Costs ($)</label><input type="number" name="holding_costs" class="form-control" value="5000"></div>
                        </div>
                    </div>

                    <!-- Wholesale specific -->
                    <div id="form-wholesale" class="calc-form d-none">
                        <div class="row g-2">
                            <div class="col-6"><label class="form-label">Your Assignment Fee ($)</label><input type="number" name="assignment_fee" class="form-control" value="10000"></div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <input type="text" name="scenario_name" class="form-control mb-2" placeholder="Scenario name (optional)">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-calculator me-2"></i>Calculate
                            <span class="htmx-indicator ms-1"><i class="bi bi-arrow-clockwise spin"></i></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div class="col-lg-7">
        <div id="calcResult">
            <?php if (empty($scenarios)): ?>
            <div class="card text-center py-5 text-muted">
                <i class="bi bi-calculator fs-1 d-block mb-3"></i>
                Enter values and click Calculate to see results.
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-header">Saved Scenarios</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Name</th><th>Type</th><th>Purchase</th><th>Cash Flow</th><th>COC</th><th>Flip Profit</th></tr></thead>
                        <tbody>
                            <?php foreach ($scenarios as $s): ?>
                            <tr>
                                <td><?= View::e($s['name'] ?? '—') ?></td>
                                <td><span class="badge bg-secondary"><?= ucfirst(str_replace('_',' ',$s['calc_type'])) ?></span></td>
                                <td><?= View::money($s['purchase_price']) ?></td>
                                <td class="<?= ($s['cash_flow'] ?? 0) < 0 ? 'text-danger' : 'text-success' ?>"><?= View::money($s['cash_flow']) ?>/mo</td>
                                <td><?= $s['coc_return'] ? number_format($s['coc_return'],1) . '%' : '—' ?></td>
                                <td><?= View::money($s['flip_profit']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
