<?php
// Include database connection file
include('connection.php');

// Set response content type to HTML
header('Content-Type: text/html');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and validate course_id from POST data, convert to integer
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    // Get date from POST data if provided
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    
    // Validate required fields
    if (!$course_id || !$date) {
        echo '<div class="no-data">Please select both course and date</div>';
        exit;
    }

    // STEP 1: Check if the selected course exists in the database
    $course_check = $conn->prepare("SELECT course_name FROM courses WHERE id = ?");
    $course_check->bind_param("i", $course_id); // "i" for integer
    $course_check->execute();
    $course_result = $course_check->get_result();
    
    // If course doesn't exist, show error and exit
    if ($course_result->num_rows == 0) {
        echo '<div class="no-data">Selected course not found</div>';
        exit;
    }
    
    // Fetch course details for display
    $course = $course_result->fetch_assoc();
    $course_check->close();

    // STEP 2: Check if attendance is already marked for this date and course
    $check_sql = "SELECT student_id, status FROM attendance WHERE course_id = ? AND attendance_date = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $course_id, $date); // "is" for integer and string
    $check_stmt->execute();
    $existing_attendance = $check_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Create an associative array mapping student_id to their attendance status
    $attendance_status = [];
    foreach ($existing_attendance as $record) {
        $attendance_status[$record['student_id']] = $record['status'];
    }
    
    // STEP 3: Get all students from the system
    $sql = "SELECT u.id, u.name, u.email 
            FROM users u
            ORDER BY u.name";
    $result = $conn->query($sql);

    // Check if students exist in the system
    if ($result && $result->num_rows > 0) {
        // Display course and date header
        echo '<div class="course-info">Course: <strong>' . htmlspecialchars($course['course_name']) . '</strong> | Date: <strong>' . $date . '</strong></div>';
        
        // Loop through each student and create attendance item
        while ($row = $result->fetch_assoc()) {
            // Determine current status: use existing if available, default to 'present'
            $current_status = isset($attendance_status[$row['id']]) ? $attendance_status[$row['id']] : 'present';
            
            // Generate HTML for each student with attendance dropdown
            echo '
            <div class="student-attendance-item">
                <div class="student-info">
                    <span class="student-name"><strong>' . htmlspecialchars($row['name']) . '</strong></span>
                    <span class="student-id">ID: ' . $row['id'] . '</span>
                    <span class="student-email">' . htmlspecialchars($row['email']) . '</span>
                </div>
                <div class="attendance-mark">
                    <select class="attendance-dropdown" data-student-id="' . $row['id'] . '">
                        <option value="present" ' . ($current_status == 'present' ? 'selected' : '') . '>Present</option>
                        <option value="absent" ' . ($current_status == 'absent' ? 'selected' : '') . '>Absent</option>
                    </select>
                </div>
            </div>';
        }
    } else {
        // No students found in the system
        echo '<div class="no-data">No students found in the system</div>';
    }
    
    // Cleanup: Close database resources
    if ($result) $result->close();
    $check_stmt->close();
} else {
    // Invalid request method
    echo '<div class="no-data">Invalid request method</div>';
}

// Close database connection
$conn->close();
?>