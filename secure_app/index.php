<?php
// Secure App – Login Page
// Lab 5 | Group 6
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Secure Login | Lab 5</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap');

    :root {
      --bg: #f0f4f8;
      --card: #ffffff;
      --border: #d0d8e4;
      --accent: #2563eb;
      --accent2: #1d4ed8;
      --text: #1e293b;
      --dim: #64748b;
      --error: #dc2626;
      --success: #16a34a;
      --mono: 'JetBrains Mono', monospace;
      --sans: 'Inter', sans-serif;
    }

    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: var(--sans);
      background: var(--bg);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .secure-banner {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: #16a34a;
      color: #fff;
      font-family: var(--mono);
      font-size: 0.7rem;
      letter-spacing: .08em;
      text-align: center;
      padding: 6px 16px;
      z-index: 100;
    }

    .container {
      width: 100%;
      max-width: 400px;
    }

    .badge {
      font-family: var(--mono);
      font-size: 0.65rem;
      color: var(--accent);
      letter-spacing: .15em;
      text-transform: uppercase;
      border: 1px solid #bfdbfe;
      background: #eff6ff;
      display: inline-block;
      padding: 3px 10px;
      border-radius: 2px;
      margin-bottom: 14px;
    }

    h1 {
      font-size: 1.6rem;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 4px;
    }

    .subtitle {
      font-size: 0.78rem;
      color: var(--dim);
      font-family: var(--mono);
      margin-bottom: 28px;
    }

    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 32px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
      position: relative;
      overflow: hidden;
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--accent), #60a5fa);
    }

    .form-group {
      margin-bottom: 18px;
    }

    label {
      display: block;
      font-size: 0.72rem;
      font-weight: 500;
      letter-spacing: .07em;
      color: var(--dim);
      text-transform: uppercase;
      margin-bottom: 7px;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      background: #f8fafc;
      border: 1px solid var(--border);
      border-radius: 6px;
      color: var(--text);
      font-family: var(--mono);
      font-size: 0.9rem;
      padding: 10px 14px;
      outline: none;
      transition: border-color .2s;
    }

    input:focus {
      border-color: var(--accent);
      background: #fff;
    }

    button[type="submit"] {
      width: 100%;
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 6px;
      font-family: var(--sans);
      font-size: 0.95rem;
      font-weight: 600;
      padding: 12px;
      cursor: pointer;
      margin-top: 8px;
      transition: background .2s, transform .1s;
    }

    button[type="submit"]:hover {
      background: var(--accent2);
    }

    button[type="submit"]:active {
      transform: scale(0.98);
    }

    .alert {
      border-radius: 6px;
      padding: 12px 16px;
      font-size: 0.82rem;
      margin-bottom: 18px;
      border: 1px solid;
    }

    .alert-success {
      background: #f0fdf4;
      border-color: #86efac;
      color: var(--success);
    }

    .alert-error {
      background: #fef2f2;
      border-color: #fca5a5;
      color: var(--error);
    }

    .security-note {
      margin-top: 22px;
      padding: 14px;
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      border-radius: 6px;
      font-family: var(--mono);
      font-size: 0.72rem;
      color: #1d4ed8;
      line-height: 1.8;
    }

    .security-note strong {
      display: block;
      margin-bottom: 6px;
      font-size: .67rem;
      letter-spacing: .12em;
      text-transform: uppercase;
    }

    .footer-note {
      margin-top: 16px;
      text-align: center;
      font-family: var(--mono);
      font-size: .68rem;
      color: var(--dim);
    }
  </style>
</head>

<body>

  <div class="secure-banner">
    ✔ SECURE APPLICATION — Prepared Statements · Hashed Passwords · Input Validation
  </div>

  <div class="container" style="padding-top:40px;">
    <span class="badge">✔ Secure App</span>
    <h1>User Login</h1>
    <p class="subtitle">// secure_app &mdash; Protected Login System</p>

    <div class="card">

      <?php if (isset($_SESSION['login_message'])): ?>
        <div class="alert <?= $_SESSION['login_status'] === 'success' ? 'alert-success' : 'alert-error' ?>">
          <?= htmlspecialchars($_SESSION['login_message']) ?>
        </div>
        <?php unset($_SESSION['login_message'], $_SESSION['login_status']); ?>
      <?php endif; ?>

      <form action="authentication.php" method="POST">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" maxlength="50" autocomplete="username" required />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" maxlength="100" autocomplete="current-password"
            required />
        </div>
        <button type="submit">Login &rarr;</button>
      </form>

      <div class="security-note">
        <strong>🔒 Security Controls Active</strong>
        ✔ Prepared statements (parameterised queries)<br>
        ✔ Password hashing with password_verify()<br>
        ✔ Input length validation &amp; sanitisation<br>
        ✔ SQL errors suppressed from output<br>
        ✔ Generic error messages (no info leakage)
      </div>

    </div>

    <p class="footer-note">Lab 5 &nbsp;|&nbsp; Group 6 &nbsp;|&nbsp; Secure App</p>
  </div>

</body>

</html>