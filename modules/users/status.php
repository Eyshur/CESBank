<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/User.php';

cb_require_role(['Administrator']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$id     = (int) ($_POST['user_id'] ?? 0);
$status = $_POST['status'] ?? '';

if (!in_array($status, ['Active', 'Inactive'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status.']);
    exit;
}

$userModel = new User();
$existing = $userModel->find($id);

if (!$existing) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

// Prevent an admin from locking themselves out.
if ((int) $_SESSION['user_id'] === $id && $status === 'Inactive') {
    echo json_encode(['success' => false, 'message' => "You can't deactivate your own account while logged in."]);
    exit;
}

$userModel->setStatus($id, $status);
$userModel->logActivity($_SESSION['user_id'], "Set {$existing['username']}'s status to {$status}");

echo json_encode(['success' => true, 'message' => "Account marked as {$status}."]);