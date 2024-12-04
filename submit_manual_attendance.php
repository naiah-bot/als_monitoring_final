<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    $attendanceData = $_POST['attendance'];
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');

    foreach ($attendanceData as $studentId => $status) {
        // Ensure all variables are assigned before using bind_param()
        $timeIn = $status === 'present' ? $currentTime : NULL;

        // Check if a record already exists for this student on the current date
        $stmtCheck = $conn->prepare("SELECT * FROM attendance WHERE student_id = ? AND date = ?");
        $stmtCheck->bind_param("ss", $studentId, $currentDate);
        $stmtCheck->execute();
        $existingRecord = $stmtCheck->get_result()->fetch_assoc();

        if ($existingRecord) {
            // Update the attendance record if it exists
            $stmtUpdate = $conn->prepare("UPDATE attendance SET remarks = ?, time_in = IF(? = 'present', ?, time_in), time_out = NULL WHERE student_id = ? AND date = ?");
            $stmtUpdate->bind_param("sssss", $status, $status, $timeIn, $studentId, $currentDate);
            $stmtUpdate->execute();
        } else {
            // Insert a new attendance record if it doesn't exist
            $stmtInsert = $conn->prepare("INSERT INTO attendance (student_id, date, time_in, remarks) VALUES (?, ?, ?, ?)");
            $stmtInsert->bind_param("ssss", $studentId, $currentDate, $timeIn, $status);
            $stmtInsert->execute();
        }
    }

    // Redirect back to the attendance page with a success message
    header("Location: student_attendance.php?message=Attendance updated successfully.");
    exit;
} else {
    // Redirect back with an error message if no attendance data is submitted
    header("Location: student_attendance.php?error=No attendance data submitted.");
    exit;
}
?>
