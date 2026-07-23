<?php
/**
 * guard.php
 * Small auth helpers included at the top of any protected page,
 * before layout/header.php. Keeps role-checking logic in one place.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function cb_require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /CESBANK/auth/login.php');
        exit;
    }
}

/**
 * Require the logged-in user to have one of the given roles.
 * Usage: cb_require_role(['Administrator']);
 */
function cb_require_role(array $allowedRoles): void
{
    cb_require_login();

    if (!in_array($_SESSION['role'], $allowedRoles, true)) {
        http_response_code(403);
        die('<div style="font-family: sans-serif; padding: 3rem; text-align: center;">
                <h2>403 — Access denied</h2>
                <p>You don\'t have permission to view this page.</p>
                <a href="/CESBANK/auth/login.php">Back to login</a>
             </div>');
    }
}

/**
 * True if the current request is an AJAX call (jQuery sends this header
 * automatically). Used by pages like modules/users/index.php that serve
 * both a full HTML page (direct visit) and JSON (AJAX refresh) from one file.
 */
function cb_is_ajax(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}