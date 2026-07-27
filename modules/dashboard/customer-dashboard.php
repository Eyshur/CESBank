<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../database/Database.php';

cb_require_role(['Customer']);

$pdo = Database::getInstance()->getConnection();

$customer = $pdo->prepare("SELECT * FROM customers WHERE user_id = :uid LIMIT 1");
$customer->execute(['uid' => $_SESSION['user_id']]);
$customer = $customer->fetch();

$accounts = [];
$recentTransactions = [];
$totalBalance = 0.0;

if ($customer) {
    $accStmt = $pdo->prepare(
        "SELECT * FROM accounts WHERE customer_id = :cid ORDER BY created_at DESC"
    );
    $accStmt->execute(['cid' => $customer['customer_id']]);
    $accounts = $accStmt->fetchAll();

    foreach ($accounts as $acc) {
        if ($acc['status'] === 'Active') {
            $totalBalance += (float) $acc['balance'];
        }
    }

    if ($accounts) {
        $accountIds = array_column($accounts, 'account_id');
        $placeholders = implode(',', array_fill(0, count($accountIds), '?'));
        $txStmt = $pdo->prepare(
            "SELECT t.*, a.account_number
             FROM transactions t
             JOIN accounts a ON a.account_id = t.account_id
             WHERE t.account_id IN ({$placeholders})
             ORDER BY t.transaction_date DESC
             LIMIT 10"
        );
        $txStmt->execute($accountIds);
        $recentTransactions = $txStmt->fetchAll();
    }
}

$pageTitle = 'My Dashboard';
require_once __DIR__ . '/../../layout/header.php';
?>

<?php if (!$customer): ?>
    <div class="cb-card p-4 text-center">
        <h1 class="h5 mb-2">Your profile isn't linked to a customer record yet</h1>
        <p class="text-muted mb-0">Please visit a branch or contact support so we can finish setting up your account.</p>
    </div>
<?php else: ?>

    <div class="mb-4">
        <h1 class="h4 mb-1">Welcome back, <?= htmlspecialchars($customer['first_name']) ?></h1>
        <p class="text-muted mb-0">Here's where things stand across your accounts.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="cb-card p-3 h-100">
                <div class="text-muted small mb-1">Total Balance</div>
                <div class="cb-figure fs-3 fw-semibold">₱<?= number_format($totalBalance, 2) ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="cb-card p-3 h-100">
                <div class="text-muted small mb-1">Active Accounts</div>
                <div class="cb-figure fs-3 fw-semibold">
                    <?= count(array_filter($accounts, fn($a) => $a['status'] === 'Active')) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="cb-card p-3">
                <div class="cb-section-title">My Accounts</div>
                <?php if (empty($accounts)): ?>
                    <p class="text-muted mb-0">You don't have any accounts on file yet.</p>
                <?php else: ?>
                    <?php foreach ($accounts as $acc): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="cb-account-number fw-medium"><?= htmlspecialchars($acc['account_number']) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($acc['account_type']) ?> account</div>
                            </div>
                            <div class="text-end">
                                <div class="cb-figure fw-semibold">₱<?= number_format($acc['balance'], 2) ?></div>
                                <span class="badge rounded-pill badge-status-<?= strtolower($acc['status']) ?>">
                                    <?= htmlspecialchars($acc['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="cb-card p-3">
                <div class="cb-section-title">Recent Transactions</div>
                <?php if (empty($recentTransactions)): ?>
                    <p class="text-muted mb-0">No transactions yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Account</th>
                                    <th>Type</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">When</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTransactions as $t): ?>
                                    <tr>
                                        <td class="cb-account-number"><?= htmlspecialchars($t['account_number']) ?></td>
                                        <td><?= htmlspecialchars($t['transaction_type']) ?></td>
                                        <td class="cb-amount text-end">₱<?= number_format($t['amount'], 2) ?></td>
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
        </div>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
