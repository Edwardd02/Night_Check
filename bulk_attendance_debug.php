<?php
ob_start();
header('Content-Type: application/json');

$debugLog = [];
$pathToGibbon = '../../gibbon.php';

try {
    // Log server environment details
    $debugLog[] = 'PHP Version: ' . phpversion();
    $debugLog[] = 'Current working directory: ' . getcwd();
    $debugLog[] = 'Request method: ' . $_SERVER['REQUEST_METHOD'];

    // Verify Gibbon core
    $debugLog[] = 'Checking Gibbon core at: ' . realpath($pathToGibbon);
    if (!file_exists($pathToGibbon)) {
        throw new RuntimeException('Gibbon core not found at: ' . $pathToGibbon);
    }
    if (!is_readable($pathToGibbon)) {
        throw new RuntimeException('Gibbon core not readable at: ' . $pathToGibbon);
    }

    require_once $pathToGibbon;
    global $connection2;

    // Verify database connection
    if (!($connection2 instanceof PDO)) {
        throw new RuntimeException('Database connection not initialized');
    }
    $debugLog[] = 'Database connection established: ' . $connection2->getAttribute(PDO::ATTR_DRIVER_NAME);

    $connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read input
    $rawInput = file_get_contents('php://input');
    $debugLog[] = 'Raw input: ' . $rawInput;
    $input = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('JSON parse error: ' . json_last_error_msg());
    }
    $debugLog[] = 'Parsed input: ' . print_r($input, true);

    // Validate required fields
    $required = ['student_ids', 'date_id', 'attendance_status'];
    foreach ($required as $field) {
        if (!isset($input[$field])) {
            throw new RuntimeException("Missing field: $field");
        }
        if (empty($input[$field])) {
            throw new RuntimeException("Empty value for required field: $field");
        }
    }

    // Prepare SQL statement with backticks
    $sql = "
        INSERT INTO gibbonNightCheck 
        (gibbonPersonID, `date`, attendance, out_of_school) 
        VALUES (:student_id, :date, :status, 'No')
        ON DUPLICATE KEY UPDATE 
        attendance = VALUES(attendance)
    ";
    $debugLog[] = "Preparing SQL: $sql";

    $stmt = $connection2->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Prepare failed: ' . implode(' ', $connection2->errorInfo()));
    }

    // Process records
    $connection2->beginTransaction();
    $debugLog[] = 'Transaction started';

    $successCount = 0;
    foreach ($input['student_ids'] as $student_id) {
        $params = [
            ':student_id' => $student_id,
            ':date' => $input['date_id'],
            ':status' => $input['attendance_status']
        ];

        $debugLog[] = 'Executing with params: ' . print_r($params, true);

        $result = $stmt->execute($params);
        if (!$result) {
            throw new RuntimeException('Execute failed for student ID: ' . $student_id);
        }

        $successCount += $stmt->rowCount();
        $debugLog[] = "Affected rows: " . $stmt->rowCount();
    }

    $connection2->commit();
    $debugLog[] = 'Transaction committed';

    // Final output
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'count' => $successCount,
        'debug' => $debugLog  // Remove this in production
    ]);

} catch (Throwable $e) {
    // Rollback transaction if active
    if (isset($connection2) && $connection2->inTransaction()) {
        $connection2->rollBack();
        $debugLog[] = 'Transaction rolled back';
    }

    // Log detailed error
    $debugLog[] = 'Error: ' . $e->getMessage();
    $debugLog[] = 'Trace: ' . $e->getTraceAsString();

    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => $debugLog  // Remove this in production
    ]);
    exit;
}