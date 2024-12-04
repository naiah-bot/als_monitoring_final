// edit_attendance.php
<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include 'config.php';

if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    // Query to get attendance record for the student
    $sql = "SELECT * FROM attendance WHERE student_id = '$student_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $attendance = $result->fetch_assoc();
    } else {
        echo "Attendance record not found!";
        exit;
    }
} else {
    echo "No student selected!";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attendance</title>
</head>
<body>
    <h1>Edit Attendance</h1>
    <form action="update_attendance.php" method="POST">
        <input type="hidden" name="student_id" value="<?php echo $attendance['student_id']; ?>">
        <label for="remarks">Remarks:</label>
        <select name="remarks">
            <option value="Present" <?php echo $attendance['remarks'] == 'Present' ? 'selected' : ''; ?>>Present</option>
            <option value="Absent" <?php echo $attendance['remarks'] == 'Absent' ? 'selected' : ''; ?>>Absent</option>
        </select>
        <button type="submit">Update Attendance</button>
    </form>
</body>
</html>
