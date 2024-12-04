<?php
session_start();

// Ensure the user is an admin
if ($_SESSION['user_type'] !== 'Admin') {
    header('Location: index.php'); // Redirect to home or another page if not admin
    exit();
}

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=als_test', 'username', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Insert a new module if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $moduleName = $_POST['module_name'];
    $stmt = $pdo->prepare("INSERT INTO modules (module_name) VALUES (:module_name)");
    $stmt->execute([':module_name' => $moduleName]);
    echo "<p>Module '$moduleName' has been successfully added!</p>";
}

// Fetch all modules from the database
$stmt = $pdo->query("SELECT * FROM modules ORDER BY id");
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Module</title>
</head>
<body>
    <h1>Add Module</h1>
    <form method="POST" action="add_module.php">
        <label for="module_name">Module Name:</label>
        <input type="text" name="module_name" id="module_name" required>
        <button type="submit">Add Module</button>
    </form>

    <h2>Existing Modules</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Module Name</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($modules as $module): ?>
                <tr>
                    <td><?php echo htmlspecialchars($module['id']); ?></td>
                    <td><?php echo htmlspecialchars($module['module_name']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
