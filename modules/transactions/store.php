<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/Transaction.php';
cb_require_role(['Administrator', 'Employee']);
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}
try {
    $id = (new Transaction())->process($_SESSION['user_id'], $_SESSION['role'], (int)($_POST['account_id'] ?? 0), $_POST['transaction_type'] ?? '', (float)($_POST['amount'] ?? 0), (int)($_POST['receiver_id'] ?? 0), trim($_POST['remarks'] ?? ''));
    require_once __DIR__ . '/../../models/User.php';
    (new User())->logActivity($_SESSION['user_id'], 'Processed ' . ($_POST['transaction_type'] ?? 'transaction') . ' #' . $id);
    echo json_encode(['success' => true, 'message' => 'Transaction processed successfully.', 'transaction_id' => $id]);
} catch (InvalidArgumentException | RuntimeException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('Transaction processing failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'The transaction could not be processed. Please try again.']);
}
