<?php
// Include the database connection file
include('connection.php');

// Get POST data
$id = $_POST['id'];

// Validate the ID (ensure it's an integer)
if (!isset($id) || !is_numeric($id)) {
    echo "Invalid ID provided.";
    exit;
}

// Prepare the SQL query to delete the attendance record
$query = "DELETE FROM attendance WHERE id = ?";
$stmt = $conn->prepare($query);

// Check if the query preparation was successful
if ($stmt === false) {
    echo "Error preparing the query: " . $conn->error;
    exit;
}

// Bind the ID parameter to the query
$stmt->bind_param("i", $id);  // "i" denotes the ID is an integer

// Execute the query and check for success
if ($stmt->execute()) {
    echo "Attendance record deleted successfully.";
} else {
    echo "Error deleting record: " . $stmt->error;
}

// Close the statement
$stmt->close();

// Close the database connection
$conn->close();
?>
