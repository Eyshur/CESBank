<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/Transaction.php';

cb_require_role(['Administrator', 'Employee']);

$accounts = (new Transaction())->accountsForUser($_SESSION['user_id'], $_SESSION['role']);

$pageTitle = 'New Transaction';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="mb-4">
    <a href="index.php" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to Transactions</a>
    <h1 class="h4 mt-2 mb-1">New Transaction</h1>
    <p class="text-muted mb-0">Process a deposit, withdrawal, or transfer.</p>
</div>

<?php if (!$accounts): ?>
    <div class="cb-card p-4" style="max-width: 720px;">
        <p class="text-muted mb-0">There are no active accounts available for this transaction.</p>
    </div>
<?php else: ?>
    <div class="cb-card p-4" style="max-width: 720px;">
        <form id="transactionForm" data-action="store.php" novalidate>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Transaction Type</label>
                    <select name="transaction_type" id="transactionTypeSelect" class="form-select cb-input" required>
                        <option value="">Select type…</option>
                        <option value="Deposit">Deposit</option>
                        <option value="Withdraw">Withdraw</option>
                        <option value="Transfer">Transfer</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" name="amount" class="form-control cb-input" min="0.01" step="0.01" required>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Source Account</label>
                    <select name="account_id" class="form-select cb-input" required>
                        <option value="">Select account…</option>
                        <?php foreach ($accounts as $a): ?>
                            <option value="<?= (int) $a['account_id'] ?>">
                                <?= htmlspecialchars($a['account_number'] . ' — ' . $a['first_name'] . ' ' . $a['last_name'] . ' (₱' . number_format($a['balance'], 2) . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 d-none" id="receiverAccountGroup">
                    <label class="form-label">Destination Account</label>
                    <select name="receiver_id" id="receiverId" class="form-select cb-input">
                        <option value="">Select destination account…</option>
                        <?php foreach ($accounts as $a): ?>
                            <option value="<?= (int) $a['account_id'] ?>">
                                <?= htmlspecialchars($a['account_number'] . ' — ' . $a['first_name'] . ' ' . $a['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Remarks <span class="text-muted">(optional)</span></label>
                    <textarea name="remarks" class="form-control cb-input" rows="2" maxlength="500"></textarea>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                <button class="btn cb-btn-primary" id="transactionSubmit" type="submit">Process Transaction</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php
$pageScripts = ['/CESBANK/js/transaction-form.js'];
require_once __DIR__ . '/../../layout/footer.php';
?>