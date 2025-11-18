<?php
// Include database connection file
include('connection.php');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize search term from POST data
    $search = trim($_POST['search']);
    
    // STEP 1: Prepare SQL query with multiple LIKE conditions
    $query = "SELECT * FROM departments 
              WHERE department_name LIKE ? OR department_code LIKE ? OR program_name LIKE ? OR faculty_name LIKE ? OR academic_year LIKE ? 
              ORDER BY id DESC";
    $stmt = $conn->prepare($query);
    
    // Prepare search term with wildcards for partial matching
    $search_term = "%$search%";
    
    // Bind the same search term to all five parameters
    $stmt->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $search_term);
    
    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    // STEP 2: Check if any results were found
    if ($result->num_rows > 0) {
        // Loop through each matching department record
        while ($row = $result->fetch_assoc()) {
            // Generate HTML list item for each department
            echo '
            <li class="list-item">
                <div class="item-info">
                    <div class="item-header">
                        <strong class="item-title">' . htmlspecialchars($row['department_name']) . '</strong>
                        <span class="item-code">' . htmlspecialchars($row['department_code']) . '</span>
                    </div>
                    <div class="item-details">
                        <div><strong>Program:</strong> ' . htmlspecialchars($row['program_name']) . '</div>
                        <div><strong>Faculty:</strong> ' . htmlspecialchars($row['faculty_name']) . '</div>
                        <div><strong>Academic Year:</strong> ' . htmlspecialchars($row['academic_year']) . '</div>
                    </div>
                </div>
                <div class="item-actions">
                    <button class="btn-action btn-edit" onclick="editDepartment(' . $row['id'] . ')">
                        ✏️ Edit
                    </button>
                    <button class="btn-action btn-delete" onclick="deleteDepartment(' . $row['id'] . ')">
                        🗑️ Delete
                    </button>
                </div>
            </li>';
        }
    } else {
        // No results found message with the search term
        echo '<li class="no-data">No departments found for "' . htmlspecialchars($search) . '"</li>';
    }
    
    // STEP 3: Close prepared statement
    $stmt->close();
} else {
    // Handle invalid request method
    echo '<li class="no-data">Invalid request method</li>';
}

// Close database connection
$conn->close();
?>