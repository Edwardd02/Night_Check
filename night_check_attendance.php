<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
// Gibbon Framework Setup

global $guid, $connection2, $page;

// Handle API Actions First
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    ob_start();

    try {
        $pathToGibbon = '../../gibbon.php';
        require_once $pathToGibbon;
        // Authentication Check
        if (!isActionAccessible($guid, $connection2, '/modules/Night Check/night_check_attendance.php')) {
            throw new RuntimeException(__('You do not have access to this action.'));
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(__('Invalid JSON input.'));
        }

        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'bulk':
                handleBulkAttendance($input);
                break;

            case 'single':
                handleSingleAttendance($input);
                break;

            default:
                throw new RuntimeException(__('Invalid action specified.'));
        }

    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
        exit;
    }

    exit; // Stop execution for API calls
}

// Normal Page Rendering
try {
    // Authentication Check
    if (!isActionAccessible($guid, $connection2, '/modules/Night Check/night_check_attendance.php')) {
        throw new RuntimeException(__('You do not have access to this action.'));
    }

    $page->breadcrumbs->add(__('Night Check Attendance'), 'nightcheck.php')->add(__('Add'));

    // Date Handling
    $selected_date = $_GET['date'] ?? date('Y-m-d');


    // Student Data Query
    $stmt = $connection2->prepare(
        "SELECT 
            gibbonPerson.gibbonPersonID as id,
            CONCAT(gibbonPerson.surname, ', ', gibbonPerson.preferredName) as name,
            gibbonPerson.gender,
            gibbonPerson.lockerNumber as dorm_room,
            gibbonHouse.name as house
        FROM gibbonPerson
        LEFT JOIN gibbonHouse ON gibbonHouse.gibbonHouseID = gibbonPerson.gibbonHouseID
        LEFT JOIN gibbonRole ON gibbonPerson.gibbonRoleIDPrimary = gibbonRole.gibbonRoleID
        WHERE gibbonRole.name = 'Student'
        ORDER BY gibbonPerson.surname, gibbonPerson.preferredName"
    );
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Attendance Data
    $attendance_data = [];
    $attendance_query = $connection2->prepare(
        "SELECT gibbonPersonID, attendance, out_of_school 
        FROM gibbonNightCheck 
        WHERE date = ?"
    );
    $attendance_query->execute([$selected_date]);
    // Merge Student and Attendance Data
    while ($row = $attendance_query->fetch(PDO::FETCH_ASSOC)) {
        // Convert numeric ID to zero-padded string format
        $studentID = str_pad($row['gibbonPersonID'], 10, '0', STR_PAD_LEFT);
        $attendance_data[$studentID] = [
            'attendance' => $row['attendance'],
            'out_of_school' => $row['out_of_school']
        ];
    }

// In your student data merge:
    foreach ($students as &$student) {
        $student_id = $student['id']; // Already in 0000002774 format
        $student['attendance'] = $attendance_data[$student_id]['attendance'] ?? 'None';
        $student['out_of_school'] = $attendance_data[$student_id]['out_of_school'] ?? 'No';
    }

} catch (Throwable $e) {
    $page->addError($e->getMessage());
}

