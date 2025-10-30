<?php
// Include the database connection file
include('connection.php');  // This file contains the necessary connection details to your MySQL database

// Set the response content type to JSON
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the action and ID from POST data
    $action = $_POST['action'];  // The action can be 'get', 'update', or 'delete'
    $id = $_POST['id'] ?? null;  // Get the ID of the user, default to null if not provided

    // Handle 'get' action (retrieve user data for editing)
    if ($action == 'get' && $id) {
        // Prepare a query to fetch the user data for the specified ID
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);  // Prepare the query
        $stmt->bind_param("i", $id);  // Bind the ID as an integer to the query
        $stmt->execute();  // Execute the query
        $result = $stmt->get_result();  // Get the result of the query
        
        // Check if the user exists in the database
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();  // Fetch the user data as an associative array
            echo json_encode(['success' => true, 'user' => $user]);  // Return the user data as JSON
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);  // If no user is found, return an error message
        }
        $stmt->close();  // Close the prepared statement
    }

    // Handle 'update' action (update user data)
    elseif ($action == 'update' && $id) {
        // Retrieve the updated user data from POST request
        $name = trim($_POST['name']);  // Get the user's name, trim leading/trailing spaces
        $email = trim($_POST['email']);  // Get the user's email
        $phone_number = trim($_POST['phone_number']);  // Get the user's phone number

        // Check if the email already exists for other users
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->bind_param("si", $email, $id);  // Bind the email and ID to the query (exclude the current user)
        $check_email->execute();  // Execute the query to check for duplicate emails
        $check_email->store_result();  // Store the result for further checks
        
        // If the email already exists for another user, return an error message
        if ($check_email->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists!']);
            $check_email->close();  // Close the prepared statement
            exit;  // Exit the script to prevent further execution
        }
        $check_email->close();  // Close the prepared statement for email check

        // Prepare the query to update the user data
        $query = "UPDATE users SET name = ?, email = ?, phone_number = ? WHERE id = ?";
        $stmt = $conn->prepare($query);  // Prepare the update query
        $stmt->bind_param("sssi", $name, $email, $phone_number, $id);  // Bind the parameters to the query
        
        // Execute the query and check if it was successful
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);  // Return success message if updated
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating user: ' . $stmt->error]);  // Return error message if update fails
        }
        $stmt->close();  // Close the prepared statement
    }

    // Handle 'delete' action (delete user data)
    elseif ($action == 'delete' && $id) {
        // Prepare the query to delete the user by ID
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);  // Prepare the delete query
        $stmt->bind_param("i", $id);  // Bind the ID as an integer to the query
        
        // Execute the delete query and check if it was successful
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);  // Return success message if deleted
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting user: ' . $stmt->error]);  // Return error message if delete fails
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
