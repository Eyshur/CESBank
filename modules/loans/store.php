<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../models/Loan.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../database/Database.php';

cb_require_role(['Administrator', 'Employee', 'Customer']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$isStaff = in_array($_SESSION['role'], ['Administrator', 'Employee'], true);
$pdo = Database::getInstance()->getConnection();

$loanAmount = (float) ($_POST['loan_amount'] ?? 0);
$loanTerm   = (int) ($_POST['loan_term'] ?? 0);

// Standard bank rate, used unless staff override it for an on-behalf-of application.
// Never trust a Customer-submitted rate, even though the field is "readonly" client-side —
// readonly can be bypassed client-side, so the server enforces the default regardless.
const STANDARD_RATE = 5.00;
$interestRate = $isStaff ? (float) ($_POST['interest_rate'] ?? STANDARD_RATE) : STANDARD_RATE;

$errors = [];
if ($loanAmount <= 0) $errors[] = 'Please enter a valid loan amount.';
if ($loanTerm <= 0 || $loanTerm > 360) $errors[] = 'Please enter a valid loan term (in months).';
if ($interestRate < 0 || $interestRate > 100) $errors[] = 'Please enter a valid interest rate.';

// Determine which customer this application is for.
if ($isStaff) {
    $customerId = (int) ($_POST['customer_id'] ?? 0);
    if ($customerId <= 0) {
        $errors[] = 'Please select a customer.';
    }
} else {
    $stmt = $pdo->prepare("SELECT customer_id FROM customers WHERE user_id = :uid LIMIT 1");
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $row = $stmt->fetch();
    $customerId = $row ? (int) $row['customer_id'] : 0;

    if ($customerId <= 0) {
        $errors[] = 'Your account isn\'t linked to a customer profile yet. Please contact support.';
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $loanModel = new Loan();
    $loanId = $loanModel->apply($customerId, $loanAmount, $interestRate, $loanTerm);

    $userModel = new User();
    $userModel->logActivity($_SESSION['user_id'], "Submitted loan application #{$loanId}");

    echo json_encode(['success' => true, 'message' => 'Loan application submitted successfully.']);
} catch (Exception $e) {
    error_log('Loan application failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Something went wrong while submitting the application. Please try again.']);
}