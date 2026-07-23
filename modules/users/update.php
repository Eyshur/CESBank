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

$id       = (int) ($_POST['user_id'] ?? 0);
$username = trim($_POST['username'] ?? '');
$password = (string) ($_POST['password'] ?? '');
$role     = $_POST['role'] ?? '';
$status   = $_POST['status'] ?? 'Active';

$userModel = new User();
$existing = $userModel->find($id);

if (!$existing) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

$errors = [];
if ($username === '') $errors[] = 'Username is required.';
if (!in_array($role, ['Administrator', 'Employee', 'Customer'], true)) $errors[] = 'Please select a valid role.';
if ($password !== '' && strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

// Only re-check uniqueness if the username actually changed.
if ($username !== '' && $username !== $existing['username'] && $userModel->usernameExists($username)) {
    $errors[] = 'That username is already taken.';
}

$customerData = null;
if ($role === 'Customer') {
    $customerData = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name'  => trim($_POST['last_name'] ?? ''),
        'birthdate'  => $_POST['birthdate'] ?? '',
        'gender'     => $_POST['gender'] ?? '',
        'email'      => trim($_POST['email'] ?? ''),
        'contact_no' => trim($_POST['contact_no'] ?? ''),
        'address'    => trim($_POST['address'] ?? ''),
    ];

    foreach (['first_name', 'last_name', 'birthdate', 'gender', 'email', 'contact_no', 'address'] as $field) {
        if ($customerData[$field] === '') {
            $errors[] = 'All customer profile fields are required.';
            break;
        }
    }

    if ($customerData['email'] !== '' && !filter_var($customerData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $userModel->updateWithProfile($id, [
        'username' => $username,
        'password' => $password,
        'role'     => $role,
    ], $customerData);

    // Status is updated separately (also reachable via status.php's quick toggle).
    $userModel->setStatus($id, $status);

    $userModel->logActivity($_SESSION['user_id'], "Updated account: {$username}");

    echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
} catch (Exception $e) {
    error_log('User update failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Something went wrong while updating the user. Please try again.']);
}