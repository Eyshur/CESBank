<?php
require_once __DIR__ . '/../../auth/guard.php';
cb_require_role(['Administrator']);
?>
<form id="userForm" data-mode="create" novalidate>
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
                <option value="">Select role…</option>
                <option value="Administrator">Administrator</option>
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
                    <option value="">Select…</option>
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
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn cb-btn-primary" id="userFormSubmit">Create User</button>
    </div>
</form>