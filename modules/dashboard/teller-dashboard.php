<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../database/Database.php';

cb_require_role(['Employee']);

$pdo = Database::getInstance()->getConnection();

$todayDeposits = (float) $pdo->query(
    "SELECT COALESCE(SUM(amount), 0) AS total FROM transactions
     WHERE transaction_type = 'Deposit' AND DATE(transaction_date) = CURDATE()"
)->fetch()['total'];

$todayWithdrawals = (float) $pdo->query(
    "SELECT COALESCE(SUM(amount), 0) AS total FROM transactions
     WHERE transaction_type = 'Withdraw' AND DATE(transaction_date) = CURDATE()"
)->fetch()['total'];

$todayCount = (int) $pdo->query(
    "SELECT COUNT(*) AS c FROM transactions WHERE DATE(transaction_date) = CURDATE()"
)->fetch()['c'];

$recentTransactions = $pdo->query(
    "SELECT t.transaction_id, t.transaction_type, t.amount, t.transaction_date, t.remarks,
            a.account_number
     FROM transactions t
     JOIN accounts a ON a.account_id = t.account_id
     ORDER BY t.transaction_date DESC
     LIMIT 10"
)->fetchAll();

$pageTitle = 'Teller Dashboard';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1">Today at the counter</h1>
        <p class="text-muted mb-0">Deposits, withdrawals, and transfers processed today.</p>
    </div>
    <a href="/CESBANK/modules/transactions/create.php" class="btn cb-btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Transaction
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-4">
        <div class="cb-card p-3 h-100">
            <div class="text-muted small mb-1">Deposits Today</div>
            <div class="cb-figure fs-4 fw-semibold text-success">₱<?= number_format($todayDeposits, 2) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="cb-card p-3 h-100">
            <div class="text-muted small mb-1">Withdrawals Today</div>
            <div class="cb-figure fs-4 fw-semibold text-danger">₱<?= number_format($todayWithdrawals, 2) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="cb-card p-3 h-100">
            <div class="text-muted small mb-1">Transactions Today</div>
            <div class="cb-figure fs-4 fw-semibold"><?= number_format($todayCount) ?></div>
        </div>
    </div>
</div>

<div class="cb-card p-3">
    <div class="cb-section-title">Recent Transactions</div>
    <?php if (empty($recentTransactions)): ?>
        <p class="text-muted mb-0">No transactions have been recorded yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr class="text-muted small">
                        <th>Account</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th>Remarks</th>
                        <th class="text-end">When</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentTransactions as $t): ?>
                        <tr>
                            <td class="cb-account-number"><?= htmlspecialchars($t['account_number']) ?></td>
                            <td><?= htmlspecialchars($t['transaction_type']) ?></td>
                            <td class="cb-amount text-end">₱<?= number_format($t['amount'], 2) ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($t['remarks']) ?></td>
                            <td class="text-end text-muted small cb-mono">
                                <?= date('M d, g:i A', strtotime($t['transaction_date'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>