<?php
// --- FIXES ADDED ---
ob_start();                           // Start output buffer to capture any stray output
header("Content-Type: application/json"); // Set response type to JSON
error_reporting(0);                   // Prevent warnings from breaking JSON output
// ---------------------

// Include database configuration file
require_once 'confi.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If not POST, return error response
    jsonResponse(false, 'Invalid request method');
}

// Sanitize and retrieve form inputs with fallback to empty string if not set
$firstName       = sanitizeInput($_POST['firstName'] ?? ''); // First name from POST data
$lastName        = sanitizeInput($_POST['lastName'] ?? '');  // Last name from POST data
$email           = sanitizeInput($_POST['email'] ?? '');     // Email from POST data
$studentId       = sanitizeInput($_POST['studentId'] ?? ''); // Student ID from POST data
$password        = $_POST['password'] ?? '';                 // Password (not sanitized for special characters)
$confirmPassword = $_POST['confirmPassword'] ?? '';          // Confirm password field
$terms           = $_POST['terms'] ?? '';                    // Terms agreement checkbox

// Initialize errors array to collect validation errors
$errors = [];

// Validate required fields
if (empty($firstName))  $errors[] = 'First name is required';
if (empty($lastName))   $errors[] = 'Last name is required';

// Validate email format
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

// Validate other required fields
if (empty($studentId))  $errors[] = 'Student ID is required';
if (empty($password))   $errors[] = 'Password is required';

// Validate password strength if password is provided
if ($password) {
    // Check minimum length
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters long';
    // Check for at least one uppercase letter
    if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Password must contain at least one uppercase letter';
    // Check for at least one lowercase letter
    if (!preg_match('/[a-z]/', $password)) $errors[] = 'Password must contain at least one lowercase letter';
    // Check for at least one number
    if (!preg_match('/[0-9]/', $password)) $errors[] = 'Password must contain at least one number';
}

// Check if password and confirmation match
if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match';
}

// Verify terms agreement
if (empty($terms)) {
    $errors[] = 'You must agree to the Terms of Service and Privacy Policy';
}

// If there are validation errors, return them
if (!empty($errors)) {
    ob_clean(); // Clear output buffer to ensure clean JSON response
    jsonResponse(false, implode('<br>', $errors)); // Combine errors with line breaks
}

// Establish database connection using function from confi.php
$conn = getDBConnection();
if (!$conn) {
    ob_clean(); // Clear output buffer
    jsonResponse(false, 'Database connection failed'); // Return connection error
}

try {
    // Check if email or student ID already exists in database
    $stmt = $conn->prepare("SELECT id FROM active_users WHERE email = ? OR student_id = ?");
    $stmt->bind_param("ss", $email, $studentId); // Bind parameters to prevent SQL injection
    $stmt->execute(); // Execute the prepared statement
    $check = $stmt->get_result(); // Get result set

    // If records found, user already exists
    if ($check->num_rows > 0) {
        ob_clean(); // Clear output buffer
        jsonResponse(false, 'Email or Student ID already registered');
    }

    $stmt->close(); // Close the prepared statement

    // Hash the password for secure storage
    $hashed = password_hash($password, PASSWORD_BCRYPT);

    // Prepare INSERT statement to create new user
    $stmt = $conn->prepare("
        INSERT INTO active_users (student_id, email, first_name, last_name, password_hash, is_active)
        VALUES (?, ?, ?, ?, ?, 1)
    ");
    // Bind parameters for the INSERT statement
    $stmt->bind_param("sssss", $studentId, $email, $firstName, $lastName, $hashed);

    // Execute the INSERT statement
    if (!$stmt->execute()) {
        ob_clean(); // Clear output buffer
        jsonResponse(false, 'Registration failed: ' . $stmt->error); // Return SQL error
    }

    // Get the auto-generated ID of the new user
    $newUserId = $stmt->insert_id;

    // Close statement and database connection
    $stmt->close();
    $conn->close();

    // Start new session for the registered user
    session_start();
    session_regenerate_id(true); // Regenerate session ID to prevent fixation attacks

    // Set session variables for the logged-in user
    $_SESSION['user_id']    = $newUserId;     // Database user ID
    $_SESSION['student_id'] = $studentId;     // Student ID
    $_SESSION['email']      = $email;         // User's email
    $_SESSION['first_name'] = $firstName;     // First name
    $_SESSION['last_name']  = $lastName;      // Last name
    $_SESSION['user_role']  = 'student';      // Default role
    $_SESSION['login_time'] = time();         // Current timestamp for session management
    $_SESSION['is_active']  = 1;              // Account active status

    // Return success response with redirect URL
    ob_clean(); // Clear output buffer
    jsonResponse(true, 'Account created successfully!', [
        'redirect' => 'dashboard.php' // Where to redirect after successful registration
    ]);

} catch (Exception $e) {
    // Catch any unexpected exceptions
    ob_clean(); // Clear output buffer
    jsonResponse(false, 'An error occurred. Please try again.'); // Generic error message
}

?>
