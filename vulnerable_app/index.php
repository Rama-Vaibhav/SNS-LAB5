<?php
// Vulnerable App – Login Page
// Lab 5 | Group 5
// WARNING: Intentionally insecure for educational purposes only.
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vulnerable Login | Lab 5</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<div class="warning-banner">
  ⚠ VULNERABLE APPLICATION — FOR EDUCATIONAL / LAB USE ONLY — DO NOT DEPLOY IN PRODUCTION ⚠
</div>

<div class="container">
  <span class="badge">&#x26A0; Insecure App</span>
  <h1>User Login</h1>
  <p class="subtitle">// vulnerable_app &mdash; SQL Injection Demo</p>

  <div class="card">

    <?php if (isset($_SESSION['login_message'])): ?>
      <div class="alert <?= $_SESSION['login_status'] === 'success' ? 'alert-success' : 'alert-error' ?>">
        <?= $_SESSION['login_message'] ?>
      </div>
      <?php unset($_SESSION['login_message'], $_SESSION['login_status']); ?>
    <?php endif; ?>

    <form action="authentication.php" method="POST">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username"
               placeholder="e.g. admin  OR  ' OR '1'='1" autocomplete="off" />
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="e.g. pass1  OR  anything" />
      </div>
      <button type="submit">Login &rarr;</button>
    </form>

    <!-- SQL Injection Hints Panel -->
    <div class="hint-box">
      <strong>⚡ SQL Injection Payloads (for lab testing)</strong>
      <b>Auth Bypass:</b><br>
      &nbsp;&nbsp;Username: <code>' OR '1'='1' --</code><br>
      &nbsp;&nbsp;Password: <code>anything</code><br><br>
      <b>Union-Based:</b><br>
      &nbsp;&nbsp;Username: <code>' UNION SELECT username,password FROM users --</code><br><br>
      <b>Blind (True):</b><br>
      &nbsp;&nbsp;Username: <code>admin' AND '1'='1</code><br><br>
      <b>Blind (False):</b><br>
      &nbsp;&nbsp;Username: <code>admin' AND '1'='2</code><br><br>
      <b>Insert new user:</b> Run in phpMyAdmin query box:<br>
      &nbsp;&nbsp;<code>'; INSERT INTO users VALUES('hacker','pwned'); --</code>
    </div>

  </div>

  <p class="footer-note">
    Lab 5 &nbsp;|&nbsp; Group 5 &nbsp;|&nbsp; Vulnerable App
    &nbsp;|&nbsp; <a href="attacks.php" style="color:var(--accent);text-decoration:none;">⚡ Attack Demo Panel →</a>
  </p>
</div>

</body>
</html>
