<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../database/Database.php';

cb_require_role(['Administrator', 'Employee']);

$id = (int) ($_GET['id'] ?? 0);
$userModel = new User();
$user = $userModel->findWithCustomerProfile($id);

$accounts = [];
if ($user && $user['role'] === 'Customer' && $user['customer_id']) {
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare("SELECT * FROM accounts WHERE customer_id = :cid ORDER BY created_at DESC");
    $stmt->execute(['cid' => $user['customer_id']]);
    $accounts = $stmt->fetchAll();
}

$pageTitle = 'User Details';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="mb-4">
    <a href="index.php" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to Users</a>
    <h1 class="h4 mt-2 mb-1">User Details</h1>
</div>

<?php if (!$user): ?>
    <div class="cb-card p-4">
        <p class="text-danger mb-0">User not found.</p>
    </div>
<?php else: ?>
    <div class="cb-card p-4" style="max-width: 720px;">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted small">Username</div>
                <div class="fw-medium"><?= htmlspecialchars($user['username']) ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Role</div>
                <div class="fw-medium"><?= htmlspecialchars($user['role']) ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Status</div>
                <div>
                    <span class="badge rounded-pill badge-status-<?= strtolower($user['status']) ?>">
                        <?= htmlspecialchars($user['status']) ?>
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Account Created</div>
                <div class="cb-mono"><?= date('M d, Y g:i A', strtotime($user['created_at'])) ?></div>
            </div>

            <?php if ($user['role'] === 'Customer' && $user['customer_id']): ?>
                <div class="col-12"><hr class="my-2"></div>
                <div class="col-md-6">
                    <div class="text-muted small">Full Name</div>
                    <div class="fw-medium"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Gender</div>
                    <div class="fw-medium"><?= htmlspecialchars($user['gender'] ?? '—') ?></div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Birthdate</div>
                    <div class="fw-medium"><?= $user['birthdate'] ? date('M d, Y', strtotime($user['birthdate'])) : '—' ?></div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Contact No.</div>
                    <div class="fw-medium cb-mono"><?= htmlspecialchars($user['contact_no'] ?? '—') ?></div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Email</div>
                    <div class="fw-medium"><?= htmlspecialchars($user['email'] ?? '—') ?></div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Address</div>
                    <div class="fw-medium"><?= htmlspecialchars($user['address'] ?? '—') ?></div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($_SESSION['role'] === 'Administrator'): ?>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="edit.php?id=<?= (int) $user['user_id'] ?>" class="btn cb-btn-primary">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($user['role'] === 'Customer' && $user['customer_id']): ?>
        <div class="cb-card p-4 mt-3" style="max-width: 720px;">
            <div class="cb-section-title">Accounts</div>

            <?php if (empty($accounts)): ?>
                <p class="text-muted small mb-3">This customer doesn't have any accounts yet.</p>
            <?php else: ?>
                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr class="text-muted small">
                                <th>Account No.</th>
                                <th>Type</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($accounts as $acc): ?>
                                <tr>
                                    <td class="cb-account-number"><?= htmlspecialchars($acc['account_number']) ?></td>
                                    <td><?= htmlspecialchars($acc['account_type']) ?></td>
                                    <td class="cb-amount text-end">₱<?= number_format($acc['balance'], 2) ?></td>
                                    <td>
                                        <span class="badge rounded-pill badge-status-<?= strtolower($acc['status']) ?>">
                                            <?= htmlspecialchars($acc['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <hr class="my-3">
            <p class="text-muted small mb-3">Open a new account for this customer</p>
            <form id="openAccountForm" novalidate>
                <input type="hidden" name="customer_id" value="<?= (int) $user['customer_id'] ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Account Type</label>
                        <select class="form-select cb-input" name="account_type" required>
                            <option value="Savings">Savings</option>
                            <option value="Current">Current</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Opening Balance</label>
                        <input type="number" class="form-control cb-input" name="opening_balance" min="0" step="0.01" value="0.00" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn cb-btn-primary w-100" id="openAccountSubmit">
                            <i class="bi bi-plus-lg me-1"></i>Open Account
                        </button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
$pageScripts = ['/CESBANK/js/open-account.js'];
require_once __DIR__ . '/../../layout/footer.php';
?>