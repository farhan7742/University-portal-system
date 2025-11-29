<?php
// Include database connection file
include('connection.php');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get course ID, date, and attendance data from POST request
    $course_id = $_POST['course_id'];
    $date = $_POST['date'];
    // Decode JSON attendance data into PHP array
    $attendance_data = json_decode($_POST['attendance_data'], true);
    
    // STEP 1: Start database transaction for data consistency
    $conn->begin_transaction();
    
    // Use try-catch block for error handling
    try {
        // STEP 2: Delete existing attendance records for this course and date
        $delete_sql = "DELETE FROM attendance WHERE course_id = ? AND attendance_date = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("is", $course_id, $date);
        $delete_stmt->execute();
        
        // STEP 3: Prepare INSERT statement for new attendance records
        $insert_sql = "INSERT INTO attendance (student_id, course_id, attendance_date, status) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        
        // Initialize counters for statistics
        $present_count = 0;
        $absent_count = 0;
        
        // STEP 4: Loop through each student's attendance record
        foreach ($attendance_data as $record) {
            // Bind parameters: student_id (i), course_id (i), date (s), status (s)
            $insert_stmt->bind_param("iiss", $record['student_id'], $course_id, $date, $record['status']);
            $insert_stmt->execute();
            
            // Count present vs absent for feedback
            if ($record['status'] == 'present') {
                $present_count++;
            } else {
                $absent_count++;
            }
        }
        
        // STEP 5: Commit transaction if all operations succeeded
        $conn->commit();
        // Success message with statistics
        echo "Attendance marked successfully! Present: $present_count, Absent: $absent_count";
        
    } catch (Exception $e) {
        // STEP 6: Rollback transaction if any error occurred
        $conn->rollback();
        // Error message with details
        echo "Error: " . $e->getMessage();
    }
    
    // STEP 7: Close prepared statements
    $delete_stmt->close();
    $insert_stmt->close();
} else {
    // Handle invalid request method
    echo "Invalid request method";
}

// Close database connection
$conn->close();
?>