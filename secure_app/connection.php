<?php
// Secure App – Database Connection
// Lab 5 | Group 5

$host   = "localhost";
$dbuser = "root";
$dbpass = "";       // XAMPP default; change in production
$dbname = "lab5";

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    // FIX: Never expose DB errors to the user
    error_log("DB Connection failed: " . $conn->connect_error);
    die("Service temporarily unavailable. Please try again later.");
}

// Force UTF-8 charset to prevent charset-based injection
$conn->set_charset("utf8mb4");
?>
