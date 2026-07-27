<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/Loan.php';
require_once __DIR__ . '/../../models/User.php';

cb_require_role(['Administrator', 'Employee']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$loanId = (int) ($_POST['loan_id'] ?? 0);
$status = $_POST['status'] ?? '';

if (!in_array($status, ['Accepted', 'Declined'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status.']);
    exit;
}

try {
    $loanModel = new Loan();
    $updated = $loanModel->setStatus($loanId, $status);

    if (!$updated) {
        echo json_encode([
            'success' => false,
            'message' => 'This application is no longer pending — it may have already been processed.',
        ]);
        exit;
    }

    $userModel = new User();
    $userModel->logActivity($_SESSION['user_id'], "Marked loan #{$loanId} as {$status}");

    echo json_encode(['success' => true, 'message' => "Loan application {$status}.", 'status' => $status]);
} catch (Exception $e) {
    error_log('Loan status update failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}