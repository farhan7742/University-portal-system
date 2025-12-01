<?php
require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['course_code']) || !isset($input['section']) || !isset($input['students'])) {
    response(false, null, 'Invalid input data.');
}

$course_code = $input['course_code'];
$section = $input['section'];
$students = $input['students'];

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Start transaction
    $conn->beginTransaction();

    foreach ($students as $student) {
        // Check if grade record exists
        $check_sql = "SELECT id FROM grades WHERE student_id = ? AND course_code = ? AND section = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([$student['student_id'], $course_code, $section]);
        $existing_grade = $check_stmt->fetch();

        if ($existing_grade) {
            // Update existing grade
            $update_sql = "UPDATE grades SET grade = ?, percentage = ?, updated_at = CURRENT_TIMESTAMP 
                          WHERE student_id = ? AND course_code = ? AND section = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->execute([
                $student['grade'],
                $student['percentage'],
                $student['student_id'],
                $course_code,
                $section
            ]);
        } else {
            // Insert new grade
            $insert_sql = "INSERT INTO grades (student_id, course_code, section, grade, percentage) 
                          VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->execute([
                $student['student_id'],
                $course_code,
                $section,
                $student['grade'],
                $student['percentage']
            ]);
        }
    }

    // Commit transaction
    $conn->commit();
    response(true, null, 'Grades saved successfully.');
} catch (PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    response(false, null, 'Database error: ' . $e->getMessage());
}
?>