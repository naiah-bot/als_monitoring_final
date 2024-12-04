<?php
// Include the database connection
include 'config.php';

if (isset($_GET['id'])) {
    $studentId = $_GET['id'];

    // Fetch student details
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        echo "<p><strong>Name:</strong> " . $student['first_name'] . " " . $student['last_name'] . "</p>";
        echo "<p><strong>Student ID:</strong> " . $student['student_id'] . "</p>";
        echo "<p><strong>Date of Birth:</strong> " . $student['birthdate'] . "</p>";
        echo "<p><strong>Address:</strong> " . $student['address'] . "</p>";
        echo "<p><strong>Email:</strong> " . $student['email'] . "</p>";
        echo "<p><strong>Phone:</strong> " . $student['phone'] . "</p>";
        echo "<p><strong>Gender:</strong> " . ucfirst($student['gender']) . "</p>";
        // You can add more fields here as needed
    } else {
        echo "No details found.";
    }

    $stmt->close();
    $conn->close();
}
?>
