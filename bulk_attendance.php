<?php
// Start output buffering and set JSON header
ob_start();
header('Content-Type: application/json');

// Path configuration
$pathToGibbon = '../../gibbon.php';

try {
    // Validate Gibbon core
    if (!file_exists($pathToGibbon) || !is_readable($pathToGibbon)) {
        throw new RuntimeException('Gibbon core not found in ' . getcwd());
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
    $required = ['student_ids', 'date_id', 'attendance_status'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new RuntimeException("Missing required field: $field");
        }
    }

    // Prepare SQL statement
    $stmt = $connection2->prepare("
        INSERT INTO gibbonNightCheck 
        (gibbonPersonID, date, attendance, out_of_school) 
        VALUES (:student_id, :date, :status, 'No')
        ON DUPLICATE KEY UPDATE 
        attendance = VALUES(attendance)
    ");

    // Process records in transaction
    $connection2->beginTransaction();
    foreach ($input['student_ids'] as $student_id) {
        $stmt->execute([
            ':student_id' => $student_id,
            ':date' => $input['date_id'],
            ':status' => $input['attendance_status']
        ]);
    }
    $connection2->commit();

    // Return success
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'count' => count($input['student_ids'])
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