<?php
session_start();
include 'config.php'; // Include database connection
include 'phpqrcode/qrlib.php'; // Include QR code library

// Handle signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $userType = $_POST['userType'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $firstName = $_POST['firstName'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $address = $_POST['address'] ?? '';
    $userName = $_POST['userName'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';  // New field for Email
    $phone = $_POST['phone'] ?? '';  // New field for Phone
    $highestEducationalAttainment = $_POST['highestEducationalAttainment'] ?? '';

    // Validate required fields
    if (empty($userType) || empty($lastName) || empty($firstName) || empty($gender) || empty($birthdate) || empty($address) || empty($userName) || empty($password) || empty($email) || empty($phone)) {
        $errorMessage = "Please fill in all required fields.";
    } else {
        // Only check highestEducationalAttainment if userType is student
        if ($userType === "student" && empty($highestEducationalAttainment)) {
            $errorMessage = "Please fill in the highest educational attainment.";
        } else {
            // Generate a unique student ID
            $studentId = uniqid('STD-');

            // Generate QR code
            $qrCodePath = "qrcodes/$lastName, $firstName.png"; // Path to save QR code
            QRcode::png($studentId, $qrCodePath, QR_ECLEVEL_L, 10); // Generate and save the QR code

            // Insert the student into the database (including email and phone)
            $stmt = $conn->prepare("INSERT INTO users (user_type, last_name, first_name, gender, birthdate, address, username, password, email, phone, highest_educational_attainment, student_id, qr_code_path) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssssss", $userType, $lastName, $firstName, $gender, $birthdate, $address, $userName, $password, $email, $phone, $highestEducationalAttainment, $studentId, $qrCodePath);

            if ($stmt->execute()) {
                // Redirect to login page after successful signup
                header("Location: login.php");
                exit; // Prevent further script execution
            } else {
                $errorMessage = "Error: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_signup.css">
    <title>ALS Attendance and Module Monitoring - Signup/Login</title>
    <style>
        /* Inline CSS for the form fields */
        input[type="text"], input[type="password"], input[type="email"], input[type="date"], input[type="number"], select, button {
            width: 100%; /* Make the input field span the entire width */
            padding: 12px 20px; /* Add padding for more space inside */
            font-size: 16px; /* Make the text size bigger */
            margin: 8px 0; /* Space between the inputs */
            border-radius: 8px; /* Rounded corners */
            border: 1px solid #ccc; /* Light border for inputs */
            box-sizing: border-box; /* Make sure padding doesn't mess up layout */
        }

        button {
            background-color: #4CAF50; /* Green background */
            color: white; /* White text */
            cursor: pointer; /* Pointer cursor on hover */
        }

        button:hover {
            background-color: #45a049; /* Darker green when hovering */
        }

        /* Style the form container */
        #modal {
            background-color:#3a326f;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 400px;
            top: 5px; /* Adjust the top value for signup */
            height: auto;
            width: 400px;
            background-color: rgba(58, 50, 111, 0.95); /* Darker background for signup */
            border-radius: 15px; /* Rounded edges */
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.3);
            z-index: 10; /* Ensure the rectangle is in front */
            position: relative; /* Make sure it's positioned properly within the page */
            padding: 30px; /* Inner spacing */
            margin: auto; /* Centering */
            text-align: left;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .account-prompt {
            margin-top: 15px;
            font-size: 14px;
        }

        .signup-link {
            text-decoration: none;
            color: #4CAF50;
        }
    </style>
    <script>
        // Function to handle enabling/disabling the educational attainment field
        function toggleEducationalAttainment() {
            const userType = document.getElementById("userType").value;
            const educationalAttainment = document.getElementById("highestEducationalAttainment");

            // Disable the field if the user selects 'Admin'
            if (userType === "admin") {
                educationalAttainment.value = ""; // Clear the field
                educationalAttainment.disabled = true; // Disable the field
            } else {
                educationalAttainment.disabled = false; // Enable the field
            }
        }
    </script>
</head>
<body>
        <div id="modal">
            <div class="container">
                <h2>Create an Account</h2>
                <form action="" method="POST">
                    <input type="hidden" name="signup" value="1">
                    <select id="userType" name="userType" required onchange="toggleEducationalAttainment()">
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                    </select>
                    <input type="text" id="lastName" name="lastName" placeholder="Enter your Last Name" required>
                    <input type="text" id="firstName" name="firstName" placeholder="Enter your First Name" required>
                    <select id="gender" name="gender" required>
                        <option value="">Select gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                    <input type="date" id="birthdate" name="birthdate" required><br>
                    <input type="text" id="address" name="address" placeholder="Enter your Address" required>
                    <input type="text" id="userName" name="userName" placeholder="Enter your Username" required>
                    <input type="password" id="password" name="password" placeholder="Enter your Password" required>
                    
                    <!-- Add Email and Phone fields with inline styles -->
                    <input type="email" id="email" name="email" placeholder="Enter your Email" required>
                    <input type="text" id="phone" name="phone" placeholder="Enter your Phone Number" required>

                    <select id="highestEducationalAttainment" name="highestEducationalAttainment" required>
                        <option value="">Select Highest Educational Attainment</option>
                        <option value="Kinder">Kinder</option>
                        <option value="Grade 1">Grade 1</option>
                        <option value="Grade 2">Grade 2</option>
                        <option value="Grade 3">Grade 3</option>
                        <option value="Grade 4">Grade 4</option>
                        <option value="Grade 5">Grade 5</option>
                        <option value="Grade 6">Grade 6</option>
                        <option value="Grade 7">Grade 7</option>
                        <option value="Grade 8">Grade 8</option>
                        <option value="Grade 9">Grade 9</option>
                        <option value="Grade 10">Grade 10</option>
                        <option value="Grade 11">Grade 11</option>
                        <option value="Grade 12">Grade 12</option>
                    </select>
                    <button type="submit" id="registerButton">Register</button>
                </form>

                <?php
                // Display error if set
                if (isset($errorMessage)) {
                    echo "<p style='color:red;'>$errorMessage</p>";
                }
                ?>
                
                <p class="account-prompt">Already have an account? <a href="login.php" class="signup-link">Login</a></p>
            </div>
        </div>
    </div>
</body>
</html>
