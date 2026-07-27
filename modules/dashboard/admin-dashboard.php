<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../database/Database.php';
require_once __DIR__ . '/../../models/User.php';

cb_require_role(['Administrator']);

$pdo = Database::getInstance()->getConnection();
$userModel = new User();

$totalCustomers = $userModel->countByRole('Customer');
$totalEmployees = $userModel->countByRole('Employee');

$totalAccounts = (int) $pdo->query(
    "SELECT COUNT(*) AS c FROM accounts WHERE status = 'Active'"
)->fetch()['c'];

$totalBalance = (float) $pdo->query(
    "SELECT COALESCE(SUM(balance), 0) AS total FROM accounts WHERE status = 'Active'"
)->fetch()['total'];

$pendingLoans = (int) $pdo->query(
    "SELECT COUNT(*) AS c FROM loans WHERE status = 'Pending'"
)->fetch()['c'];

$recentActivity = $pdo->query(
    "SELECT al.action, al.log_time, u.username, u.role
     FROM activity_logs al
     JOIN users u ON u.user_id = al.user_id
     ORDER BY al.log_time DESC
     LIMIT 10"
)->fetchAll();

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1">Overview</h1>
        <p class="text-muted mb-0">A snapshot of CESBANK right now.</p>
    </div>
    <a href="/CESBANK/modules/users/index.php" class="btn cb-btn-primary">
        <i class="bi bi-people me-1"></i> Manage Users
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="cb-card p-3 h-100">
            <div class="text-muted small mb-1">Customers</div>
            <div class="cb-figure fs-3 fw-semibold text-primary"><?= number_format($totalCustomers) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="cb-card p-3 h-100">
            <div class="text-muted small mb-1">Active Accounts</div>
            <div class="cb-figure fs-3 fw-semibold text-info"><?= number_format($totalAccounts) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="cb-card p-3 h-100">
            <div class="text-muted small mb-1">Total Deposits Held</div>
            <div class="cb-figure fs-4 fw-semibold text-success">₱<?= number_format($totalBalance, 2) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="cb-card p-3 h-100">
            <div class="text-muted small mb-1">Pending Loan Applications</div>
            <div class="cb-figure fs-3 fw-semibold text-warning"><?= number_format($pendingLoans) ?></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="cb-card p-3">
            <div class="cb-section-title">Recent Activity</div>
            <?php if (empty($recentActivity)): ?>
                <p class="text-muted mb-0">No activity has been recorded yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr class="text-muted small">
                                <th>User</th>
                                <th>Role</th>
                                <th>Action</th>
                                <th class="text-end">When</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentActivity as $log): ?>
                                <tr>
                                    <td><?= htmlspecialchars($log['username']) ?></td>
                                    <td><span class="text-muted small"><?= htmlspecialchars($log['role']) ?></span></td>
                                    <td><?= htmlspecialchars($log['action']) ?></td>
                                    <td class="text-end text-muted small cb-mono">
                                        <?= date('M d, Y g:i A', strtotime($log['log_time'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="cb-card p-3">
            <div class="cb-section-title">Quick Links</div>
            <div class="d-grid gap-2">
                <a href="/CESBANK/modules/users/index.php" class="btn btn-outline-secondary text-start">
                    <i class="bi bi-people me-2"></i>Manage Users &amp; Customers
                </a>
                <a href="/CESBANK/modules/transactions/index.php" class="btn btn-outline-secondary text-start">
                    <i class="bi bi-arrow-left-right me-2"></i>View Transactions
                </a>
                <a href="/CESBANK/modules/reports/index.php" class="btn btn-outline-secondary text-start">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i>Generate Reports
                </a>
            </div>
            <hr class="my-3">
            <div class="text-muted small">Employees on record</div>
            <div class="cb-figure fs-5 fw-semibold"><?= number_format($totalEmployees) ?></div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>