<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/Transaction.php';
cb_require_role(['Administrator', 'Employee', 'Customer']);

$transactionModel = new Transaction();
if (cb_is_ajax()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $transactionModel->listTransactions($_SESSION['user_id'], $_SESSION['role'], $_GET['type'] ?? '', trim($_GET['search'] ?? ''))]);
    exit;
}
$pageTitle = 'Transactions';
require_once __DIR__ . '/../../layout/header.php';
?>
<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
    <div><h1 class="h4 mb-1">Transactions</h1><p class="text-muted mb-0">Review and process account activity.</p></div>
    <button class="btn cb-btn-primary" id="btnNewTransaction"><i class="bi bi-plus-lg me-1"></i> New Transaction</button>
</div>
<div class="cb-card p-3 mb-3"><div class="row g-2"><div class="col-md-5"><input id="transactionSearch" class="form-control cb-input" placeholder="Search account or customerâ€¦"></div><div class="col-md-3"><select id="transactionType" class="form-select cb-input"><option value="">All transaction types</option><option>Deposit</option><option>Withdraw</option><option>Transfer</option></select></div></div></div>
<div class="cb-card p-0"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr class="text-muted small"><th class="ps-3">Date</th><th>Account</th><th>Customer</th><th>Type</th><th class="text-end">Amount</th><th class="text-end pe-3">Actions</th></tr></thead><tbody id="transactionsTableBody"><tr><td colspan="6" class="text-center text-muted py-4">Loading transactionsâ€¦</td></tr></tbody></table></div></div>
<div class="modal fade" id="transactionModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="transactionModalTitle">Transaction</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="transactionModalBody"></div></div></div></div>
<?php $pageScripts = ['/CESBANK/js/transactions.js']; require_once __DIR__ . '/../../layout/footer.php'; ?>
