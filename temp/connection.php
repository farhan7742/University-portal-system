<?php
// Database configuration
// Define database connection parameters
$servername = "localhost"; // Server where database is hosted
$username = "root";        // Database username (default for XAMPP/WAMP)
$password = "";            // Database password (empty by default in local development)
$dbname = "university_db"; // Name of the database to be used/created

// Create connection to MySQL server (without selecting database yet)
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Terminate script if connection fails
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    // Select the database for subsequent operations
    $conn->select_db($dbname);
    
    // Array of SQL statements to create tables if they don't exist
    $tables_sql = [
        // Users table to store student/teacher information
        "CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,        // Unique identifier for each user
            name VARCHAR(100) NOT NULL,               // User's full name
            email VARCHAR(100) NOT NULL UNIQUE,       // Unique email address
            phone_number VARCHAR(20),                 // Contact number (optional)
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP // Auto-record creation time
        )",
        
        // Courses table to store course information
        "CREATE TABLE IF NOT EXISTS courses (
            id INT PRIMARY KEY AUTO_INCREMENT,        // Unique course identifier
            course_name VARCHAR(200) NOT NULL,        // Full course name
            course_code VARCHAR(50) NOT NULL UNIQUE,  // Short course code (e.g., CS101)
            term_name VARCHAR(100) NOT NULL,          // Human-readable term (e.g., Fall 2024)
            term_code VARCHAR(50) NOT NULL,           // Machine-readable term code (e.g., F24)
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        // Departments table for academic department information
        "CREATE TABLE IF NOT EXISTS departments (
            id INT PRIMARY KEY AUTO_INCREMENT,        // Unique department identifier
            department_name VARCHAR(200) NOT NULL,    // Full department name
            department_code VARCHAR(50) NOT NULL UNIQUE, // Short department code
            program_name VARCHAR(200),                // Academic program name
            faculty_name VARCHAR(200),                // Faculty/college name
            academic_year VARCHAR(20),                // Academic year reference
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        // Attendance table to track student attendance in courses
        "CREATE TABLE IF NOT EXISTS attendance (
            id INT PRIMARY KEY AUTO_INCREMENT,        // Unique attendance record identifier
            student_id INT,                           // Foreign key referencing users table
            course_id INT,                            // Foreign key referencing courses table
            attendance_date DATE,                     // Date of attendance record
            status ENUM('present', 'absent') DEFAULT 'present', // Attendance status
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];
    
    // Execute each table creation query
    foreach ($tables_sql as $sql) {
        if (!$conn->query($sql)) {
            error_log("Error creating table: " . $conn->error); // Log errors without stopping execution
        }
    }
    
    // Add sample data if tables are empty
    addSampleData($conn);
} else {
    die("Error creating database: " . $conn->error); // Terminate if database creation fails
}

/**
 * Populates database with sample data if tables are empty
 * @param mysqli $conn Database connection object
 */
function addSampleData($conn) {
    // Add sample users if users table is empty
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $sample_users = [
            "INSERT INTO users (name, email, phone_number) VALUES ('John Doe', 'john@example.com', '123-456-7890')",
            "INSERT INTO users (name, email, phone_number) VALUES ('Jane Smith', 'jane@example.com', '123-456-7891')",
            "INSERT INTO users (name, email, phone_number) VALUES ('Mike Johnson', 'mike@example.com', '123-456-7892')",
            "INSERT INTO users (name, email, phone_number) VALUES ('Sarah Wilson', 'sarah@example.com', '123-456-7893')"
        ];
        
        // Execute each user insertion query
        foreach ($sample_users as $sql) {
            $conn->query($sql);
        }
    }
    
    // Add sample courses if courses table is empty
    $result = $conn->query("SELECT COUNT(*) as count FROM courses");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $sample_courses = [
            "INSERT INTO courses (course_name, course_code, term_name, term_code) VALUES ('Web Development', 'CS101', 'Fall 2024', 'F24')",
            "INSERT INTO courses (course_name, course_code, term_name, term_code) VALUES ('Database Systems', 'CS102', 'Fall 2024', 'F24')",
            "INSERT INTO courses (course_name, course_code, term_name, term_code) VALUES ('Mathematics', 'MATH101', 'Fall 2024', 'F24')"
        ];
        
        // Execute each course insertion query
        foreach ($sample_courses as $sql) {
            $conn->query($sql);
        }
    }
    
    // Add sample departments if departments table is empty
    $result = $conn->query("SELECT COUNT(*) as count FROM departments");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $sample_departments = [
            "INSERT INTO departments (department_name, department_code, program_name, faculty_name, academic_year) VALUES ('Computer Science', 'CS', 'BSc Computer Science', 'Engineering', '2024')",
            "INSERT INTO departments (department_name, department_code, program_name, faculty_name, academic_year) VALUES ('Mathematics', 'MATH', 'BSc Mathematics', 'Science', '2024')"
        ];
        
        // Execute each department insertion query
        foreach ($sample_departments as $sql) {
            $conn->query($sql);
        }
    }
}
?>

