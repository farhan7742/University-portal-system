<?php
ob_start();
header("Content-Type: application/json");
error_reporting(0);

require_once 'confi.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
    return;
}

// Get input
$loginInput = sanitizeInput($_POST['login'] ?? '');
$password   = $_POST['password'] ?? '';

// Validation
if (empty($loginInput)) {
    jsonResponse(false, 'Student ID or Email is required');
    return;
}

if (empty($password)) {
    jsonResponse(false, 'Password is required');
    return;
}

// DB connection
$conn = getDBConnection();
if (!$conn) {
    jsonResponse(false, 'Database connection failed');
    return;
}

try {
    // Email or student ID
    if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare(
            "SELECT id, student_id, email, first_name, last_name, password_hash, is_active
             FROM active_users WHERE email = ?"
        );
    } else {
        $stmt = $conn->prepare(
            "SELECT id, student_id, email, first_name, last_name, password_hash, is_active
             FROM active_users WHERE student_id = ?"
        );
    }

    if (!$stmt) {
        jsonResponse(false, 'Database prepare failed');
        return;
    }

    $stmt->bind_param("s", $loginInput);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        jsonResponse(false, 'Invalid login credentials');
        return;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Active check
    if ((int)$user['is_active'] !== 1) {
        jsonResponse(false, 'Your account is not active. Please contact support.');
        return;
    }

    // Password check
    if (!password_verify($password, $user['password_hash'])) {
        jsonResponse(false, 'Invalid login credentials');
        return;
    }

    // Start session ONLY if not unit testing
    if (!defined('UNIT_TESTING')) {
        session_start();
        session_regenerate_id(true);

        $_SESSION['user_id']    = $user['id'];
        $_SESSION['student_id'] = $user['student_id'];
        $_SESSION['email']      = $user['email'];
        $_SESSION['user_role']  = 'student';
    }

    // Update last login
    $update = $conn->prepare(
        "UPDATE active_users SET last_login = NOW() WHERE id = ?"
    );
    $update->bind_param("i", $user['id']);
    $update->execute();
    $update->close();

    $conn->close();

    ob_clean();
    jsonResponse(true, 'Login successful!', [
        'redirect' => 'dashboard.php'
    ]);
    return;

} catch (Exception $e) {
    ob_clean();
    jsonResponse(false, 'An error occurred. Please try again.');
    return;
}
?>