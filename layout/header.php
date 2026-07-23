<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine a friendly page title if the including page set one.
$pageTitle = $pageTitle ?? 'CESBANK';
$role      = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> · CESBANK</title>

    <!-- Google Fonts: Sora for display headings, Inter for body, JetBrains Mono for figures -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (for UI affordances) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- SweetAlert2 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- CESBANK custom styles -->
    <link rel="stylesheet" href="/CESBANK/css/style.css">
</head>
<body>

<?php if ($role): ?>
<nav class="cb-navbar navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="cb-brand navbar-brand" href="#">
            <span class="cb-brand-mark">CES</span>BANK
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#cbNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="cbNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($role === 'Administrator'): ?>
                    <li class="nav-item"><a class="nav-link" href="/CESBANK/modules/dashboard/admin-dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/CESBANK/modules/users/index.php">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="/CESBANK/modules/transactions/index.php">Transactions</a></li>
                    <li class="nav-item"><a class="nav-link" href="/CESBANK/modules/reports/index.php">Reports</a></li>
                <?php elseif ($role === 'Employee'): ?>
                    <li class="nav-item"><a class="nav-link" href="/CESBANK/modules/dashboard/teller-dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/CESBANK/modules/transactions/index.php">Transactions</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/CESBANK/modules/dashboard/customer-dashboard.php">Dashboard</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav align-items-lg-center">
                <li class="nav-item me-lg-3">
                    <span class="cb-role-pill"><?= htmlspecialchars($role) ?></span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/CESBANK/auth/logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i>Log out
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="cb-main">