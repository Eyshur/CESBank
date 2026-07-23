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

$username = trim($_POST['username'] ?? '');
$password = (string) ($_POST['password'] ?? '');
$role     = $_POST['role'] ?? '';
$status   = $_POST['status'] ?? 'Active';

$errors = [];

if ($username === '') $errors[] = 'Username is required.';
if ($password === '' || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
if (!in_array($role, ['Administrator', 'Employee', 'Customer'], true)) $errors[] = 'Please select a valid role.';

$userModel = new User();

if ($username !== '' && $userModel->usernameExists($username)) {
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
    $userId = $userModel->createWithProfile([
        'username' => $username,
        'password' => $password,
        'role'     => $role,
        'status'   => $status,
    ], $customerData);

    $userModel->logActivity($_SESSION['user_id'], "Created {$role} account: {$username}");

    echo json_encode(['success' => true, 'message' => 'User created successfully.', 'user_id' => $userId]);
} catch (Exception $e) {
    error_log('User creation failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Something went wrong while creating the user. Please try again.']);
}