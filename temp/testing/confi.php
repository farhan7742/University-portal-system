<?php
// config.php

// -----------------------------------------------------
// JSON-SAFE DEBUG SETTINGS
// -----------------------------------------------------

// DO NOT output warnings as HTML (breaks JSON)
error_reporting(E_ALL);
ini_set('display_errors', 0);       // changed from 1 → 0

// If you need to see errors, check Apache/PHP error logs instead.

// -----------------------------------------------------
// SESSION START
// -----------------------------------------------------

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -----------------------------------------------------
// DATABASE CONFIG
// -----------------------------------------------------

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'university_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('USER_TABLE', 'active_users');

// -----------------------------------------------------
// JSON RESPONSE HELPER (CLEAN & SAFE)
// -----------------------------------------------------

function jsonResponse($success, $message = '', $data = []) {
    // clean any accidental output BEFORE JSON
    if (ob_get_length()) { ob_clean(); }

    header('Content-Type: application/json; charset=utf-8');
    http_response_code($success ? 200 : 400);

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ], JSON_UNESCAPED_UNICODE);

    exit();
}

// -----------------------------------------------------
// SANITIZATION
// -----------------------------------------------------

function sanitizeInput($data) {
    if (!isset($data)) return '';
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// -----------------------------------------------------
// DATABASE CONNECTION
// -----------------------------------------------------

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        error_log("DB ERROR: " . $conn->connect_error);
        return false;
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

// -----------------------------------------------------
// PASSWORD FUNCTIONS
// -----------------------------------------------------

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// -----------------------------------------------------
// AUTH HELPERS
// -----------------------------------------------------

function isLoggedIn() {
    return isset($_SESSION['user_id']) &&
           isset($_SESSION['is_active']) &&
           $_SESSION['is_active'] == 1;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: uniportal.html');
        exit();
    }
}

function logout() {
    session_destroy();
    $_SESSION = [];
    session_regenerate_id(true);
    header('Location: uniportal.html');
    exit();
}

// -----------------------------------------------------
// SECURE SESSION
// -----------------------------------------------------

function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) { 
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

?>
