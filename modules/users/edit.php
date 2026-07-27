<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/User.php';

cb_require_role(['Administrator']);

$id = (int) ($_GET['id'] ?? 0);
$userModel = new User();
$user = $userModel->findWithCustomerProfile($id);

$pageTitle = 'Edit User';
require_once __DIR__ . '/../../layout/header.php';

function cb_selected($value, $current): string
{
    return $value === $current ? 'selected' : '';
}
?>

<div class="mb-4">
    <a href="index.php" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to Users</a>
    <h1 class="h4 mt-2 mb-1">Edit User</h1>
    <p class="text-muted mb-0">Update account details or profile information.</p>
</div>

<?php if (!$user): ?>
    <div class="cb-card p-4">
        <p class="text-danger mb-0">User not found.</p>
    </div>
<?php else: ?>
    <div class="cb-card p-4" style="max-width: 720px;">
        <form id="userForm" data-mode="edit" data-action="update.php" data-user-id="<?= (int) $user['user_id'] ?>" novalidate>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control cb-input" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control cb-input" name="password" placeholder="Leave blank to keep current password">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select class="form-select cb-input" name="role" id="roleSelect" required>
                        <option value="Administrator" <?= cb_selected('Administrator', $user['role']) ?>>Administrator</option>
                        <option value="Employee" <?= cb_selected('Employee', $user['role']) ?>>Employee</option>
                        <option value="Customer" <?= cb_selected('Customer', $user['role']) ?>>Customer</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select class="form-select cb-input" name="status">
                        <option value="Active" <?= cb_selected('Active', $user['status']) ?>>Active</option>
                        <option value="Inactive" <?= cb_selected('Inactive', $user['status']) ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div id="customerFields" class="<?= $user['role'] === 'Customer' ? '' : 'd-none' ?> mt-2">
                <hr class="my-3">
                <p class="text-muted small mb-3">Customer profile details</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control cb-input" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control cb-input" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Birthdate</label>
                        <input type="date" class="form-control cb-input" name="birthdate" value="<?= htmlspecialchars($user['birthdate'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender</label>
                        <select class="form-select cb-input" name="gender">
                            <option value="">Select…</option>
                            <option value="Male" <?= cb_selected('Male', $user['gender'] ?? '') ?>>Male</option>
                            <option value="Female" <?= cb_selected('Female', $user['gender'] ?? '') ?>>Female</option>
                            <option value="Other" <?= cb_selected('Other', $user['gender'] ?? '') ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contact No.</label>
                        <input type="text" class="form-control cb-input" name="contact_no" value="<?= htmlspecialchars($user['contact_no'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control cb-input" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control cb-input" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn cb-btn-primary" id="userFormSubmit">Save Changes</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php
$pageScripts = ['/CESBANK/js/user-form.js'];
require_once __DIR__ . '/../../layout/footer.php';
?>