<?php
// Start output buffering immediately
ob_start();

// Set JSON header right away
header('Content-Type: application/json');

// Path to Gibbon core - verify this matches your server
$pathToGibbon = '/var/www/gibbon.local/gibbon.php';

try {
    // Verify Gibbon exists
    if (!file_exists($pathToGibbon) || !is_readable($pathToGibbon)) {
        throw new RuntimeException('Gibbon core not found');
    }

    // Include Gibbon
    require_once $pathToGibbon;

    // Get database connection
    global $connection2;
    try {
        $test = $connection2->query("SELECT COUNT(*) FROM gibbonPerson");
        error_log('Connection test: ' . $test->fetchColumn());
    } catch (PDOException $e) {
        error_log('CONNECTION FAILURE: ' . $e->getMessage());
    }
    // Force error reporting
    $connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Validate input
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
    error_log('===== STARTING BULK UPDATE =====');
    error_log('Received input: ' . print_r($input, true));
    // Prepare SQL
    $sql = "INSERT INTO gibbonNightCheck 
            (gibbonPersonID, date, attendance, out_of_school) 
            VALUES (:student_id, :date, :status, 'No')
            ON DUPLICATE KEY UPDATE 
            attendance = VALUES(attendance)";

    $stmt = $connection2->prepare($sql);

    // Execute in transaction
    $connection2->beginTransaction();

    foreach ($input['student_ids'] as $student_id) {
        error_log("Processing student ID: $student_id");

        $result = $stmt->execute([
            ':student_id' => $student_id,
            ':date' => $input['date_id'],
            ':status' => $input['attendance_status']
        ]);

        error_log("Execution result: " . ($result ? 'Success' : 'Failed'));
        error_log("Last insert ID: " . $connection2->lastInsertId());
    }

    $connection2->commit();
    error_log('Transaction committed successfully');
    // Return success
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'count' => count($input['student_ids'])
    ]);

} catch (Throwable $e) {
    // Clean up and return error
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    error_log('Bulk Attendance Error: '.$e->getMessage());
    exit;
}