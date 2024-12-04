<?php
include 'config.php';

// Set the response header to JSON for API-like communication
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';

    if (empty($student_id)) {
        echo json_encode(['status' => 'error', 'message' => 'No QR Code data received.']);
        exit;
    }

    // Validate the student ID
    $stmt = $conn->prepare("SELECT id FROM users WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Student ID.']);
        exit;
    }

    // Mark attendance
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');

    // Check if the student already has a record for today
    $stmt = $conn->prepare("SELECT * FROM attendance WHERE student_id = ? AND date = ?");
    $stmt->bind_param("ss", $student_id, $currentDate);
    $stmt->execute();
    $attendance = $stmt->get_result()->fetch_assoc();

    if ($attendance) {
        // Update time_out if time_out is not already set
        if (empty($attendance['time_out'])) {
            $stmt = $conn->prepare("UPDATE attendance SET time_out = ? WHERE student_id = ? AND date = ?");
            $stmt->bind_param("sss", $currentTime, $student_id, $currentDate);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Time out recorded successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to record time out.']);
            }
        } else {
            echo json_encode(['status' => 'info', 'message' => 'Attendance already marked for today.']);
        }
    } else {
        // Insert a new attendance record
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, date, time_in) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $student_id, $currentDate, $currentTime);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Time in recorded successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to record time in.']);
        }
    }
} else {
    // Reject non-POST requests
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
