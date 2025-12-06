<?php
// Include database connection file
include('connection.php');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // STEP 1: Get and sanitize form input data
    $name = trim($_POST['name']);        // Remove whitespace from name
    $email = trim($_POST['email']);      // Remove whitespace from email
    $subject = trim($_POST['subject']);  // Remove whitespace from subject
    $message = trim($_POST['message']);  // Remove whitespace from message
    // Get rating with default value of 5 if not provided
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 5;

    // STEP 2: Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo "All fields are required!";
        exit;
    }

    // STEP 3: Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Please enter a valid email address!";
        exit;
    }

    // STEP 4: Validate rating range (1-5)
    if ($rating < 1 || $rating > 5) {
        echo "Please select a valid rating!";
        exit;
    }

    // STEP 5: Insert feedback into database
    $sql = "INSERT INTO feedback (name, email, subject, message, rating) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    // Bind parameters: "ssssi" = string, string, string, string, integer
    $stmt->bind_param("ssssi", $name, $email, $subject, $message, $rating);

    // STEP 6: Execute and check result
    if ($stmt->execute()) {
        echo "Feedback submitted successfully!";
    } else {
        echo "Error submitting feedback: " . $conn->error;
    }

    // STEP 7: Close prepared statement
    $stmt->close();
} else {
    // Handle invalid request method
    echo "Invalid request method!";
}

// Close database connection
$conn->close();
?>