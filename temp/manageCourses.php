<?php
// Include the database connection file
include('connection.php');  // This file contains the connection details to your MySQL database

// Set the response content type to JSON
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the action and id from the POST request
    $action = $_POST['action'];  // The action could be 'get', 'update', or 'delete'
    $id = $_POST['id'] ?? null;  // Get the ID (if available) or set it to null if not

    // Handle 'get' action (retrieve course data for editing)
    if ($action == 'get' && $id) {
        // Prepare a query to fetch the course data for the specified ID
        $query = "SELECT * FROM courses WHERE id = ?";  // Use a prepared statement to select the course by its ID
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);  // Bind the ID as an integer to the query
        $stmt->execute();  // Execute the query
        $result = $stmt->get_result();  // Get the result of the query
        
        // Check if any rows are returned
        if ($result->num_rows > 0) {
            // Fetch the course data as an associative array
            $course = $result->fetch_assoc();
            // Ensure that all expected keys exist with default values (in case any are missing)
            $course = array_merge([
                'course_name' => '',
                'course_code' => '',
                'term_name' => '',
                'term_code' => ''
            ], $course);  // Merge default values with the fetched data
            echo json_encode(['success' => true, 'course' => $course]);  // Return the course data in JSON format
        } else {
            // If no course is found, return a failure message
            echo json_encode(['success' => false, 'message' => 'Course not found']);
        }
        $stmt->close();  // Close the prepared statement
    }
    // Handle 'update' action (update course data)
    elseif ($action == 'update' && $id) {
        // Get the course data from the POST request
        $course_name = trim($_POST['course_name']);  // Course name
        $course_code = trim($_POST['course_code']);  // Course code
        $term_name = trim($_POST['term_name']);  // Term name
        $term_code = trim($_POST['term_code']);  // Term code

        // Check if the course code already exists for another course
        $check_code = $conn->prepare("SELECT id FROM courses WHERE course_code = ? AND id != ?");
        $check_code->bind_param("si", $course_code, $id);  // Bind the course code and ID to the query
        $check_code->execute();  // Execute the query to check if the course code already exists
        $check_code->store_result();  // Store the result for checking
        
        // If the course code already exists for another course, return an error
        if ($check_code->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Course code already exists!']);
            $check_code->close();  // Close the prepared statement
            exit;  // Exit the script to prevent further execution
        }
        $check_code->close();  // Close the prepared statement

        // Prepare the query to update the course data
        $query = "UPDATE courses SET course_name = ?, course_code = ?, term_name = ?, term_code = ? WHERE id = ?";
        $stmt = $conn->prepare($query);  // Prepare the update query
        $stmt->bind_param("ssssi", $course_name, $course_code, $term_name, $term_code, $id);  // Bind parameters

        // Execute the update query and check if it was successful
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
        } else {
            // If the update fails, return an error message
            echo json_encode(['success' => false, 'message' => 'Error updating course: ' . $stmt->error]);
        }
        $stmt->close();  // Close the prepared statement
    }
    // Handle 'delete' action (delete course data)
    elseif ($action == 'delete' && $id) {
        // Prepare the query to delete the course by its ID
        $query = "DELETE FROM courses WHERE id = ?";
        $stmt = $conn->prepare($query);  // Prepare the delete query
        $stmt->bind_param("i", $id);  // Bind the ID as an integer

        // Execute the delete query and check if it was successful
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
        } else {
            // If the delete fails, return an error message
            echo json_encode(['success' => false, 'message' => 'Error deleting course: ' . $stmt->error]);
        }
        $stmt->close();  // Close the prepared statement
    }
} else {
    // If the request method is not POST, return an error message
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Close the database connection
$conn->close();
?>
