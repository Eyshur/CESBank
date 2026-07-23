<?php
require_once __DIR__ . '/../models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['user_id'])) {
    (new User())->logActivity($_SESSION['user_id'], 'Logged out');
}

$_SESSION = [];
session_destroy();

header('Location: /CESBANK/auth/login.php');
exit;