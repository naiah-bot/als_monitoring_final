<?php
session_start();
include('config.php');

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$surname = $_SESSION['lastName'] ?? 'lastName';
$firstName = $_SESSION['firstName'] ?? 'firstName';
$userType = $_SESSION['user_type'] ?? 'Admin';

// Check if there's a success message or error message in the session
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';

// Clear messages after displaying
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Fetch students from the database
$query = "SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE user_type = 'Student'";
$result = mysqli_query($conn, $query);
$students = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_admin-dashboard.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <title>Assign Modules</title>
    <style>
        /* Make the module list scrollable */
        .module-list {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 10px;
            margin-bottom: 20px;
        }

        .module-list div {
            margin-bottom: 10px;
        }

        /* Success button style */
        .modal-content button {
            background-color: #4CAF50; /* Green */
            color: white;
            border: none;
            padding: 10px 20px;
            margin-top: 15px;
            font-size: 14px;
            border-radius: 8px; /* Rounded button */
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .modal-content button:hover {
            background-color: #45a049; /* Darker green */
        }

        /* Custom styles for the button */
            button[type="submit"] {
            background-color: #4CBB17; /* Blue background */
            color: white;              /* White text */
            padding: 8px 16px;         /* Smaller padding around the text */
            font-size: 12px;           /* Smaller font size */
            border-radius: 4px;        /* Smaller rounded corners */
            cursor: pointer;          /* Pointer cursor on hover */
            border: none;              /* Remove the default border */
            transition: background-color 0.3s ease;
            margin-top: 30px;          /* Add more margin to move the button down */
        }

        button[type="submit"]:hover {
            background-color: #228B22; /* Darker blue on hover */
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
            <h2>Assign Modules to Students</h2>
            <form action="assign_modules.php" method="POST">
                <div class="module-list">
                    <?php 
                    // List of modules
                    $modules = [
                        'LS1A Are you a Critical Reader',
                        'LS1B Describing ideas and Feelings',
                        'LS1C Effective Communication',
                        'LS1D Effective Writing',
                        'LS1E Filling Up Forms Accurately',
                        'LS1F Giving and Receiving Constructive Feedback',
                        'LS1G How To Become An Intelligent Listener',
                        'LS1H Know Your Rights',
                        'LS11 Learning Good Values from Literature',
                        'LS1J Let us Talk',
                        'LS1K Outlining',
                        'LS1L Summarizing',
                        'LS1M The Interview',
                        'LS1N A Language of Our Own',
                        'LS2A Addictive and Dangerous Drugs 1',
                        'LS2B Addictive and Dangerous Drugs 2',
                        'LS2BB Organs of the Body',
                        'LS2C Advances in Communication Technology',
                        'LS2CC Multiplication and Division of Fractions',
                        'LS2E Eat right, Be Healthy',
                        'LS2EE The Nervous System',
                        'LS2F Ang Volume',
                        'LS2FF Pagdaragdag at Pagbabawas ng Desimal',
                        'LS2G Business Math 1',
                        'LS2GG Pagdaragdag at Pagbabawas ng Praksyon',
                        'LS2H Buying Wisely',
                        'LS2HH Panumbasan at Proporsyon',
                        'LS21 The Circulatory System',
                        'LS211 Percent and Percentages',
                        'LS2J Classification of Plants',
                        'LS2JJ Percentages, Ratio and Proportion',
                        'LS2K Composting',
                        'LS2KK Proper Use of Electricity',
                        'LS2L Developing Scientific Thinking Skills',
                        'LS2L4 The Muscular System',
                        'LS2LL Ratio and Proportion',
                        'LS2M Iwasan ang Bulati',
                        'LS2M4 Measuring Weight Part 1',
                        'LS2MM Reproductive Health',
                        'LS2N The Ecosystem',
                        'LS2N4 Multiplication and Division of Decimal',
                        'LS2NN The Skeletal System',
                        'LS2P Geometric Shapes',
                        'LS2PP Taking Care of Our Aquatic Resources',
                        'LS2QQ The Cost of Environmental Degradation',
                        'LS2R Interpreting Electric Meters and Bills',
                        'LS2RR Understanding How Our Sense Organ Works',
                        'LS2S Learning About Fractions',
                        'LS2SS Using Scientific Method in Agriculture',
                        'LS2T Lines and Angles',
                        'LS2TT Volume',
                        'LS2U Mean, Median, Mode',
                        'LS2V Measurement, Perimeter and Circumference',
                        'LS2VV Water and Its Costs',
                        'LS2W Measuring Volume',
                        'LS2Y Measuring Weight - Part 2',
                        'LS2Z Measuring Weight - Part 1',
                        'LS3A Aquatic and Man-made Ecosystems',
                        'LS3B Balance in Nature',
                        'LS3C Ideas for Income Generating Projects',
                        'LS3D Pesticides',
                        'LS3E Is Your Workplace Safe',
                        'LS3U How to Grow A Vegetable Garden',
                        'LS4A Building Relationship with Others',
                        'LS4B Dealing with Anger, Fear and Frustration',
                        'LS4C Heroes Then and Now',
                        'LS4D I\'m Proud to be a Filipino',
                        'LS4E Ito ang Aking Kultura',
                        'LS4F Kahalagahan ng Pamilya',
                        'LS4G Know Your News',
                        'LS4H Let\'s Help One Another',
                        'LS41 Major Religions in the Philippines',
                        'LS4J Mga Sagisag ng Ating Bansa',
                        'LS4K National Treasures',
                        'LS4L Participate in Elections',
                        'LS4M Respect One Another\'s Religion',
                        'LS4N Are You Looking For A Job',
                        'LS4V Kaya Natin Makamit Lahat Kung Magtulungan Tayo'
                    ];
                    
                    foreach ($modules as $module): ?>
                        <div>
                            <input type="checkbox" name="modules[]" value="<?php echo htmlspecialchars($module); ?>" id="<?php echo htmlspecialchars($module); ?>">
                            <label for="<?php echo htmlspecialchars($module); ?>"><?php echo htmlspecialchars($module); ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div>
                    <select name="student_id" id="student" style="width: 100%;">
                        <option value="">Select a Student</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">Assign Modules</button>
            </form>

            <!-- Display success or error messages -->
            <?php if ($success_message): ?>
                <div id="successModal" class="modal">
                    <div class="modal-content">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <h3><?php echo $success_message; ?></h3>
                    </div>
                </div>
                <script>
                    document.getElementById("successModal").style.display = "block";

                    function closeModal() {
                        document.getElementById("successModal").style.display = "none";
                    }
                </script>
            <?php elseif ($error_message): ?>
                <p style="color: red;"><?php echo $error_message; ?></p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Initialize Select2 for the student dropdown to enable search functionality
        $(document).ready(function() {
            $('#student').select2();
        });

        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "logout.php";
            }
        }

    </script>

</body>
</html>
