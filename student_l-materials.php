<?php
session_start();

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$surname = $_SESSION['lastName'] ?? 'lastName';  // Default to 'Surname' if not set
$firstName = $_SESSION['firstName'] ?? 'firstName';  // Default to 'FirstName' if not set
$userType = $_SESSION['user_type'] ?? 'Admin';  // Default to 'Admin' if not set

// Database connection (assume you've already set it in your config file)
include 'config.php';

// Get user_id from session (or some other way if needed)
$user_id = $_SESSION['user']; // Or however you store it in session

// Fetch module assignments for the student
$query = "SELECT ma.module_id, m.module_name, ma.date_distributed, ma.date_received
          FROM module_assignments ma
          JOIN modules m ON ma.module_id = m.id
          WHERE ma.user_id = ?"; // Use prepared statement for security

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);  // Bind the user_id (session value)
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_student-dashboard.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Learning Materials</title>
    <style>
        /* Center the table and content */
        table {
            width: 80%;
            margin: 0 auto; /* Center the table horizontally */
            border-collapse: collapse;
        }

        table th, table td {
            padding: 8px 15px;
            text-align: center; /* Center text in table cells */
            border: 1px solid #ddd;
        }

        table th {
            background-color: #211c41;
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
                    <a href="student_dashboard.php">
                        <span class="icon"><i class="fa fa-home" aria-hidden="true"></i></span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="student_attendance.php">
                        <span class="icon"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></span>
                        <span class="title">Attendance</span>
                    </a>
                </li>
                <li>
                    <a href="student_l-materials.php">
                        <span class="icon"><i class="fa fa-book" aria-hidden="true"></i></span>
                        <span class="title">Learning Material</span>
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
            <h2>Assigned Modules</h2>
            <table>
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Date Received</th>
                        <th>Date Returned</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['module_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['date_received']); ?></td>
                            <td><?php echo htmlspecialchars($row['date_returned']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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
