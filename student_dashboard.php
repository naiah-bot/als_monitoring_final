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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_student-dashboard.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Dashboard</title>
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
                        <!-- Dynamically display the name of the user -->
                        <h2><?php echo "$surname, $firstName"; ?></h2> <!-- Display surname and first name -->
                        <p><?php echo ucfirst($userType); ?></p> <!-- Display the user type (e.g., Admin) -->
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
        <div id="status-container">
            <div class="status-rectangle">
                <span class="status-icon"><i class="fa fa-percent"></i></span>
                <p>Keep up the good work in attending your classes</p>
            </div>
            <div class="status-rectangle">
                <span class="status-icon"><i class="fa fa-book"></i></span>
                <p>All learning materials have been returned</p>
            </div>
            <div class="status-rectangle">
                <span class="status-icon"><i class="fa fa-check-circle"></i></span>
                <p>Self-assessment already answered!</p>
            </div>
        </div>
          
        <div class="content">
            <div id="rectangle-container">
                <div id="rectangle_mission">
                    <h2>Mission</h2>
                    <p>To develop exemplary programs and open learning opportunities for out-of-school youth and adults to achieve multiple competencies and skills for industry and personal development.</p>
                </div>
                <div id="rectangle_vision">
                    <h2>Vision</h2>
                    <p>By 2022, we will have nation-loving and competent lifelong learners able to rise to challenges and opportunities through quality, accessible, relevant, and liberating K to 12 Program delivered by a modern, professional, proactive, nimble, truly nurturing DepEd.</p>
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
