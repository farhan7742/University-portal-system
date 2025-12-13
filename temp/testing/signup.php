<?php
// --- FIXES ADDED ---
ob_start();                           // Start output buffer
header("Content-Type: application/json");
error_reporting(0);                   // Prevent warnings from breaking JSON
// ---------------------

require_once 'confi.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

$firstName       = sanitizeInput($_POST['firstName'] ?? '');
$lastName        = sanitizeInput($_POST['lastName'] ?? '');
$email           = sanitizeInput($_POST['email'] ?? '');
$studentId       = sanitizeInput($_POST['studentId'] ?? '');
$password        = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$terms           = $_POST['terms'] ?? '';

$errors = [];

if (empty($firstName))  $errors[] = 'First name is required';
if (empty($lastName))   $errors[] = 'Last name is required';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

if (empty($studentId))  $errors[] = 'Student ID is required';
if (empty($password))   $errors[] = 'Password is required';

if ($password) {
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters long';
    if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Password must contain at least one uppercase letter';
    if (!preg_match('/[a-z]/', $password)) $errors[] = 'Password must contain at least one lowercase letter';
    if (!preg_match('/[0-9]/', $password)) $errors[] = 'Password must contain at least one number';
}

if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match';
}

if (empty($terms)) {
    $errors[] = 'You must agree to the Terms of Service and Privacy Policy';
}

if (!empty($errors)) {
    ob_clean();
    jsonResponse(false, implode('<br>', $errors));
}

// DB connection
$conn = getDBConnection();
if (!$conn) {
    ob_clean();
    jsonResponse(false, 'Database connection failed');
}

try {
    // Check duplicates
    $stmt = $conn->prepare("SELECT id FROM active_users WHERE email = ? OR student_id = ?");
    $stmt->bind_param("ss", $email, $studentId);
    $stmt->execute();
    $check = $stmt->get_result();

    if ($check->num_rows > 0) {
        ob_clean();
        jsonResponse(false, 'Email or Student ID already registered');
    }

    $stmt->close();

    // Create account
    $hashed = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("
        INSERT INTO active_users (student_id, email, first_name, last_name, password_hash, is_active)
        VALUES (?, ?, ?, ?, ?, 1)
    ");
    $stmt->bind_param("sssss", $studentId, $email, $firstName, $lastName, $hashed);

    if (!$stmt->execute()) {
        ob_clean();
        jsonResponse(false, 'Registration failed: ' . $stmt->error);
    }

    $newUserId = $stmt->insert_id;

    $stmt->close();
    $conn->close();

    // Start session
    session_start();
    session_regenerate_id(true);

    $_SESSION['user_id']    = $newUserId;
    $_SESSION['student_id'] = $studentId;
    $_SESSION['email']      = $email;
    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name']  = $lastName;
    $_SESSION['user_role']  = 'student';
    $_SESSION['login_time'] = time();
    $_SESSION['is_active']  = 1;

    // RETURN CLEAN JSON
    ob_clean();
    jsonResponse(true, 'Account created successfully!', [
        'redirect' => 'dashboard.php'
    ]);

} catch (Exception $e) {
    ob_clean();
    jsonResponse(false, 'An error occurred. Please try again.');
}

?>

