<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/Transaction.php';
require_once __DIR__ . '/../../models/User.php';

cb_require_role(['Administrator', 'Employee']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$customerId     = (int) ($_POST['customer_id'] ?? 0);
$accountType    = $_POST['account_type'] ?? '';
$openingBalance = (float) ($_POST['opening_balance'] ?? 0);

if ($customerId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing customer.']);
    exit;
}

if (!in_array($accountType, ['Savings', 'Current'], true)) {
    echo json_encode(['success' => false, 'message' => 'Please select a valid account type.']);
    exit;
}

if ($openingBalance < 0) {
    echo json_encode(['success' => false, 'message' => 'Opening balance cannot be negative.']);
    exit;
}

try {
    $txModel = new Transaction();
    $result  = $txModel->createAccount($customerId, $accountType, $openingBalance);

    $userModel = new User();
    $userModel->logActivity(
        $_SESSION['user_id'],
        "Opened a {$accountType} account ({$result['account_number']}) for customer #{$customerId}"
    );

    echo json_encode([
        'success' => true,
        'message' => "Account {$result['account_number']} opened successfully.",
    ]);
} catch (Exception $e) {
    error_log('Open account failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Something went wrong while opening the account. Please try again.']);
}