<?php
// Include the database connection file
include('connection.php');  // This includes the file where the database connection is established

// Get the ID of the user to be deleted from the POST data
$id = $_POST['id'];  // Retrieve the user ID passed through a POST request. This ID identifies which user to delete from the database

// Prepare the SQL DELETE query using a prepared statement
$query = "DELETE FROM users WHERE id = ?";  // The query deletes a user from the 'users' table where the 'id' matches the provided ID
$stmt = $conn->prepare($query);  // Prepare the query for execution

// Bind the ID parameter as an integer to the prepared statement
$stmt->bind_param("i", $id);  // "i" means the parameter type is an integer. This binds the provided ID to the query

// Execute the query and check if the operation is successful
if ($stmt->execute()) {  // Execute the query. If successful, it returns true
    echo "User deleted successfully!";  // If the user is successfully deleted, display this success message
} else {
    // If there's an error executing the query (e.g., invalid ID or database issue), display the error
    echo "Error: " . $conn->error;  // Output error message from MySQL
}

// Close the prepared statement and the database connection to free up resources
$conn->close();
?>
