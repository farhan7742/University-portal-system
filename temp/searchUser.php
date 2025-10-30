<?php
// Include the database connection file
include('connection.php');

// Check if the request method is POST (form submission)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize the search term from POST data
    $search = trim($_POST['search']); // Remove whitespace from both ends
    
    // Prepare SQL query with LIKE for partial matching across multiple fields
    $query = "SELECT * FROM users 
              WHERE name LIKE ? OR email LIKE ? OR phone_number LIKE ? 
              ORDER BY id DESC"; // Show newest users first
    
    // Create prepared statement to prevent SQL injection
    $stmt = $conn->prepare($query);
    
    // Add wildcards for partial matching (search anywhere in the field)
    $search_term = "%$search%";
    
    // Bind parameters: "sss" indicates three string parameters
    $stmt->bind_param("sss", $search_term, $search_term, $search_term);
    
    // Execute the prepared statement
    $stmt->execute();
    
    // Get the result set from the executed statement
    $result = $stmt->get_result();

    // Check if any users were found
    if ($result->num_rows > 0) {
        // Loop through each user in the result set
        while ($row = $result->fetch_assoc()) {
            // Output HTML list item for each user
            echo '
            <li class="list-item">
                <div class="item-info">
                    <div class="item-header">
                        <strong class="item-title">' . htmlspecialchars($row['name']) . '</strong>
                        <span class="item-code">ID: ' . $row['id'] . '</span>
                    </div>
                    <div class="item-details">
                        <div><strong>Email:</strong> ' . htmlspecialchars($row['email']) . '</div>
                        <div><strong>Phone:</strong> ' . htmlspecialchars($row['phone_number']) . '</div>
                    </div>
                </div>
                <div class="item-actions">
                    <!-- Edit button triggers JavaScript editUser function with user ID -->
                    <button class="btn-action btn-edit" onclick="editUser(' . $row['id'] . ')">
                        ✏️ Edit
                    </button>
                    <!-- Delete button triggers JavaScript deleteUser function with user ID -->
                    <button class="btn-action btn-delete" onclick="deleteUser(' . $row['id'] . ')">
                        🗑️ Delete
                    </button>
                </div>
            </li>';
        }
    } else {
        // Display message when no users match the search criteria
        echo '<li class="no-data">No users found for "' . htmlspecialchars($search) . '"</li>';
    }
    
    // Close the prepared statement to free memory
    $stmt->close();
} else {
    // Handle invalid request method (GET instead of POST)
    echo '<li class="no-data">Invalid request method</li>';
}

// Close the database connection
$conn->close();
?>








