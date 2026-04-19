<?php
// Vulnerable App - Database Connection
// WARNING: This connection file is intentionally basic and insecure for lab demonstration.

$host     = "localhost";
$dbuser   = "root";
$dbpass   = "";          // Default XAMPP MySQL password is empty
$dbname   = "lab5";

$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);

if (!$conn) {
    // Intentionally displays connection error (insecure practice)
    die("Connection failed: " . mysqli_connect_error());
}
?>
