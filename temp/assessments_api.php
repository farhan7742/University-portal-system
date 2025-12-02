<?php
// assessments_api.php - Alternative version using query parameters
require_once 'config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Handle preflight CORS requests
    if ($method === 'OPTIONS') {
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type");
        exit(0);
    }

    switch($method) {
        case 'GET':
            if ($id) {
                // Get single assessment
                $query = "SELECT a.*, c.course_code, c.course_name 
                         FROM assessments a 
                         JOIN courses c ON a.course_id = c.id 
                         WHERE a.id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$id]);
                $assessment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($assessment) {
                    echo json_encode(['success' => true, 'data' => $assessment]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Assessment not found']);
                }
            } else {
                // Get all assessments
                $query = "SELECT a.*, c.course_code, c.course_name 
                         FROM assessments a 
                         JOIN courses c ON a.course_id = c.id 
                         ORDER BY a.due_date";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $assessments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $assessments]);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['assessment_name']) || empty($input['course_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                break;
            }
            
            $query = "INSERT INTO assessments 
                     (assessment_name, course_id, assessment_type, due_date, total_marks, weight, description, status) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            
            $stmt->execute([
                $input['assessment_name'],
                $input['course_id'],
                $input['assessment_type'] ?? 'assignment',
                $input['due_date'] ?? date('Y-m-d'),
                $input['total_marks'] ?? 100,
                $input['weight'] ?? 0,
                $input['description'] ?? '',
                $input['status'] ?? 'Not Started'
            ]);
            
            echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            break;

        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Assessment ID is required']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Check if assessment exists
            $checkStmt = $db->prepare("SELECT id FROM assessments WHERE id = ?");
            $checkStmt->execute([$id]);
            if (!$checkStmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Assessment not found']);
                break;
            }
            
            $query = "UPDATE assessments SET 
                     assessment_name = ?, course_id = ?, assessment_type = ?, due_date = ?, 
                     total_marks = ?, weight = ?, description = ?, status = ? 
                     WHERE id = ?";
            $stmt = $db->prepare($query);
            
            $stmt->execute([
                $input['assessment_name'] ?? '',
                $input['course_id'] ?? 0,
                $input['assessment_type'] ?? 'assignment',
                $input['due_date'] ?? date('Y-m-d'),
                $input['total_marks'] ?? 100,
                $input['weight'] ?? 0,
                $input['description'] ?? '',
                $input['status'] ?? 'Not Started',
                $id
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Assessment updated successfully']);
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Assessment ID is required']);
                break;
            }
            
            // Check if assessment exists
            $checkStmt = $db->prepare("SELECT id FROM assessments WHERE id = ?");
            $checkStmt->execute([$id]);
            if (!$checkStmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Assessment not found']);
                break;
            }
            
            $query = "DELETE FROM assessments WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Assessment deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete assessment']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>