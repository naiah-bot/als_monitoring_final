<?php
include 'config.php'; // Include database connection

// Get the student_id from the query string
if (isset($_GET['student_id'])) {
    $studentId = $_GET['student_id'];

    // SQL query to fetch attendance dates (both Present and Absent)
    $sql = "SELECT a.remarks, a.date 
            FROM attendance a
            WHERE a.student_id = ? 
            ORDER BY a.date";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $studentId); // Bind the student_id parameter
        $stmt->execute();
        $result = $stmt->get_result();

        $attendanceRecords = [];
        while ($row = $result->fetch_assoc()) {
            $attendanceRecords[] = $row;
        }

        // Return the data as JSON
        echo json_encode($attendanceRecords);
    } else {
        echo json_encode(["error" => "Failed to prepare the SQL statement."]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["error" => "Student ID not provided."]);
}
?>
