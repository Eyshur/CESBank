<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/Loan.php';

cb_require_role(['Administrator', 'Employee', 'Customer']);

$id = (int) ($_GET['id'] ?? 0);
$loanModel = new Loan();
$loan = $loanModel->findWithCustomer($id);

// A Customer may only view their own loan application.
if ($loan && $_SESSION['role'] === 'Customer' && (int) $loan['user_id'] !== (int) $_SESSION['user_id']) {
    $loan = false;
}

$isStaff = in_array($_SESSION['role'], ['Administrator', 'Employee'], true);

$pageTitle = 'Loan Details';
require_once __DIR__ . '/../../layout/header.php';

$statusClass = [
    'Pending'  => 'pending',
    'Accepted' => 'active',
    'Declined' => 'closed',
];
?>

<div class="mb-4">
    <a href="index.php" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to Loans</a>
    <h1 class="h4 mt-2 mb-1">Loan Application</h1>
</div>

<?php if (!$loan): ?>
    <div class="cb-card p-4">
        <p class="text-danger mb-0">Loan application not found.</p>
    </div>
<?php else: ?>
    <div class="cb-card p-4" style="max-width: 640px;" id="loanDetailCard" data-loan-id="<?= (int) $loan['loan_id'] ?>">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted small">Applicant</div>
                <div class="fw-medium"><?= htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']) ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Status</div>
                <div>
                    <span class="badge rounded-pill badge-status-<?= $statusClass[$loan['status']] ?>" id="loanStatusBadge">
                        <?= htmlspecialchars($loan['status']) ?>
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Loan Amount</div>
                <div class="cb-figure fw-semibold fs-5">₱<?= number_format($loan['loan_amount'], 2) ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Interest Rate</div>
                <div class="fw-medium"><?= number_format($loan['interest_rate'], 2) ?>%</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Loan Term</div>
                <div class="fw-medium"><?= (int) $loan['loan_term'] ?> months</div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">Application Date</div>
                <div class="cb-mono"><?= date('M d, Y', strtotime($loan['application_date'])) ?></div>
            </div>
        </div>

        <?php if ($isStaff && $loan['status'] === 'Pending'): ?>
            <div class="d-flex justify-content-end gap-2 mt-4" id="loanActions">
                <button type="button" class="btn btn-outline-danger" id="btnDeclineLoan">
                    <i class="bi bi-x-lg me-1"></i>Decline
                </button>
                <button type="button" class="btn cb-btn-primary" id="btnAcceptLoan">
                    <i class="bi bi-check-lg me-1"></i>Accept
                </button>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
$pageScripts = ['/CESBANK/js/loan-actions.js'];
require_once __DIR__ . '/../../layout/footer.php';
?>