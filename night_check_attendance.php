<?php
$selected_date = date('Y-m-d');
if (isset($_GET['date'])) {
    // Process the date manually
    $selected_date = $_GET['date'];

}

$students = null
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
                            class="attendance-select <?php echo strtolower($student['attendance']); ?>"
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