<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/Transaction.php';
cb_require_role(['Administrator', 'Employee', 'Customer']);
$accounts = (new Transaction())->accountsForUser($_SESSION['user_id'], $_SESSION['role']);
?>
<?php if (!$accounts): ?><p class="text-muted mb-0">There are no active accounts available for this transaction.</p><?php else: ?>
<form id="transactionForm" novalidate><div class="row g-3">
<div class="col-md-6"><label class="form-label">Transaction Type</label><select name="transaction_type" id="transactionTypeSelect" class="form-select cb-input" required><option value="">Select typeâ€¦</option><option value="Deposit">Deposit</option><option value="Withdraw">Withdraw</option><option value="Transfer">Transfer</option></select></div>
<div class="col-md-6"><label class="form-label">Amount</label><div class="input-group"><span class="input-group-text">â‚±</span><input type="number" name="amount" class="form-control cb-input" min="0.01" step="0.01" required></div></div>
<div class="col-12"><label class="form-label">Source Account</label><select name="account_id" class="form-select cb-input" required><option value="">Select accountâ€¦</option><?php foreach ($accounts as $a): ?><option value="<?= (int)$a['account_id'] ?>"><?= htmlspecialchars($a['account_number'] . ' â€” ' . $a['first_name'] . ' ' . $a['last_name'] . ' (â‚±' . number_format($a['balance'], 2) . ')') ?></option><?php endforeach; ?></select></div>
<div class="col-12 d-none" id="receiverAccountGroup"><label class="form-label">Destination Account</label><select name="receiver_id" id="receiverId" class="form-select cb-input"><option value="">Select destination accountâ€¦</option><?php foreach ($accounts as $a): ?><option value="<?= (int)$a['account_id'] ?>"><?= htmlspecialchars($a['account_number'] . ' â€” ' . $a['first_name'] . ' ' . $a['last_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Remarks <span class="text-muted">(optional)</span></label><textarea name="remarks" class="form-control cb-input" rows="2" maxlength="500"></textarea></div>
</div><div class="d-flex justify-content-end gap-2 mt-4"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn cb-btn-primary" id="transactionSubmit" type="submit">Process Transaction</button></div></form>
<?php endif; ?>
