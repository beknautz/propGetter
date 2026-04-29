<?php /** @var array $result @var string $type @var array $inputs */ ?>

<div class="calc-result-card mb-3">
    <div class="mb-2 text-muted small"><?= ucfirst(str_replace('_',' ',$type)) ?> Analysis</div>
    <div class="row g-3">
        <?php if ($type === 'buy_hold' || $type === 'brrrr'): ?>
        <div class="col-6 col-md-4">
            <div class="calc-metric-label">Monthly Payment</div>
            <div class="calc-metric-value"><?= View::money($result['monthly_payment'] ?? 0) ?></div>
        </div>
        <div class="col-6 col-md-4">
            <div class="calc-metric-label">Monthly Cash Flow</div>
            <div class="calc-metric-value <?= ($result['cash_flow'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                <?= View::money($result['cash_flow'] ?? 0) ?>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="calc-metric-label">Annual Cash Flow</div>
            <div class="calc-metric-value <?= ($result['annual_cash_flow'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                <?= View::money($result['annual_cash_flow'] ?? 0) ?>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="calc-metric-label">NOI</div>
            <div class="calc-metric-value"><?= View::money($result['noi'] ?? 0) ?>/yr</div>
        </div>
        <div class="col-6 col-md-4">
            <div class="calc-metric-label">Cap Rate</div>
            <div class="calc-metric-value <?= ($result['cap_rate'] ?? 0) >= 6 ? 'positive' : 'warning' ?>">
                <?= number_format($result['cap_rate'] ?? 0, 2) ?>%
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="calc-metric-label">DSCR</div>
            <div class="calc-metric-value <?= ($result['dscr'] ?? 0) >= 1.2 ? 'positive' : 'negative' ?>">
                <?= number_format($result['dscr'] ?? 0, 2) ?>x
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="calc-metric-label">Cash-on-Cash</div>
            <div class="calc-metric-value <?= ($result['coc_return'] ?? 0) >= 8 ? 'positive' : 'warning' ?>">
                <?= number_format($result['coc_return'] ?? 0, 2) ?>%
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="calc-metric-label">Cash Needed</div>
            <div class="calc-metric-value"><?= View::money($result['cash_needed'] ?? 0) ?></div>
        </div>
        <?php if ($type === 'brrrr'): ?>
        <div class="col-6 col-md-4">
            <div class="calc-metric-label">Cash Left In</div>
            <div class="calc-metric-value <?= ($result['cash_left_in'] ?? 0) == 0 ? 'positive' : '' ?>">
                <?= View::money($result['cash_left_in'] ?? 0) ?>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="calc-metric-label">Equity Created</div>
            <div class="calc-metric-value positive"><?= View::money($result['equity_created'] ?? 0) ?></div>
        </div>
        <?php endif; ?>

        <?php elseif ($type === 'flip'): ?>
        <div class="col-6 col-md-4"><div class="calc-metric-label">All-In Cost</div><div class="calc-metric-value"><?= View::money($result['all_in_cost'] ?? 0) ?></div></div>
        <div class="col-6 col-md-4"><div class="calc-metric-label">Selling Costs</div><div class="calc-metric-value"><?= View::money($result['sell_costs'] ?? 0) ?></div></div>
        <div class="col-6 col-md-4">
            <div class="calc-metric-label">Flip Profit</div>
            <div class="calc-metric-value <?= ($result['flip_profit'] ?? 0) > 0 ? 'positive' : 'negative' ?>"><?= View::money($result['flip_profit'] ?? 0) ?></div>
        </div>
        <div class="col-6 col-md-4"><div class="calc-metric-label">ROI</div><div class="calc-metric-value"><?= number_format($result['roi_pct'] ?? 0, 1) ?>%</div></div>
        <div class="col-6 col-md-4"><div class="calc-metric-label">MAO (70% Rule)</div><div class="calc-metric-value warning"><?= View::money($result['mao'] ?? 0) ?></div></div>

        <?php elseif ($type === 'wholesale'): ?>
        <div class="col-6 col-md-4"><div class="calc-metric-label">MAO</div><div class="calc-metric-value warning"><?= View::money($result['mao'] ?? 0) ?></div></div>
        <div class="col-6 col-md-4"><div class="calc-metric-label">Your Max Offer</div><div class="calc-metric-value"><?= View::money($result['your_max_offer'] ?? 0) ?></div></div>
        <div class="col-6 col-md-4">
            <div class="calc-metric-label">Potential Fee</div>
            <div class="calc-metric-value positive"><?= View::money($result['wholesale_fee'] ?? 0) ?></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($result['mao']) && $type !== 'wholesale'): ?>
        <div class="col-12 mt-2 pt-2" style="border-top:1px solid rgba(255,255,255,.1)">
            <span class="calc-metric-label">Max Allowable Offer (70% Rule): </span>
            <span class="calc-metric-value warning"><?= View::money($result['mao']) ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>
