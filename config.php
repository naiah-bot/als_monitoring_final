<?php
$servername = "localhost";
$username = "root";       // MySQL username
$password = "";           // MySQL password (usually empty by default for localhost)
$dbname = "als_test";     // Update database name as needed
$port = 3308;             // Your database port (default is usually 3306)

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully!";
?>