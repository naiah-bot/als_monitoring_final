<?php
session_start();

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include 'config.php'; // Include database connection

// Retrieve session details
$surname = $_SESSION['lastName'] ?? 'lastName';  // Default to 'lastName' if not set
$firstName = $_SESSION['firstName'] ?? 'firstName';  // Default to 'firstName' if not set
$userType = $_SESSION['user_type'] ?? 'Admin';  // Default to 'Admin' if not set

// SQL Query to get attendance summary
$sql = "SELECT 
            CONCAT(u.last_name, ', ', u.first_name) AS full_name,
            u.student_id,
            SUM(CASE WHEN a.remarks = 'Present' THEN 1 ELSE 0 END) AS total_present,
            SUM(CASE WHEN a.remarks = 'Absent' THEN 1 ELSE 0 END) AS total_absent
        FROM 
            users u
        LEFT JOIN 
            attendance a 
        ON 
            u.student_id = a.student_id
        WHERE 
            u.user_type != 'Admin'  /* Exclude Admins */
        GROUP BY 
            u.student_id
        ORDER BY 
            u.last_name, u.first_name";
            
$result = $conn->query($sql);

// Prepare data for the table
$attendanceRecords = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $attendanceRecords[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_admin-dashboard.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Admin Attendance Summary</title>
    <style>
        table {
            width: 70%;
            margin-left: auto;
            margin-right: 10%;
            border-collapse: collapse;
            font-size: 1em;
            background-color: #fff;
            border: 1px solid black;
            text-align: center;
        }

        table th, table td {
            padding: 15px;
            border: 1px solid black;
        }

        table thead {
            background-color: #211c41;
            color: white;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .main-content {
            padding: 20px;
            margin: 0 auto;
            text-align: center;
        }

        .main-content h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #ffffff;
        }

        /* Modal for Attendance Details */
        .absence-modal {
            display: none;
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            max-width: 300px;
            width: 100%;
            text-align: center;
        }

        /* Close button */
        .close-btn {
            position: absolute;
            top: 5px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
        }

        .absence-modal-content {
            padding: 20px;
        }

        button.view-btn {
            background-color: #3a326f;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button.view-btn:hover {
            background-color: #2a1f2a;
        }

        .print-btn {
            background-color: #3a326f;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }

        .print-btn:hover {
            background-color: #2a1f2a;
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
            <?php if (count($attendanceRecords) > 0): ?>
                <table id="attendanceTable">
                    <thead>
                        <h2>Admin Attendance Summary</h2>
                        <tr>
                            <th>Student Name</th>
                            <th>Total Present</th>
                            <th>Total Absent</th>
                            <th></th> <!-- Action column for View button -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendanceRecords as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['full_name']); ?></td>
                                <td><?php echo (int)$record['total_present']; ?></td>
                                <td><?php echo (int)$record['total_absent']; ?></td>
                                <td>
                                    <!-- View Button -->
                                    <button class="view-btn" data-student-id="<?php echo $record['student_id']; ?>">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No attendance records found.</p>
            <?php endif; ?>

            <!-- Print Button -->
            <button onclick="printAttendance()" class="print-btn">Print Attendance</button>
        </div>
    </div>

    <!-- Floating modal for attendance details -->
    <div id="absenceModal" class="absence-modal">
        <div class="absence-modal-content">
            <span class="close-btn">&times;</span>
            <h3>Attendance Dates</h3>
            <div id="attendanceDates"></div> <!-- Display attendance dates here -->
        </div>
    </div>

    <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "logout.php";
            }
        }

        // View attendance details
        document.querySelectorAll('.view-btn').forEach(button => {
            button.addEventListener('click', function () {
                const studentId = this.getAttribute('data-student-id');
                fetchAttendanceDates(studentId);
            });
        });

        // Fetch attendance dates for the clicked student
        function fetchAttendanceDates(studentId) {
            fetch('get_attendance_dates.php?student_id=' + studentId)
                .then(response => response.json())
                .then(data => {
                    const attendanceDatesDiv = document.getElementById('attendanceDates');
                    attendanceDatesDiv.innerHTML = ''; // Clear previous data

                    if (data.length > 0) {
                        let presentDates = '';
                        let absentDates = '';
                        data.forEach(item => {
                            if (item.remarks === 'Present') {
                                presentDates += `<p>Present: ${item.date}</p>`;
                            } else if (item.remarks === 'Absent') {
                                absentDates += `<p>Absent: ${item.date}</p>`;
                            }
                        });

                        attendanceDatesDiv.innerHTML = `<h4>Present Dates</h4>${presentDates}<h4>Absent Dates</h4>${absentDates}`;
                    } else {
                        attendanceDatesDiv.textContent = 'No attendance records found.';
                    }

                    // Show the modal
                    document.getElementById('absenceModal').style.display = 'block';
                });
        }

        // Close modal when the close button is clicked
        document.querySelector('.close-btn').addEventListener('click', function () {
            document.getElementById('absenceModal').style.display = 'none';
        });

        // Print attendance
        function printAttendance() {
            const table = document.getElementById('attendanceTable');
            const printWindow = window.open('', '', 'height=600, width=800');
            
            printWindow.document.write('<html><head><title>Attendance Summary</title>');
            printWindow.document.write('<style>body {font-family: Arial, sans-serif;} table {width: 100%; border-collapse: collapse;} th, td {border: 1px solid black; padding: 8px; text-align: center;} th {background-color: #211c41; color: white;} tbody tr:nth-child(even) {background-color: #f9f9f9;} tbody tr:hover {background-color: #f1f1f1;}</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<h1>Attendance Summary</h1>');
            printWindow.document.write(table.outerHTML);
            printWindow.document.write('</body></html>');
            
            printWindow.document.close();
            printWindow.print();
        }
    </script>
</body>
</html>
