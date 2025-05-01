<?php
// Start output buffering and set JSON header
ob_start();
header('Content-Type: application/json');

// Path configuration (same relative path as bulk_attendance.php)
$pathToGibbon = '../../gibbon.php';

try {
    // Validate Gibbon core
    if (!file_exists($pathToGibbon) || !is_readable($pathToGibbon)) {
        throw new RuntimeException('Gibbon core not found');
    }

    require_once $pathToGibbon;
    global $connection2;

    // Configure database connection
    $connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Validate and parse input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Invalid JSON input');
    }

    // Validate required fields
    $required = ['student_id', 'date_id', 'attendance_status'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new RuntimeException("Missing required field: $field");
        }
    }

    // Validate student ID format
    if (!ctype_digit((string)$input['student_id'])) {
        throw new RuntimeException("Invalid student ID format");
    }

    // Prepare SQL statement (same structure as bulk version)
    $stmt = $connection2->prepare("
        INSERT INTO gibbonNightCheck 
        (gibbonPersonID, date, attendance, out_of_school) 
        VALUES (:student_id, :date, :status, 'No')
        ON DUPLICATE KEY UPDATE 
        attendance = VALUES(attendance)
    ");

    // Execute the statement
    $stmt->execute([
        ':student_id' => $input['student_id'],
        ':date' => $input['date_id'],
        ':status' => $input['attendance_status']
    ]);

    // Return success
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Attendance updated successfully'
    ]);

} catch (Throwable $e) {
    // Handle errors
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}