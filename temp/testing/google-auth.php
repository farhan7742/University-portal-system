<?php
require_once 'config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['credential'])) {
    jsonResponse(false, "Missing Google token");
}

$token = $input['credential'];

/* Verify token */
$googleUrl = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $token;
$response = file_get_contents($googleUrl);
$userData = json_decode($response, true);

if (!isset($userData['email'])) {
    jsonResponse(false, "Invalid Google token");
}

$email = sanitizeInput($userData['email']);
$name  = sanitizeInput($userData['name']);
$googleId = sanitizeInput($userData['sub']);

$conn = getDBConnection();
if (!$conn) jsonResponse(false, "DB error");

/* Check user */
$stmt = $conn->prepare(
    "SELECT id FROM active_users WHERE email = ? LIMIT 1"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Auto signup
    $stmt = $conn->prepare(
        "INSERT INTO active_users (full_name, email, google_id, is_active)
         VALUES (?, ?, ?, 1)"
    );
    $stmt->bind_param("sss", $name, $email, $googleId);
    $stmt->execute();
    $userId = $stmt->insert_id;
} else {
    $user = $result->fetch_assoc();
    $userId = $user['id'];
}

/* Session */
$_SESSION['user_id'] = $userId;
$_SESSION['is_active'] = 1;

jsonResponse(true, "Google login successful");
