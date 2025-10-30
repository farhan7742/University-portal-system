<?php
// Database configuration
// Define database connection parameters
$servername = "localhost"; // Server where database is hosted
$username = "root";        // Database username (default for XAMPP/WAMP)
$password = "";            // Database password (empty by default in local development)
$dbname = "university_db"; // Name of the database to be used

// Create connection to MySQL server with database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Terminate script if connection fails
}

// Set connection character set to UTF-8
$conn->set_charset("utf8mb4");

// Error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>

