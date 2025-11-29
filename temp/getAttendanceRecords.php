<?php
// Include database connection file
include('connection.php');

// Set response content type to HTML
header('Content-Type: text/html');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get course_id from POST data
    $course_id = $_POST['course_id'];
    // Get date from POST data if provided, otherwise set to null
    $date = isset($_POST['date']) ? $_POST['date'] : null;
    
    // SQL query to fetch attendance records with joins to get student and course names
    $sql = "SELECT a.*, u.name as student_name, u.id as student_id, c.course_name 
            FROM attendance a 
            JOIN users u ON a.student_id = u.id 
            JOIN courses c ON a.course_id = c.id 
            WHERE a.course_id = ?";
    
    // Initialize parameters array and parameter types string for prepared statement
    $params = [$course_id];
    $types = "i"; // 'i' for integer (course_id)
    
    // If date filter is provided, add to SQL query and parameters
    if ($date) {
        $sql .= " AND a.attendance_date = ?";
        $params[] = $date;
        $types .= "s"; // 's' for string (date)
    }
    
    // Add ordering to the SQL query
    $sql .= " ORDER BY a.attendance_date DESC, u.name";
    
    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
    // Bind parameters to the prepared statement
    $stmt->bind_param($types, ...$params);
    // Execute the query
    $stmt->execute();
    // Get result set
    $result = $stmt->get_result();
    
    // Check if records were found
    if ($result->num_rows > 0) {
        // Loop through each attendance record
        while ($row = $result->fetch_assoc()) {
            // Determine CSS class based on attendance status
            $status_class = $row['status'] == 'present' ? 'status-present' : 'status-absent';
            
            // Output HTML for each attendance record
            echo '
            <div class="list-item">
                <div class="item-info">
                    <div class="item-header">
                        <strong class="item-title">' . htmlspecialchars($row['student_name']) . '</strong>
                        <span class="item-code">ID: ' . $row['student_id'] . '</span>
                        <span class="' . $status_class . '">' . ucfirst($row['status']) . '</span>
                    </div>
                    <div class="item-details">
                        <div><strong>Course:</strong> ' . htmlspecialchars($row['course_name']) . '</div>
                        <div><strong>Date:</strong> ' . $row['attendance_date'] . '</div>
                        <div><strong>Recorded:</strong> ' . $row['created_at'] . '</div>
                    </div>
                </div>
                <div class="item-actions">
                    <button class="btn-action btn-delete" onclick="deleteAttendance(' . $row['id'] . ')">
                        🗑️ Delete
                    </button>
                </div>
            </div>';
        }
    } else {
        // No records found message
        echo '<div class="no-data">No attendance records found for the selected criteria</div>';
    }
    
    // Close prepared statement
    $stmt->close();
} else {
    // Invalid request method message
    echo '<div class="no-data">Please select a course to view records</div>';
}

// Close database connection
$conn->close();
?>