<?php
// Include the database connection file
include('connection.php');  // This file contains the connection to your MySQL database

// Get the ID of the course to be deleted from the POST data
$id = $_POST['id'];  // The ID of the course is passed via a POST request

// Delete the course from the 'courses' table based on the given ID
$query = "DELETE FROM courses WHERE id = $id";  // The query will delete the course with the specified ID from the 'courses' table

// Execute the query and check if it was successful
if ($conn->query($query)) {  // The query is executed and if successful, the message is displayed
    echo "Course deleted successfully!";  // This message will be displayed if the deletion was successful
} else {
    // If there was an error executing the query, the error message from the database is displayed
    echo "Error: " . $conn->error;  // This will output the error message from the MySQL database
}
?>
