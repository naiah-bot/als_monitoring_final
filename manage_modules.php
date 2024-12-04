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

// Handle Add, Edit, and Delete operations
if (isset($_POST['add_module'])) {
    $newModule = $_POST['new_module'];
    if (!empty($newModule)) {
        $query = "INSERT INTO modules (module_name) VALUES ('$newModule')";
        mysqli_query($conn, $query);
    }
}

if (isset($_POST['edit_module'])) {
    $moduleId = $_POST['module_id'];
    $newModuleName = $_POST['new_module_name'];
    if (!empty($newModuleName)) {
        $query = "UPDATE modules SET module_name = '$newModuleName' WHERE id = $moduleId";
        mysqli_query($conn, $query);
    }
}

if (isset($_POST['delete_module'])) {
    $moduleId = $_POST['module_id'];
    $query = "DELETE FROM modules WHERE id = $moduleId";
    mysqli_query($conn, $query);
}

// Get all modules from the database
$modules = [];
$query = "SELECT * FROM modules";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $modules[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_admin-dashboard.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Manage Modules</title>
    <style>
        /* Main container for the content */
        .container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }

        /* Style the content area */
        .main-content {
            background-color: #3a326f;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 800px;
            overflow: hidden;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #ffffff;
        }

        /* Search input box */
        #searchInput {
            width: 90%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        /* Filter input box */
        #filterInput {
            width: 90%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        /* Scrollable list container */
        #modulesListContainer {
            max-height: 400px;
            overflow-y: auto;
            margin-top: 20px;
        }

        /* Style for the list items */
        #modulesList {
            list-style: none;
            padding: 0;
            width: 95%;
        }

        #modulesList li {
            padding: 8px;
            margin: 3px 0;
            border-bottom: 1px solid #ddd;
            background-color: #f9f9f9;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #modulesList li:hover {
            background-color: #f1f1f1;
        }

        .module-actions {
            display: flex;
            gap: 10px;
        }

        .edit-btn, .delete-btn {
            padding: 2px 2px;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-size: 12px;
        }

        .edit-btn {
            background-color: #007bff;
        }

        .delete-btn {
            background-color: #dc3545;
        }

        /* Add Module Form */
        #addModuleForm {
            margin-top: 20px;
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
                ]<li>
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
            <h2>Manage Modules</h2>
            
            <!-- Search and Filter -->
            <input type="text" id="searchInput" onkeyup="searchModules()" placeholder="Search for modules...">
            <input type="text" id="filterInput" onkeyup="filterModules()" placeholder="Filter by LS1, LS2, LS3...">

            <!-- Add New Module Form -->
            <div id="addModuleForm">
                <form method="POST">
                    <input type="text" name="new_module" id="newModule" placeholder="Add new module" required>
                    <button type="submit" name="add_module">Add Module</button>
                </form>
            </div>

            <div id="modulesListContainer">
                <ul id="modulesList">
                    <?php foreach ($modules as $module): ?>
                        <li>
                            <span><?php echo htmlspecialchars($module['module_name']); ?></span>
                            <div class="module-actions">
                                <!-- Edit Module Form -->
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="module_id" value="<?php echo $module['id']; ?>">
                                    <input type="text" name="new_module_name" placeholder="Edit module" required>
                                    <button type="submit" name="edit_module" class="edit-btn">Edit</button>
                                </form>
                                
                                <!-- Delete Module -->
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="module_id" value="<?php echo $module['id']; ?>">
                                    <button type="submit" name="delete_module" class="delete-btn">Delete</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Function to search through the modules
        function searchModules() {
            var input, filter, ul, li, i, txtValue;
            input = document.getElementById('searchInput');
            filter = input.value.toLowerCase();
            ul = document.getElementById("modulesList");
            li = ul.getElementsByTagName('li');

            for (i = 0; i < li.length; i++) {
                txtValue = li[i].textContent || li[i].innerText;
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    li[i].style.display = "";
                } else {
                    li[i].style.display = "none";
                }
            }
        }

        // Function to filter modules by prefix (LS1, LS2, etc.)
        function filterModules() {
            var filterInput, filter, ul, li, i, txtValue;
            filterInput = document.getElementById('filterInput');
            filter = filterInput.value.toUpperCase();
            ul = document.getElementById("modulesList");
            li = ul.getElementsByTagName('li');

            for (i = 0; i < li.length; i++) {
                txtValue = li[i].textContent || li[i].innerText;
                if (txtValue.toUpperCase().startsWith(filter)) {
                    li[i].style.display = "";
                } else {
                    li[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>
