<?php
// Include database connection file
include('connection.php');

// SQL query to fetch all departments ordered by most recent first
$query = "SELECT * FROM departments ORDER BY id DESC";
$result = $conn->query($query);

// Check if any departments were found
if ($result->num_rows > 0) {
    // Loop through each department record
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
    // Display message when no departments exist
    echo '<li class="no-data">No departments found</li>';
}

// Close database connection
$conn->close();
?>