?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Night Check Attendance</title>
        <link rel="stylesheet" href="modules/Night Check/css/nightcheck.css">
        <script src="modules/Night Check/js/nightcheck.js" defer></script>
    </head>
    <body>
    <h1>Night Check Attendance</h1>

    <div class="date-controls">
        <form method="get" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <input type="hidden" name="q" value="/modules/Night Check/night_check_attendance.php">
            <input type="date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">
            <button type="submit" class="date-submit-btn">Submit</button>
            <button type="button" class="quick-date-btn" onclick="setDate('<?php echo date('Y-m-d'); ?>');">Today</button>
            <button type="button" class="quick-date-btn" onclick="setDate('<?php echo date('Y-m-d', strtotime('-1 day')); ?>');">Yesterday</button>
        </form>
    </div>

    <form method="post" class="form-container">
        <input type="hidden" name="attendance_date" value="<?php echo htmlspecialchars($selected_date); ?>">
        <div class="bulk-actions">
            <button type="button" class="bulk-btn" onclick="setAll('Present')">Mark All Present</button>
        </div>

        <table>
            <thead>
            <tr>
                <th id="studentName" class='nightcheck-th' onclick="sortTable(0, 'text')">Student Name</th>
                <th id="gender" class='nightcheck-th' onclick="sortTable(1, 'text')">Gender</th>
                <th id="grade" class='nightcheck-th' onclick="sortTable(2, 'text')">Grade</th>
                <th id="dormRoom" class='nightcheck-th' onclick="sortTable(3, 'text')">Dorm Room</th>
                <th id="house" class='nightcheck-th' onclick="sortTable(4, 'text')">House</th>
                <th id="advisor" class='nightcheck-th' onclick="sortTable(5, 'text')">Advisor</th>
                <th id="outOfSchool" class='nightcheck-th' >Out of School</th>
                <th id="attendance" class='nightcheck-th' >Attendance</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($student['gender'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($student['grade'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($student['dorm_room'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($student['house'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($student['advisor'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($student['out_of_school'] ?? ''); ?></td>
                    <td>
                        <select name="attendance[<?php echo $student['id']; ?>]"
                                class="attendance-select <?php echo strtolower($student['attendance'] ?? 'none'); ?>"
                                data-student-id="<?php echo $student['id']; ?>"
                                data-student-name="<?php echo $student['name']; ?>">
                            <option value="" class="null" hidden <?php echo !isset($student['attendance']) ? 'selected' : ''; ?>>
                                ⚪ None
                            </option>
                            <option value="Present" class="present" <?php echo $student['attendance'] === 'Present' ? 'selected' : ''; ?>>
                                ✅ Present
                            </option>
                            <option value="Absent" class="absent" <?php echo $student['attendance'] === 'Absent' ? 'selected' : ''; ?>>
                                ❌ Absent
                            </option>
                            <option value="Late" class="late" <?php echo $student['attendance'] === 'Late' ? 'selected' : ''; ?>>
                                🕒 Late
                            </option>
                        </select>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </form>
    </body>
    </html>

<?php
// API Handler Functions
function handleBulkAttendance(array $input) {
    global $connection2;

    $required = ['student_ids', 'date_id', 'attendance_status'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new RuntimeException(__("Missing required field: {0}", [$field]));
        }
    }

    $connection2->beginTransaction();
    try {
        $stmt = $connection2->prepare(
            "INSERT INTO gibbonNightCheck 
            (gibbonPersonID, date, attendance, out_of_school) 
            VALUES (:student_id, :date, :status, 'No')
            ON DUPLICATE KEY UPDATE attendance = VALUES(attendance)"
        );

        foreach ($input['student_ids'] as $student_id) {
            $stmt->execute([
                ':student_id' => $student_id,
                ':date' => $input['date_id'],
                ':status' => $input['attendance_status']
            ]);
        }

        $connection2->commit();
        echo json_encode([
            'success' => true,
            'count' => count($input['student_ids'])
        ]);

    } catch (Exception $e) {
        $connection2->rollBack();
        throw $e;
    }
}

function handleSingleAttendance(array $input) {
    global $connection2;

    $required = ['student_id', 'date_id', 'attendance_status'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new RuntimeException(__("Missing required field: {0}", [$field]));
        }
    }

    $stmt = $connection2->prepare(
        "INSERT INTO gibbonNightCheck 
        (gibbonPersonID, date, attendance, out_of_school) 
        VALUES (:student_id, :date, :status, 'No')
        ON DUPLICATE KEY UPDATE attendance = VALUES(attendance)"
    );

    $stmt->execute([
        ':student_id' => $input['student_id'],
        ':date' => $input['date_id'],
        ':status' => $input['attendance_status']
    ]);

    echo json_encode(['success' => true]);
}