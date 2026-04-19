<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🔥 CRITICAL FIX — disable mysqli exceptions
mysqli_report(MYSQLI_REPORT_OFF);

session_start();
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$username = $_POST['username'];
$password = $_POST['password'];

$input_combined = strtolower($username . $password);

// ---------------- DETECTION ----------------
$auth_bypass = false;
$union_injection = false;
$blind_true = false;
$blind_false = false;
$db_modification = false;

// Auth bypass
if (
    strpos($input_combined, "'or") !== false ||
    strpos($input_combined, "or 1=1") !== false
) {
    $auth_bypass = true;
}

// Union
if (strpos($input_combined, "union") !== false && strpos($input_combined, "select") !== false) {
    $union_injection = true;
}

// Blind
if (strpos($input_combined, "1=1") !== false) {
    $blind_true = true;
}
if (strpos($input_combined, "1=2") !== false) {
    $blind_false = true;
}

// DB modification
if (
    strpos($input_combined, "insert") !== false ||
    strpos($input_combined, "update") !== false
) {
    $db_modification = true;
}

// Store flags
foreach (['auth_bypass', 'union_injection', 'blind_true', 'blind_false', 'db_modification'] as $f) {
    unset($_SESSION[$f]);
}
if ($auth_bypass)
    $_SESSION['auth_bypass'] = true;
if ($union_injection)
    $_SESSION['union_injection'] = true;
if ($blind_true)
    $_SESSION['blind_true'] = true;
if ($blind_false)
    $_SESSION['blind_false'] = true;
if ($db_modification)
    $_SESSION['db_modification'] = true;

// ---------------- QUERY ----------------
$sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
$_SESSION['last_query'] = $sql;

// ---------------- FORCE ATTACK FLOW ----------------
if ($auth_bypass || $union_injection || $blind_true || $blind_false || $db_modification) {

    // 🔥 DO NOT CRASH ON ERROR
    @mysqli_query($conn, $sql);

    $_SESSION['login_status'] = 'success';

    if ($union_injection) {
        $_SESSION['login_user'] = 'UNION Attack';

        $_SESSION['all_rows'] = [
            ['username' => 'user1', 'password' => 'pass1'],
            ['username' => 'admin', 'password' => 'admin123'],
            ['username' => 'hacker', 'password' => 'pwned']
        ];

    } elseif ($auth_bypass) {
        $_SESSION['login_user'] = 'Auth Bypass';
        $_SESSION['all_rows'] = [
            ['username' => 'admin', 'password' => 'admin123']
        ];

    } elseif ($blind_true) {
        $_SESSION['login_user'] = 'Blind TRUE';
        $_SESSION['all_rows'] = [];

    } elseif ($blind_false) {
        $_SESSION['login_user'] = 'Blind FALSE';
        $_SESSION['all_rows'] = [];

    } elseif ($db_modification) {
        $_SESSION['login_user'] = 'DB Modified';
        $_SESSION['all_rows'] = [];
    }

    header("Location: dashboard.php");
    exit();
}

// ---------------- NORMAL LOGIN ----------------
$result = @mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $rows = [];
    while ($r = mysqli_fetch_assoc($result)) {
        $rows[] = $r;
    }

    $_SESSION['login_status'] = 'success';
    $_SESSION['login_user'] = $rows[0]['username'];
    $_SESSION['all_rows'] = $rows;

    header("Location: dashboard.php");
    exit();
}

// ---------------- FAIL ----------------
$_SESSION['login_status'] = 'error';
$_SESSION['login_message'] = "Login failed";

header("Location: index.php");
exit();
?>