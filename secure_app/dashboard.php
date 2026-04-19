<?php
// Secure App – Dashboard
// Lab 5 | Group 6
session_start();

if (!isset($_SESSION['login_status']) || $_SESSION['login_status'] !== 'success') {
  header("Location: index.php");
  exit();
}

$login_user = htmlspecialchars($_SESSION['login_user'] ?? 'User');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Dashboard | Secure App</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400&display=swap');

    :root {
      --accent: #2563eb;
      --text: #1e293b;
      --dim: #64748b;
      --bg: #f0f4f8;
      --card: #fff;
      --border: #d0d8e4;
      --success: #16a34a;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .banner {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: #16a34a;
      color: #fff;
      font-family: 'JetBrains Mono', monospace;
      font-size: .7rem;
      text-align: center;
      padding: 6px;
    }

    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 36px;
      max-width: 500px;
      width: 100%;
      box-shadow: 0 4px 24px rgba(0, 0, 0, .07);
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

    h1 {
      font-size: 1.5rem;
      color: var(--text);
      margin-bottom: 8px;
    }

    p {
      color: var(--dim);
      font-size: .9rem;
      line-height: 1.6;
    }

    .badge {
      font-family: 'JetBrains Mono', monospace;
      font-size: .65rem;
      color: var(--accent);
      letter-spacing: .15em;
      border: 1px solid #bfdbfe;
      background: #eff6ff;
      display: inline-block;
      padding: 3px 10px;
      border-radius: 2px;
      margin-bottom: 14px;
    }

    .checks {
      margin-top: 20px;
      font-family: 'JetBrains Mono', monospace;
      font-size: .78rem;
      color: var(--success);
      line-height: 2;
    }

    .logout {
      display: inline-block;
      margin-top: 24px;
      background: transparent;
      border: 1px solid var(--accent);
      color: var(--accent);
      padding: 8px 20px;
      border-radius: 6px;
      text-decoration: none;
      font-size: .82rem;
    }

    .logout:hover {
      background: var(--accent);
      color: #fff;
    }
  </style>
</head>

<body>
  <div class="banner">✔ SECURE APPLICATION — All Attacks Blocked</div>

  <div style="padding-top:50px;width:100%;max-width:500px;">
    <span class="badge">✔ Secure App — Dashboard</span>
    <div class="card">
      <h1>Welcome, <?= $login_user ?>!</h1>
      <p>You have successfully authenticated through the secure login system.</p>
      <div class="checks">
        ✔ SQL Injection — Blocked (Prepared Statements)<br>
        ✔ Auth Bypass — Blocked (Parameterised Query)<br>
        ✔ Union Injection — Blocked<br>
        ✔ Blind Injection — Blocked<br>
        ✔ Password Exposure — Blocked (Hashing)
      </div>
      <a class="logout" href="logout.php">← Logout</a>
    </div>
  </div>
</body>

</html>