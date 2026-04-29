<?php /** @var array $lead @var array $errors @var array $statuses @var array $propTypes */ ?>

<div class="page-header">
    <div>
        <a href="/leads/<?= $lead['id'] ?>" class="text-muted small"><i class="bi bi-arrow-left"></i> Back to Lead</a>
        <h1 class="page-title mt-1">Edit: <?= View::e($lead['address']) ?></h1>
    </div>
</div>

<form method="POST" action="/leads/<?= $lead['id'] ?>/edit" novalidate>
    <?= Auth::csrfField() ?>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><i class="bi bi-house me-2"></i>Property Information</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Property Address *</label>
                        <input type="text" name="address" class="form-control" value="<?= View::e($lead['address']) ?>" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">City *</label><input type="text" name="city" class="form-control" value="<?= View::e($lead['city']) ?>" required></div>
                        <div class="col-3"><label class="form-label">State *</label><input type="text" name="state" class="form-control" maxlength="2" value="<?= View::e($lead['state']) ?>" required></div>
                        <div class="col-3"><label class="form-label">ZIP *</label><input type="text" name="zip" class="form-control" value="<?= View::e($lead['zip']) ?>" required></div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-6"><label class="form-label">County</label><input type="text" name="county" class="form-control" value="<?= View::e($lead['county'] ?? '') ?>"></div>
                        <div class="col-6"><label class="form-label">APN</label><input type="text" name="apn" class="form-control" value="<?= View::e($lead['apn'] ?? '') ?>"></div>
                    </div>
                    <div class="mt-2">
                        <label class="form-label">Property Type</label>
                        <select name="property_type" class="form-select">
                            <?php foreach ($propTypes as $pt): ?>
                            <option value="<?= View::e($pt) ?>" <?= $lead['property_type'] === $pt ? 'selected' : '' ?>><?= View::e($pt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-3"><label class="form-label">Beds</label><input type="number" name="beds" class="form-control" step=".5" value="<?= View::e($lead['beds'] ?? '') ?>"></div>
                        <div class="col-3"><label class="form-label">Baths</label><input type="number" name="baths" class="form-control" step=".5" value="<?= View::e($lead['baths'] ?? '') ?>"></div>
                        <div class="col-3"><label class="form-label">Sq Ft</label><input type="number" name="sqft" class="form-control" value="<?= View::e($lead['sqft'] ?? '') ?>"></div>
                        <div class="col-3"><label class="form-label">Year Built</label><input type="number" name="year_built" class="form-control" value="<?= View::e($lead['year_built'] ?? '') ?>"></div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-6"><label class="form-label">Lot Size</label><input type="text" name="lot_size" class="form-control" value="<?= View::e($lead['lot_size'] ?? '') ?>"></div>
                        <div class="col-6"><label class="form-label">Last Sale Date</label><input type="date" name="last_sale_date" class="form-control" value="<?= View::e($lead['last_sale_date'] ?? '') ?>"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-currency-dollar me-2"></i>Financials</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">Est. Value ($)</label><input type="number" name="estimated_value" class="form-control" value="<?= View::e($lead['estimated_value'] ?? '') ?>"></div>
                        <div class="col-6"><label class="form-label">Est. Rent/mo ($)</label><input type="number" name="estimated_rent" class="form-control" value="<?= View::e($lead['estimated_rent'] ?? '') ?>"></div>
                        <div class="col-6"><label class="form-label">Loan Balance ($)</label><input type="number" name="loan_balance" class="form-control" value="<?= View::e($lead['loan_balance'] ?? '') ?>"></div>
                        <div class="col-6"><label class="form-label">Equity Est. ($)</label><input type="number" name="equity_estimate" class="form-control" value="<?= View::e($lead['equity_estimate'] ?? '') ?>"></div>
                        <div class="col-6"><label class="form-label">Last Sale Price ($)</label><input type="number" name="last_sale_price" class="form-control" value="<?= View::e($lead['last_sale_price'] ?? '') ?>"></div>
                        <div class="col-6"><label class="form-label">ARV ($)</label><input type="number" name="arv_estimate" class="form-control" value="<?= View::e($lead['arv_estimate'] ?? '') ?>"></div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-flag me-2"></i>Lead Flags</div>
                <div class="card-body">
                    <div class="row g-2">
                        <?php $flags = ['is_absentee_owner'=>'Absentee Owner','is_vacant'=>'Vacant','is_pre_foreclosure'=>'Pre-Foreclosure','is_tax_delinquent'=>'Tax Delinquent','is_probate'=>'Probate','is_tired_landlord'=>'Tired Landlord','is_high_equity'=>'High Equity','is_mls_listed'=>'MLS Listed']; ?>
                        <?php foreach ($flags as $fname => $flabel): ?>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="<?= $fname ?>" value="1"
                                       id="<?= $fname ?>" <?= !empty($lead[$fname]) ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="<?= $fname ?>"><?= $flabel ?></label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="bi bi-sliders me-2"></i>Status</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach ($statuses as $s): ?>
                                <option value="<?= View::e($s) ?>" <?= $lead['status'] === $s ? 'selected' : '' ?>><?= View::e($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Follow-Up Date</label>
                            <input type="date" name="follow_up_date" class="form-control" value="<?= View::e($lead['follow_up_date'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><i class="bi bi-person me-2"></i>Owner Information</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label">Owner Name</label><input type="text" name="owner_name" class="form-control" value="<?= View::e($lead['owner_name'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Phone</label><input type="tel" name="owner_phone" class="form-control" value="<?= View::e($lead['owner_phone'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="owner_email" class="form-control" value="<?= View::e($lead['owner_email'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Mailing Address</label><input type="text" name="mailing_address" class="form-control" value="<?= View::e($lead['mailing_address'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Mailing City</label><input type="text" name="mailing_city" class="form-control" value="<?= View::e($lead['mailing_city'] ?? '') ?>"></div>
                        <div class="col-md-1"><label class="form-label">State</label><input type="text" name="mailing_state" class="form-control" maxlength="2" value="<?= View::e($lead['mailing_state'] ?? '') ?>"></div>
                        <div class="col-md-2"><label class="form-label">ZIP</label><input type="text" name="mailing_zip" class="form-control" value="<?= View::e($lead['mailing_zip'] ?? '') ?>"></div>
                        <div class="col-md-2"><label class="form-label">Ownership Yrs</label><input type="number" name="ownership_years" class="form-control" value="<?= View::e($lead['ownership_years'] ?? '') ?>"></div>
                        <div class="col-auto d-flex align-items-end gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="out_of_state_owner" value="1" id="out_state" <?= !empty($lead['out_of_state_owner']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="out_state">Out-of-State</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skip_traced" value="1" id="skip_traced" <?= !empty($lead['skip_traced']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="skip_traced">Skip Traced</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="do_not_contact" value="1" id="dnc" <?= !empty($lead['do_not_contact']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="dnc">Do Not Contact</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-floppy me-2"></i>Save Changes</button>
            <a href="/leads/<?= $lead['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
            <form method="POST" action="/leads/<?= $lead['id'] ?>/delete" class="ms-auto"
                  onsubmit="return confirm('Delete this lead? This cannot be undone.')">
                <?= Auth::csrfField() ?>
                <button class="btn btn-outline-danger"><i class="bi bi-trash me-2"></i>Delete Lead</button>
            </form>
        </div>

    </div>
</form>
