<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../database/Database.php';

cb_require_role(['Administrator', 'Employee']);

/**
 * AJAX branch: returns the filtered user list as JSON.
 * The page's own JS calls this same URL with a jQuery $.get(),
 * which automatically sends the X-Requested-With header.
 */
if (cb_is_ajax()) {
    header('Content-Type: application/json');

    $pdo    = Database::getInstance()->getConnection();
    $role   = $_GET['role'] ?? '';
    $search = trim($_GET['search'] ?? '');

    $sql = "SELECT u.user_id, u.username, u.role, u.status, u.created_at,
                   c.customer_id, c.first_name, c.last_name, c.email, c.contact_no
            FROM users u
            LEFT JOIN customers c ON c.user_id = u.user_id
            WHERE 1=1";
    $params = [];

    if ($role !== '') {
        $sql .= " AND u.role = :role";
        $params['role'] = $role;
    }

    if ($search !== '') {
        $sql .= " AND (u.username LIKE :s1 OR c.first_name LIKE :s2 OR c.last_name LIKE :s3 OR c.email LIKE :s4)";
        $like = "%{$search}%";
        $params['s1'] = $like;
        $params['s2'] = $like;
        $params['s3'] = $like;
        $params['s4'] = $like;
    }

    $sql .= " ORDER BY u.user_id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
    exit;
}

$pageTitle = 'Users & Customers';
require_once __DIR__ . '/../../layout/header.php';
?>

<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1">Users &amp; Customers</h1>
        <p class="text-muted mb-0">Manage login accounts for staff and customers.</p>
    </div>
    <?php if ($_SESSION['role'] === 'Administrator'): ?>
        <a href="create.php" class="btn cb-btn-primary">
            <i class="bi bi-person-plus me-1"></i> Add User
        </a>
    <?php endif; ?>
</div>

<div class="cb-card p-3 mb-3">
    <div class="row g-2 align-items-center">
        <div class="col-md-4">
            <input type="text" class="form-control cb-input" id="searchInput" placeholder="Search name, username, or email…">
        </div>
        <div class="col-md-3">
            <select class="form-select cb-input" id="roleFilter">
                <option value="">All roles</option>
                <option value="Administrator">Administrator</option>
                <option value="Employee">Employee</option>
                <option value="Customer">Customer</option>
            </select>
        </div>
    </div>
</div>

<div class="cb-card p-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0" id="usersTable">
            <thead>
                <tr class="text-muted small">
                    <th class="ps-3">Name / Username</th>
                    <th>Role</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody id="usersTableBody">
                <tr><td colspan="6" class="text-center text-muted py-4">Loading users…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    window.CB_ROLE = <?= json_encode($_SESSION['role']) ?>;
</script>

<?php
$pageScripts = ['/CESBANK/js/users.js'];
require_once __DIR__ . '/../../layout/footer.php';
?>