<?php
include('connection.php');

$query = "SELECT * FROM users ORDER BY id DESC";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
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
                <button class="btn-action btn-edit" onclick="editUser(' . $row['id'] . ')">
                    ✏️ Edit
                </button>
                <button class="btn-action btn-delete" onclick="deleteUser(' . $row['id'] . ')">
                    🗑️ Delete
                </button>
            </div>
        </li>';
    }
} else {
    echo '<li class="no-data">No users found</li>';
}

$conn->close();
?>