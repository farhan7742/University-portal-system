<?php
// Include the database connection file
include('connection.php');  // This file contains the necessary connection details to your MySQL database

// Set the response content type to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);  // Report all errors (useful for debugging during development)
ini_set('display_errors', 1);  // Display all errors

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve action and ID from POST data
    $action = $_POST['action'] ?? '';  // Action can be 'get', 'update', or 'delete'
    $id = $_POST['id'] ?? null;  // ID of the department (required for get, update, and delete actions)

    // Debug logging: Log the action and ID
    error_log("Department Action: $action, ID: $id");

    // Handle 'get' action (retrieve department data for editing)
    if ($action == 'get' && $id) {
        // Prepare a query to fetch the department data for the specified ID
        $query = "SELECT * FROM departments WHERE id = ?";
        $stmt = $conn->prepare($query);  // Prepare the query statement

        // Check if the statement preparation was successful
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            exit;
        }

        $stmt->bind_param("i", $id);  // Bind the ID as an integer to the query
        $stmt->execute();  // Execute the query
        $result = $stmt->get_result();  // Get the result of the query

        // Check if any department is found
        if ($result->num_rows > 0) {
            $department = $result->fetch_assoc();  // Fetch the department data
            echo json_encode([
                'success' => true,
                'department' => [
                    'department_name' => $department['department_name'] ?? '',
                    'department_code' => $department['department_code'] ?? '',
                    'program_name' => $department['program_name'] ?? '',
                    'faculty_name' => $department['faculty_name'] ?? '',
                    'academic_year' => $department['academic_year'] ?? ''
                ]
            ]);
        } else {
            // If no department is found, return an error message
            echo json_encode(['success' => false, 'message' => 'Department not found']);
        }
        $stmt->close();  // Close the prepared statement
    }

    // Handle 'update' action (update department data)
    elseif ($action == 'update' && $id) {
        // Retrieve the updated department data from the POST request
        $department_name = trim($_POST['dept_name'] ?? '');
        $department_code = trim($_POST['dept_code'] ?? '');
        $program_name = trim($_POST['program_name'] ?? '');
        $faculty_name = trim($_POST['faculty'] ?? '');
        $academic_year = trim($_POST['year'] ?? '');

        // Validate required fields (department name and code)
        if (empty($department_name) || empty($department_code)) {
            echo json_encode(['success' => false, 'message' => 'Department Name and Code are required!']);
            exit;
        }

        // Check if the department code already exists for another department (excluding the current department)
        $check_code = $conn->prepare("SELECT id FROM departments WHERE department_code = ? AND id != ?");
        $check_code->bind_param("si", $department_code, $id);  // Bind the department code and ID to the query
        $check_code->execute();  // Execute the query to check for duplicate department codes
        $check_code->store_result();  // Store the result for further checks

        // If the department code already exists, return an error
        if ($check_code->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Department code already exists!']);
            $check_code->close();
            exit;
        }
        $check_code->close();  // Close the prepared statement for checking the department code

        // Prepare the SQL query to update the department data
        $query = "UPDATE departments SET department_name = ?, department_code = ?, program_name = ?, faculty_name = ?, academic_year = ? WHERE id = ?";
        $stmt = $conn->prepare($query);  // Prepare the update query

        // Check if the statement preparation was successful
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            exit;
        }

        // Set the academic year to null if it's empty (optional field)
        $academic_year = !empty($academic_year) ? $academic_year : null;
        
        // Bind the updated department data to the query
        $stmt->bind_param("sssssi", $department_name, $department_code, $program_name, $faculty_name, $academic_year, $id);

        // Execute the update query and return success or failure
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Department updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating department: ' . $stmt->error]);
        }
        $stmt->close();  // Close the prepared statement
    }

    // Handle 'delete' action (delete department data)
    elseif ($action == 'delete' && $id) {
        // Log the delete attempt for debugging purposes
        error_log("Attempting to delete department ID: $id");

        // First, check if the department exists
        $check_query = "SELECT id FROM departments WHERE id = ?";
        $check_stmt = $conn->prepare($check_query);  // Prepare the check query
        if (!$check_stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            exit;
        }

        // Bind the department ID to the check query and execute
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_stmt->store_result();  // Store the result to check if the department exists

        // If no department is found, return an error message
        if ($check_stmt->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'Department not found!']);
            $check_stmt->close();
            exit;
        }
        $check_stmt->close();  // Close the prepared statement for checking the department existence

        // Prepare the query to delete the department
        $query = "DELETE FROM departments WHERE id = ?";
        $stmt = $conn->prepare($query);  // Prepare the delete query
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
            exit;
        }

        // Bind the department ID to the delete query and execute
        $stmt->bind_param("i", $id);

        // Execute the delete query and return success or failure
        if ($stmt->execute()) {
            // Check if any rows were affected (i.e., the department was deleted)
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Department deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No department was deleted. Department may not exist.']);
            }
        } else {
            // If there's a foreign key constraint error, handle it separately
            if (strpos($stmt->error, 'foreign key constraint') !== false) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete department. It is being used by other records.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting department: ' . $stmt->error]);
            }
        }
        $stmt->close();  // Close the prepared statement
    } else {
        // If the action is invalid or the ID is missing, return an error message
        echo json_encode(['success' => false, 'message' => 'Invalid action or missing ID']);
    }
} else {
    // If the request method is not POST, return an error message
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Close the database connection
$conn->close();
?>
