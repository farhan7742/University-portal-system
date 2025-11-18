<?php
// Include the database connection file
include('connection.php');  // This includes the file where the database connection is established

// Get the ID of the department to be deleted from the POST data
$id = $_POST['id'];  // Retrieve the department ID from the POST request. This ID is expected to be passed from a form or API request

// SQL query to delete the department record based on the given ID
$query = "DELETE FROM departments WHERE id = $id";  // Constructing the SQL query to delete the department with the specified ID

// Execute the query and check if it was successful
if ($conn->query($query)) {  // The query is executed using the connection object
    echo "Department deleted successfully!";  // If the query runs successfully, a success message is displayed
} else {
    // If there's an error while executing the query (e.g., invalid ID or database issues), display the error
    echo "Error: " . $conn->error;  // This will output the error message returned by the MySQL database
}
?>
