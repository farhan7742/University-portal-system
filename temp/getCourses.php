<?php
// Include the database connection file
include('connection.php');

// Set the content type to HTML for proper rendering
header('Content-Type: text/html');

// Check if the request is for dropdown options or course list display
if (isset($_GET['type']) && $_GET['type'] === 'dropdown') {
    // DROPDOWN MODE: Return course options for select dropdown
    $query = "SELECT * FROM courses ORDER BY course_name"; // Get all courses sorted by name
    $result = $conn->query($query);
    
    // Check if query was successful and has results
    if ($result && $result->num_rows > 0) {
        // Loop through each course and create dropdown options
        while ($row = $result->fetch_assoc()) {
            // Create option element with course ID as value and formatted display text
            echo '<option value="' . $row['id'] . '">' . 
                 htmlspecialchars($row['course_name']) . ' (' . 
                 htmlspecialchars($row['course_code']) . ')</option>';
        }
    } else {
        // Display message when no courses are available
        echo '<option value="">No courses available</option>';
    }
} else {
    // LIST MODE: Return formatted course list for display
    $query = "SELECT * FROM courses ORDER BY id DESC"; // Get courses, newest first
    $result = $conn->query($query);
    
    // Check if query was successful and has results
    if ($result && $result->num_rows > 0) {
        // Loop through each course and create list items
        while ($row = $result->fetch_assoc()) {
            // Create list item with course information and action buttons
            echo '
            <li class="list-item">
                <div class="item-info">
                    <div class="item-header">
                        <strong class="item-title">' . htmlspecialchars($row['course_name']) . '</strong>
                        <span class="item-code">' . htmlspecialchars($row['course_code']) . '</span>
                    </div>
                    <div class="item-details">
                        <div><strong>Term:</strong> ' . htmlspecialchars($row['term_name']) . '</div>
                        <div><strong>Term Code:</strong> ' . htmlspecialchars($row['term_code']) . '</div>
                    </div>
                </div>
                <div class="item-actions">
                    <!-- Edit button with onclick event to trigger edit function -->
                    <button class="btn-action btn-edit" onclick="editCourse(' . $row['id'] . ')">
                        ✏️ Edit
                    </button>
                    <!-- Delete button with onclick event to trigger delete function -->
                    <button class="btn-action btn-delete" onclick="deleteCourse(' . $row['id'] . ')">
                        🗑️ Delete
                    </button>
                </div>
            </li>';
        }
    } else {
        // Display message when no courses are found
        echo '<li class="no-data">No courses found</li>';
    }
}

// Close the database connection to free resources
$conn->close();
?>