<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$surname = $_SESSION['lastName'] ?? 'lastName';
$firstName = $_SESSION['firstName'] ?? 'firstName';
$userType = $_SESSION['user_type'] ?? 'Admin';

// Include the database connection
include 'config.php';

// Get the search term from the query string (if any)
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Modify the SQL query to include a LIKE condition for searching
$sql = "SELECT * FROM users WHERE user_type = 'student' AND (first_name LIKE ? OR last_name LIKE ? OR student_id LIKE ?)";
$stmt = $conn->prepare($sql);

// Bind parameters to prevent SQL injection
$searchTermWithWildcards = "%" . $searchTerm . "%";
$stmt->bind_param("sss", $searchTermWithWildcards, $searchTermWithWildcards, $searchTermWithWildcards);

// Execute the query
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_admin-dashboard.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>List of Students</title>
    <style>
        .container {
            display: flex;
            justify-content: flex-end;
            padding: 20px;
        }

        .table-container {
            width: 70%;
            margin-right: 20px;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-table th, .student-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .student-table th {
            background-color: #211c41;
        }

        /* Floating Container */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
        }

        .floating-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1000;
            width: 300px;
        }

        .floating-container h3 {
            margin-top: 0;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 20px;
        }

        /* Styling for the search bar */
        .search-container {
            display: flex;
            align-items: center;
            justify-content: flex-end; /* Align search bar to the right */
        }

        .search-container input {
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 5px 0 0 5px;
            font-size: 16px;
            outline: none;
            background-color: white;
            transition: border-color 0.3s ease;
        }

        .search-container input:focus {
            border-color: #211c41;
        }

        .search-container button {
            border: 1px solid #211c41;
            background-color: #211c41;
            color: white;
            font-size: 16px;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-container button:hover {
            background-color: #333;
        }

        /* Action buttons inside floating container */
        .action-btns {
            display: flex;
            justify-content: space-between;
        }

        .action-btns button {
            padding: 5px 10px;
            margin: 2px;
            border: none;
            background-color: #211c41;
            color: white;
            cursor: pointer;
            font-size: 14px;
        }

        .action-btns button:hover {
            background-color: #333;
        }

        /* Print button below the table */
        .print-btn-container {
            text-align: center;
            margin-top: 20px;
        }

        .print-btn-container button {
            padding: 10px 20px;
            background-color: #211c41;
            color: white;
            border: none;
            cursor: pointer;
        }

        .print-btn-container button:hover {
            background-color: #333;
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

        <div class="table-container">
            <h2>List of Students</h2>

            <div class="search-container">
                <form method="GET" action="admin_student-list.php">
                    <input type="text" name="search" placeholder="Search by name or student ID" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" />
                    <button type="submit">Search</button>
                </form>
            </div>

            <?php if (isset($_GET['search']) && $_GET['search'] != '') { ?>
                <p>Showing results for: "<?php echo htmlspecialchars($_GET['search']); ?>"</p>
            <?php } ?>

            <table class="student-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Student ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['first_name'] . " " . $row['last_name']; ?></td>
                                <td><?php echo $row['student_id']; ?></td>
                                <td><a href="#" onclick="viewStudentDetails(<?php echo $row['id']; ?>)">View Details</a></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr><td colspan="3">No students found.</td></tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Print Button -->
            <div class="print-btn-container">
                <button onclick="window.open('print_student.php', '_blank')">Print Student List</button>
            </div>
        </div>

        <!-- Floating Container for Student Details -->
        <div class="overlay" id="overlay"></div>
        <div class="floating-container" id="floatingContainer">
            <span class="close-btn" onclick="closeFloatingContainer()">×</span>
            <h3>Student Information</h3>
            <div id="studentDetails"></div>

            <!-- Edit & Delete Buttons -->
            <div class="action-btns" id="actionButtons"></div>
        </div>
    </div>

    <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "logout.php";
            }
        }

        function viewStudentDetails(studentId) {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "get_student_details.php?id=" + studentId, true);
            xhr.onload = function() {
                if (xhr.status == 200) {
                    document.getElementById("studentDetails").innerHTML = xhr.responseText;
                    document.getElementById("actionButtons").innerHTML = `
                        <button onclick="editStudent(${studentId})">Edit</button>
                        <button onclick="confirmDelete(${studentId})">Delete</button>
                    `;
                    document.getElementById("overlay").style.display = "block";
                    document.getElementById("floatingContainer").style.display = "block";
                }
            };
            xhr.send();
        }

        function closeFloatingContainer() {
            document.getElementById("overlay").style.display = "none";
            document.getElementById("floatingContainer").style.display = "none";
        }

        function confirmDelete(studentId) {
            if (confirm("Are you sure you want to delete this student?")) {
                window.location.href = "delete_student.php?id=" + studentId;
            }
        }

        function editStudent(studentId) {
            window.location.href = "edit_student.php?id=" + studentId;
        }
    </script>
</body>
</html>
