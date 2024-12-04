<?php
session_start();
include 'config.php'; // Ensure database connection is correctly included

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepare and bind the SQL statement
    if ($stmt = $conn->prepare("SELECT student_id, first_name, last_name, password, user_type FROM users WHERE username = ?")) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        // Check if user exists and verify the password
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($studentId, $firstName, $lastName, $dbPassword, $userType);
            $stmt->fetch();

            // Use plain text comparison if passwords are not hashed (but hashing is recommended)
            // Replace the next line with: if (password_verify($password, $dbPassword)) if you use hashed passwords
            if ($password === $dbPassword) {
                $_SESSION['user'] = $username;  // Store the username in session
                $_SESSION['student_id'] = $studentId; // Store the student_id in session
                $_SESSION['firstName'] = $firstName; // Store first name
                $_SESSION['lastName'] = $lastName; // Store last name
                $_SESSION['user_type'] = $userType; // Store user type (admin/student)

                // Redirect to appropriate dashboard based on user type
                if ($userType === 'admin') {
                    header("Location: admin_dashboard.php");
                } elseif ($userType === 'student') {
                    header("Location: student_dashboard.php");
                }
                exit;
            } else {
                $error = "Invalid credentials. Password does not match.";
            }
        } else {
            $error = "Invalid credentials. Username not found.";
        }

        // Close statement
        $stmt->close();
    } else {
        echo "Error: Could not prepare SQL statement.";  // Debugging message
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALS Attendance and Module Monitoring</title>
    <link rel="stylesheet" href="style_signup.css">
</head>
<body>
    <img src="als-logo.svg" id="logo">
    <div id="rectangle">

        <div class="container">
            <form action="" method="POST">
                <input type="text" placeholder="Enter Username" name="username" required>
                <input type="password" placeholder="Enter Password" name="password" required>
                <button type="submit" id="loginButton">Login</button>
            </form>

            <?php
            // Display error if set
            if (isset($error)) {
                echo "<p style='color:red;'>$error</p>";
            }
            ?>

            <p class="account-prompt">Don't have an account? <a href="signup.php" class="signup-link">Create an account</a></p>
        </div>
    </div>

</body>
</html>
