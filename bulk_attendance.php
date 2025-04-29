<?php
include __DIR__.'/gibbon.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Night Check/night_check_attendance.php') == false) {
    $page->addError(__('You do not have access to this action.'));
} else {
    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $connection2->beginTransaction();

            // Prepare insert/update statement
            $stmt = $connection2->prepare("
                INSERT INTO gibbonNightCheck 
                (gibbonPersonID, attendance, out_of_school, date) 
                VALUES (:id, :status, 'No', :date)
                ON DUPLICATE KEY UPDATE 
                attendance = VALUES(attendance),
                out_of_school = VALUES(out_of_school)
            ");

            $date = $_POST['attendance_date'] ?? date('Y-m-d');
            $attendanceData = $_POST['attendance'] ?? [];

            foreach ($attendanceData as $studentID => $status) {
                $stmt->execute([
                    ':id'     => $studentID,
                    ':status' => $status,
                    ':date'   => $date
                ]);
            }

            $connection2->commit();
            $page->addMessage(__('Attendance records updated successfully.'));
        } catch (PDOException $e) {
            $connection2->rollBack();
            $page->addError(__('Failed to update attendance: ').$e->getMessage());
        }
    }

}
?>

