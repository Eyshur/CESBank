<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/User.php';

cb_require_role(['Administrator']);

$id = (int) ($_GET['id'] ?? 0);
$userModel = new User();
$user = $userModel->findWithCustomerProfile($id);

if (!$user) {
    echo '<p class="text-danger mb-0">User not found.</p>';
    exit;
}
?>
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

<div class="d-flex justify-content-end mt-4">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
</div>