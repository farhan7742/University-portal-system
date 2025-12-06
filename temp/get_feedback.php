<?php
// Include the database connection file
include('connection.php');

// Query to fetch all feedback from the database, ordered by the created_at field in descending order
$sql = "SELECT * FROM feedback ORDER BY created_at DESC";
$result = $conn->query($sql);

// Check if the query was successful and there are results
if ($result && $result->num_rows > 0) {
    // Loop through each feedback row and display the details
    while ($row = $result->fetch_assoc()) {
        
        // Construct the status class dynamically based on the feedback status
        $status_class = 'status-' . $row['status'];
        
        // Generate the stars based on the rating value
        $stars = str_repeat('⭐', $row['rating']);
        
        // Output the feedback HTML
        echo '
        <div class="list-item">
            <div class="item-info">
                <div class="item-header">
                    <!-- Display feedback name (escaped to prevent XSS) -->
                    <strong class="item-title">' . htmlspecialchars($row['name']) . '</strong>
                    <!-- Display email (escaped to prevent XSS) -->
                    <span class="item-code">' . htmlspecialchars($row['email']) . '</span>
                    <!-- Display the status badge with dynamic status class -->
                    <span class="status-badge ' . $status_class . '">' . ucfirst($row['status']) . '</span>
                    <!-- Display rating as stars -->
                    <span class="item-code">' . $stars . '</span>
                </div>
                <div class="item-details">
                    <!-- Display feedback subject (escaped to prevent XSS) -->
                    <div><strong>Subject:</strong> ' . htmlspecialchars($row['subject']) . '</div>
                    <!-- Display feedback message (escaped to prevent XSS) -->
                    <div><strong>Message:</strong> ' . htmlspecialchars($row['message']) . '</div>
                    <!-- Display the submission date -->
                    <div><strong>Submitted:</strong> ' . $row['created_at'] . '</div>
                </div>
            </div>
            <div class="item-actions">
                <div class="feedback-actions">
                    <!-- Dropdown to update feedback status with onchange event to call updateFeedbackStatus function -->
                    <select onchange="updateFeedbackStatus(' . $row['id'] . ', this.value)" class="attendance-dropdown">
                        <option value="new" ' . ($row['status'] == 'new' ? 'selected' : '') . '>New</option>
                        <option value="read" ' . ($row['status'] == 'read' ? 'selected' : '') . '>Read</option>
                        <option value="replied" ' . ($row['status'] == 'replied' ? 'selected' : '') . '>Replied</option>
                    </select>
                    <!-- Button to delete feedback, calls deleteFeedback function on click -->
                    <button class="btn-action btn-delete" onclick="deleteFeedback(' . $row['id'] . ')">
                        🗑️ Delete
                    </button>
                </div>
            </div>
        </div>';
    }
} else {
    // Display a message if no feedback is available
    echo '<div class="no-data">No feedback submitted yet.</div>';
}

// Close the database connection
$conn->close();
?>
