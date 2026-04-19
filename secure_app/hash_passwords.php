<?php
// Secure App – Password Hashing Helper (run ONCE, then delete this file)
// Lab 5 | Group 6
//
// PURPOSE:
//   The lab5 database stores passwords as plaintext (pass1, admin123).
//   This script hashes them using bcrypt so the secure_app can use
//   password_verify() for authentication.
//
// HOW TO USE:
//   1. Open browser: http://localhost/secure_app/hash_passwords.php
//   2. Verify output shows "Updated successfully"
//   3. DELETE this file immediately after use (security risk if left)
//
// WARNING: Delete this file after running!

require_once 'connection.php';

$users = [
    'user1' => 'pass1',
    'admin' => 'admin123',
];

$updated = 0;
$errors = [];

foreach ($users as $username => $plaintext) {
    $hash = password_hash($plaintext, PASSWORD_DEFAULT);   // bcrypt
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("ss", $hash, $username);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $updated++;
            echo "✔ Updated password for <b>$username</b><br>";
            echo "&nbsp;&nbsp;Hash: <code>" . htmlspecialchars($hash) . "</code><br><br>";
        } else {
            $errors[] = "Could not update $username (user may not exist)";
        }
        $stmt->close();
    }
}

echo "<hr>";
echo "<b>Done.</b> $updated password(s) hashed.<br>";
if ($errors) {
    foreach ($errors as $e)
        echo "⚠ $e<br>";
}
echo "<br><b style='color:red'>⚠ DELETE this file now! (hash_passwords.php)</b>";

$conn->close();
?>