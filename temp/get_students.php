<?php
require_once 'config.php';

$course_code = $_GET['course_code'] ?? '';
$section = $_GET['section'] ?? '';

if (empty($course_code) || empty($section)) {
    response(false, null, 'Course code and section are required.');
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    $sql = "
        SELECT 
            u.name, 
            u.email, 
            sp.student_id,
            g.grade,
            g.percentage
        FROM student_courses sc
        JOIN student_profiles sp ON sp.student_id = sc.student_id
        JOIN users u ON u.id = sp.user_id
        LEFT JOIN grades g ON g.student_id = sc.student_id 
            AND g.course_code = sc.course_code 
            AND g.section = sc.section
        WHERE sc.course_code = ? AND sc.section = ?
        ORDER BY u.name
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$course_code, $section]);
    $students = $stmt->fetchAll();

    response(true, $students);
} catch (PDOException $e) {
    response(false, null, 'Database error: ' . $e->getMessage());
}
?>