<?php /** @var array $results @var array $filters */ ?>
<?php $rows = $results['rows'] ?? []; $total = $results['total'] ?? 0; ?>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span>
            <i class="bi bi-table me-2"></i>
            <?= number_format($total) ?> lead<?= $total !== 1 ? 's' : '' ?> found
        </span>
        <div class="d-flex gap-2 align-items-center">
            <select name="sort" form="filterForm" class="form-select form-select-sm" style="width:auto;">
                <option value="l.created_at" <?= ($filters['sort'] ?? '') === 'l.created_at' ? 'selected' : '' ?>>Newest First</option>
                <option value="l.lead_score"  <?= ($filters['sort'] ?? '') === 'l.lead_score'  ? 'selected' : '' ?>>Score (High→Low)</option>
                <option value="lpd.equity_estimate" <?= ($filters['sort'] ?? '') === 'lpd.equity_estimate' ? 'selected' : '' ?>>Equity (High→Low)</option>
                <option value="l.follow_up_date" <?= ($filters['sort'] ?? '') === 'l.follow_up_date' ? 'selected' : '' ?>>Follow-Up Date</option>
                <option value="l.address"    <?= ($filters['sort'] ?? '') === 'l.address'    ? 'selected' : '' ?>>Address A→Z</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover leads-table mb-0">
            <thead class="table-light">
                <tr>
                    <th>Score</th>
                    <th>Address</th>
                    <th>Owner</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Equity</th>
                    <th>Flags</th>
                    <th>Status</th>
                    <th>Follow-Up</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>No leads found. Try adjusting filters.
                    </td>
                </tr>
                <?php endif; ?>

                <?php foreach ($rows as $lead): ?>
                <tr>
                    <td>
                        <?= View::scoreBadge((int)$lead['lead_score']) ?>
                    </td>
                    <td>
                        <a href="/leads/<?= $lead['id'] ?>" class="fw-semibold text-decoration-none">
                            <?= View::e($lead['address']) ?>
                        </a>
                        <div class="text-muted small"><?= View::e($lead['city'] . ', ' . $lead['state'] . ' ' . $lead['zip']) ?></div>
                    </td>
                    <td>
                        <div><?= View::e($lead['owner_name'] ?? '—') ?></div>
                        <?php if ($lead['owner_phone']): ?>
                        <div class="text-muted small"><?= View::e($lead['owner_phone']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="small"><?= View::e($lead['property_type']) ?></td>
                    <td class="small"><?= View::money($lead['estimated_value']) ?></td>
                    <td class="small fw-semibold <?= ($lead['equity_estimate'] ?? 0) > 100000 ? 'text-success' : '' ?>">
                        <?= View::money($lead['equity_estimate']) ?>
                    </td>
                    <td>
                        <div class="flag-icons d-flex gap-1 flex-wrap">
                            <?php if ($lead['is_absentee_owner']): ?>
                                <i class="bi bi-house-x text-warning" title="Absentee Owner" data-bs-toggle="tooltip"></i>
                            <?php endif; ?>
                            <?php if ($lead['is_vacant']): ?>
                                <i class="bi bi-building-x text-danger" title="Vacant" data-bs-toggle="tooltip"></i>
                            <?php endif; ?>
                            <?php if ($lead['is_pre_foreclosure']): ?>
                                <i class="bi bi-exclamation-triangle text-danger" title="Pre-Foreclosure" data-bs-toggle="tooltip"></i>
                            <?php endif; ?>
                            <?php if ($lead['is_tax_delinquent']): ?>
                                <i class="bi bi-cash-stack text-danger" title="Tax Delinquent" data-bs-toggle="tooltip"></i>
                            <?php endif; ?>
                            <?php if ($lead['is_probate']): ?>
                                <i class="bi bi-file-earmark-text text-secondary" title="Probate" data-bs-toggle="tooltip"></i>
                            <?php endif; ?>
                            <?php if ($lead['is_high_equity']): ?>
                                <i class="bi bi-graph-up-arrow text-success" title="High Equity" data-bs-toggle="tooltip"></i>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="status-wrap" id="status-wrap-<?= $lead['id'] ?>">
                            <?= View::statusBadge($lead['status']) ?>
                        </div>
                    </td>
                    <td class="small <?= (!empty($lead['follow_up_date']) && strtotime($lead['follow_up_date']) <= time()) ? 'text-danger fw-bold' : '' ?>">
                        <?= View::date($lead['follow_up_date']) ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="/leads/<?= $lead['id'] ?>" class="btn btn-sm btn-outline-primary py-0 px-2">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="/leads/<?= $lead['id'] ?>/edit" class="btn btn-sm btn-outline-secondary py-0 px-2">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if (($results['last_page'] ?? 1) > 1): ?>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <span class="text-muted small">
            Page <?= $results['page'] ?> of <?= $results['last_page'] ?>
        </span>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= $results['last_page']; $p++): ?>
                <li class="page-item <?= $p === $results['page'] ? 'active' : '' ?>">
                    <a class="page-link"
                       href="?"
                       hx-get="/leads?page=<?= $p ?>&<?= http_build_query(array_diff_key($filters, ['page'=>''])) ?>"
                       hx-target="#leadResults"><?= $p ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
