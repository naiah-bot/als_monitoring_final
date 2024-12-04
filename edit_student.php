<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
include 'config.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: admin_student-list.php");
    exit;
}

$studentId = $_GET['id'];

// Fetch student data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Handle form submission for updating student
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $address = $_POST['address'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $studentId = $_POST['student_id'];
    $highestEducation = $_POST['highest_educational_attainment'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Update the student details
    $updateSql = "UPDATE users SET first_name = ?, last_name = ?, gender = ?, birthdate = ?, address = ?, username = ?, password = ?, student_id = ?, highest_educational_attainment = ?, email = ?, phone = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sssssssssssi", $firstName, $lastName, $gender, $birthdate, $address, $username, $password, $studentId, $highestEducation, $email, $phone, $student['id']);
    
    if ($updateStmt->execute()) {
        header("Location: admin_student-list.php");
        exit;
    } else {
        echo "Error updating student details.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style_signup.css">
    <title>Edit Student</title>
    <style>
    /* Style the form container */
    #modal {
        background-color:#3a326f;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 400px;
        top: 5px; /* Adjust the top value for signup */
        height: auto;
        background-color: rgba(58, 50, 111, 0.95); /* Darker background for signup */
        border-radius: 15px; /* Rounded edges */
        box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.3);
        z-index: 10; /* Ensure the rectangle is in front */
        position: relative; /* Make sure it's positioned properly within the page */
        padding: 30px; /* Inner spacing */
        margin: auto; /* Centering */
        text-align: left;
    }

    /* Style for all form input fields */
    #modal input[type="text"],
    #modal input[type="password"],
    #modal input[type="date"],
    #modal input[type="email"],
    #modal select {
        width: 100%; /* Full width */
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 16px;
    }

    /* Specific style for email input to make it more spacious */
    #modal input[type="email"] {
        width: 100%; /* Ensure email input is also full width */
    }

    /* Style for form buttons */
    .form-actions button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        font-size: 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    /* Style for cancel link */
    .cancel-btn {
        text-decoration: none;
        color: #f44336;
        font-size: 16px;
        padding: 10px 20px;
        border-radius: 5px;
        background-color: transparent;
        border: 1px solid #f44336;
        margin-left: 10px;
    }

    .cancel-btn:hover {
        background-color: #f44336;
        color: white;
    }
</style>

</head>
<body>
    <div id="modal">
        <h1>Edit Student</h1>
        <div class="container">
            <form method="POST" action="edit_student.php?id=<?php echo $student['id']; ?>">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo $student['first_name']; ?>">

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo $student['last_name']; ?>">

                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="Male" <?php echo $student['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo $student['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo $student['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                </select>

                <label for="birthdate">Birthdate:</label>
                <input type="date" id="birthdate" name="birthdate" value="<?php echo $student['birthdate']; ?>">

                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo $student['address']; ?>">

                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo $student['username']; ?>">

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" value="<?php echo $student['password']; ?>">

                <label for="student_id">Student ID:</label>
                <input type="text" id="student_id" name="student_id" value="<?php echo $student['student_id']; ?>">

                <label for="highestEducationalAttainment">Highest Educational Attainment:</label>
                <select id="highestEducationalAttainment" name="highestEducationalAttainment">
                    <option value="">Select Highest Educational Attainment</option>
                    <option value="Kinder" <?php echo ($student['highest_educational_attainment'] == 'Kinder') ? 'selected' : ''; ?>>Kinder</option>
                    <option value="Grade 1" <?php echo ($student['highest_educational_attainment'] == 'Grade 1') ? 'selected' : ''; ?>>Grade 1</option>
                    <option value="Grade 2" <?php echo ($student['highest_educational_attainment'] == 'Grade 2') ? 'selected' : ''; ?>>Grade 2</option>
                    <option value="Grade 3" <?php echo ($student['highest_educational_attainment'] == 'Grade 3') ? 'selected' : ''; ?>>Grade 3</option>
                    <option value="Grade 4" <?php echo ($student['highest_educational_attainment'] == 'Grade 4') ? 'selected' : ''; ?>>Grade 4</option>
                    <option value="Grade 5" <?php echo ($student['highest_educational_attainment'] == 'Grade 5') ? 'selected' : ''; ?>>Grade 5</option>
                    <option value="Grade 6" <?php echo ($student['highest_educational_attainment'] == 'Grade 6') ? 'selected' : ''; ?>>Grade 6</option>
                    <option value="Grade 7" <?php echo ($student['highest_educational_attainment'] == 'Grade 7') ? 'selected' : ''; ?>>Grade 7</option>
                    <option value="Grade 8" <?php echo ($student['highest_educational_attainment'] == 'Grade 8') ? 'selected' : ''; ?>>Grade 8</option>
                    <option value="Grade 9" <?php echo ($student['highest_educational_attainment'] == 'Grade 9') ? 'selected' : ''; ?>>Grade 9</option>
                    <option value="Grade 10" <?php echo ($student['highest_educational_attainment'] == 'Grade 10') ? 'selected' : ''; ?>>Grade 10</option>
                    <option value="Grade 11" <?php echo ($student['highest_educational_attainment'] == 'Grade 11') ? 'selected' : ''; ?>>Grade 11</option>
                    <option value="Grade 12" <?php echo ($student['highest_educational_attainment'] == 'Grade 12') ? 'selected' : ''; ?>>Grade 12</option>
                </select>


                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo $student['email']; ?>">

                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" value="<?php echo $student['phone']; ?>">

                <div class="form-actions">
                    <button type="submit">Save Changes</button>
                    <a href="admin_student-list.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
