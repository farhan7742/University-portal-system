<?php
// assessments.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'), true);

try {
    $database = new Database();
    $db = $database->getConnection();

    switch($method) {
        case 'GET':
            // Get all assessments with course info
            if (empty($request[0])) {
                $query = "SELECT a.*, c.course_code, c.course_name 
                         FROM assessments a 
                         JOIN courses c ON a.course_id = c.id 
                         ORDER BY a.due_date";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                response(true, $assessments);
            } else {
                // Get single assessment
                $id = $request[0];
                $query = "SELECT a.*, c.course_code, c.course_name 
                         FROM assessments a 
                         JOIN courses c ON a.course_id = c.id 
                         WHERE a.id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$id]);
                $assessment = $stmt->fetch(PDO::FETCH_ASSOC);
                response(true, $assessment);
            }
            break;

        case 'POST':
            // Create new assessment
            $query = "INSERT INTO assessments 
                     (assessment_name, course_id, assessment_type, due_date, total_marks, weight, description, status) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                $input['assessment_name'],
                $input['course_id'],
                $input['assessment_type'],
                $input['due_date'],
                $input['total_marks'],
                $input['weight'],
                $input['description'],
                $input['status'] ?? 'Not Started'
            ]);
            response(true, ['id' => $db->lastInsertId()]);
            break;

        case 'PUT':
            // Update assessment
            $id = $request[0];
            $query = "UPDATE assessments SET 
                     assessment_name = ?, course_id = ?, assessment_type = ?, due_date = ?, 
                     total_marks = ?, weight = ?, description = ?, status = ? 
                     WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([
                $input['assessment_name'],
                $input['course_id'],
                $input['assessment_type'],
                $input['due_date'],
                $input['total_marks'],
                $input['weight'],
                $input['description'],
                $input['status'],
                $id
            ]);
            response(true, ['message' => 'Assessment updated successfully']);
            break;

        case 'DELETE':
            // Delete assessment
            $id = $request[0];
            $query = "DELETE FROM assessments WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$id]);
            response(true, ['message' => 'Assessment deleted successfully']);
            break;

        default:
            response(false, null, 'Method not allowed');
    }
} catch (Exception $e) {
    response(false, null, $e->getMessage());
}
?>
