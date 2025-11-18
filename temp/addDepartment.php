<?php
// Include database connection file
include('connection.php');

// Check if the request method is POST (form submission)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Retrieve and trim data from the POST request, using null coalescing to handle missing fields
    $department_name = trim($_POST['dept_name'] ?? '');  // The name of the department
    $department_code = trim($_POST['dept_code'] ?? '');  // The unique code for the department
    $program_name = trim($_POST['program_name'] ?? '');  // The name of the program associated with the department
    $faculty_name = trim($_POST['faculty'] ?? '');  // The name of the faculty
    $academic_year = trim($_POST['year'] ?? '');  // The academic year associated with the department

    // Validate input: Ensure department name and code are not empty
    if (empty($department_name) || empty($department_code)) {
        echo "Department Name and Code are required.";  // Error message if required fields are empty
        exit;  // Stop further execution if validation fails
    }

    // Check if the department code already exists in the database
    $check_code = $conn->prepare("SELECT id FROM departments WHERE department_code = ?");
    $check_code->bind_param("s", $department_code);  // Bind the department code to the query
    $check_code->execute();  // Execute the query to check for existing department code
    $check_code->store_result();  // Store the result for checking if the department code exists
    
    // If department code already exists, display an error message and exit
    if ($check_code->num_rows > 0) {
        echo "Department code already exists!";  // Error message if code exists
        $check_code->close();  // Close the prepared statement for checking code
        exit;  // Stop further execution
    }

    // Close the statement for checking department code
    $check_code->close();

    // Prepare and insert the new department data into the database
    $query = "INSERT INTO departments (department_name, department_code, program_name, faculty_name, academic_year) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);  // Prepare the SQL query
    $academic_year = !empty($academic_year) ? $academic_year : null;  // Set academic year to null if empty
    $stmt->bind_param("ssssi", $department_name, $department_code, $program_name, $faculty_name, $academic_year);  // Bind the parameters to the query
    
    // Execute the insert query and check if it was successful
    if ($stmt->execute()) {
        echo "Department added successfully!";  // Success message if insertion is successful
    } else {
        echo "Error adding department: " . $stmt->error;  // Error message if insertion fails
    }

    // Close the prepared statement
    $stmt->close();
} else {
    echo "Invalid request method.";  // Error message if the request is not POST
}

// Close the database connection
$conn->close();
?>
