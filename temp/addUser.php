<?php
// Include the database connection file
include('connection.php');

// Check if the request method is POST (form submission)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Retrieve and trim the form data from the POST request
    $name = trim($_POST['name']);            // User's name
    $email = trim($_POST['email']);          // User's email address
    $phone_number = trim($_POST['phone_number']);  // User's phone number

    // Validate the input: Ensure no fields are empty
    if (empty($name) || empty($email) || empty($phone_number)) {
        echo "All fields are required.";  // Error message if any field is missing
        exit;  // Stop further execution if validation fails
    }

    // Check if the email already exists in the 'users' table
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);  // Bind the email parameter to the query
    $check_email->execute();  // Execute the query to check for existing email
    $check_email->store_result();  // Store the result of the query

    // If the email already exists, display an error and exit
    if ($check_email->num_rows > 0) {
        echo "Email already exists!";  // Error message if email exists
        $check_email->close();  // Close the prepared statement
        exit;  // Stop further execution
    }

    // Close the statement after checking email
    $check_email->close();

    // Prepare and insert the new user data into the 'users' table
    $query = "INSERT INTO users (name, email, phone_number) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);  // Prepare the SQL query
    $stmt->bind_param("sss", $name, $email, $phone_number);  // Bind the parameters to the query

    // Execute the insert query and check if it was successful
    if ($stmt->execute()) {
        echo "User added successfully!";  // Success message if insertion is successful
    } else {
        echo "Error adding user: " . $stmt->error;  // Error message if insertion fails
    }

    // Close the prepared statement
    $stmt->close();
} else {
    echo "Invalid request method.";  // Error message if the request is not POST
}

// Close the database connection
$conn->close();
?>

