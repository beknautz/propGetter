<?php /** @var array $errors @var array $old @var array $statuses @var array $propTypes */ ?>

<div class="page-header">
    <div>
        <a href="/leads" class="text-muted small"><i class="bi bi-arrow-left"></i> Back to Leads</a>
        <h1 class="page-title mt-1">Add New Lead</h1>
    </div>
</div>

<?php if (!empty($errors['general'])): ?>
<div class="alert alert-danger"><?= View::e($errors['general'][0]) ?></div>
<?php endif; ?>

<form method="POST" action="/leads/create" novalidate>
    <?= Auth::csrfField() ?>

    <div class="row g-3">

        <!-- Property Info -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><i class="bi bi-house me-2"></i>Property Information</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Property Address *</label>
                        <input type="text" name="address" class="form-control <?= !empty($errors['address']) ? 'is-invalid' : '' ?>"
                               value="<?= View::e($old['address'] ?? '') ?>" required>
                        <?php if (!empty($errors['address'])): ?><div class="invalid-feedback"><?= View::e($errors['address'][0]) ?></div><?php endif; ?>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">City *</label>
                            <input type="text" name="city" class="form-control <?= !empty($errors['city']) ? 'is-invalid' : '' ?>"
                                   value="<?= View::e($old['city'] ?? '') ?>" required>
                        </div>
                        <div class="col-3">
                            <label class="form-label">State *</label>
                            <input type="text" name="state" class="form-control" maxlength="2"
                                   value="<?= View::e($old['state'] ?? 'WA') ?>" required>
                        </div>
                        <div class="col-3">
                            <label class="form-label">ZIP *</label>
                            <input type="text" name="zip" class="form-control"
                                   value="<?= View::e($old['zip'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-6">
                            <label class="form-label">County</label>
                            <input type="text" name="county" class="form-control" value="<?= View::e($old['county'] ?? '') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">APN</label>
                            <input type="text" name="apn" class="form-control" value="<?= View::e($old['apn'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="form-label">Property Type *</label>
                        <select name="property_type" class="form-select" required>
                            <?php foreach ($propTypes as $pt): ?>
                            <option value="<?= View::e($pt) ?>" <?= ($old['property_type'] ?? 'Single Family') === $pt ? 'selected' : '' ?>><?= View::e($pt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-3"><label class="form-label">Beds</label><input type="number" name="beds" class="form-control" step=".5" value="<?= View::e($old['beds'] ?? '') ?>"></div>
                        <div class="col-3"><label class="form-label">Baths</label><input type="number" name="baths" class="form-control" step=".5" value="<?= View::e($old['baths'] ?? '') ?>"></div>
                        <div class="col-3"><label class="form-label">Sq Ft</label><input type="number" name="sqft" class="form-control" value="<?= View::e($old['sqft'] ?? '') ?>"></div>
                        <div class="col-3"><label class="form-label">Year Built</label><input type="number" name="year_built" class="form-control" value="<?= View::e($old['year_built'] ?? '') ?>"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financials + Status -->
        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-currency-dollar me-2"></i>Financials</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label">Est. Value ($)</label><input type="number" name="estimated_value" class="form-control" value="<?= View::e($old['estimated_value'] ?? '') ?>"></div>
                        <div class="col-6"><label class="form-label">Est. Rent/mo ($)</label><input type="number" name="estimated_rent" class="form-control" value="<?= View::e($old['estimated_rent'] ?? '') ?>"></div>
                        <div class="col-6"><label class="form-label">Loan Balance ($)</label><input type="number" name="loan_balance" class="form-control" value="<?= View::e($old['loan_balance'] ?? '') ?>"></div>
                        <div class="col-6"><label class="form-label">Equity Est. ($)</label><input type="number" name="equity_estimate" class="form-control" value="<?= View::e($old['equity_estimate'] ?? '') ?>"></div>
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
                                       id="<?= $fname ?>" <?= !empty($old[$fname]) ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="<?= $fname ?>"><?= $flabel ?></label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="bi bi-sliders me-2"></i>Status & Follow-Up</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach ($statuses as $s): ?>
                                <option value="<?= View::e($s) ?>" <?= ($old['status'] ?? 'New') === $s ? 'selected' : '' ?>><?= View::e($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Follow-Up Date</label>
                            <input type="date" name="follow_up_date" class="form-control" value="<?= View::e($old['follow_up_date'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Owner Info -->
        <div class="col-12">
            <div class="card">
                <div class="card-header"><i class="bi bi-person me-2"></i>Owner Information</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label">Owner Name</label><input type="text" name="owner_name" class="form-control" value="<?= View::e($old['owner_name'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Phone</label><input type="tel" name="owner_phone" class="form-control" value="<?= View::e($old['owner_phone'] ?? '') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="owner_email" class="form-control" value="<?= View::e($old['owner_email'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Mailing Address</label><input type="text" name="mailing_address" class="form-control" value="<?= View::e($old['mailing_address'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">City</label><input type="text" name="mailing_city" class="form-control" value="<?= View::e($old['mailing_city'] ?? '') ?>"></div>
                        <div class="col-md-1"><label class="form-label">State</label><input type="text" name="mailing_state" class="form-control" maxlength="2" value="<?= View::e($old['mailing_state'] ?? '') ?>"></div>
                        <div class="col-md-2"><label class="form-label">ZIP</label><input type="text" name="mailing_zip" class="form-control" value="<?= View::e($old['mailing_zip'] ?? '') ?>"></div>
                        <div class="col-md-2"><label class="form-label">Ownership Years</label><input type="number" name="ownership_years" class="form-control" value="<?= View::e($old['ownership_years'] ?? '') ?>"></div>
                        <div class="col-auto d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="out_of_state_owner" value="1" id="out_state" <?= !empty($old['out_of_state_owner']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="out_state">Out-of-State Owner</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-plus-circle me-2"></i>Create Lead
            </button>
            <a href="/leads" class="btn btn-outline-secondary">Cancel</a>
        </div>

    </div>
</form>
