<?php
session_start();

// Include the database connection
include 'config.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $student_id = $_POST['student_id'];
    $birthdate = $_POST['birthdate']; // Date format YYYY-MM-DD
    $address = $_POST['address'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    
    // Handle file upload (photo)
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo_name = $_FILES['photo']['name'];
        $photo_tmp_name = $_FILES['photo']['tmp_name'];
        $photo_size = $_FILES['photo']['size'];
        $photo_ext = strtolower(pathinfo($photo_name, PATHINFO_EXTENSION));

        // Allowed file extensions for the photo
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        // Check if the file extension is valid
        if (in_array($photo_ext, $allowed_extensions)) {
            // Generate a unique name for the photo
            $photo_new_name = uniqid('student_', true) . '.' . $photo_ext;
            $upload_dir = 'uploads/photos/';

            // Try to move the uploaded file to the upload directory
            if (move_uploaded_file($photo_tmp_name, $upload_dir . $photo_new_name)) {
                // Photo uploaded successfully
            } else {
                echo "Error uploading photo.";
                exit;
            }
        } else {
            echo "Invalid photo format.";
            exit;
        }
    } else {
        echo "Please upload a photo.";
        exit;
    }

    // Prepare and bind the SQL query to insert the data into the database
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, student_id, birthdate, address, email, phone, gender, photo, user_type) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'student')");
    $stmt->bind_param("sssssssss", $first_name, $last_name, $student_id, $birthdate, $address, $email, $phone, $gender, $photo_new_name);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect to the student list page after successful registration
        header("Location: admin_student-list.php");
        exit;
    } else {
        echo "Error registering student: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    // Redirect to the registration page if the form was not submitted properly
    header("Location: admin_registration.php");
    exit;
}
?>
