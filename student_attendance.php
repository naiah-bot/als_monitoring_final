<?php
session_start();

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Set the correct timezone
date_default_timezone_set('Asia/Manila'); // Replace with your timezone

// Include database connection
include 'config.php';

// Retrieve session details
$surname = $_SESSION['lastName'] ?? 'lastName';
$firstName = $_SESSION['firstName'] ?? 'firstName';
$userType = $_SESSION['user_type'] ?? 'Admin';
$studentId = $_SESSION['student_id'] ?? null;

// Initialize attendanceRecords
$attendanceRecords = [];

// Fetch attendance records for the logged-in user
if ($studentId) {
    $sql = "SELECT date, time_in, time_out, 
               CASE 
                   WHEN time_out IS NULL THEN 'N/A' 
                   ELSE TIMEDIFF(time_out, time_in) 
               END AS duration, 
               remarks 
            FROM attendance 
            WHERE student_id = ? 
            ORDER BY date DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $attendanceRecords = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Execute failed: " . htmlspecialchars($stmt->error));
    }
    $stmt->close();
}

// Fetch the logged-in user's full name for manual attendance form
$userInfoSql = "SELECT CONCAT(last_name, ', ', first_name) AS full_name 
                FROM users 
                WHERE student_id = ?";
$stmtUser = $conn->prepare($userInfoSql);
if ($stmtUser === false) {
    die("Prepare failed: " . htmlspecialchars($conn->error));
}
$stmtUser->bind_param("s", $studentId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userInfo = $resultUser->fetch_assoc(); // This will contain the logged-in student's full name
$stmtUser->close();

// Determine today's attendance status
$todayDate = date('Y-m-d');
$timeInExists = false;
$timeOutExists = false;
$todayRecord = null;

foreach ($attendanceRecords as $record) {
    if ($record['date'] == $todayDate) {
        $todayRecord = $record;
        $timeInExists = !empty($record['time_in']);
        $timeOutExists = !empty($record['time_out']);
        break;
    }
}


// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'time_in') {
        if (!$timeInExists) {
            $time_in = $_POST['time_in'];
            // Validate time_in format
            if (DateTime::createFromFormat('Y-m-d H:i:s', $time_in) === false) {
                $_SESSION['error_message'] = "Invalid Time In format.";
            } else {
                $sql = "INSERT INTO attendance (student_id, date, time_in, remarks) 
                        VALUES (?, CURDATE(), ?, 'Present')";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    $_SESSION['error_message'] = "Prepare failed: " . htmlspecialchars($conn->error);
                } else {
                    $stmt->bind_param("ss", $studentId, $time_in);
                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = "Time In Recorded Successfully!";
                        // Update the variables since we've just recorded Time In
                        $timeInExists = true;
                        $timeOutExists = false;
                        $todayRecord = [
                            'date' => $todayDate,
                            'time_in' => $time_in,
                            'time_out' => 'N/A',
                            'duration' => 'N/A',
                            'remarks' => 'Present'
                        ];
                    } else {
                        $_SESSION['error_message'] = "Failed to record Time In: " . htmlspecialchars($stmt->error);
                    }
                    $stmt->close();
                }
            }
        } else {
            $_SESSION['error_message'] = "Time In already recorded for today.";
        }
    } elseif ($action === 'time_out') {
        if ($timeInExists && !$timeOutExists) {
            $time_out = $_POST['time_out'];
            // Validate time_out format
            if (DateTime::createFromFormat('Y-m-d H:i:s', $time_out) === false) {
                $_SESSION['error_message'] = "Invalid Time Out format.";
            } else {
                $sql = "UPDATE attendance 
                        SET time_out = ?, 
                            duration = TIMEDIFF(time_out, time_in) 
                        WHERE student_id = ? AND date = CURDATE() AND time_out IS NULL";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    $_SESSION['error_message'] = "Prepare failed: " . htmlspecialchars($conn->error);
                } else {
                    $stmt->bind_param("ss", $time_out, $studentId);
                    if ($stmt->execute()) {
                        if ($stmt->affected_rows > 0) {
                            $_SESSION['success_message'] = "Time Out Recorded Successfully!";
                            // Update the variables since we've just recorded Time Out
                            $timeOutExists = true;
                            $todayRecord['time_out'] = $time_out;
                            $todayRecord['duration'] = date("H:i:s", strtotime($time_out) - strtotime($todayRecord['time_in']));
                        } else {
                            $_SESSION['error_message'] = "Failed to record Time Out. No matching Time In found.";
                        }
                    } else {
                        $_SESSION['error_message'] = "Failed to record Time Out: " . htmlspecialchars($stmt->error);
                    }
                    $stmt->close();
                }
            }
        } else {
            $_SESSION['error_message'] = "Cannot record Time Out. Either Time In is not recorded or Time Out is already recorded.";
        }
    } elseif ($action === 'mark_absent') {
        if (!$timeInExists && !$timeOutExists) {
            $sql = "INSERT INTO attendance (student_id, date, remarks) 
                    VALUES (?, CURDATE(), 'Absent')";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                $_SESSION['error_message'] = "Prepare failed: " . htmlspecialchars($conn->error);
            } else {
                $stmt->bind_param("s", $studentId);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Marked as Absent Successfully!";
                } else {
                    $_SESSION['error_message'] = "Failed to mark as Absent: " . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            }
        } else {
            $_SESSION['error_message'] = "Cannot mark as Absent. Attendance already recorded.";
        }
    }

    // Redirect to refresh the page after submission
    header("Location: student_attendance.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
    <link rel="stylesheet" href="style_student-dashboard.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode/minified/html5-qrcode.min.js"></script>
    <style>
        /* Styling for tables and buttons */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 1em;
            background-color: #fff;
            border: 1px solid black;
        }

        table th, table td {
            padding: 10px;
            text-align: center;
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

        button {
            padding: 10px 15px;
            font-size: 1em;
            color: white;
            background-color: #211c41;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px;
        }

        button:hover {
            background-color: #003580;
        }

        /* Overlay styling for dimmed background */
        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100;
            display: none;
        }

        /* Manual attendance form styling */
        #manual-attendance-form {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 101;
            display: none;
        }

        /* Close button styling */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.5em;
            cursor: pointer;
        }

        /* QR Scanner container styling */
        #qr-scanner-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 101;
            display: none;
        }

        #qr-reader {
            width: 100%;
            height: 300px;
            margin: auto;
        }

        #qr-scanner-container button {
            margin-top: 15px;
            background-color: #d9534f;
        }

        #qr-scanner-container button:hover {
            background-color: #c9302c;
        }

        /* Success prompt container */
        #success-prompt {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 102;
            display: none;
        }

        /* Error prompt container */
        #error-prompt {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f44336;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 103;
            display: none;
        }

        /* Flex container for buttons */
        .attendance-buttons {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }

        .attendance-buttons form {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .attendance-buttons button {
            width: 100%;
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
                        <h2><?php echo htmlspecialchars("$surname, $firstName"); ?></h2>
                        <p><?php echo ucfirst(htmlspecialchars($userType)); ?></p>
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
            <h2>Attendance Records</h2>
            <?php
            // Display success or error messages
            if (isset($_SESSION['success_message'])) {
                echo '<div id="success-prompt">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                unset($_SESSION['success_message']);
            }

            if (isset($_SESSION['error_message'])) {
                echo '<div id="error-prompt">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                unset($_SESSION['error_message']);
            }
            ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Duration</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($attendanceRecords) > 0): ?>
                        <?php foreach ($attendanceRecords as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['date']); ?></td>
                                <td><?php echo htmlspecialchars($record['time_in']); ?></td>
                                <td><?php echo htmlspecialchars($record['time_out'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($record['duration'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($record['remarks']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No attendance records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <button onclick="toggleManualAttendance()">Manual Attendance</button>
            <button onclick="toggleQRScanner()" style="background-color: #28a745;">Scan QR Code</button>

            <div id="overlay"></div>

                        <!-- Manual Attendance Form -->
                        <div id="manual-attendance-form">
                <span class="close-btn" onclick="toggleManualAttendance()">&times;</span>
                <h2>Manual Attendance</h2>

                <div class="attendance-buttons">
                    <!-- Time In Form -->
                    <?php if (!$timeInExists): ?>
                        <form method="POST" action="student_attendance.php" id="time-in-form">
                            <input type="hidden" name="action" value="time_in">
                            <input type="hidden" id="time-in" name="time_in" value="">
                            <button type="button" onclick="recordTimeIn()">Record Time In</button>
                        </form>
                    <?php else: ?>
                        <form id="time-in-recorded-form">
                            <p>Time In Recorded: <?php echo htmlspecialchars($todayRecord['time_in']); ?></p>
                        </form>
                    <?php endif; ?>

                    <!-- Time Out Form -->
                    <?php if ($timeInExists && !$timeOutExists): ?>
                        <form method="POST" action="student_attendance.php" id="time-out-form">
                            <input type="hidden" name="action" value="time_out">
                            <input type="hidden" id="time-out" name="time_out" value="">
                            <button type="button" onclick="recordTimeOut()">Record Time Out</button>
                        </form>
                    <?php elseif ($timeInExists && $timeOutExists): ?>
                        <form id="time-out-recorded-form">
                            <p>Time Out Recorded: <?php echo htmlspecialchars($todayRecord['time_out']); ?></p>
                        </form>
                    <?php endif; ?>

                    <!-- Mark Absent Form -->
                    <?php if (!$timeInExists && !$timeOutExists): ?>
                        <form method="POST" action="student_attendance.php" id="absent-form">
                            <input type="hidden" name="action" value="mark_absent">
                            <button type="submit">Mark as Absent</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>


    <!-- QR Scanner Container -->
    <div id="qr-scanner-container">
        <div id="qr-reader"></div>
        <button onclick="stopQRScanner()">Stop Scanner</button>
    </div>

    <!-- Success Prompt Container -->
    <div id="success-prompt"></div>
    <!-- Error Prompt Container -->
    <div id="error-prompt"></div>

    <script>
        let qrScanner;

        // Function to toggle the visibility of QR Scanner
        function toggleQRScanner() {
            const qrContainer = document.getElementById('qr-scanner-container');

            // Toggle visibility of the QR scanner container
            if (qrContainer.style.display === 'none' || qrContainer.style.display === '') {
                qrContainer.style.display = 'block'; // Show the scanner
                startQRScanner(); // Start the QR scanner when it's visible
            } else {
                qrContainer.style.display = 'none'; // Hide the scanner
                stopQRScanner(); // Stop the scanner when it's hidden
            }
        }

        // Initialize and start QR Scanner
        function startQRScanner() {
            if (!qrScanner) {
                qrScanner = new Html5Qrcode("qr-reader"); // Initialize QR code scanner

                qrScanner.start(
                    { facingMode: "environment" },
                    {
                        fps: 10,  // Frames per second
                        qrbox: { width: 250, height: 250 } // Size of the scanning box
                    },
                    (decodedText) => {
                        alert("QR Code Scanned: " + decodedText);
                        // Optionally, you can process the scanned data here (e.g., submit it to the server)
                        // For example, auto-fill fields or perform actions based on the QR code
                    },
                    (errorMessage) => {
                        console.error("QR Scanner Error: ", errorMessage);
                    }
                ).catch(err => {
                    console.error("Error initializing QR scanner: ", err);
                });
            }
        }

        // Function to stop the QR Scanner
        function stopQRScanner() {
            if (qrScanner) {
                qrScanner.stop().then(() => {
                    qrScanner = null; // Reset the QR scanner
                    document.getElementById('qr-scanner-container').style.display = 'none'; // Hide the scanner
                }).catch(err => console.error("Error stopping QR scanner: ", err));
            }
        }

            // Function to toggle the Manual Attendance form visibility
            function toggleManualAttendance() {
                const form = document.getElementById('manual-attendance-form');
                const overlay = document.getElementById('overlay');
                const isVisible = form.style.display === 'block';

                form.style.display = isVisible ? 'none' : 'block';
                overlay.style.display = isVisible ? 'none' : 'block';
            }

            // Function to record Time In
            function recordTimeIn() {
                if (confirm("Are you sure you want to record Time In?")) {
                    const timeIn = new Date().toISOString().slice(0, 19).replace("T", " ");
                    document.getElementById("time-in").value = timeIn;

                    // Automatically submit the Time In form
                    document.getElementById("time-in-form").submit();
                }
            }

            // Function to record Time Out
            function recordTimeOut() {
                if (confirm("Are you sure you want to record Time Out?")) {
                    const timeOut = new Date().toISOString().slice(0, 19).replace("T", " ");
                    document.getElementById("time-out").value = timeOut;

                    // Automatically submit the Time Out form
                    document.getElementById("time-out-form").submit();
                }
            }


        // Function to confirm logout
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "logout.php";
            }
        }

        // Display success or error messages
        window.onload = function() {
            const successPrompt = document.getElementById('success-prompt');
            const errorPrompt = document.getElementById('error-prompt');

            <?php if (isset($_SESSION['success_message'])): ?>
                successPrompt.textContent = "<?php echo htmlspecialchars($_SESSION['success_message']); ?>";
                successPrompt.style.background = '#4CAF50';
                successPrompt.style.display = 'block';
                setTimeout(() => {
                    successPrompt.style.display = 'none';
                }, 3000);
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                errorPrompt.textContent = "<?php echo htmlspecialchars($_SESSION['error_message']); ?>";
                errorPrompt.style.background = '#f44336';
                errorPrompt.style.display = 'block';
                setTimeout(() => {
                    errorPrompt.style.display = 'none';
                }, 3000);
            <?php endif; ?>
        }
    </script>
</body>
</html>
