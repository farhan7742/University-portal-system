<?php
// Include the configuration file which likely contains database connection settings
// and possibly the 'response()' function definition
require_once 'config.php';

// Retrieve course code and section from URL query parameters with fallback to empty strings
// Using null coalescing operator (??) to avoid undefined index warnings
$course_code = $_GET['course_code'] ?? '';  // Get course code from URL, e.g., ?course_code=CS101
$section = $_GET['section'] ?? '';           // Get section from URL, e.g., &section=A01

// Validate that both required parameters are provided
if (empty($course_code) || empty($section)) {
    // Send error response if either parameter is missing
    response(false, null, 'Course code and section are required.');
}

// Try block for database operations to catch any exceptions
try {
    // Create a new Database object (assuming Database class is defined in config.php)
    $db = new Database();
    
    // Get database connection object
    $conn = $db->getConnection();

    // SQL query to fetch student data with their grades for a specific course and section
    $sql = "
        SELECT 
            u.name,                    // Student's name from users table
            u.email,                   // Student's email from users table
            sp.student_id,             // Student ID from student_profiles table
            g.grade,                   // Grade letter (A, B, C, etc.) - may be NULL if no grade assigned
            g.percentage               // Percentage score - may be NULL if no grade assigned
        FROM student_courses sc        // Starting from student_courses junction table
        JOIN student_profiles sp ON sp.student_id = sc.student_id  // Link to student profiles
        JOIN users u ON u.id = sp.user_id  // Link to user account information
        LEFT JOIN grades g ON g.student_id = sc.student_id 
            AND g.course_code = sc.course_code 
            AND g.section = sc.section  // Left join to get grades if they exist
        WHERE sc.course_code = ? AND sc.section = ?  // Filter by specific course and section
        ORDER BY u.name                // Alphabetical order by student name
    ";
    
    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare($sql);
    
    // Execute the prepared statement with the provided parameters
    // Parameters are bound to the placeholders (?) in order
    $stmt->execute([$course_code, $section]);
    
    // Fetch all results as an associative array
    $students = $stmt->fetchAll();

    // Send successful response with the student data
    response(true, $students);
    
} catch (PDOException $e) {
    // Catch any database-related exceptions (PDO exceptions)
    // Send error response with the exception message
    response(false, null, 'Database error: ' . $e->getMessage());
}
?>
