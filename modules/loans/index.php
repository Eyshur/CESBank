<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/Loan.php';

cb_require_role(['Administrator', 'Employee', 'Customer']);

$loanModel = new Loan();

if (cb_is_ajax()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $loanModel->listForUser(
            $_SESSION['user_id'],
            $_SESSION['role'],
            $_GET['status'] ?? ''
        ),
    ]);
    exit;
}

$isStaff = in_array($_SESSION['role'], ['Administrator', 'Employee'], true);
$pageTitle = 'Loans';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1"><?= $isStaff ? 'Loan Applications' : 'My Loans' ?></h1>
        <p class="text-muted mb-0">
            <?= $isStaff ? 'Review and process customer loan applications.' : 'Track the status of your loan applications.' ?>
        </p>
    </div>
    <a href="create.php" class="btn cb-btn-primary">
        <i class="bi bi-plus-lg me-1"></i> <?= $isStaff ? 'New Application' : 'Apply for a Loan' ?>
    </a>
</div>

<div class="cb-card p-3 mb-3">
    <div class="row g-2">
        <div class="col-md-4">
            <select id="loanStatus" class="form-select cb-input">
                <option value="">All statuses</option>
                <option value="Pending">Pending</option>
                <option value="Accepted">Accepted</option>
                <option value="Declined">Declined</option>
            </select>
        </div>
    </div>
</div>

<div class="cb-card p-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr class="text-muted small">
                    <th class="ps-3">Applied On</th>
                    <?php if ($isStaff): ?><th>Customer</th><?php endif; ?>
                    <th class="text-end">Amount</th>
                    <th>Interest</th>
                    <th>Term</th>
                    <th>Status</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody id="loansTableBody">
                <tr>
                    <td colspan="<?= $isStaff ? 7 : 6 ?>" class="text-center text-muted py-4">Loading loans…</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    window.CB_IS_STAFF = <?= $isStaff ? 'true' : 'false' ?>;
</script>

<?php
$pageScripts = ['/CESBANK/js/loans.js'];
require_once __DIR__ . '/../../layout/footer.php';
?>