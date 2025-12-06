<?php
// Include database connection file
include('connection.php');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize the feedback ID from POST data
    $id = intval($_POST['id']);
    // Get the new status from POST data
    $status = $_POST['status'];

    // STEP 1: Validate the status value against allowed options
    $allowed_statuses = ['new', 'read', 'replied'];
    if (!in_array($status, $allowed_statuses)) {
        echo "Invalid status!";
        exit;
    }

    // STEP 2: Prepare SQL update statement
    $sql = "UPDATE feedback SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    // Bind parameters: "s" for string (status), "i" for integer (id)
    $stmt->bind_param("si", $status, $id);

    // STEP 3: Execute the update and check result
    if ($stmt->execute()) {
        echo "Status updated successfully!";
    } else {
        echo "Error updating status: " . $conn->error;
    }

    // STEP 4: Close prepared statement
    $stmt->close();
} else {
    // Handle invalid request method
    echo "Invalid request method!";
}

// Close database connection
$conn->close();
?>