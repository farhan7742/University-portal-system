<?php
// Include database connection file
include('connection.php');

// Check if the request method is POST (form submission)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Trim and retrieve data from the POST request
    $course_name = trim($_POST['course_name']);  // The name of the course
    $course_code = trim($_POST['course_code']);  // The unique code for the course
    $term_name = trim($_POST['term_name']);      // The term in which the course is offered
    $term_code = trim($_POST['term_code']);      // The unique code for the term

    // Validate input: Ensure no fields are empty
    if (empty($course_name) || empty($course_code) || empty($term_name) || empty($term_code)) {
        echo "All fields are required.";  // Error message if any field is empty
        exit;  // Stop execution if validation fails
    }

    // Check if the course code already exists in the database
    $check_code = $conn->prepare("SELECT id FROM courses WHERE course_code = ?");
    $check_code->bind_param("s", $course_code);  // Bind the course code to the query
    $check_code->execute();  // Execute the query to check for existing course code
    $check_code->store_result();  // Store the result for checking if the course code exists
    
    // If course code already exists, display an error message and exit
    if ($check_code->num_rows > 0) {
        echo "Course code already exists!";  // Display error message
        $check_code->close();  // Close the prepared statement
        exit;  // Stop further execution
    }

    // Close the statement for checking course code
    $check_code->close();

    // Prepare and insert the new course data into the database
    $query = "INSERT INTO courses (course_name, course_code, term_name, term_code) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);  // Prepare the SQL query
    $stmt->bind_param("ssss", $course_name, $course_code, $term_name, $term_code);  // Bind the parameters to the query
    
    // Execute the insert query and check if it was successful
    if ($stmt->execute()) {
        echo "Course added successfully!";  // Success message
    } else {
        echo "Error adding course: " . $stmt->error;  // Error message if insertion fails
    }

    // Close the prepared statement
    $stmt->close();
} else {
    echo "Invalid request method.";  // Error message if the request is not POST
}

// Close the database connection
$conn->close();
?>
