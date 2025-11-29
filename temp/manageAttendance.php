<?php
// Include database connection file
include('connection.php');

// Set response content type to JSON for API responses
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the action from POST data to determine what operation to perform
    $action = $_POST['action'];
    
    // Check if the requested action is 'delete'
    if ($action == 'delete') {
        // Get the attendance record ID from POST data
        $id = $_POST['id'];
        
        // STEP 1: Prepare SQL delete statement
        $sql = "DELETE FROM attendance WHERE id = ?";
        $stmt = $conn->prepare($sql);
        // Bind parameter: "i" for integer (id)
        $stmt->bind_param("i", $id);
        
        // STEP 2: Execute the deletion and check result
        if ($stmt->execute()) {
            // Success response
            echo json_encode(["success" => true, "message" => "Attendance record deleted successfully"]);
        } else {
            // Error response
            echo json_encode(["success" => false, "message" => "Error deleting attendance record"]);
        }
        
        // STEP 3: Close prepared statement
        $stmt->close();
    }
} else {
    // Handle invalid request method with JSON response
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close database connection
$conn->close();
?>