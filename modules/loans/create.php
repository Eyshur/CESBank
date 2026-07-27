<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/Loan.php';

cb_require_role(['Administrator', 'Employee', 'Customer']);

$isStaff = in_array($_SESSION['role'], ['Administrator', 'Employee'], true);
$loanModel = new Loan();
$customers = $isStaff ? $loanModel->listCustomersForDropdown() : [];

$pageTitle = 'Apply for a Loan';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="mb-4">
    <a href="index.php" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to Loans</a>
    <h1 class="h4 mt-2 mb-1"><?= $isStaff ? 'New Loan Application' : 'Apply for a Loan' ?></h1>
    <p class="text-muted mb-0">
        <?= $isStaff ? 'File an application on behalf of a customer.' : 'Fill in the details below to submit your application.' ?>
    </p>
</div>

<div class="cb-card p-4" style="max-width: 640px;">
    <form id="loanForm" data-action="store.php" novalidate>
        <div class="row g-3">
            <?php if ($isStaff): ?>
                <div class="col-12">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select cb-input" required>
                        <option value="">Select customer…</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= (int) $c['customer_id'] ?>">
                                <?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="col-md-6">
                <label class="form-label">Loan Amount</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="loan_amount" class="form-control cb-input" min="1" step="0.01" required>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Loan Term (months)</label>
                <input type="number" name="loan_term" class="form-control cb-input" min="1" max="360" step="1" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Interest Rate (%)</label>
                <input type="number" name="interest_rate" class="form-control cb-input" min="0" max="100" step="0.01" value="5.00" <?= $isStaff ? 'required' : 'readonly' ?>>
                <?php if (!$isStaff): ?>
                    <div class="form-text">Standard rate — set by the bank.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn cb-btn-primary" id="loanFormSubmit">Submit Application</button>
        </div>
    </form>
</div>

<?php
$pageScripts = ['/CESBANK/js/loan-form.js'];
require_once __DIR__ . '/../../layout/footer.php';
?>