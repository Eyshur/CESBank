<?php
require_once __DIR__ . '/../../auth/guard.php';

cb_require_role(['Administrator']);

$pageTitle = 'Add User';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="mb-4">
    <a href="index.php" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Back to Users</a>
    <h1 class="h4 mt-2 mb-1">Add User</h1>
    <p class="text-muted mb-0">Create a login account for staff or a customer.</p>
</div>

<div class="cb-card p-4" style="max-width: 720px;">
    <form id="userForm" data-mode="create" data-action="store.php" novalidate>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Username</label>
                <input type="text" class="form-control cb-input" name="username" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input type="password" class="form-control cb-input" name="password" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select class="form-select cb-input" name="role" id="roleSelect" required>
                    <option value="" hidden>Select role…</option>
                    <option value="Employee">Employee</option>
                    <option value="Customer">Customer</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select cb-input" name="status" required>
                    <option value="Active" selected>Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div id="customerFields" class="d-none mt-2">
            <hr class="my-3">
            <p class="text-muted small mb-3">Customer profile details</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control cb-input" name="first_name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control cb-input" name="last_name">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Birthdate</label>
                    <input type="date" class="form-control cb-input" name="birthdate">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender</label>
                    <select class="form-select cb-input" name="gender">
                        <option value="" hidden>Select…</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact No.</label>
                    <input type="text" class="form-control cb-input" name="contact_no">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control cb-input" name="email">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control cb-input" name="address">
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn cb-btn-primary" id="userFormSubmit">Create User</button>
        </div>
    </form>
</div>

<?php
$pageScripts = ['/CESBANK/js/user-form.js'];
require_once __DIR__ . '/../../layout/footer.php';
?>