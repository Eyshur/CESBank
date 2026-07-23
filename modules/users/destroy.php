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

$id = (int) ($_POST['user_id'] ?? 0);

if ((int) $_SESSION['user_id'] === $id) {
    echo json_encode(['success' => false, 'message' => "You can't delete your own account while logged in."]);
    exit;
}

$userModel = new User();
$existing = $userModel->find($id);

if (!$existing) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

try {
    $result = $userModel->deleteUserCascade($id);

    if ($result['success']) {
        $userModel->logActivity($_SESSION['user_id'], "Deleted account: {$existing['username']}");
    }

    echo json_encode($result);
} catch (Exception $e) {
    error_log('User deletion failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Something went wrong while deleting the user. Please try again.']);
}