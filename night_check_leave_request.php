<!DOCTYPE html>
<html>
<head>
    <title>Date Submission Form</title>
</head>
<body>
<h1>Date Submission Form</h1>

<form method="post" action="">
    <label for="date">Enter a Date:</label>
    <input type="date" id="date" name="date" required>
    <input type="submit" name="submit" value="Submit">
</form>

<?php
if (isset($_POST['submit'])) {
    $submittedDate = $_POST['date'];
    echo "<p>You submitted the date: " . htmlspecialchars($submittedDate) . "</p>";
}
?>
</body>
</html>