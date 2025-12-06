<?php
// Include the connection file to establish the database connection
include('connection.php');

// Query to get the total count of feedback from the 'feedback' table
$total_sql = "SELECT COUNT(*) as total FROM feedback";
$total_result = $conn->query($total_sql); // Execute the query
$total = $total_result->fetch_assoc()['total']; // Fetch the result and get the 'total' value

// Query to get the count of feedback where the status is 'new'
$new_sql = "SELECT COUNT(*) as new_count FROM feedback WHERE status = 'new'";
$new_result = $conn->query($new_sql); // Execute the query
$new_count = $new_result->fetch_assoc()['new_count']; // Fetch the result and get the 'new_count' value

// Query to get the average rating from the 'feedback' table
$avg_sql = "SELECT AVG(rating) as avg_rating FROM feedback";
$avg_result = $conn->query($avg_sql); // Execute the query
$avg_rating = $avg_result->fetch_assoc()['avg_rating']; // Fetch the result and get the 'avg_rating' value
$avg_rating = $avg_rating ? round($avg_rating, 1) : 0; // If avg_rating is not null, round it to one decimal place; otherwise, set it to 0

// Query to get the count of feedback grouped by their status
$status_sql = "SELECT status, COUNT(*) as count FROM feedback GROUP BY status";
$status_result = $conn->query($status_sql); // Execute the query
$status_counts = []; // Initialize an empty array to store the counts for each status
while ($row = $status_result->fetch_assoc()) { // Loop through each row of the result
    $status_counts[$row['status']] = $row['count']; // Store the count of each status
}

// Output the feedback statistics in HTML format
echo '
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">' . $total . '</div>
        <div class="stat-label">Total Feedback</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: #e74c3c;">' . $new_count . '</div>
        <div class="stat-label">New Feedback</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: #f39c12;">' . $avg_rating . '/5</div>
        <div class="stat-label">Average Rating</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color: #27ae60;">' . ($status_counts['replied'] ?? 0) . '</div>
        <div class="stat-label">Replied</div>
    </div>
</div>

<div class="status-breakdown">
    <h4>Status Breakdown</h4>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <span class="status-badge status-new">New: ' . ($status_counts['new'] ?? 0) . '</span>
        <span class="status-badge status-read">Read: ' . ($status_counts['read'] ?? 0) . '</span>
        <span class="status-badge status-replied">Replied: ' . ($status_counts['replied'] ?? 0) . '</span>
    </div>
</div>';

$conn->close(); // Close the database connection
?>
