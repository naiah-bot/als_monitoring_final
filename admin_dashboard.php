<?php
session_start();

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Fetch user details from session
$surname = $_SESSION['lastName'] ?? 'lastName';
$firstName = $_SESSION['firstName'] ?? 'firstName';
$userType = $_SESSION['user_type'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Dashboard</title>
    <style>
        /* General page layout */
        body {
            background-image: url('als-bg.svg');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            min-height: 100vh;
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            overflow-y: hidden;
            font-family: Arial, sans-serif;
        }

            header {
                background-color: #211c41;
                color: white; /* White text color */
                display: flex;
                align-items: center; /* Center items vertically */
                padding: 10px 10px; /* Padding around the header */
                z-index: 10; /* Ensure header is above other elements */
                position: relative; /* Positioning context for z-index */
            }

            .logo img {
                height: 30px; /* Adjust as needed */
                margin-right: 10px; /* Space between logo and title */
            }

            h1 {
                font-family: 'Times New Roman', Times, serif;
                font-size: 14px; /* Adjust font size */
                margin: 0; /* Remove default margin */
            }

        /* Container setup */
        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Navigation Drawer */
        .navigation {
            position: fixed;
            width: 250px;
            background-color: #3a326f;
            color: #fff;
            top: 45px; /* Adjusted to position below the header */
            left: 0;
            bottom: 0;
            padding: 20px 0;
            overflow: auto;
        }

        .navigation ul {
            list-style-type: none;
            padding: 0;
        }

        .navigation ul li {
            padding: 15px 0;
        }

        .navigation ul li a {
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px;
            transition: background-color 0.3s;
        }

        .navigation ul li a:hover {
            color: black;
            background-color: #7868dd;
        }

        .navigation ul li a .icon {
            margin-right: 15px;
        }

        .navigation ul li a .title {
            font-size: 18px;
        }

        /* Main content section next to the fixed drawer */
        .main-content {
            margin-left: 250px; /* Prevent overlap with the navigation drawer */
            padding: 20px;
            flex: 1;
        }

        #rectangle-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 20px; /* Adjust spacing between rectangles */
            margin-top: 20px; /* Space above the rectangles */
        }

        .status-rectangle {
            background-color: #3a326f;
            opacity: 0.9;
            color: white; /* Text color */
            border-radius: 10px; /* Rounded corners */
            padding: 20px; /* Inner padding */
            width: 150px; /* Fixed width */
            text-align: center; /* Center text */
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.3s; /* Smooth scale effect */
        }

        .status-rectangle:hover {
            transform: scale(1.05); /* Slightly enlarge on hover */
        }

        .status-icon {
            font-size: 30px; /* Adjust icon size */
            margin-bottom: 10px; /* Space between icon and text */
        }

        /* Profile Styling */
        .profile_icon {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .profile_icon i {
            font-size: 100px;
        }

        .profile-details h2 {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            margin: 10px 0 5px 0;
            font-size: 18px;
            font-weight: bold;
        }

        .profile-details p {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            font-size: 14px;
            font-weight: normal;
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
            <div id="rectangle-container">
                <div class="status-rectangle">
                    <div class="status-icon">
                        <i class="fa fa-calendar-check-o"></i>
                    </div>
                    <p>View Attendance Record</p>
                </div>
                <div class="status-rectangle">
                    <div class="status-icon">
                        <i class="fa fa-book"></i>
                    </div>
                    <p>View Learning Materials Record</p>
                </div>
                <div class="status-rectangle">
                    <div class="status-icon">
                        <i class="fa fa-pencil"></i>
                    </div>
                    <p>Manage Learning Materials</p>
                </div>
                <div class="status-rectangle">
                    <div class="status-icon">
                        <i class="fa fa-user"></i>
                    </div>
                    <p>Student Registration</p>
                </div>
                <div class="status-rectangle">
                    <div class="status-icon">
                        <i class="fa fa-list"></i>
                    </div>
                    <p>List of Students Information</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "logout.php";  // Redirect to logout.php
            }
        }
    </script>
</body>
</html>
