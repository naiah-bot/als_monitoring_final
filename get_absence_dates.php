<?php
// Include the database connection
include 'config.php';

// Check if student_id is provided
if (isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];

    // Query to fetch absence dates for the student
    $sql = "SELECT date FROM attendance WHERE student_id = ? AND remarks = 'Absent'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $absenceDates = [];
    while ($row = $result->fetch_assoc()) {
        $absenceDates[] = $row;  // Store each date
    }

    // Return the data as JSON
    echo json_encode($absenceDates);
}

$conn->close();
?>
