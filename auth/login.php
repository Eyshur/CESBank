<?php
require_once __DIR__ . '/../models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, bounce straight to the right dashboard.
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . dashboardForRole($_SESSION['role']));
    exit;
}

function dashboardForRole(string $role): string
{
    return match ($role) {
        'Administrator' => '/CESBANK/modules/dashboard/admin-dashboard.php',
        'Employee'      => '/CESBANK/modules/dashboard/teller-dashboard.php',
        default         => '/CESBANK/modules/dashboard/customer-dashboard.php',
    };
}

/**
 * --- AJAX handler ---
 * Handles the POST login request and returns JSON only.
 * Runs and exits BEFORE any HTML is rendered below.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter both your username and password.',
        ]);
        exit;
    }

    $userModel = new User();
    $user = $userModel->authenticate($username, $password);

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Incorrect username or password, or the account is inactive.',
        ]);
        exit;
    }

    // Regenerate session id on privilege change to prevent session fixation.
    session_regenerate_id(true);

    $_SESSION['user_id']  = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];

    $userModel->logActivity($user['user_id'], 'Logged in');

    echo json_encode([
        'success'  => true,
        'message'  => 'Login successful. Redirecting…',
        'redirect' => dashboardForRole($user['role']),
    ]);
    exit;
}

/**
 * --- GET request: render the login page ---
 */
$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in · CESBANK</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="/CESBANK/css/style.css">
</head>
<body class="cb-auth-body">

<div class="cb-auth-shell">
    <!-- Ledger panel: brand side -->
    <div class="cb-auth-ledger">
        <div class="cb-auth-ledger-inner">
            <div class="cb-brand cb-brand-lg">
                <span class="cb-brand-mark">CES</span>BANK
            </div>
            <p class="cb-auth-tagline">
                A clear, dependable ledger for every account, transaction, and customer you manage.
            </p>
            <div class="cb-ledger-lines" aria-hidden="true">
                <div></div><div></div><div></div><div></div><div></div><div></div>
            </div>
        </div>
    </div>

    <!-- Form panel -->
    <div class="cb-auth-form-panel">
        <form id="loginForm" class="cb-auth-form" novalidate>
            <h1 class="cb-auth-title">Welcome back</h1>
            <p class="cb-auth-subtitle">Log in to access your CESBANK account.</p>

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control cb-input" id="username" name="username" autocomplete="username" required>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group cb-input-group">
                    <input type="password" class="form-control cb-input" id="password" name="password" autocomplete="current-password" required>
                    <button class="btn cb-btn-toggle" type="button" id="togglePassword" tabindex="-1">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn cb-btn-primary w-100" id="loginBtn">
                Log in
            </button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    $('#togglePassword').on('click', function () {
        const input = $('#password');
        const icon = $(this).find('i');
        const isHidden = input.attr('type') === 'password';
        input.attr('type', isHidden ? 'text' : 'password');
        icon.toggleClass('bi-eye bi-eye-slash');
    });

    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        const username = $('#username').val().trim();
        const password = $('#password').val();

        if (!username || !password) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing details',
                text: 'Please enter both your username and password.',
                confirmButtonColor: '#1F4FD8',
            });
            return;
        }

        const $btn = $('#loginBtn');
        $btn.prop('disabled', true).text('Logging in…');

        $.ajax({
            url: 'login.php',
            method: 'POST',
            dataType: 'json',
            data: { username, password },
        })
        .done(function (res) {
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Welcome back',
                    text: res.message,
                    timer: 1200,
                    showConfirmButton: false,
                }).then(() => {
                    window.location.href = res.redirect;
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Login failed',
                    text: res.message,
                    confirmButtonColor: '#1F4FD8',
                });
            }
        })
        .fail(function () {
            Swal.fire({
                icon: 'error',
                title: 'Something went wrong',
                text: 'Could not reach the server. Please try again.',
                confirmButtonColor: '#1F4FD8',
            });
        })
        .always(function () {
            $btn.prop('disabled', false).text('Log in');
        });
    });
});
</script>
</body>
</html>