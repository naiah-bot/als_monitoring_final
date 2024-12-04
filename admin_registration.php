<?php
session_start();

// Include the database connection config
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$surname = $_SESSION['lastName'] ?? 'lastName';
$firstName = $_SESSION['firstName'] ?? 'firstName';
$userType = $_SESSION['user_type'] ?? 'Admin';

// Include QR code library
include 'phpqrcode/qrlib.php';

// Initialize variables for the form
$studentId = ''; // Will be set after retrieving the student ID from the form
$qrCodePath = ''; // Path to the generated QR code

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate a unique student ID (e.g., using a timestamp or any logic)
    $studentId = uniqid('STD-');// Generates a unique ID based on current timestamp

    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $address = $_POST['address'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $highestEducationalAttainment = $_POST['highest_educational_attainment'] ?? '';  // New field for highest education

    // Generate QR code for the student ID
    $qrCodePath = "qrcodes/$lastName, $firstName.png"; // Path to save QR code
    QRcode::png($studentId, $qrCodePath, QR_ECLEVEL_L, 10); // Generate and save the QR code

    // Check if highest education is provided, if not set to NULL
    $highestEducationalAttainment = !empty($highestEducationalAttainment) ? $highestEducationalAttainment : NULL;

    // Insert student data into the database including highest education and user type as "student"
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, student_id, birthdate, address, email, phone, gender, highest_educational_attainment, qr_code_path, user_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $userType = 'student'; // Automatically set the user type to 'student'
    
    // Bind parameters, adjusting for NULL highestEducation if not provided
    if ($highestEducationalAttainment !== NULL) {
        $stmt->bind_param("sssssssssss", $firstName, $lastName, $studentId, $birthdate, $address, $email, $phone, $gender, $highestEducationalAttainment, $qrCodePath, $userType);
    } else {
        $stmt->bind_param("ssssssssss", $firstName, $lastName, $studentId, $birthdate, $address, $email, $phone, $gender, $qrCodePath, $userType);
    }
    
    if ($stmt->execute()) {
        // Redirect to the student list page after successful registration
        header("Location: admin_student-list.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_admin-dashboard.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Student Registration</title>
    <style>
        .main-content {
            max-height: 80vh; /* Adjust to your preferred height */
            overflow-y: auto; /* Scrollable */
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-buttons {
            display: flex;
            justify-content: space-between;
        }

        .form-buttons button {
            width: 48%;
            padding: 10px;
            font-size: 16px;
        }

        /* Style for displaying the QR code */
        .qr-code-container {
            margin-top: 20px;
            text-align: center;
        }

        .qr-code-container img {
            width: 150px;
            height: 150px;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="als-logo.svg" alt="Logo" />
        </div>
        <h1>ALTERNATIVE LEARNING SYSTEM - BUENAVISTA CHAPTER</h1>
    </header>
    <div class="container">
        <div class="navigation" id="navigationDrawer">
            <ul>
                <li>
                    <span class="profile_icon"><i class="fa fa-user" aria-hidden="true"></i></span>
                    <div class="profile-details">
                        <h2><?php echo "$surname, $firstName"; ?></h2>
                        <p><?php echo ucfirst($userType); ?></p>
                    </div>
                </li>
                <li>
                    <a href="admin_dashboard.php">
                        <span class="icon"><i class="fa fa-home" aria-hidden="true"></i></span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="admin_attendance.php">
                        <span class="icon"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></span>
                        <span class="title">Attendance</span>
                    </a>
                </li>
                <li>
                    <a href="admin_l-materials.php">
                        <span class="icon"><i class="fa fa-book" aria-hidden="true"></i></span>
                        <span class="title">Learning Materials</span>
                    </a>
                </li>
                <li>
                    <a href="manage_modules.php">
                        <span class="icon"><i class="fa fa-pencil" aria-hidden="true"></i></span>
                        <span class="title">Manage Learning Materials</span>
                    </a>
                </li>
                <li>
                    <a href="admin_registration.php">
                        <span class="icon"><i class="fa fa-user-plus" aria-hidden="true"></i></span>
                        <span class="title">Student Registration</span>
                    </a>
                </li>
                <li>
                    <a href="admin_student-list.php">
                        <span class="icon"><i class="fa fa-list" aria-hidden="true"></i></span>
                        <span class="title">List of Students</span>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="confirmLogout()">
                        <span class="icon"><i class="fa fa-sign-out" aria-hidden="true"></i></span>
                        <span class="title">Log Out</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="main-content">
            <h2>Student Registration</h2>
            <form action="admin_registration.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="photo">Upload Photo:</label>
                    <input type="file" name="photo" id="photo" accept="image/*">
                </div>
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" name="first_name" id="first_name" placeholder="Enter first name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" name="last_name" id="last_name" placeholder="Enter last name" required>
                </div>
                <div class="form-group">
                    <label for="birthdate">Date of Birth:</label>
                    <input type="date" name="birthdate" id="birthdate" required>
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <input type="text" name="address" id="address" placeholder="Enter address" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" name="email" id="email" placeholder="Enter email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number:</label>
                    <input type="text" name="phone" id="phone" placeholder="Enter phone number" required>
                </div>
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select name="gender" id="gender" required>
                        <option value="">Select gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="highest_educational_attainment">Highest Educational Attainment:</label>
                        <select id="highestEducationalAttainment" name="highest_educational_attainment" required>
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
                </div>
                <div class="form-buttons">
                    <button type="reset" class="btn-cancel">Cancel</button>
                    <button type="submit" class="btn-save">Save</button>
                </div>
            </form>

            <!-- Display the generated QR Code -->
            <div class="qr-code-container">
                <?php if (!empty($studentId)): ?>
                    <h3>Student ID QR Code:</h3>
                    <img src="qrcodes/<?php echo $studentId; ?>.png" alt="QR Code">
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "logout.php";
            }
        }
    </script>
</body>
</html>
