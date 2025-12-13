<?php
// --- FIXES ADDED ---
ob_start();                           // Start output buffer
header("Content-Type: application/json");
error_reporting(0);                   // Prevent warnings from breaking JSON
// ---------------------

require_once 'config.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

// Get and sanitize input data
$loginInput = sanitizeInput($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($loginInput)) jsonResponse(false, 'Student ID or Email is required');
if (empty($password)) jsonResponse(false, 'Password is required');

// DB connection
$conn = getDBConnection();
if (!$conn) jsonResponse(false, 'Database connection failed');

try {
    // Determine if email or student ID
    if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("SELECT id, student_id, email, first_name, last_name, password_hash, is_active 
                                FROM active_users WHERE email = ?");
    } else {
        $stmt = $conn->prepare("SELECT id, student_id, email, first_name, last_name, password_hash, is_active 
                                FROM active_users WHERE student_id = ?");
    }

    if (!$stmt) {
        jsonResponse(false, 'Database prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $loginInput);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        jsonResponse(false, 'Invalid login credentials');
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Check active status
    if ($user['is_active'] != 1) {
        jsonResponse(false, 'Your account is not active. Please contact support.');
    }

    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        jsonResponse(false, 'Invalid login credentials');
    }

    // Start session
    session_start();
    session_regenerate_id(true);

    $_SESSION['user_id']     = $user['id'];
    $_SESSION['student_id']  = $user['student_id'];
    $_SESSION['email']       = $user['email'];
    $_SESSION['first_name']  = $user['first_name'];
    $_SESSION['last_name']   = $user['last_name'];
    $_SESSION['user_role']   = 'student';
    $_SESSION['login_time']  = time();
    $_SESSION['is_active']   = $user['is_active'];

    // Update last login
    $update = $conn->prepare("UPDATE active_users SET last_login = NOW() WHERE id = ?");
    $update->bind_param("i", $user['id']);
    $update->execute();
    $update->close();

    $conn->close();

    // RETURN CLEAN JSON
    ob_clean();
    jsonResponse(true, 'Login successful!', [
        'redirect' => 'dashboard.php'
    ]);

} catch (Exception $e) {
    ob_clean();
    jsonResponse(false, 'An error occurred. Please try again.');
}

?>
