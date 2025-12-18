<?php
define('UNIT_TESTING', true);

function runTest($title, $postData, $expectedSuccess) {

    // 🔥 DESTROY SESSION BEFORE EACH TEST
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }

    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = $postData;

    ob_start();
    include "login.php";
    $output = ob_get_clean();

    echo "<h4>$title</h4>";
    echo "<pre>$output</pre>";

    $json = json_decode($output, true);

    if ($json && isset($json['success'])) {
        if ($json['success'] === $expectedSuccess) {
            echo "RESULT: PASS ✅<hr>";
        } else {
            echo "RESULT: FAIL ❌<hr>";
        }
    } else {
        echo "RESULT: INVALID RESPONSE ❌<hr>";
    }
}
?>


h2>Unit Testing – Login Module</h2>

<?php
// Test 1: Valid Login (should PASS)
runTest(
    "Test 1: Valid Login",
    ["login" => "farhansadik91683@gmail.com", "password" => "password"],
    true
);

// Test 2: Invalid Password (should FAIL)
runTest(
    "Test 2: Invalid Password",
    ["login" => "farhansadik91683@gmail.com", "password" => "wrongpass"],
    false
);

// Test 3: Empty Password (should FAIL)
runTest(
    "Test 3: Empty Password",
    ["login" => "farhansadik91683@gmail.com", "password" => ""],
    false
);

// Test 4: Inactive Account (should FAIL)
runTest(
    "Test 4: Inactive Account",
    ["login" => "TEST123", "password" => "password"],
    false
);
?>