<?php
// Database configuration
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "university_db");

// Database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        return false;
    }
    return $conn;
}

// Input sanitization
function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

// JSON response handler (UPDATED FOR UNIT TESTING)
function jsonResponse($success, $message, $data = []) {
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);

    // 🔥 IMPORTANT FIX: stop exit() during unit testing
    if (!defined('UNIT_TESTING')) {
        exit;
    }
}
?>