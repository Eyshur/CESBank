<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/Transaction.php';

cb_require_role(['Administrator', 'Employee', 'Customer']);

$id = (int) ($_GET['id'] ?? 0);

$transactionModel = new Transaction();
$transaction = $transactionModel->findWithDetails($id, $_SESSION['user_id'], $_SESSION['role']);

$pageTitle = 'Transaction Details';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="mb-4">
    <a href="index.php" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to Transactions</a>
    <h1 class="h4 mt-2 mb-1">Transaction Details</h1>
</div>

<?php if (!$transaction): ?>
    <div class="cb-card p-4">
        <p class="text-danger mb-0">Transaction not found.</p>
    </div>
<?php else: ?>
    <div class="cb-card p-4" style="max-width: 720px;">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted small">Transaction ID</div>
                <div class="fw-medium cb-mono">#<?= (int) $transaction['transaction_id'] ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Type</div>
                <div class="fw-medium"><?= htmlspecialchars($transaction['transaction_type']) ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Date</div>
                <div class="cb-mono"><?= date('M d, Y g:i A', strtotime($transaction['transaction_date'])) ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Amount</div>
                <div class="cb-amount fw-semibold">₱<?= number_format($transaction['amount'], 2) ?></div>
            </div>

            <div class="col-12"><hr class="my-2"></div>

            <div class="col-md-6">
                <div class="text-muted small">Account</div>
                <div class="fw-medium cb-account-number"><?= htmlspecialchars($transaction['account_number']) ?></div>
                <div class="text-muted small"><?= htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']) ?></div>
            </div>

            <?php if ($transaction['transaction_type'] === 'Transfer'): ?>
                <div class="col-md-6">
                    <div class="text-muted small">Destination Account</div>
                    <div class="fw-medium cb-account-number">
                        <?= htmlspecialchars($transaction['receiver_account_number'] ?? '—') ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-12">
                <div class="text-muted small">Remarks</div>
                <div class="fw-medium"><?= htmlspecialchars($transaction['remarks'] !== '' ? $transaction['remarks'] : '—') ?></div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
require_once __DIR__ . '/../../layout/footer.php';
?>