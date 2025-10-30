<?php
include('connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $search = trim($_POST['search']);
    
    $query = "SELECT * FROM courses 
              WHERE course_name LIKE ? OR course_code LIKE ? OR term_name LIKE ? OR term_code LIKE ? 
              ORDER BY id DESC";
    $stmt = $conn->prepare($query);
    $search_term = "%$search%";
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
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
                    <button class="btn-action btn-edit" onclick="editCourse(' . $row['id'] . ')">
                        ✏️ Edit
                    </button>
                    <button class="btn-action btn-delete" onclick="deleteCourse(' . $row['id'] . ')">
                        🗑️ Delete
                    </button>
                </div>
            </li>';
        }
    } else {
        echo '<li class="no-data">No courses found for "' . htmlspecialchars($search) . '"</li>';
    }
    
    $stmt->close();
} else {
    echo '<li class="no-data">Invalid request method</li>';
}

$conn->close();
?>