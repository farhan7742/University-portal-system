<?php
require_once 'config.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    $sql = "SELECT id, course_name, course_code FROM courses";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $courses = $stmt->fetchAll();

    response(true, $courses);
} catch (PDOException $e) {
    response(false, null, 'Database error: ' . $e->getMessage());
}
?>