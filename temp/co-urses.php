<?php
// co-urses.php
require_once 'config.php';

$database = new Database();
$db = $database->getConnection();

$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestMethod) {
    case 'GET':
        // Get all courses with optional filtering
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $department = isset($_GET['department']) ? $_GET['department'] : '';

        $query = "SELECT * FROM courses WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (course_name LIKE ? OR course_code LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($department)) {
            // Assuming the course_code starts with department code
            $query .= " AND course_code LIKE ?";
            $params[] = "$department%";
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $courses = $stmt->fetchAll();

        response(true, $courses);
        break;

    case 'DELETE':
        // Delete a course
        $input = json_decode(file_get_contents('php://input'), true);
        $courseId = $input['id'] ?? null;

        if (!$courseId) {
            response(false, null, 'Course ID is required', 400);
        }

        // Check if there are enrollments for this course
        $stmt = $db->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ?");
        $stmt->execute([$courseId]);
        if ($stmt->fetchColumn() > 0) {
            response(false, null, 'Cannot delete course with existing enrollments', 400);
        }

        $stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
        if ($stmt->execute([$courseId])) {
            response(true, null, 'Course deleted successfully');
        } else {
            response(false, null, 'Failed to delete course', 500);
        }
        break;

    default:
        response(false, null, 'Method not allowed', 405);
        break;
}
?>