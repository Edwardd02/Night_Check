<?php
// Include Gibbon core
include __DIR__.'/gibbon.php';

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['student_id'], $data['date_id'], $data['attendance_status'])) {
    die(json_encode(['success' => false, 'error' => 'Missing parameters']));
}

try {
    // Prepare statement for Gibbon's database structure
    $stmt = $connection2->prepare("
        INSERT INTO gibbonNightCheck 
        (gibbonPersonID, date, attendance, out_of_school)
        VALUES (:id, :date, :status, 'No')
        ON DUPLICATE KEY UPDATE
        attendance = VALUES(attendance),
        out_of_school = VALUES(out_of_school)
    ");

    // Bind parameters
    $stmt->execute([
        ':id'     => $data['student_id'],
        ':date'   => $data['date_id'],
        ':status' => $data['attendance_status']
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}