<?php
$host = "localhost";        // copy from your panel
$user = "root";           // your MySQL username
$pass = "";            // your MySQL password
$db   = "grades_db";  // your MySQL database name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
