<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
include 'config.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: admin_student-list.php");
    exit;
}

$studentId = $_GET['id'];

// Delete the student record
$sql = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $studentId);

if ($stmt->execute()) {
    header("Location: admin_student-list.php");
    exit;
} else {
    echo "Error deleting student.";
}
?>
