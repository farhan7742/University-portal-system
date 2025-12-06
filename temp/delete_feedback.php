<?php
// Include database connection file
include('connection.php');

// Check if request method is POST (for security and data integrity)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate the ID from POST data
    // Using intval() to ensure we get an integer, preventing SQL injection
    $id = intval($_POST['id']);

    // Prepare SQL statement with parameterized query for security
    $sql = "DELETE FROM feedback WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    // Bind parameter: "i" indicates integer type for the ID
    $stmt->bind_param("i", $id);

    // Execute the prepared statement and check result
    if ($stmt->execute()) {
        // Success message (consider JSON response for AJAX calls)
        echo "Feedback deleted successfully!";
    } else {
        // Error handling with database error details
        echo "Error deleting feedback: " . $conn->error;
        // Note: In production, log errors instead of displaying to users
    }

    // Close prepared statement to free resources
    $stmt->close();
} else {
    // Invalid request method - only POST is accepted
    echo "Invalid request method!";
}

// Close database connection
$conn->close();
?>