<?php
// Include database connection file
include('connection.php');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize search term from POST data
    $search = trim($_POST['search']);
    
    // STEP 1: Prepare SQL query to search across multiple feedback fields
    $sql = "SELECT * FROM feedback 
            WHERE name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ? 
            ORDER BY created_at DESC";
    
    // Create prepared statement
    $stmt = $conn->prepare($sql);
    // Add wildcards for partial matching
    $search_term = "%$search%";
    // Bind search term to all four parameters
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();

    // STEP 2: Check if any results were found
    if ($result->num_rows > 0) {
        // Loop through each matching feedback record
        while ($row = $result->fetch_assoc()) {
            // Determine CSS class based on feedback status
            $status_class = 'status-' . $row['status'];
            // Generate star rating visual representation
            $stars = str_repeat('⭐', $row['rating']);
            
            // STEP 3: Generate HTML for each feedback item
            echo '
            <div class="list-item">
                <div class="item-info">
                    <div class="item-header">
                        <strong class="item-title">' . htmlspecialchars($row['name']) . '</strong>
                        <span class="item-code">' . htmlspecialchars($row['email']) . '</span>
                        <span class="status-badge ' . $status_class . '">' . ucfirst($row['status']) . '</span>
                        <span class="item-code">' . $stars . '</span>
                    </div>
                    <div class="item-details">
                        <div><strong>Subject:</strong> ' . htmlspecialchars($row['subject']) . '</div>
                        <div><strong>Message:</strong> ' . htmlspecialchars($row['message']) . '</div>
                        <div><strong>Submitted:</strong> ' . $row['created_at'] . '</div>
                    </div>
                </div>
                <div class="item-actions">
                    <div class="feedback-actions">
                        <!-- Status update dropdown -->
                        <select onchange="updateFeedbackStatus(' . $row['id'] . ', this.value)" class="attendance-dropdown">
                            <option value="new" ' . ($row['status'] == 'new' ? 'selected' : '') . '>New</option>
                            <option value="read" ' . ($row['status'] == 'read' ? 'selected' : '') . '>Read</option>
                            <option value="replied" ' . ($row['status'] == 'replied' ? 'selected' : '') . '>Replied</option>
                        </select>
                        <!-- Delete button -->
                        <button class="btn-action btn-delete" onclick="deleteFeedback(' . $row['id'] . ')">
                            🗑️ Delete
                        </button>
                    </div>
                </div>
            </div>';
        }
    } else {
        // No results found - show message with search term
        echo '<div class="no-data">No feedback found for "' . htmlspecialchars($search) . '"</div>';
    }
    
    // STEP 4: Close prepared statement
    $stmt->close();
} else {
    // Handle invalid request method
    echo '<div class="no-data">Invalid request</div>';
}

// Close database connection
$conn->close();
?>