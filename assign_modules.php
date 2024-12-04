<?php
session_start();
include 'config.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$success_message = "";  // Initialize the success message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = $_POST['student_id'];
    $modules = $_POST['modules'];

    // Check if student and modules are selected
    if (!empty($studentId) && !empty($modules)) {
        foreach ($modules as $module) {
            // Insert each module assignment into the database
            $sql = "INSERT INTO student_modules (student_id, module_name) VALUES (?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("is", $studentId, $module);
                $stmt->execute();
            }
        }
        
        // Set success message in the session to pass it to the next page
        $_SESSION['success_message'] = "Modules assigned successfully!";
        header("Location: admin_l-materials.php");  // Redirect to the materials page after success
        exit;
    } else {
        $_SESSION['error_message'] = "Please select a student and modules.";  // Set error message if fields are empty
        header("Location: admin_l-materials.php");
        exit;
    }
}
