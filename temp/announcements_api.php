<?php
// announcements_api.php
require_once 'config.php';

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? '';

try {
    switch($action) {
        case 'list':
            getAnnouncements($db);
            break;
        case 'create':
            createAnnouncement($db);
            break;
        case 'update':
            updateAnnouncement($db);
            break;
        case 'delete':
            deleteAnnouncement($db);
            break;
        case 'search':
            searchAnnouncements($db);
            break;
        case 'stats':
            getStats($db);
            break;
        default:
            response(false, null, 'Invalid action specified', 400);
    }
} catch(Exception $e) {
    response(false, null, 'Server error: ' . $e->getMessage(), 500);
}

function getAnnouncements($db) {
    $query = "SELECT * FROM announcements ORDER BY date DESC, id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $announcements = $stmt->fetchAll();
    
    // Format dates for display
    foreach($announcements as &$announcement) {
        $announcement['date'] = date('Y-m-d', strtotime($announcement['date']));
        if($announcement['expiry_date']) {
            $announcement['expiry_date'] = date('Y-m-d', strtotime($announcement['expiry_date']));
        }
    }
    
    response(true, $announcements);
}

function searchAnnouncements($db) {
    $searchTerm = $_GET['q'] ?? '';
    
    if(empty($searchTerm)) {
        getAnnouncements($db);
        return;
    }
    
    $query = "SELECT * FROM announcements 
              WHERE title LIKE :search OR content LIKE :search 
              ORDER BY date DESC, id DESC";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':search', "%$searchTerm%");
    $stmt->execute();
    
    $announcements = $stmt->fetchAll();
    
    // Format dates for display
    foreach($announcements as &$announcement) {
        $announcement['date'] = date('Y-m-d', strtotime($announcement['date']));
        if($announcement['expiry_date']) {
            $announcement['expiry_date'] = date('Y-m-d', strtotime($announcement['expiry_date']));
        }
    }
    
    response(true, $announcements);
}

function createAnnouncement($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if(empty($data['title']) || empty($data['content']) || empty($data['priority']) || empty($data['target_audience'])) {
        response(false, null, 'All required fields must be filled', 400);
    }
    
    $query = "INSERT INTO announcements 
              (title, content, date, priority, target_audience, expiry_date, status) 
              VALUES (:title, :content, :date, :priority, :target_audience, :expiry_date, 'active')";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindValue(':title', $data['title']);
    $stmt->bindValue(':content', $data['content']);
    $stmt->bindValue(':date', date('Y-m-d'));
    $stmt->bindValue(':priority', $data['priority']);
    $stmt->bindValue(':target_audience', $data['target_audience']);
    $stmt->bindValue(':expiry_date', !empty($data['expiry_date']) ? $data['expiry_date'] : null);
    
    if($stmt->execute()) {
        $newId = $db->lastInsertId();
        response(true, ['id' => $newId], 'Announcement created successfully');
    } else {
        response(false, null, 'Failed to create announcement', 500);
    }
}

function updateAnnouncement($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if(empty($data['id'])) {
        response(false, null, 'Announcement ID is required', 400);
    }
    
    // Validate required fields
    if(empty($data['title']) || empty($data['content']) || empty($data['priority']) || empty($data['target_audience'])) {
        response(false, null, 'All required fields must be filled', 400);
    }
    
    $query = "UPDATE announcements 
              SET title = :title, content = :content, priority = :priority, 
                  target_audience = :target_audience, expiry_date = :expiry_date,
                  updated_at = CURRENT_TIMESTAMP
              WHERE id = :id";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindValue(':id', $data['id']);
    $stmt->bindValue(':title', $data['title']);
    $stmt->bindValue(':content', $data['content']);
    $stmt->bindValue(':priority', $data['priority']);
    $stmt->bindValue(':target_audience', $data['target_audience']);
    $stmt->bindValue(':expiry_date', !empty($data['expiry_date']) ? $data['expiry_date'] : null);
    
    if($stmt->execute()) {
        response(true, null, 'Announcement updated successfully');
    } else {
        response(false, null, 'Failed to update announcement', 500);
    }
}

function deleteAnnouncement($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if(empty($data['id'])) {
        response(false, null, 'Announcement ID is required', 400);
    }
    
    $query = "DELETE FROM announcements WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $data['id']);
    
    if($stmt->execute()) {
        response(true, null, 'Announcement deleted successfully');
    } else {
        response(false, null, 'Failed to delete announcement', 500);
    }
}

function getStats($db) {
    $stats = [];
    
    // Total announcements
    $query = "SELECT COUNT(*) as total FROM announcements";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['total'] = $stmt->fetch()['total'];
    
    // Active announcements
    $query = "SELECT COUNT(*) as active FROM announcements WHERE status = 'active'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['active'] = $stmt->fetch()['active'];
    
    // Today's announcements
    $query = "SELECT COUNT(*) as today FROM announcements WHERE date = CURDATE()";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['today'] = $stmt->fetch()['today'];
    
    response(true, $stats);
}
?>