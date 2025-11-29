<?php
// Include database connection file
include('connection.php');

// Set response content type to HTML
header('Content-Type: text/html');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get course_id from POST data
    $course_id = $_POST['course_id'];
    
    // QUERY 1: Get total number of distinct students in the course
    $total_sql = "SELECT COUNT(DISTINCT student_id) as total FROM attendance WHERE course_id = ?";
    $total_stmt = $conn->prepare($total_sql);
    $total_stmt->bind_param("i", $course_id); // "i" for integer parameter
    $total_stmt->execute();
    $total_result = $total_stmt->get_result()->fetch_assoc();
    $total_students = $total_result['total']; // Extract total count
    
    // QUERY 2: Get count of present attendance records
    $present_sql = "SELECT COUNT(*) as present_count FROM attendance WHERE course_id = ? AND status = 'present'";
    $present_stmt = $conn->prepare($present_sql);
    $present_stmt->bind_param("i", $course_id);
    $present_stmt->execute();
    $present_result = $present_stmt->get_result()->fetch_assoc();
    $present_count = $present_result['present_count']; // Extract present count
    
    // QUERY 3: Get count of absent attendance records
    $absent_sql = "SELECT COUNT(*) as absent_count FROM attendance WHERE course_id = ? AND status = 'absent'";
    $absent_stmt = $conn->prepare($absent_sql);
    $absent_stmt->bind_param("i", $course_id);
    $absent_stmt->execute();
    $absent_result = $absent_stmt->get_result()->fetch_assoc();
    $absent_count = $absent_result['absent_count']; // Extract absent count
    
    // CALCULATIONS: Compute percentages for present and absent rates
    $total_records = $present_count + $absent_count; // Total attendance records
    
    // Calculate present percentage (handle division by zero)
    $present_percentage = $total_records > 0 ? round(($present_count / $total_records) * 100, 2) : 0;
    
    // Calculate absent percentage (handle division by zero)
    $absent_percentage = $total_records > 0 ? round(($absent_count / $total_records) * 100, 2) : 0;
    
    // OUTPUT: Generate HTML statistics display
    echo '
    <div class="attendance-stats">
        <h4>📊 Attendance Statistics</h4>
        <div class="stats-grid">
            <!-- Total Students Card -->
            <div class="stat-card">
                <div class="stat-value">' . $total_students . '</div>
                <div class="stat-label">Total Students</div>
            </div>
            
            <!-- Present Count Card -->
            <div class="stat-card">
                <div class="stat-value" style="color: #27ae60;">' . $present_count . '</div>
                <div class="stat-label">Present</div>
            </div>
            
            <!-- Absent Count Card -->
            <div class="stat-card">
                <div class="stat-value" style="color: #e74c3c;">' . $absent_count . '</div>
                <div class="stat-label">Absent</div>
            </div>
            
            <!-- Present Percentage Card -->
            <div class="stat-card">
                <div class="stat-value" style="color: #27ae60;">' . $present_percentage . '%</div>
                <div class="stat-label">Present Rate</div>
            </div>
            
            <!-- Absent Percentage Card -->
            <div class="stat-card">
                <div class="stat-value" style="color: #e74c3c;">' . $absent_percentage . '%</div>
                <div class="stat-label">Absent Rate</div>
            </div>
        </div>
    </div>';
    
    // Cleanup: Close all prepared statements
    $total_stmt->close();
    $present_stmt->close();
    $absent_stmt->close();
} else {
    // Error message for non-POST requests
    echo '<div class="no-data">Select a course to view statistics</div>';
}

// Close database connection
$conn->close();
?>