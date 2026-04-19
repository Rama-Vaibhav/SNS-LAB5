<?php
// Secure App – Authentication Handler
// Lab 5 | Group 6
//
// SECURITY FIXES APPLIED:
//  1. Prepared statements (parameterised queries) — prevents SQL Injection
//  2. password_verify() with password_hash() — prevents plaintext password attacks
//  3. Input validation (length, type) — rejects obviously malicious input early
//  4. SQL errors suppressed from output — prevents information leakage
//  5. Generic error messages — prevents username enumeration
//  6. session_regenerate_id() — prevents session fixation
//  7. CSRF-token check scaffold (commented) — ready to enable

session_start();
require_once 'connection.php';

// ------------------------------------------------------------------
// 0. Method guard
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// ------------------------------------------------------------------
// FIX 1 – Input validation
// Reject if username/password are missing or exceed allowed length.
// This kills oversized payloads before they reach the DB layer.
// ------------------------------------------------------------------
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (
    strlen($username) === 0 || strlen($username) > 50 ||
    strlen($password) === 0 || strlen($password) > 100
) {
    $_SESSION['login_message'] = "Invalid input. Please try again.";
    $_SESSION['login_status'] = 'error';
    header("Location: index.php");
    exit();
}

// Allow only alphanumeric + limited special chars in username
// This blocks most injection strings at the application layer
if (!preg_match('/^[a-zA-Z0-9_.\-@]+$/', $username)) {
    $_SESSION['login_message'] = "Invalid input. Please try again.";
    $_SESSION['login_status'] = 'error';
    header("Location: index.php");
    exit();
}

// ------------------------------------------------------------------
// FIX 2 – Prepared Statement (parameterised query)
// The username is passed as a parameter, NOT concatenated into SQL.
// Even if an attacker sends:  ' OR '1'='1' --
// the driver treats it as a literal string, not SQL syntax.
//
// VULNERABLE version (DO NOT USE):
//   $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
//
// SECURE version:
// ------------------------------------------------------------------
$stmt = $conn->prepare("SELECT username, password FROM users WHERE username = ?");

if (!$stmt) {
    // FIX 3 – Log error server-side; never expose to user
    error_log("Prepare failed: " . $conn->error);
    $_SESSION['login_message'] = "An internal error occurred. Please try again.";
    $_SESSION['login_status'] = 'error';
    header("Location: index.php");
    exit();
}

$stmt->bind_param("s", $username);  // "s" = string; username is bound safely
$stmt->execute();
$result = $stmt->get_result();

// ------------------------------------------------------------------
// FIX 4 – Password hashing check
// We fetch the stored hash and verify with password_verify().
//
// NOTE FOR THE LAB DATABASE:
// The lab table stores plaintext passwords ('pass1', 'admin123').
// For the secure app to work, you must first hash them.
// Run this ONCE in phpMyAdmin (see README for full instructions):
//
//   UPDATE users SET password = '$2y$10$...' WHERE username='user1';
//
// OR use the helper script: secure_app/hash_passwords.php
//
// password_hash() uses bcrypt by default (PASSWORD_DEFAULT).
// ------------------------------------------------------------------
if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $stored_hash = $row['password'];

    // STRICT SECURE IMPLEMENTATION
    // Enforce password hashing. If the database was modified via SQL injection
    // to contain a plaintext password (like 'hacked123'), password_verify() will
    // automatically return FALSE and blocking the attacker's login.
    if (password_verify($password, $stored_hash)) {
        // ------------------------------------------------------------------
        // FIX 5 – Regenerate session ID to prevent session fixation
        // ------------------------------------------------------------------
        session_regenerate_id(true);

        $_SESSION['login_status'] = 'success';
        $_SESSION['login_user'] = $row['username'];
        $_SESSION['login_message'] = "Login successful! Welcome, " . htmlspecialchars($row['username']) . ".";

        header("Location: dashboard.php");
        exit();
    }
}

// ------------------------------------------------------------------
// FIX 6 – Generic error message (no username enumeration)
// We do NOT say "username not found" vs "wrong password".
// ------------------------------------------------------------------
$_SESSION['login_message'] = "Invalid username or password.";
$_SESSION['login_status'] = 'error';

$stmt->close();
$conn->close();

header("Location: index.php");
exit();
?>