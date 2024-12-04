<?php
session_start();
include('config.php');

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Get the student ID from URL parameter
$student_id = $_GET['student_id'] ?? null;
if (!$student_id) {
    header("Location: admin_student-list.php");
    exit;
}

// Fetch assigned modules for this student
$query = "SELECT * FROM module_assignments WHERE student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $student_id);
$stmt->execute();
$result = $stmt->get_result();
$assigned_modules = $result->fetch_all(MYSQLI_ASSOC);

// Handle add module
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_module'])) {
    $module_name = $_POST['module_name'];
    
    if (!empty($module_name)) {
        // Insert new module assignment
        $insert_query = "INSERT INTO module_assignments (student_id, module_name) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param('is', $student_id, $module_name);
        if ($insert_stmt->execute()) {
            $_SESSION['success_message'] = "Module successfully assigned.";
            header("Location: assign_student_modules.php?student_id=$student_id");
            exit;
        } else {
            $_SESSION['error_message'] = "Failed to assign module.";
        }
    }
}

// Handle delete module
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM module_assignments WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param('i', $delete_id);
    if ($delete_stmt->execute()) {
        $_SESSION['success_message'] = "Module successfully removed.";
    } else {
        $_SESSION['error_message'] = "Failed to remove module.";
    }
    header("Location: assign_student_modules.php?student_id=$student_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_admin-dashboard.css">
    <title>Manage Assigned Modules</title>
    <style>
        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Dark background */
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            width: 40%;
            margin: auto;
            text-align: center;
        }

        .modal-content input {
            padding: 10px;
            width: 80%;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .modal-content button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .modal-content button:hover {
            background-color: #45a049;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 25px;
            color: #aaa;
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
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
        <div class="main-content">
            <h2>Assigned Modules for Student</h2>
            <h3>Student ID: <?php echo htmlspecialchars($student_id); ?> </h3>

            <!-- Success or Error Messages -->
            <?php if ($_SESSION['success_message']): ?>
                <p style="color: green;"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
            <?php elseif ($_SESSION['error_message']): ?>
                <p style="color: red;"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
            <?php endif; ?>

            <!-- List of Assigned Modules -->
            <h4>Assigned Modules</h4>
            <ul>
                <?php foreach ($assigned_modules as $module): ?>
                    <li>
                        <?php echo htmlspecialchars($module['module_name']); ?>
                        <a href="assign_student_modules.php?student_id=<?php echo $student_id; ?>&delete_id=<?php echo $module['id']; ?>" onclick="return confirm('Are you sure you want to delete this module?');">Delete</a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Button to open the modal -->
            <button id="addModuleBtn">Add New Module</button>
        </div>
    </div>

    <!-- Modal for adding module -->
    <div id="addModuleModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Add a New Module</h3>
            <form action="assign_student_modules.php?student_id=<?php echo $student_id; ?>" method="POST">
                <input type="text" name="module_name" placeholder="Enter module name" required>
                <button type="submit" name="add_module">Assign Module</button>
            </form>
        </div>
    </div>

    <script>
        // Get the modal and button elements
        var modal = document.getElementById("addModuleModal");
        var btn = document.getElementById("addModuleBtn");

        // Open the modal when the button is clicked
        btn.onclick = function() {
            modal.style.display = "block";
        }

        // Close the modal when the close button is clicked
        function closeModal() {
            modal.style.display = "none";
        }

        // Close the modal if the user clicks outside of the modal content
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
