<?php

require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/Reports.php';

cb_require_role(['Administrator', 'Employee']);

$pageTitle = 'Reports';

require_once __DIR__ . '/../../layout/header.php';

$defaultFrom = date('Y-m-01');
$defaultTo   = date('Y-m-d');
?>

<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1">Reports</h1>
        <p class="text-muted mb-0">
            Generate printable PDF reports for transactions and accounts.
        </p>
    </div>
</div>

<div class="row g-4">

    <!-- Transactions Report -->
    <div class="col-lg-6">

        <div class="cb-card p-4 h-100">

            <h4 class="mb-3">
                <i class="bi bi-receipt-cutoff me-2"></i>
                Transactions Report
            </h4>

            <form
                method="GET"
                action="view.php"
                target="_blank">

                <input type="hidden" name="type" value="transactions">

                <div class="mb-3">
                    <label class="form-label">From</label>
                    <input
                        type="date"
                        class="form-control cb-input"
                        name="from"
                        value="<?= $defaultFrom ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">To</label>
                    <input
                        type="date"
                        class="form-control cb-input"
                        name="to"
                        value="<?= $defaultTo ?>"
                        required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Transaction Type</label>

                    <select
                        name="transaction_type"
                        class="form-select cb-input">

                        <option value="">All Transactions</option>
                        <option value="Deposit">Deposit</option>
                        <option value="Withdraw">Withdraw</option>
                        <option value="Transfer">Transfer</option>

                    </select>
                </div>

                <button
                    type="submit"
                    class="btn cb-btn-primary w-100">

                    <i class="bi bi-file-earmark-pdf me-2"></i>
                    Generate PDF

                </button>

            </form>

        </div>

    </div>

    <!-- Accounts Report -->
    <div class="col-lg-6">

        <div class="cb-card p-4 h-100">

            <h4 class="mb-3">
                <i class="bi bi-bank me-2"></i>
                Accounts Report
            </h4>

            <form
                method="GET"
                action="view.php"
                target="_blank">

                <input type="hidden" name="type" value="accounts">

                <div class="mb-4">

                    <label class="form-label">Account Type</label>

                    <select
                        name="account_type"
                        class="form-select cb-input">

                        <option value="">All Accounts</option>
                        <option value="Savings">Savings</option>
                        <option value="Current">Current</option>

                    </select>

                </div>

                <button
                    type="submit"
                    class="btn cb-btn-primary w-100">

                    <i class="bi bi-file-earmark-pdf me-2"></i>
                    Generate PDF

                </button>

            </form>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>